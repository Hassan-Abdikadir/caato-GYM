<?php
Auth::need('customers.view');
$q = req('q'); $br = (int) req('branch'); $st = req('status');
if (!Auth::isSuper()) $br = (int) (Auth::branchId() ?: -1);
if (!in_array($st, ['none', 'pending', 'active', 'expired', 'suspended', 'cancelled'], true)) $st = '';
[$rows, $total] = Customer::search($q, $br, $st, 15, 0);
$pg = paginate($total, 15);
if ($pg['offset'] > 0) [$rows] = Customer::search($q, $br, $st, 15, $pg['offset']);

[$sc, $sp] = scope_branch('c.registered_branch_id', $br);
$counts = [
    'total' => (int) DB::val("SELECT COUNT(*) FROM customers c WHERE 1=1 $sc", $sp),
    'active' => (int) DB::val("SELECT COUNT(DISTINCT m.customer_id) FROM memberships m JOIN customers c ON c.id=m.customer_id WHERE m.status='active' AND m.end_date>=CURDATE() $sc", $sp),
    'biometric' => (int) DB::val("SELECT COUNT(*) FROM customers c WHERE c.fingerprint_id IS NOT NULL AND c.fingerprint_id<>'' $sc", $sp),
];

render('customers', ['pageTitle' => 'Customer Directory', 'rows' => $rows, 'pg' => $pg, 'q' => $q, 'br' => $br, 'st' => $st,
    'counts' => $counts, 'allBranches' => branch_list(false)]);
