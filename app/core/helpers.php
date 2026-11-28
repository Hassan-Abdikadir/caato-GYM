<?php
function e($v): string { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); }
function url(string $page, array $p = []): string { return BASE . '/index.php?' . http_build_query(['page' => $page] + $p); }
function asset(string $p): string { return BASE . '/assets/' . $p; }
function redirect(string $page, array $p = []): void { header('Location: ' . url($page, $p)); exit; }
function flash(string $type, string $msg): void { $_SESSION['flash'][] = [$type, $msg]; }
function is_post(): bool { return ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST'; }
function req(string $k, $d = '') { $v = $_POST[$k] ?? $_GET[$k] ?? $d; return is_string($v) ? trim($v) : $v; }
function json_out(array $a, int $code = 200): void { http_response_code($code); header('Content-Type: application/json'); echo json_encode($a); exit; }

function jsattr($v): string { return htmlspecialchars(json_encode($v), ENT_QUOTES, 'UTF-8'); }
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        if (!empty($_COOKIE['CAATOCSRF']) && preg_match('/^[a-f0-9]{64}$/', (string) $_COOKIE['CAATOCSRF'])) {
            $_SESSION['csrf'] = $_COOKIE['CAATOCSRF'];
        } else {
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
        }
    }
    if (!headers_sent() && ($_COOKIE['CAATOCSRF'] ?? '') !== $_SESSION['csrf']) {
        $isHttps = !empty($_SERVER['HTTPS']) || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
        setcookie('CAATOCSRF', $_SESSION['csrf'], [
            'expires' => 0, 'path' => '/', 'httponly' => true,
            'samesite' => 'Lax', 'secure' => $isHttps,
        ]);
    }
    return $_SESSION['csrf'];
}
function csrf_field(): string { return '<input type="hidden" name="_csrf" value="' . csrf_token() . '">'; }
function csrf_check(): void
{
    $t = $_POST['_csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    $expected = csrf_token();
    $cookieTok = $_COOKIE['CAATOCSRF'] ?? '';
    $valid = hash_equals($expected, (string) $t) || ($cookieTok !== '' && hash_equals((string) $cookieTok, (string) $t));
    if (!$valid) {
        if (($_GET['page'] ?? '') === 'api') json_out(['ok' => false, 'message' => 'Security token expired. Refresh the page.'], 419);
        http_response_code(419);
        exit('Security token expired. Please go back, refresh the page and try again.');
    }
}

function setting(string $k, string $d = ''): string
{
    static $c = null;
    if ($c === null) {
        $c = [];
        try { foreach (DB::all('SELECT skey, svalue FROM settings') as $r) $c[$r['skey']] = $r['svalue']; } catch (Throwable $e) {}
    }
    return $c[$k] ?? $d;
}
function set_setting(string $k, string $v): void
{
    if (DB::isSqlite()) {
        DB::q('INSERT OR REPLACE INTO settings (skey, svalue) VALUES (?,?)', [$k, $v]);
    } else {
        DB::q('REPLACE INTO settings (skey, svalue) VALUES (?,?)', [$k, $v]);
    }
}

function money($v): string { return setting('currency', '$') . number_format((float) $v, 2); }
function fmt_date($d): string { return $d ? date('d M Y', strtotime($d)) : '—'; }
function fmt_dt($d): string { return $d ? date('d M Y, H:i', strtotime($d)) : '—'; }
function normalize_phone(string $p): string { return preg_replace('/\D+/', '', $p); }
function short_branch(?string $name): string
{
    $s = preg_replace('/^CAATO\s+FITNESS\s+GYM\s*[^A-Za-z0-9]+/iu', '', (string) $name);
    return trim($s) !== '' ? trim($s) : (string) $name;
}

function audit(string $action, string $entity = '', $id = null, string $details = ''): void
{
    try {
        DB::q('INSERT INTO audit_logs (user_id, branch_id, action, entity, entity_id, details, ip_address) VALUES (?,?,?,?,?,?,?)',
            [Auth::id(), Auth::branchId(), $action, $entity ?: null, $id, mb_substr($details, 0, 500), $_SERVER['REMOTE_ADDR'] ?? '']);
    } catch (Throwable $e) {}
}

function notify(string $type, string $title, string $msg, ?int $branch = null, ?int $customer = null, ?string $key = null): void
{
    try {
        DB::q('INSERT IGNORE INTO notifications (branch_id, customer_id, type, title, message, dedupe_key) VALUES (?,?,?,?,?,?)',
            [$branch, $customer, $type, $title, mb_substr($msg, 0, 255), $key]);
    } catch (Throwable $e) {}
}

function badge(string $s): string
{
    $map = ['active' => 'success', 'paid' => 'success', 'success' => 'success', 'operational' => 'success',
            'pending' => 'warning', 'partial' => 'warning', 'warning' => 'warning', 'maintenance' => 'warning',
            'expired' => 'danger', 'unpaid' => 'danger', 'inactive' => 'danger', 'cancelled' => 'danger',
            'blocked' => 'danger', 'danger' => 'danger', 'out_of_order' => 'danger',
            'suspended' => 'secondary', 'none' => 'secondary'];
    $lbl = str_replace('_', ' ', $s === 'none' ? 'no membership' : $s);
    return '<span class="badge text-bg-' . ($map[$s] ?? 'secondary') . '">' . e(ucfirst($lbl)) . '</span>';
}

/** Formats a Somali/International phone for direct WhatsApp wa.me links */
function whatsapp_url(string $phone, string $text = ''): string
{
    $d = preg_replace('/\D+/', '', $phone);
    if ($d === '') return '#';
    if (strlen($d) === 9 && ($d[0] === '6' || $d[0] === '7')) $d = '252' . $d;
    elseif (strlen($d) === 10 && $d[0] === '0') $d = '252' . substr($d, 1);
    return 'https://wa.me/' . $d . ($text !== '' ? '?text=' . rawurlencode($text) : '');
}

/** Calculates BMI category and badge color */
function bmi_status(?float $bmi): array
{
    if (!$bmi || $bmi <= 0) return ['—', 'secondary'];
    if ($bmi < 18.5) return ['Underweight', 'info'];
    if ($bmi < 25.0) return ['Optimal / Normal', 'success'];
    if ($bmi < 30.0) return ['Overweight', 'warning'];
    return ['Obese Class', 'danger'];
}

function paginate(int $total, int $per = 15): array
{
    $pages = max(1, (int) ceil($total / $per));
    $page = min(max(1, (int) ($_GET['p'] ?? 1)), $pages);
    return ['page' => $page, 'pages' => $pages, 'per' => $per, 'total' => $total, 'offset' => ($page - 1) * $per];
}

function pager(array $pg): string
{
    if ($pg['pages'] <= 1) return '<small class="text-muted">' . $pg['total'] . ' record(s)</small>';
    $q = $_GET;
    $li = function (int $n, string $label, string $cls = '') use ($q) {
        $q['p'] = $n;
        return '<li class="page-item ' . $cls . '"><a class="page-link" href="?' . e(http_build_query($q)) . '">' . $label . '</a></li>';
    };
    $h = '<div class="d-flex justify-content-between align-items-center flex-wrap gap-2"><small class="text-muted">' . $pg['total'] . ' record(s) · page ' . $pg['page'] . ' of ' . $pg['pages'] . '</small><ul class="pagination pagination-sm mb-0">';
    $h .= $li(max(1, $pg['page'] - 1), '&laquo;', $pg['page'] == 1 ? 'disabled' : '');
    for ($i = max(1, $pg['page'] - 2); $i <= min($pg['pages'], $pg['page'] + 2); $i++) $h .= $li($i, (string) $i, $i == $pg['page'] ? 'active' : '');
    $h .= $li(min($pg['pages'], $pg['page'] + 1), '&raquo;', $pg['page'] == $pg['pages'] ? 'disabled' : '');
    return $h . '</ul></div>';
}

function opts(array $rows, $sel, string $v = 'id', string $l = 'name', ?string $blank = null): string
{
    $h = $blank !== null ? '<option value="">' . e($blank) . '</option>' : '';
    foreach ($rows as $r) $h .= '<option value="' . e($r[$v]) . '"' . ((string) $sel === (string) $r[$v] ? ' selected' : '') . '>' . e($r[$l]) . '</option>';
    return $h;
}

/** Branches the current user may pick from (Super Admin: all, others: own). */
function branch_list(bool $activeOnly = true): array
{
    $sql = 'SELECT id, name, code, status FROM branches WHERE 1=1' . ($activeOnly ? " AND status='active'" : '');
    $p = [];
    if (!Auth::isSuper()) { $sql .= ' AND id=?'; $p[] = Auth::branchId(); }
    return DB::all($sql . ' ORDER BY id', $p);
}

/** Branch restriction fragment: non-super users are ALWAYS locked to their own branch. */
function scope_branch(string $col, $requested = null): array
{
    if (!Auth::isSuper()) return [" AND $col = ? ", [Auth::branchId() ?? 0]];
    if ($requested) return [" AND $col = ? ", [(int) $requested]];
    return ['', []];
}

/** Renders the signature CAATO 2026 Cyber Hero Banner for any page section */
function page_banner(string $badge, string $title, string $accent, string $subtitle, string $actionsHtml = '', array $metrics = []): void
{
    $u = Auth::user();
    $roleBadgeExtra = '';
    if ($u && !Auth::isSuper() && !empty($u['branch_name'])) {
        $bShort = short_branch($u['branch_name']);
        $bCode  = $u['branch_code'] ?? 'BR';
        $roleBadgeExtra = ' <span class="ms-1 px-2 py-0 rounded-pill" style="background:rgba(16,185,129,0.22);border:1px solid rgba(16,185,129,0.45);color:#6ee7b7;font-size:0.68rem"><i class="bi bi-geo-alt-fill me-1"></i>' . e($bCode . ' · ' . strtoupper($bShort)) . '</span>';
    }
    echo '<div class="cyber-hero mb-4"><div class="row g-3 align-items-center position-relative" style="z-index:2">';
    echo '<div class="' . ($metrics ? 'col-xl-8' : 'col-12 d-flex flex-wrap align-items-center justify-content-between gap-3') . '">';
    echo '<div>';
    echo '<div class="d-inline-flex flex-wrap align-items-center gap-2 px-3 py-1 rounded-pill mb-2" style="background:rgba(255,87,34,0.18);border:1px solid rgba(255,87,34,0.4);font-size:0.72rem;font-weight:700;letter-spacing:0.08em;color:#ffab91">';
    echo '<span class="pulse-dot"></span> ' . e(strtoupper($badge)) . $roleBadgeExtra . '</div>';
    echo '<h1 class="display-6 fw-bold text-white mb-1" style="font-family:\'Barlow Condensed\',sans-serif;letter-spacing:0.02em;line-height:1.05">'
        . e($title) . ($accent !== '' ? ' <span style="color:#ff7043">' . e($accent) . '</span>' : '') . '</h1>';
    echo '<p class="mb-' . ($actionsHtml !== '' && $metrics ? '3' : '0') . ' text-white-50 small" style="max-width:660px">' . e($subtitle) . '</p>';
    echo '</div>';
    if ($actionsHtml !== '') {
        echo '<div class="d-flex flex-wrap gap-2 align-items-center' . ($metrics ? ' mt-3' : ' ms-auto') . '">' . $actionsHtml . '</div>';
    }
    echo '</div>';
    if ($metrics) {
        echo '<div class="col-xl-4"><div class="p-2 px-3 rounded-4" style="background:rgba(15,23,42,0.65);border:1px solid rgba(255,255,255,0.12);backdrop-filter:blur(12px)"><div class="row g-2 text-center">';
        $colCls = count($metrics) === 2 ? 'col-6' : (count($metrics) === 1 ? 'col-12' : 'col-4');
        foreach ($metrics as [$val, $lbl, $clr]) {
            echo '<div class="' . $colCls . '"><div class="p-2 rounded-3" style="background:rgba(255,255,255,0.05)">';
            echo '<div class="fw-bold fs-4 text-' . e($clr) . '" style="font-family:\'Barlow Condensed\'">' . e($val) . '</div>';
            echo '<small class="text-white-50 d-block text-truncate" style="font-size:0.65rem;letter-spacing:0.06em">' . e(strtoupper($lbl)) . '</small>';
            echo '</div></div>';
        }
        echo '</div></div></div>';
    }
    echo '</div></div>';
}

function render(string $view, array $d = []): void
{
    extract($d);
    include ROOT . '/app/views/layout/header.php';
    include ROOT . "/app/views/$view.php";
    include ROOT . '/app/views/layout/footer.php';
}

function save_photo(array $f): ?string
{
    if (empty($f['name']) || $f['error'] === UPLOAD_ERR_NO_FILE) return null;
    if ($f['error'] !== UPLOAD_ERR_OK) throw new RuntimeException('Photo upload failed.');
    if ($f['size'] > 2 * 1024 * 1024) throw new RuntimeException('Photo must be smaller than 2 MB.');
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
    $ext = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$mime] ?? null;
    if (!$ext || !@getimagesize($f['tmp_name'])) throw new RuntimeException('Photo must be a JPG, PNG or WEBP image.');
    $dir = ROOT . '/uploads/profiles';
    if (!is_writable($dir)) {
        $bytes = @file_get_contents($f['tmp_name']);
        if ($bytes !== false && strlen($bytes) <= 250 * 1024) {
            return 'data:' . $mime . ';base64,' . base64_encode($bytes);
        }
        return null;
    }
    $name = bin2hex(random_bytes(12)) . '.' . $ext;
    if (!move_uploaded_file($f['tmp_name'], $dir . '/' . $name)) throw new RuntimeException('Could not save the photo.');
    return $name;
}
function photo_url(?string $p): string
{
    if (!$p) return asset('img/avatar.svg');
    if (strpos($p, 'data:image/') === 0 || strpos($p, 'http') === 0) return $p;
    return BASE . '/uploads/profiles/' . $p;
}

/** Saves a base64 data-URI photo (from webcam canvas). Returns filename or null. */
function save_photo_base64(string $data): ?string
{
    if ($data === '') return null;
    // Strip the data-URI prefix: data:image/jpeg;base64,...
    if (!preg_match('/^data:image\/(jpeg|png|webp);base64,(.+)$/s', $data, $m)) {
        throw new RuntimeException('Invalid camera photo data.');
    }
    $ext   = $m[1] === 'jpeg' ? 'jpg' : $m[1];
    $bytes = base64_decode($m[2], true);
    if ($bytes === false || strlen($bytes) < 100) throw new RuntimeException('Camera photo is empty or corrupt.');
    if (strlen($bytes) > 4 * 1024 * 1024) throw new RuntimeException('Camera photo must be smaller than 4 MB.');
    $dir = ROOT . '/uploads/profiles';
    if (!is_writable($dir)) {
        return strlen($data) <= 350000 ? $data : null;
    }
    $name = bin2hex(random_bytes(12)) . '.' . $ext;
    $path = $dir . '/' . $name;
    if (file_put_contents($path, $bytes) === false) throw new RuntimeException('Could not save the camera photo.');
    return $name;
}

/**
 * Sends a welcome email to a newly registered customer.
 * Silently fails if no email is provided or SMTP is misconfigured.
 *
 * @param string      $toEmail   Customer email (may be empty)
 * @param string      $toName    Customer full name
 * @param string|null $planName  Membership plan name (null if none at registration)
 * @param string|null $startDate Y-m-d start date
 * @param string|null $endDate   Y-m-d end date
 */
function send_welcome_message(string $toEmail, string $toName, ?string $planName, ?string $startDate, ?string $endDate): void
{
    if (!filter_var($toEmail, FILTER_VALIDATE_EMAIL)) return;

    $gymName      = setting('gym_name', 'Caato Gym');
    $gymPhone     = setting('gym_phone');
    $gymWhatsapp  = setting('gym_whatsapp');
    $gymAddress   = setting('gym_address');
    $currency     = setting('currency', '$');

    // Build membership block
    $membershipHtml = '';
    if ($planName && $startDate && $endDate) {
        $days  = (int) round((strtotime($endDate) - strtotime($startDate)) / 86400) + 1;
        $membershipHtml = '
        <table width="100%" cellpadding="0" cellspacing="0" style="margin:24px 0;border-collapse:collapse;">
          <tr>
            <td style="background:#1a1a1a;border-radius:8px;padding:20px;">
              <h3 style="margin:0 0 14px;color:#d4af37;font-size:15px;text-transform:uppercase;letter-spacing:1px;">Your Membership</h3>
              <table width="100%" style="border-collapse:collapse;color:#fff;font-size:14px;">
                <tr><td style="padding:6px 0;color:#aaa;">Plan</td><td style="padding:6px 0;text-align:right;font-weight:600;">' . htmlspecialchars($planName, ENT_QUOTES) . '</td></tr>
                <tr><td style="padding:6px 0;color:#aaa;border-top:1px solid #333;">Start date</td><td style="padding:6px 0;text-align:right;border-top:1px solid #333;">' . fmt_date($startDate) . '</td></tr>
                <tr><td style="padding:6px 0;color:#aaa;border-top:1px solid #333;">End date</td><td style="padding:6px 0;text-align:right;border-top:1px solid #333;">' . fmt_date($endDate) . '</td></tr>
                <tr><td style="padding:6px 0;color:#aaa;border-top:1px solid #333;">Duration</td><td style="padding:6px 0;text-align:right;border-top:1px solid #333;font-weight:600;color:#d4af37;">' . $days . ' days</td></tr>
              </table>
            </td>
          </tr>
        </table>';
    } else {
        $membershipHtml = '<p style="color:#888;font-size:13px;margin:16px 0;">A membership plan will be assigned to your account by our staff.</p>';
    }

    // Contact links
    $contactHtml = '';
    if ($gymPhone)     $contactHtml .= '<a href="tel:' . e($gymPhone) . '" style="color:#d4af37;display:block;margin-bottom:4px;">📞 ' . e($gymPhone) . '</a>';
    if ($gymWhatsapp)  $contactHtml .= '<a href="https://wa.me/' . preg_replace('/\D+/', '', $gymWhatsapp) . '" style="color:#25D366;display:block;margin-bottom:4px;">💬 WhatsApp: ' . e($gymWhatsapp) . '</a>';
    if ($gymAddress)   $contactHtml .= '<span style="color:#888;font-size:13px;">📍 ' . e($gymAddress) . '</span>';

    $subject = "Kusoo dhawow " . $gymName . " — Welcome!";
    $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body style="margin:0;padding:0;background:#f5f5f5;font-family:Arial,Helvetica,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0"><tr><td align="center" style="padding:40px 16px;">
<table width="560" cellpadding="0" cellspacing="0" style="max-width:560px;width:100%;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,.12);">
  <!-- Header -->
  <tr><td style="background:#111;padding:32px;text-align:center;">
    <h1 style="margin:0 0 4px;color:#d4af37;font-size:26px;letter-spacing:2px;">' . htmlspecialchars($gymName, ENT_QUOTES) . '</h1>
    <p style="margin:0;color:#888;font-size:13px;text-transform:uppercase;letter-spacing:1px;">Member Welcome</p>
  </td></tr>
  <!-- Body -->
  <tr><td style="padding:32px;">
    <h2 style="margin:0 0 8px;color:#111;font-size:20px;">Kusoo dhawow, ' . htmlspecialchars($toName, ENT_QUOTES) . '! 🎉</h2>
    <p style="color:#555;font-size:14px;line-height:1.7;margin:0 0 8px;">
      Waxaad hada xubin ka noqotay <strong>' . htmlspecialchars($gymName, ENT_QUOTES) . '</strong>. Farxad ayaan kula wadaagnaa jidkaagaan cusub ee caafimaadka.
    </p>
    <p style="color:#555;font-size:14px;line-height:1.7;margin:0;">
      You are now officially a member of <strong>' . htmlspecialchars($gymName, ENT_QUOTES) . '</strong>. We are excited to be part of your fitness journey!
    </p>
    ' . $membershipHtml . '
    <div style="background:#fffbf0;border-left:4px solid #d4af37;padding:14px 16px;border-radius:4px;margin-bottom:20px;">
      <p style="margin:0;color:#666;font-size:13px;">Please bring your membership card or quote your phone number at the reception on each visit.</p>
    </div>
    ' . ($contactHtml ? '<div style="margin-top:20px;">' . $contactHtml . '</div>' : '') . '
  </td></tr>
  <!-- Footer -->
  <tr><td style="background:#f9f9f9;padding:16px 32px;text-align:center;">
    <p style="margin:0;color:#bbb;font-size:12px;">&copy; ' . date('Y') . ' ' . htmlspecialchars($gymName, ENT_QUOTES) . '. Thank you for choosing us.</p>
  </td></tr>
</table>
</td></tr></table></body></html>';

    try {
        Mailer::send($toEmail, $toName, $subject, $html);
    } catch (Throwable $e) {
        @file_put_contents(ROOT . '/storage/error.log',
            date('c') . ' [Welcome mail] ' . $e->getMessage() . "\n", FILE_APPEND);
    }
}
