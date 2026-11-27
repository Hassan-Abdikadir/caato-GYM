<?php
Auth::need('payments.view');
$tab = req('tab') === 'pending' ? 'pending' : 'all';
$br = (int) req('branch'); $q = req('q'); $df = req('date_from'); $dt = req('date_to'); $mt = (int) req('method');
$methods = DB::all('SELECT id, name FROM payment_methods ORDER BY id');
if ($tab === 'pending') {
    [$sc, $sp] = scope_branch('m.branch_id', $br);
    $w = " WHERE m.status='pending' $sc"; $p = $sp;
    if ($q !== '') {
        $w .= ' AND (c.full_name LIKE ? OR c.customer_code LIKE ? OR c.phone LIKE ?)';
        array_push($p, "%$q%", "%$q%", "%$q%");
    }
    $rows = DB::all("SELECT m.id, m.price, m.amount_paid, m.created_at, c.id AS cid, c.full_name, c.customer_code, c.phone, pl.name AS plan_name, b.name AS branch_name
        FROM memberships m JOIN customers c ON c.id=m.customer_id JOIN membership_plans pl ON pl.id=m.plan_id JOIN branches b ON b.id=m.branch_id
        $w ORDER BY m.id DESC LIMIT 200", $p);
    $outstanding = array_sum(array_map(fn($r) => $r['price'] - $r['amount_paid'], $rows));
    render('payments', ['pageTitle' => 'Payments', 'tab' => $tab, 'rows' => $rows, 'outstanding' => $outstanding, 'methods' => $methods, 'br' => $br, 'q' => $q, 'df' => $df, 'dt' => $dt, 'mt' => $mt, 'pg' => null, 'sum' => 0]);
    exit;
}
[$sc, $sp] = scope_branch('p.branch_id', $br);
$w = " WHERE 1=1 $sc"; $p = $sp;
if ($q !== '') { $w .= ' AND (p.receipt_no LIKE ? OR p.reference LIKE ? OR c.full_name LIKE ? OR c.customer_code LIKE ? OR c.phone LIKE ?)'; array_push($p, "%$q%", "%$q%", "%$q%", "%$q%", "%$q%"); }
if ($df !== '') { $w .= ' AND DATE(p.payment_date)>=?'; $p[] = $df; }
if ($dt !== '') { $w .= ' AND DATE(p.payment_date)<=?'; $p[] = $dt; }
if ($mt) { $w .= ' AND p.method_id=?'; $p[] = $mt; }
$from = ' FROM payments p JOIN customers c ON c.id=p.customer_id JOIN branches b ON b.id=p.branch_id JOIN payment_methods pm ON pm.id=p.method_id JOIN memberships m ON m.id=p.membership_id JOIN membership_plans pl ON pl.id=m.plan_id';
$sum = (float) DB::val('SELECT COALESCE(SUM(p.amount),0)' . $from . $w, $p);
$pg = paginate((int) DB::val('SELECT COUNT(*)' . $from . $w, $p), 15);
$rows = DB::all('SELECT p.*, c.full_name, c.customer_code, b.name AS branch_name, pm.name AS method, pl.name AS plan_name' . $from . $w . ' ORDER BY p.id DESC LIMIT 15 OFFSET ' . $pg['offset'], $p);
render('payments', ['pageTitle' => 'Payments', 'tab' => $tab, 'rows' => $rows, 'pg' => $pg, 'sum' => $sum, 'methods' => $methods, 'br' => $br, 'q' => $q, 'df' => $df, 'dt' => $dt, 'mt' => $mt, 'outstanding' => 0]);
