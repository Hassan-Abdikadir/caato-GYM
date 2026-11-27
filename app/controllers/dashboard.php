<?php
Auth::need('dashboard.view');
$B = Auth::isSuper() ? (int) req('branch') : (int) (Auth::branchId() ?: -1);
$cw = $B ? " AND c.registered_branch_id=$B" : '';
$pw = $B ? " AND p.branch_id=$B" : '';
$aw = $B ? " AND a.branch_id=$B" : '';
$xw = $B ? " AND x.branch_id=$B" : '';
$sw = $B ? " AND s.branch_id=$B" : '';
$days = (int) setting('expiry_warning_days', '7');
$showMoney = Auth::can('payments.view');

$msRevTotal = (float) DB::val("SELECT COALESCE(SUM(p.amount),0) FROM payments p WHERE 1=1$pw");
$msRevMonth = (float) DB::val("SELECT COALESCE(SUM(p.amount),0) FROM payments p WHERE p.payment_date>=DATE_FORMAT(CURDATE(),'%Y-%m-01')$pw");
$msRevToday = (float) DB::val("SELECT COALESCE(SUM(p.amount),0) FROM payments p WHERE DATE(p.payment_date)=CURDATE()$pw");

$posRevTotal = 0.0; $posRevMonth = 0.0; $posRevToday = 0.0;
try {
    $posRevTotal = (float) DB::val("SELECT COALESCE(SUM(s.total_amount),0) FROM pos_sales s WHERE 1=1$sw");
    $posRevMonth = (float) DB::val("SELECT COALESCE(SUM(s.total_amount),0) FROM pos_sales s WHERE s.sold_at>=DATE_FORMAT(CURDATE(),'%Y-%m-01')$sw");
    $posRevToday = (float) DB::val("SELECT COALESCE(SUM(s.total_amount),0) FROM pos_sales s WHERE DATE(s.sold_at)=CURDATE()$sw");
} catch (Throwable $e) {}

$expMonth = (float) DB::val("SELECT COALESCE(SUM(x.amount),0) FROM expenses x WHERE x.expense_date>=DATE_FORMAT(CURDATE(),'%Y-%m-01')$xw");
$expTotal = (float) DB::val("SELECT COALESCE(SUM(x.amount),0) FROM expenses x WHERE 1=1$xw");

$eqAlert = 0;
$eqAlertList = [];
try {
    $eqAlert = (int) DB::val("SELECT COUNT(*) FROM equipment WHERE condition_status<>'operational'" . ($B ? " AND branch_id=$B" : ''));
    $eqAlertList = DB::all("SELECT e.*, b.code AS branch_code FROM equipment e JOIN branches b ON b.id=e.branch_id WHERE e.condition_status<>'operational'" . ($B ? " AND e.branch_id=$B" : '') . " ORDER BY e.condition_status DESC LIMIT 5");
} catch (Throwable $e) {}

$lowStockList = [];
try {
    $lowStockList = DB::all("SELECT * FROM pos_products WHERE status='active' AND stock_qty <= 15 ORDER BY stock_qty ASC LIMIT 5");
} catch (Throwable $e) {}

$classesCount = 0;
$activeClasses = [];
try {
    $classesCount = (int) DB::val("SELECT COUNT(*) FROM gym_classes WHERE status='active'" . ($B ? " AND branch_id=$B" : ''));
    $activeClasses = DB::all("SELECT c.*, b.code AS branch_code, t.full_name AS trainer_name FROM gym_classes c JOIN branches b ON b.id=c.branch_id LEFT JOIN trainers t ON t.id=c.trainer_id WHERE c.status='active'" . ($B ? " AND c.branch_id=$B" : '') . " ORDER BY c.start_time LIMIT 6");
} catch (Throwable $e) {}

// Payment Channel Split (EVC Plus / Salaam Bank / E-Dahab) for This Month
$channelSplit = [];
if ($showMoney) {
    $channelSplit = DB::all("SELECT pm.id, pm.name,
        (SELECT COALESCE(SUM(p.amount),0) FROM payments p WHERE p.method_id=pm.id AND p.payment_date>=DATE_FORMAT(CURDATE(),'%Y-%m-01')$pw) +
        (SELECT COALESCE(SUM(s.total_amount),0) FROM pos_sales s WHERE s.method_id=pm.id AND s.sold_at>=DATE_FORMAT(CURDATE(),'%Y-%m-01')$sw) AS month_amount,
        (SELECT COUNT(*) FROM payments p WHERE p.method_id=pm.id AND p.payment_date>=DATE_FORMAT(CURDATE(),'%Y-%m-01')$pw) +
        (SELECT COUNT(*) FROM pos_sales s WHERE s.method_id=pm.id AND s.sold_at>=DATE_FORMAT(CURDATE(),'%Y-%m-01')$sw) AS month_txns
        FROM payment_methods pm WHERE pm.status='active' ORDER BY month_amount DESC, pm.id");
}

// Gender Split
$maleCount = (int) DB::val("SELECT COUNT(*) FROM customers c WHERE c.gender='male'$cw");
$femaleCount = (int) DB::val("SELECT COUNT(*) FROM customers c WHERE c.gender='female'$cw");
$biometricCount = (int) DB::val("SELECT COUNT(*) FROM customers c WHERE c.fingerprint_id IS NOT NULL AND c.fingerprint_id<>''$cw");

$S = [
 'customers' => (int) DB::val("SELECT COUNT(*) FROM customers c WHERE 1=1$cw"),
 'male' => $maleCount,
 'female' => $femaleCount,
 'biometric' => $biometricCount,
 'active' => (int) DB::val("SELECT COUNT(DISTINCT m.customer_id) FROM memberships m JOIN customers c ON c.id=m.customer_id WHERE m.status='active' AND m.end_date>=CURDATE()$cw"),
 'expired' => (int) DB::val("SELECT COUNT(*) FROM customers c WHERE c.status='active'$cw AND EXISTS(SELECT 1 FROM memberships x WHERE x.customer_id=c.id AND x.status='expired') AND NOT EXISTS(SELECT 1 FROM memberships y WHERE y.customer_id=c.id AND y.status='active' AND y.end_date>=CURDATE())"),
 'today' => (int) DB::val("SELECT COUNT(*) FROM attendance a WHERE a.attendance_date=CURDATE()$aw"),
 'month_visits' => (int) DB::val("SELECT COUNT(*) FROM attendance a WHERE a.attendance_date>=DATE_FORMAT(CURDATE(),'%Y-%m-01')$aw"),
 'revenue' => $msRevTotal + $posRevTotal,
 'month' => $msRevMonth + $posRevMonth,
 'today_rev' => $msRevToday + $posRevToday,
 'pos_month' => $posRevMonth,
 'expenses_month' => $expMonth,
 'net_month' => ($msRevMonth + $posRevMonth) - $expMonth,
 'branches' => (int) DB::val("SELECT COUNT(*) FROM branches WHERE status='active'" . ($B ? " AND id=$B" : '')),
 'employees' => (int) DB::val("SELECT COUNT(*) FROM employees WHERE status='active'" . ($B ? " AND branch_id=$B" : '')),
 'trainers' => (int) DB::val("SELECT COUNT(*) FROM trainers WHERE status='active'" . ($B ? " AND branch_id=$B" : '')),
 'classes' => $classesCount,
 'eq_alert' => $eqAlert,
 'pending_n' => (int) DB::val("SELECT COUNT(*) FROM memberships m JOIN customers c ON c.id=m.customer_id WHERE m.status='pending'$cw"),
 'pending_amt' => (float) DB::val("SELECT COALESCE(SUM(m.price-m.amount_paid),0) FROM memberships m JOIN customers c ON c.id=m.customer_id WHERE m.status='pending'$cw"),
 'expiring' => (int) DB::val("SELECT COUNT(*) FROM memberships m JOIN customers c ON c.id=m.customer_id WHERE m.status='active' AND m.end_date BETWEEN CURDATE() AND CURDATE() + INTERVAL $days DAY$cw"),
];

$fill = function (array $keys, array $rows, string $k, string $v) {
    $m = array_fill_keys($keys, 0);
    foreach ($rows as $r) if (isset($m[$r[$k]])) $m[$r[$k]] = (float) $r[$v];
    return array_values($m);
};
$months = []; for ($i = 11; $i >= 0; $i--) $months[] = date('Y-m', strtotime("first day of -$i month"));
$from = $months[0] . '-01';
$revRows = DB::all("SELECT DATE_FORMAT(p.payment_date,'%Y-%m') ym, SUM(p.amount) t FROM payments p WHERE p.payment_date>=?$pw GROUP BY ym", [$from]);
$posMonthRows = [];
try {
    $posMonthRows = DB::all("SELECT DATE_FORMAT(s.sold_at,'%Y-%m') ym, SUM(s.total_amount) t FROM pos_sales s WHERE s.sold_at>=?$sw GROUP BY ym", [$from]);
} catch (Throwable $e) {}
$expRows = DB::all("SELECT DATE_FORMAT(x.expense_date,'%Y-%m') ym, SUM(x.amount) t FROM expenses x WHERE x.expense_date>=?$xw GROUP BY ym", [$from]);
$regRows = DB::all("SELECT DATE_FORMAT(c.registration_date,'%Y-%m') ym, COUNT(*) t FROM customers c WHERE c.registration_date>=?$cw GROUP BY ym", [$from]);
$dayKeys = []; for ($i = 13; $i >= 0; $i--) $dayKeys[] = date('Y-m-d', strtotime("-$i day"));
$attRows = DB::all("SELECT a.attendance_date d, COUNT(*) t FROM attendance a WHERE a.attendance_date>=?$aw GROUP BY d", [$dayKeys[0]]);
$planRows = DB::all("SELECT pl.name, COUNT(m.id) n FROM membership_plans pl LEFT JOIN memberships m ON m.plan_id=pl.id AND m.status<>'cancelled'" . ($B ? " AND m.branch_id=$B" : '') . ' GROUP BY pl.id ORDER BY pl.id');
$branchStats = Report::branchStats();

$msRevArr = $fill($months, $revRows, 'ym', 't');
$posRevArr = $fill($months, $posMonthRows, 'ym', 't');
$combinedRevArr = [];
foreach ($msRevArr as $idx => $val) {
    $combinedRevArr[] = round($val + ($posRevArr[$idx] ?? 0), 2);
}

$charts = [
 'months' => array_map(fn($m) => date('M y', strtotime($m . '-01')), $months),
 'revenue' => $combinedRevArr,
 'pos_revenue' => $posRevArr,
 'expenses' => $fill($months, $expRows, 'ym', 't'),
 'registrations' => $fill($months, $regRows, 'ym', 't'),
 'days' => array_map(fn($d) => date('d M', strtotime($d)), $dayKeys),
 'attendance' => $fill($dayKeys, $attRows, 'd', 't'),
 'plans' => ['labels' => array_column($planRows, 'name'), 'data' => array_map('intval', array_column($planRows, 'n'))],
 'branches' => ['labels' => array_map(fn($b) => $b['code'], $branchStats),
                'revenue' => array_map(fn($b) => (float) $b['revenue_month'], $branchStats),
                'total' => array_map(fn($b) => (float) $b['revenue_total'], $branchStats),
                'visits' => array_map(fn($b) => (int) $b['attendance_month'], $branchStats),
                'customers' => array_map(fn($b) => (int) $b['customers'], $branchStats)],
];
$expiring = DB::all("SELECT c.id, c.full_name, c.customer_code, c.phone, c.profile_picture, m.end_date, pl.name AS plan FROM memberships m JOIN customers c ON c.id=m.customer_id JOIN membership_plans pl ON pl.id=m.plan_id WHERE m.status='active' AND m.end_date BETWEEN CURDATE() AND CURDATE() + INTERVAL $days DAY$cw ORDER BY m.end_date LIMIT 8");
$recent = DB::all("SELECT a.check_in_at, a.checkin_method, c.id, c.full_name, c.customer_code, c.profile_picture, b.name AS branch, rb.name AS reg_branch FROM attendance a JOIN customers c ON c.id=a.customer_id JOIN branches b ON b.id=a.branch_id JOIN branches rb ON rb.id=c.registered_branch_id WHERE 1=1$aw ORDER BY a.id DESC LIMIT 8");

render('dashboard', [
    'pageTitle' => 'Operations Console',
    'S' => $S,
    'charts' => $charts,
    'expiring' => $expiring,
    'recent' => $recent,
    'branchStats' => $branchStats,
    'channelSplit' => $channelSplit,
    'activeClasses' => $activeClasses,
    'eqAlertList' => $eqAlertList,
    'lowStockList' => $lowStockList,
    'B' => $B,
    'showMoney' => $showMoney,
    'days' => $days
]);
