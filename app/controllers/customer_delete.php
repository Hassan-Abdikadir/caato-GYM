<?php
Auth::need('customers.edit');
if (!is_post()) redirect('customers');
$id = (int) req('id');
$c = DB::one('SELECT id, full_name, customer_code FROM customers WHERE id=?', [$id]);
if (!$c) { flash('error', 'Customer not found.'); redirect('customers'); }
// Prevent deleting customer with active/paid membership
$activePaid = DB::val("SELECT COUNT(*) FROM memberships WHERE customer_id=? AND status IN ('active','pending') AND payment_status='paid'", [$id]);
if ($activePaid) { flash('error', "Cannot delete {$c['full_name']}: they have an active paid membership. Cancel or expire it first."); redirect('customer_view', ['id' => $id]); }
try {
    DB::tx(function() use ($id) {
        try { DB::exec('DELETE FROM body_metrics WHERE customer_id=?', [$id]); } catch (Throwable $e) {}
        try { DB::exec('UPDATE pos_sales SET customer_id=NULL WHERE customer_id=?', [$id]); } catch (Throwable $e) {}
        DB::exec('DELETE FROM notifications WHERE customer_id=?', [$id]);
        DB::exec('DELETE FROM audit_logs WHERE entity="customer" AND entity_id=?', [$id]);
        DB::exec('DELETE FROM attendance WHERE customer_id=?', [$id]);
        DB::exec('DELETE FROM payments WHERE customer_id=?', [$id]);
        DB::exec('DELETE FROM memberships WHERE customer_id=?', [$id]);
        DB::exec('DELETE FROM customers WHERE id=?', [$id]);
    });
    audit('customer_delete', 'customer', $id, $c['full_name']);
    flash('success', "Customer {$c['full_name']} ({$c['customer_code']}) has been permanently deleted.");
} catch (Throwable $e) {
    flash('error', 'Delete failed: ' . $e->getMessage());
}
redirect('customers');
