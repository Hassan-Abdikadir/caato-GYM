<?php
Auth::need('payments.create');
if (!is_post()) redirect('payments');
$mid = (int) req('membership_id');
try {
    if (!req('verified')) throw new RuntimeException('Confirm that the payment was received and verified.');
    $branch = Auth::resolveBranch(req('branch_id'));
    $pid = Payment::record($mid, (float) req('amount'), (int) req('method_id'), $branch, req('reference'), req('notes'));
    flash('success', 'Payment recorded.');
    redirect('receipt', ['id' => $pid]);
} catch (RuntimeException $e) {
    flash('error', $e->getMessage());
    $cid = (int) DB::val('SELECT customer_id FROM memberships WHERE id=?', [$mid]);
    $cid ? redirect('customer_view', ['id' => $cid]) : redirect('payments');
}
