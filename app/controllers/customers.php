<?php
Auth::need('customers.view');
$q = req('q'); $br = (int) req('branch'); $st = req('status');
if (!in_array($st, ['none', 'pending', 'active', 'expired', 'suspended', 'cancelled'], true)) $st = '';
[$rows, $total] = Customer::search($q, $br, $st, 15, 0);
$pg = paginate($total, 15);
if ($pg['offset'] > 0) [$rows] = Customer::search($q, $br, $st, 15, $pg['offset']);

$counts = [
    'total' => (int) DB::val('SELECT COUNT(*) FROM customers'),
    'active' => (int) DB::val("SELECT COUNT(DISTINCT customer_id) FROM memberships WHERE status='active' AND end_date>=CURDATE()"),
    'biometric' => (int) DB::val("SELECT COUNT(*) FROM customers WHERE fingerprint_id IS NOT NULL AND fingerprint_id<>''"),
];

render('customers', ['pageTitle' => 'Customer Directory', 'rows' => $rows, 'pg' => $pg, 'q' => $q, 'br' => $br, 'st' => $st,
    'counts' => $counts, 'allBranches' => DB::all('SELECT id, name FROM branches ORDER BY id')]);
