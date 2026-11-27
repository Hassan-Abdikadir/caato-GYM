<?php
final class Customer
{
    // Current membership = running one first, then a future-dated paid one, then the most recent record
    const CUR = "(SELECT m2.id FROM memberships m2 WHERE m2.customer_id=c.id ORDER BY
        (m2.status='active' AND m2.start_date<=CURDATE() AND m2.end_date>=CURDATE()) DESC,
        (m2.status='active' AND m2.start_date>CURDATE()) DESC, m2.id DESC LIMIT 1)";
    const EFF = "CASE WHEN m.id IS NULL THEN 'none' WHEN m.status='active' AND m.end_date<CURDATE() THEN 'expired' ELSE m.status END";

    public static function base(): string
    {
        return "SELECT c.*, b.name AS reg_branch, t.full_name AS trainer_name, m.id AS ms_id, m.status AS ms_status, m.payment_status, m.start_date, m.end_date,
                m.price AS ms_price, m.amount_paid, p.name AS plan_name, " . self::EFF . " AS eff_status,
                (SELECT MAX(a.check_in_at) FROM attendance a WHERE a.customer_id=c.id) AS last_attendance,
                (SELECT COUNT(*) FROM attendance a2 WHERE a2.customer_id=c.id) AS total_visits
                FROM customers c JOIN branches b ON b.id=c.registered_branch_id
                LEFT JOIN trainers t ON t.id=c.assigned_trainer_id
                LEFT JOIN memberships m ON m.id=" . self::CUR . "
                LEFT JOIN membership_plans p ON p.id=m.plan_id ";
    }

    public static function find(int $id): ?array { return DB::one(self::base() . ' WHERE c.id=?', [$id]); }

    public static function findByFingerprint(string $token): ?array
    {
        $token = trim($token);
        if ($token === '') return null;
        $norm = normalize_phone($token);
        return DB::one(self::base() . ' WHERE c.fingerprint_id=? OR c.customer_code=? OR (c.phone_norm=? AND ?<>"") LIMIT 1',
            [$token, $token, $norm, $norm]);
    }

    /** Cross-branch search: name, phone, code or fingerprint ID. Returns [rows,total]. */
    public static function search(string $q, int $branch, string $status, int $limit, int $offset = 0): array
    {
        $w = ' WHERE 1=1'; $p = [];
        if ($q !== '') {
            $like = '%' . $q . '%'; $digits = normalize_phone($q);
            $w .= ' AND (c.full_name LIKE ? OR c.customer_code LIKE ? OR c.fingerprint_id LIKE ? OR c.phone LIKE ?' . ($digits !== '' ? ' OR c.phone_norm LIKE ?' : '') . ')';
            array_push($p, $like, $like, $like, $like);
            if ($digits !== '') $p[] = '%' . $digits . '%';
        }
        if ($branch) { $w .= ' AND c.registered_branch_id=?'; $p[] = $branch; }
        if ($status !== '') { $w .= ' AND (' . self::EFF . ')=?'; $p[] = $status; }
        $total = (int) DB::val('SELECT COUNT(*) FROM customers c LEFT JOIN memberships m ON m.id=' . self::CUR . $w, $p);
        $rows = DB::all(self::base() . $w . ' ORDER BY c.full_name LIMIT ' . (int) $limit . ' OFFSET ' . (int) $offset, $p);
        return [$rows, $total];
    }

    public static function create(array $d): int
    {
        $norm = normalize_phone($d['phone']);
        $dup = DB::one('SELECT id, customer_code, full_name FROM customers WHERE phone_norm=?', [$norm]);
        if ($dup) throw new RuntimeException("This phone number is already registered to {$dup['full_name']} ({$dup['customer_code']}). Search for the customer instead of registering again.");
        $row = [
            'customer_code' => 'TMP' . bin2hex(random_bytes(8)), 'full_name' => $d['full_name'], 'phone' => $d['phone'], 'phone_norm' => $norm,
            'email' => $d['email'] ?: null, 'gender' => $d['gender'], 'date_of_birth' => $d['dob'] ?: null, 'address' => $d['address'] ?: null,
            'profile_picture' => $d['photo'] ?? null, 'emergency_name' => $d['emergency_name'] ?: null, 'emergency_phone' => $d['emergency_phone'] ?: null,
            'registered_branch_id' => $d['branch_id'], 'registration_date' => date('Y-m-d'), 'created_by' => Auth::id(),
        ];
        if (array_key_exists('fitness_goal', $d)) $row['fitness_goal'] = $d['fitness_goal'] ?: 'General Fitness';
        if (array_key_exists('medical_notes', $d)) $row['medical_notes'] = $d['medical_notes'] ?: null;
        if (array_key_exists('assigned_trainer_id', $d)) $row['assigned_trainer_id'] = $d['assigned_trainer_id'] ? (int) $d['assigned_trainer_id'] : null;
        $id = DB::insert('customers', $row);
        DB::update('customers', ['customer_code' => 'CF-' . str_pad((string) $id, 6, '0', STR_PAD_LEFT)], 'id=?', [$id]);
        return $id;
    }
}
