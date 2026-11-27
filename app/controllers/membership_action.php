<?php
if (!is_post()) redirect('customers');
$action = req('action'); $cid = (int) req('customer_id');
if (!DB::val('SELECT id FROM customers WHERE id=?', [$cid])) redirect('customers');
try {
    if ($action === 'renew') {
        Auth::need('memberships.renew');
        $branch = Auth::resolveBranch(req('branch_id')); $amount = (float) req('amount'); $method = (int) req('method_id');
        $startDate = req('start_date');
        if ($startDate !== '') {
            if (!strtotime($startDate)) throw new RuntimeException('Invalid start date.');
            if ($startDate < date('Y-m-d')) throw new RuntimeException('Membership start date cannot be in the past. Taariikhdii hore ee la soo dhaafey lama ogola (Must be today or future date).');
        }
        if ($amount > 0 && !req('verified')) throw new RuntimeException('Confirm that the payment was received and verified.');
        DB::tx(function () use ($cid, $branch, $amount, $method, $startDate) {
            $mid = Membership::create($cid, (int) req('plan_id'), $branch, $startDate ?: null);
            if ($amount > 0) {
                if (!$method) throw new RuntimeException('Choose the payment method.');
                Payment::record($mid, $amount, $method, $branch, req('reference'), 'Membership renewal / sale');
            }
        });
        flash('success', 'Membership renewed successfully. Xubinnimadii waa la cusboonaysiiyay.');
    } else {
        Auth::need('memberships.manage');
        $mid = (int) req('membership_id');
        if (!DB::val('SELECT id FROM memberships WHERE id=? AND customer_id=?', [$mid, $cid])) throw new RuntimeException('Membership not found.');
        switch ($action) {
            case 'extend': Membership::extend($mid, (int) req('days')); flash('success', 'Membership extended.'); break;
            case 'suspend': Membership::suspend($mid); flash('success', 'Membership suspended.'); break;
            case 'resume': Membership::resume($mid); flash('success', 'Membership reactivated.'); break;
            case 'cancel': Membership::cancel($mid); flash('success', 'Unpaid membership cancelled.'); break;
            default: throw new RuntimeException('Unknown action.');
        }
    }
} catch (RuntimeException $e) { flash('error', $e->getMessage()); }
redirect('customer_view', ['id' => $cid]);
