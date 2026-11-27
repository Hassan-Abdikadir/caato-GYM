<?php
/* CAATO FITNESS GYM – installation check & cross-branch self-test.
   Open http://localhost/caato-gym/check.php   (localhost only – DELETE this file before going live). */
require __DIR__ . '/app/core/bootstrap.php';
if (!in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true)) { http_response_code(403); exit('check.php only runs on localhost.'); }
$rows = [];
$t = function (string $name, bool $ok, string $note = '') use (&$rows) { $rows[] = [$name, $ok, $note]; return $ok; };

$t('PHP 8.0 or newer', version_compare(PHP_VERSION, '8.0.0', '>='), PHP_VERSION);
foreach (['pdo_mysql', 'mbstring', 'fileinfo', 'iconv'] as $x) $t("Extension: $x", extension_loaded($x));
$t('Extension: zip (Excel export; falls back to .xls without it)', extension_loaded('zip'));
$t('uploads/profiles is writable', is_writable(ROOT . '/uploads/profiles'));
$t('storage is writable', is_writable(ROOT . '/storage'));
$dbOk = false;
try { DB::pdo(); $dbOk = true; $t('Database connection', true, cfg('db')['name']); } catch (Throwable $e) { $t('Database connection', false, $e->getMessage() . ' – check config/config.php and import database/caato_gym.sql'); }

if ($dbOk) {
    $t('Six branches present', (int) DB::val('SELECT COUNT(*) FROM branches') >= 6, DB::val('SELECT COUNT(*) FROM branches') . ' found');
    $t('Super Admin account exists', (bool) DB::val("SELECT u.id FROM users u JOIN roles r ON r.id=u.role_id WHERE r.name='super_admin'"));
    $t('Membership plans exist', (int) DB::val('SELECT COUNT(*) FROM membership_plans') >= 1);

    if (isset($_GET['selftest'])) {
        $pdo = DB::pdo(); $pdo->beginTransaction();
        try {
            $branches = array_column(DB::all("SELECT id FROM branches WHERE status='active' ORDER BY id"), 'id');
            $plan = DB::one("SELECT * FROM membership_plans WHERE type='monthly' AND status='active' LIMIT 1") ?: DB::one("SELECT * FROM membership_plans WHERE status='active' LIMIT 1");
            $cash = (int) (DB::val("SELECT id FROM payment_methods WHERE requires_reference=0 AND status='active' LIMIT 1")
                ?: DB::val("SELECT id FROM payment_methods WHERE status='active' LIMIT 1"));
            $mk = function (int $regBranch, string $tag) {
                return Customer::create(['full_name' => "ZZ Selftest $tag", 'phone' => '999' . random_int(1000000, 9999999), 'email' => '', 'gender' => 'male', 'dob' => '', 'address' => '', 'emergency_name' => '', 'emergency_phone' => '', 'branch_id' => $regBranch]);
            };
            // 1. Unpaid / partial membership must not allow entry
            $c0 = $mk($branches[0], 'A');
            $m0 = Membership::create($c0, (int) $plan['id'], $branches[0]);
            $t('Unpaid membership is NOT active and entry is refused', !Membership::access(Customer::find($c0))[0] && DB::val('SELECT status FROM memberships WHERE id=?', [$m0]) === 'pending');
            Payment::record($m0, round($plan['price'] / 2, 2), $cash, $branches[0], 'TEST-REF-1');
            $t('Partial payment keeps membership pending (no activation)', DB::val('SELECT status FROM memberships WHERE id=?', [$m0]) === 'pending' && !Attendance::checkIn($c0, $branches[0])[0]);
            Payment::record($m0, round($plan['price'] - round($plan['price'] / 2, 2), 2), $cash, $branches[min(2, count($branches) - 1)], 'TEST-REF-2');
            $t('Full payment (received at another branch) activates membership', DB::val('SELECT status FROM memberships WHERE id=?', [$m0]) === 'active');
            $t('Payment stores the branch that received it', (int) DB::val('SELECT branch_id FROM payments WHERE membership_id=? ORDER BY id DESC LIMIT 1', [$m0]) === (int) $branches[min(2, count($branches) - 1)]);
            // 2. Cross-branch visit from every branch
            foreach ($branches as $i => $b) {
                $reg = $branches[($i + 1) % count($branches)];                     // registered somewhere else
                $c = $mk($reg, 'B' . $b); $m = Membership::create($c, (int) $plan['id'], $reg);
                Payment::record($m, (float) $plan['price'], $cash, $reg, 'TEST-REF-B' . $b);
                $found = Customer::search(DB::val('SELECT customer_code FROM customers WHERE id=?', [$c]), 0, '', 5)[0];
                $r1 = Attendance::checkIn($c, (int) $b); $r2 = Attendance::checkIn($c, (int) $b);
                $row = DB::one('SELECT branch_id FROM attendance WHERE customer_id=?', [$c]);
                $t("Branch #$b finds a customer registered at branch #$reg, check-in recorded with branch #$b", count($found) === 1 && $r1[0] && (int) $row['branch_id'] === (int) $b);
                $t("Branch #$b blocks duplicate check-in the same day", !$r2[0]);
            }
            // 3. Expired and suspended
            DB::exec("UPDATE memberships SET end_date=CURDATE() - INTERVAL 1 DAY, start_date=CURDATE() - INTERVAL 31 DAY WHERE id=?", [$m0]); Membership::sync();
            $t('Expired membership is refused with expiry warning', !Attendance::checkIn($c0, $branches[0])[0] && DB::val('SELECT status FROM memberships WHERE id=?', [$m0]) === 'expired');
            $dup = false; try { $mk($branches[0], 'DUP'); } catch (Throwable $e) {} // random phone, so fabricate a true duplicate:
            try { Customer::create(['full_name' => 'ZZ Dup', 'phone' => DB::val('SELECT phone FROM customers WHERE id=?', [$c0]), 'email' => '', 'gender' => 'male', 'dob' => '', 'address' => '', 'emergency_name' => '', 'emergency_phone' => '', 'branch_id' => $branches[1] ?? $branches[0]]); }
            catch (RuntimeException $e) { $dup = true; }
            $t('Duplicate phone registration is rejected', $dup);
        } catch (Throwable $e) { $t('Self-test crashed', false, $e->getMessage()); }
        $pdo->rollBack();
        $rows[] = ['All self-test data was rolled back – nothing was saved.', true, ''];
    }
}
$fail = count(array_filter($rows, fn($r) => !$r[1]));
?>
<!DOCTYPE html><html><head><meta charset="utf-8"><title>CAATO check</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><link href="assets/css/app.css" rel="stylesheet"></head>
<body class="p-4"><div class="container" style="max-width:860px"><img src="assets/img/logo.png" height="70" alt=""><h1 class="page-title mt-2">Installation check</h1>
<div class="alert alert-<?= $fail ? 'danger' : 'success' ?>"><?= $fail ? "$fail check(s) need attention." : 'Everything looks good.' ?></div>
<table class="table"><?php foreach ($rows as [$n, $ok, $note]): ?><tr><td style="width:40px"><?= $ok ? '✅' : '❌' ?></td><td><?= e($n) ?><?= $note ? '<br><small class="text-muted">' . e($note) . '</small>' : '' ?></td></tr><?php endforeach; ?></table>
<?php if ($dbOk && !isset($_GET['selftest'])): ?><a class="btn btn-brand" href="?selftest=1">Run cross-branch self-test (safe, rolled back)</a> <?php endif; ?>
<a class="btn btn-outline-secondary" href="index.php">Go to login</a><p class="text-muted small mt-3">Delete check.php when you are done.</p></div></body></html>
