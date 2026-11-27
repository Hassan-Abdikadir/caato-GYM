<?php
/**
 * CAATO GYM — Clear All Transactional Data
 * Keeps: roles, permissions, branches, users, membership_plans, payment_methods, settings
 * Removes: customers, memberships, payments, attendance, expenses, notifications, audit_logs
 *
 * HOW TO USE:
 *   1. Make sure XAMPP Apache + MySQL are running
 *   2. Open: http://localhost/caato-gym/database/clear_data.php
 *   3. Click "Confirm Delete" button
 *   4. After success, DELETE this file for security
 */

// ── Load config from the app ──────────────────────────────────────────────────
define('BASE_PATH', dirname(__DIR__));
$cfgFile = BASE_PATH . '/config/config.php';
if (!file_exists($cfgFile)) {
    die('<b style="color:red">Config file not found: ' . htmlspecialchars($cfgFile) . '</b>');
}

function cfg(string $k): array {
    static $c = null;
    if ($c === null) {
        $c = require BASE_PATH . '/config/config.php';
    }
    return $c[$k] ?? [];
}

$confirmed = ($_POST['confirm'] ?? '') === 'DELETE_ALL_DATA';

$error   = '';
$success = '';
$counts  = [];

if ($confirmed) {
    try {
        $db = cfg('db');
        $pdo = new PDO(
            "mysql:host={$db['host']};port={$db['port']};dbname={$db['name']};charset={$db['charset']}",
            $db['user'], $db['pass'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        $tables = ['audit_logs','notifications','attendance','payments','memberships','expenses','body_metrics','pos_sales','customers'];

        $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
        foreach ($tables as $t) {
            try { $pdo->exec("TRUNCATE TABLE `$t`"); } catch (Throwable $e) {}
        }
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');

        // Verify
        foreach ($tables as $t) {
            try { $counts[$t] = (int) $pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn(); } catch (Throwable $e) {}
        }
        $success = 'All transactional data cleared successfully!';

    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="so">
<head>
<meta charset="UTF-8">
<title>CAATO GYM – Clear Data</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>
  *{box-sizing:border-box;margin:0;padding:0}
  body{font-family:system-ui,sans-serif;background:#0f172a;color:#f1f5f9;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}
  .card{background:#1e293b;border:1px solid #334155;border-radius:16px;padding:40px;max-width:520px;width:100%;box-shadow:0 20px 60px rgba(0,0,0,.5)}
  h1{font-size:1.4rem;color:#ef4444;margin-bottom:8px;display:flex;align-items:center;gap:10px}
  .sub{color:#94a3b8;font-size:.88rem;margin-bottom:24px;line-height:1.6}
  .warn{background:#450a0a;border:1px solid #991b1b;border-radius:10px;padding:16px;margin-bottom:20px;font-size:.88rem;color:#fca5a5;line-height:1.7}
  .keep{background:#0c1a2e;border:1px solid #1e3a5f;border-radius:10px;padding:14px;margin-bottom:20px}
  .keep h3{font-size:.8rem;text-transform:uppercase;color:#38bdf8;margin-bottom:8px;letter-spacing:.05em}
  .keep ul{list-style:none;display:flex;flex-wrap:wrap;gap:8px}
  .keep ul li{background:#1e3a5f;border-radius:6px;padding:3px 10px;font-size:.8rem;color:#bae6fd}
  .del h3{color:#f87171}
  .del ul li{background:#450a0a;border:1px solid #991b1b;color:#fca5a5}
  form{margin-top:4px}
  input[type=text]{width:100%;padding:11px 14px;border-radius:8px;border:1px solid #ef4444;background:#0f172a;color:#f1f5f9;font-size:.9rem;margin-bottom:14px;outline:none}
  input[type=text]:focus{border-color:#f87171;box-shadow:0 0 0 3px rgba(239,68,68,.2)}
  button{width:100%;padding:13px;border-radius:10px;border:none;background:linear-gradient(135deg,#dc2626,#991b1b);color:#fff;font-size:1rem;font-weight:700;cursor:pointer;letter-spacing:.03em;transition:.2s}
  button:hover{background:linear-gradient(135deg,#ef4444,#dc2626)}
  .label{font-size:.82rem;color:#94a3b8;margin-bottom:6px}
  .success{background:#052e16;border:1px solid #166534;border-radius:10px;padding:20px;color:#86efac}
  .success h2{color:#4ade80;margin-bottom:12px;font-size:1.1rem}
  .err{background:#450a0a;border:1px solid #991b1b;border-radius:10px;padding:16px;color:#fca5a5}
  table{width:100%;border-collapse:collapse;margin-top:12px;font-size:.85rem}
  td,th{padding:6px 10px;text-align:left;border-bottom:1px solid #1e3a5f}
  th{color:#38bdf8;font-weight:600}
  .zero{color:#4ade80;font-weight:700}
  .back{display:block;text-align:center;margin-top:18px;color:#38bdf8;text-decoration:none;font-size:.88rem}
  .back:hover{text-decoration:underline}
  .security-note{margin-top:20px;background:#1a1a0a;border:1px solid #713f12;border-radius:8px;padding:12px;font-size:.8rem;color:#fde68a}
</style>
</head>
<body>
<div class="card">

<?php if ($success): ?>
  <div class="success">
    <h2>✅ <?= htmlspecialchars($success) ?></h2>
    <p style="margin-bottom:14px;color:#86efac;font-size:.88rem">Xogta macaamiisha oo dhammaanteed si guul leh ayaa laga saaray. Seed data (plans, branches, admin user) waa salim.</p>
    <table>
      <tr><th>Table</th><th>Remaining Rows</th></tr>
      <?php foreach ($counts as $tbl => $cnt): ?>
      <tr>
        <td><?= htmlspecialchars($tbl) ?></td>
        <td class="zero"><?= $cnt === 0 ? '✓ 0' : $cnt ?></td>
      </tr>
      <?php endforeach; ?>
    </table>
    <div class="security-note">
      ⚠️ <strong>Security:</strong> Delete this file now! <code>database/clear_data.php</code> — waa in aad si degdeg ah u tirtirtaa.
    </div>
    <a href="/caato-gym" class="back">← Go to Gym System</a>
  </div>

<?php elseif ($error): ?>
  <div class="err">
    <strong>❌ Error:</strong><br><?= htmlspecialchars($error) ?><br><br>
    <small>XAMPP MySQL ma shaqaynayso? Apache iyo MySQL hubi inay socdaan.</small>
  </div>
  <a href="clear_data.php" class="back">← Try Again</a>

<?php else: ?>

  <h1>⚠️ Clear All Data</h1>
  <p class="sub">Ficilkan wuu tirtiraa xogta macaamiisha oo dhan — lama noqon karo. Ka akhri si taxadar leh.</p>

  <div class="warn">
    <strong>🗑️ Waxaa la TIRTIRAAYAA (oo dhan):</strong><br>
    Customers · Memberships · Payments · Attendance · Expenses · Notifications · Audit Logs
  </div>

  <div class="keep">
    <h3>✅ Waxaa la ILAALINAAYAA (seed data)</h3>
    <ul>
      <li>Roles & Permissions</li>
      <li>Branches (6)</li>
      <li>Admin User</li>
      <li>Membership Plans</li>
      <li>Payment Methods</li>
      <li>Settings</li>
    </ul>
  </div>

  <form method="post">
    <div class="label">Xaqiiji: Ku qor halkan: <strong>DELETE_ALL_DATA</strong></div>
    <input type="text" name="confirm" placeholder="DELETE_ALL_DATA" autocomplete="off" required>
    <button type="submit">🗑️ Tirtir Xogta Oo Dhan</button>
  </form>

  <a href="/caato-gym" class="back">← Ka Noqo Systemka</a>

<?php endif; ?>

</div>
</body>
</html>
