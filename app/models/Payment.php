<?php
final class Payment
{
    /** Records a payment atomically; activates the membership only when it is fully paid. */
    public static function record(int $membershipId, float $amount, int $methodId, int $branchId, string $ref = '', string $notes = ''): int
    {
        return DB::tx(function () use ($membershipId, $amount, $methodId, $branchId, $ref, $notes) {
            $ms = DB::one('SELECT m.*, pl.duration_days, pl.name AS plan_name FROM memberships m JOIN membership_plans pl ON pl.id=m.plan_id WHERE m.id=? FOR UPDATE', [$membershipId]);
            if (!$ms) throw new RuntimeException('Membership not found.');
            if ($ms['status'] === 'cancelled') throw new RuntimeException('This membership was cancelled.');
            $amount = round($amount, 2);
            $balance = round((float) $ms['price'] - (float) $ms['amount_paid'], 2);
            if ($amount <= 0) throw new RuntimeException('Enter a payment amount greater than zero.');
            if ($amount > $balance + 0.001) throw new RuntimeException('Amount exceeds the outstanding balance of ' . money($balance) . '.');
            $method = DB::one("SELECT * FROM payment_methods WHERE id=? AND status='active'", [$methodId]);
            if (!$method) throw new RuntimeException('Select a valid payment method.');
            if ($method['requires_reference'] && $ref === '') throw new RuntimeException($method['name'] . ' payments need a transaction reference number.');

            $pid = DB::insert('payments', [
                'receipt_no' => 'TMP' . bin2hex(random_bytes(8)), 'customer_id' => $ms['customer_id'], 'membership_id' => $membershipId,
                'branch_id' => $branchId, 'method_id' => $methodId, 'amount' => $amount, 'payment_date' => date('Y-m-d H:i:s'),
                'reference' => $ref ?: null, 'notes' => $notes ?: null, 'received_by' => Auth::id(),
            ]);
            DB::update('payments', ['receipt_no' => 'RC-' . date('Ymd') . '-' . str_pad((string) $pid, 6, '0', STR_PAD_LEFT)], 'id=?', [$pid]);

            $paid = round((float) $ms['amount_paid'] + $amount, 2);
            $full = $paid >= (float) $ms['price'] - 0.001;
            $upd = ['amount_paid' => $paid, 'payment_status' => $full ? 'paid' : 'partial'];
            if ($full && $ms['status'] === 'pending') {
                $start = max($ms['start_date'], date('Y-m-d'));   // clock starts when the payment is verified
                $upd += ['status' => 'active', 'start_date' => $start,
                         'end_date' => date('Y-m-d', strtotime($start . ' +' . ((int) $ms['duration_days'] - 1) . ' days'))];
            }
            DB::update('memberships', $upd, 'id=?', [$membershipId]);

            $c = DB::one('SELECT full_name, customer_code FROM customers WHERE id=?', [$ms['customer_id']]);
            notify('payment_success', 'Payment received', "{$c['full_name']} paid " . money($amount) . " ({$method['name']}) for {$ms['plan_name']}", $branchId, (int) $ms['customer_id']);
            if ($full && DB::val('SELECT COUNT(*) FROM memberships WHERE customer_id=? AND id<?', [$ms['customer_id'], $membershipId]) > 0)
                notify('membership_renewal', 'Membership renewed', "{$c['full_name']} ({$c['customer_code']}) renewed {$ms['plan_name']}", $branchId, (int) $ms['customer_id']);
            audit('payment_record', 'payment', $pid, money($amount) . " via {$method['name']} for membership #$membershipId");
            return $pid;
        });
    }

    public static function receipt(int $id): ?array
    {
        return DB::one("SELECT p.*, c.full_name, c.customer_code, c.phone, b.name AS branch_name, pm.name AS method_name,
                        m.price, m.amount_paid, m.start_date, m.end_date, m.status AS ms_status, m.payment_status, pl.name AS plan_name, u.full_name AS received_name
                        FROM payments p JOIN customers c ON c.id=p.customer_id JOIN branches b ON b.id=p.branch_id
                        JOIN payment_methods pm ON pm.id=p.method_id JOIN memberships m ON m.id=p.membership_id
                        JOIN membership_plans pl ON pl.id=m.plan_id LEFT JOIN users u ON u.id=p.received_by WHERE p.id=?", [$id]);
    }
}
