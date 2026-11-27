<?php
final class Membership
{
    /** Business rules deciding whether a customer may enter ANY branch. Returns [allowed, code, message, level]. */
    public static function access(array $r): array
    {
        $today = date('Y-m-d');
        if (($r['status'] ?? 'active') !== 'active') return [false, 'blocked', 'Customer account is blocked. Please see the branch manager.', 'danger'];
        if (empty($r['ms_id'])) return [false, 'none', 'No membership found. Please sell a membership plan first.', 'warning'];
        $s = $r['ms_status'];
        if ($s === 'suspended') return [false, 'suspended', 'Membership is suspended. A manager must reactivate it.', 'danger'];
        if ($s === 'cancelled') return [false, 'cancelled', 'Membership was cancelled. Please sell a new plan.', 'danger'];
        if ($s === 'pending' || $r['payment_status'] !== 'paid')
            return [false, 'unpaid', 'PAYMENT WARNING: membership not fully paid. Outstanding balance ' . money($r['ms_price'] - $r['amount_paid']) . '.', 'danger'];
        if ($s === 'expired' || $r['end_date'] < $today) return [false, 'expired', 'EXPIRED on ' . fmt_date($r['end_date']) . '. Renewal required before entry.', 'danger'];
        if ($r['start_date'] > $today) return [false, 'future', 'Membership starts on ' . fmt_date($r['start_date']) . '.', 'warning'];
        $left = (int) floor((strtotime($r['end_date']) - strtotime($today)) / 86400);
        return [true, 'ok', 'Valid until ' . fmt_date($r['end_date']) . ' (' . $left . ' day' . ($left === 1 ? '' : 's') . ' left).', $left <= (int) setting('expiry_warning_days', '7') ? 'warning' : 'success'];
    }

    public static function nextStart(int $customerId): string
    {
        $e = DB::val("SELECT MAX(end_date) FROM memberships WHERE customer_id=? AND status='active' AND end_date>=CURDATE()", [$customerId]);
        return $e ? date('Y-m-d', strtotime($e . ' +1 day')) : date('Y-m-d');
    }

    /** Creates a PENDING membership. It only becomes active once the full payment is recorded. */
    public static function create(int $customerId, int $planId, int $branchId, ?string $start = null): int
    {
        $plan = DB::one("SELECT * FROM membership_plans WHERE id=? AND status='active'", [$planId]);
        if (!$plan) throw new RuntimeException('Please choose an active membership plan.');
        if (DB::val("SELECT id FROM memberships WHERE customer_id=? AND status='pending' LIMIT 1", [$customerId]))
            throw new RuntimeException('This customer already has an unpaid membership. Record its payment (or cancel it) first.');
        $start = $start ?: self::nextStart($customerId);
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $start) || !strtotime($start)) throw new RuntimeException('Invalid start date.');
        if ($start < date('Y-m-d')) throw new RuntimeException('Membership start date cannot be in the past. Taariikhdii hore ee la soo dhaafey lama ogola (Select today or a future date).');
        $end = date('Y-m-d', strtotime($start . ' +' . ((int) $plan['duration_days'] - 1) . ' days'));
        $id = DB::insert('memberships', [
            'customer_id' => $customerId, 'plan_id' => $planId, 'branch_id' => $branchId, 'start_date' => $start, 'end_date' => $end,
            'price' => $plan['price'], 'created_by' => Auth::id(),
        ]);
        $c = DB::one('SELECT full_name, customer_code, registered_branch_id FROM customers WHERE id=?', [$customerId]);
        notify('payment_pending', 'Payment pending', "{$c['full_name']} ({$c['customer_code']}) – {$plan['name']} plan awaiting payment of " . money($plan['price']), $branchId, $customerId, 'pend-' . $id);
        audit('membership_create', 'membership', $id, "Plan {$plan['name']} for customer #$customerId");
        return $id;
    }

    private static function lock(int $id): array
    {
        $m = DB::one('SELECT * FROM memberships WHERE id=? FOR UPDATE', [$id]);
        if (!$m) throw new RuntimeException('Membership not found.');
        return $m;
    }

    public static function extend(int $id, int $days): void
    {
        if ($days < 1 || $days > 365) throw new RuntimeException('Extension must be between 1 and 365 days.');
        DB::tx(function () use ($id, $days) {
            $m = self::lock($id);
            if (!in_array($m['status'], ['active', 'expired'], true) || $m['payment_status'] !== 'paid') throw new RuntimeException('Only paid active or expired memberships can be extended.');
            $end = date('Y-m-d', strtotime($m['end_date'] . " +$days days"));
            $st = ($m['status'] === 'expired' && $end >= date('Y-m-d')) ? 'active' : $m['status'];
            DB::update('memberships', ['end_date' => $end, 'status' => $st], 'id=?', [$id]);
            audit('membership_extend', 'membership', $id, "+$days days, new expiry $end");
        });
    }

    public static function suspend(int $id): void
    {
        DB::tx(function () use ($id) {
            $m = self::lock($id);
            if ($m['status'] !== 'active') throw new RuntimeException('Only active memberships can be suspended.');
            DB::update('memberships', ['status' => 'suspended', 'suspended_on' => date('Y-m-d')], 'id=?', [$id]);
            audit('membership_suspend', 'membership', $id);
        });
    }

    public static function resume(int $id): void
    {
        DB::tx(function () use ($id) {
            $m = self::lock($id);
            if ($m['status'] !== 'suspended') throw new RuntimeException('Membership is not suspended.');
            $lost = max(0, (int) floor((strtotime(date('Y-m-d')) - strtotime($m['suspended_on'] ?: date('Y-m-d'))) / 86400));
            $end = date('Y-m-d', strtotime($m['end_date'] . " +$lost days"));
            DB::update('memberships', ['status' => $m['payment_status'] === 'paid' ? 'active' : 'pending', 'end_date' => $end, 'suspended_on' => null], 'id=?', [$id]);
            audit('membership_activate', 'membership', $id, "Resumed, +$lost days added");
        });
    }

    public static function cancel(int $id): void
    {
        DB::tx(function () use ($id) {
            $m = self::lock($id);
            if ($m['status'] !== 'pending' || (float) $m['amount_paid'] > 0) throw new RuntimeException('Only unpaid pending memberships (with no payments) can be cancelled.');
            DB::update('memberships', ['status' => 'cancelled'], 'id=?', [$id]);
            audit('membership_cancel', 'membership', $id);
        });
    }

    /** Marks lapsed memberships as expired and raises expiry notifications (once per day). */
    public static function sync(): void
    {
        DB::exec("UPDATE memberships SET status='expired' WHERE status='active' AND end_date < CURDATE()");
        if (setting('last_notify_run') === date('Y-m-d')) return;
        set_setting('last_notify_run', date('Y-m-d'));
        $days = (int) setting('expiry_warning_days', '7');
        $q = "SELECT m.id, m.end_date, m.customer_id, c.full_name, c.customer_code, c.registered_branch_id FROM memberships m JOIN customers c ON c.id=m.customer_id ";
        foreach (DB::all($q . "WHERE m.status='expired' AND m.end_date >= CURDATE() - INTERVAL 14 DAY") as $r)
            notify('membership_expired', 'Membership expired', "{$r['full_name']} ({$r['customer_code']}) expired on " . fmt_date($r['end_date']), (int) $r['registered_branch_id'], (int) $r['customer_id'], 'exp-' . $r['id']);
        foreach (DB::all($q . "WHERE m.status='active' AND m.end_date BETWEEN CURDATE() AND CURDATE() + INTERVAL $days DAY") as $r)
            notify('expiring_soon', 'Membership expiring soon', "{$r['full_name']} ({$r['customer_code']}) expires on " . fmt_date($r['end_date']), (int) $r['registered_branch_id'], (int) $r['customer_id'], 'soon-' . $r['id']);
    }
}
