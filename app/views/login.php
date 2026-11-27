<?php
$branchesLive = [];
try {
    $branchesLive = DB::all("SELECT code, name, status FROM branches ORDER BY id LIMIT 6");
} catch (Throwable $e) {}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Sign in · CAATO FITNESS GYM OS v4.0</title>
<link rel="icon" href="<?= asset('img/favicon.png') ?>">
<script>document.documentElement.setAttribute('data-bs-theme', localStorage.getItem('caato-theme') || 'dark');</script>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Barlow+Condensed:wght@600;700;800&family=Barlow:wght@400;500;600&display=swap" rel="stylesheet">
<link href="<?= asset('css/app.css') ?>" rel="stylesheet">
</head>
<body class="login-body">
<div class="login-wrap">
  <!-- LEFT CINEMATIC SHOWCASE -->
  <section class="login-art">
    <!-- Animated Floating Plasma Orbs & Cyber Grid -->
    <div class="login-orb login-orb-1"></div>
    <div class="login-orb login-orb-2"></div>
    <div class="login-orb login-orb-3"></div>
    <div class="login-grid-mesh"></div>

    <!-- Top Telemetry Bar inside Art Panel -->
    <div class="login-art-topbar">
      <div class="login-sys-chip">
        <span class="pulse-dot"></span>
        <span>CAATO ENTERPRISE OS v4.0</span>
        <span class="text-white-50">·</span>
        <span class="text-warning">2026 EDITION</span>
      </div>
      <div class="login-clock-pill">
        <i class="bi bi-clock-fill text-brand"></i>
        <span id="loginLiveClock"><?= date('h:i:s A') ?></span>
      </div>
    </div>

    <!-- Center Hero Brand & 3D Floating Visuals -->
    <div class="login-art-center">
      <div class="login-logo-showcase">
        <div class="login-logo-ring login-logo-ring-outer"></div>
        <div class="login-logo-ring login-logo-ring-inner"></div>
        <div class="logo-tile-hero">
          <img src="<?= asset('img/logo.png') ?>" alt="CAATO FITNESS GYM">
        </div>
        <!-- Floating 3D Glass Telemetry Badges around Logo -->
        <div class="login-float-badge login-float-left">
          <div class="float-ico bg-success bg-opacity-25 text-success"><i class="bi bi-fingerprint"></i></div>
          <div class="text-start">
            <small>BIOMETRIC GATE</small>
            <strong>6 Doors Active</strong>
          </div>
        </div>
        <div class="login-float-badge login-float-right">
          <div class="float-ico bg-warning bg-opacity-25 text-warning"><i class="bi bi-shield-lock-fill"></i></div>
          <div class="text-start">
            <small>SECURITY ENGINE</small>
            <strong>Zero-Trust RBAC</strong>
          </div>
        </div>
      </div>

      <h1 class="login-hero-title">
        One Membership.<br>
        <span>Six Elite Facilities.</span>
      </h1>
      <p class="login-hero-sub">
        Next-generation multi-branch gym command center: real-time biometric turnstile control, EVC Plus / Salaam Bank / E-Dahab telemetry, InBody BMI analytics &amp; POS supplement bar.
      </p>

      <!-- Interactive 3D Feature Cards -->
      <div class="login-feature-grid">
        <div class="login-feature-card">
          <div class="lfc-icon"><i class="bi bi-fingerprint"></i></div>
          <div class="lfc-title">Biometric &amp; QR Gate</div>
          <div class="lfc-desc">1-check-in/day anti-passback &amp; optical camera scanner</div>
        </div>
        <div class="login-feature-card">
          <div class="lfc-icon"><i class="bi bi-diagram-3-fill"></i></div>
          <div class="lfc-title">6-Branch Cloud Sync</div>
          <div class="lfc-desc">Register once, train at any CAATO facility nationwide</div>
        </div>
        <div class="login-feature-card">
          <div class="lfc-icon"><i class="bi bi-graph-up-arrow"></i></div>
          <div class="lfc-title">Revenue &amp; POS Suite</div>
          <div class="lfc-desc">Live P&amp;L, 80mm thermal receipts &amp; supplement inventory</div>
        </div>
      </div>
    </div>

    <!-- Bottom Live 6-Branch Network Status Ticker -->
    <div class="login-branch-ticker">
      <div class="ticker-label"><i class="bi bi-broadcast text-danger me-1"></i> LIVE NETWORK NODES:</div>
      <div class="ticker-nodes">
        <?php if ($branchesLive): foreach ($branchesLive as $bl): ?>
          <span class="ticker-node">
            <span class="pulse-dot"></span>
            <strong><?= e($bl['code']) ?></strong>
            <small><?= e(str_replace('CAATO FITNESS GYM – ', '', $bl['name'])) ?></small>
          </span>
        <?php endforeach; else: ?>
          <?php for ($i = 1; $i <= 6; $i++): ?>
            <span class="ticker-node"><span class="pulse-dot"></span><strong>B<?= $i ?></strong><small>Branch <?= $i ?></small></span>
          <?php endfor; ?>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <!-- RIGHT INTERACTIVE AUTHENTICATION PORTAL -->
  <section class="login-form">
    <!-- Ambient Background Glow in Right Panel -->
    <div class="login-form-glow"></div>

    <!-- Topbar in Right Panel -->
    <div class="login-form-topbar">
      <div class="d-flex align-items-center gap-2">
        <span class="badge badge-futuristic"><i class="bi bi-cpu-fill"></i> CAATO OS v4.0</span>
      </div>
      <button type="button" class="theme-switch-pill" id="loginThemeBtn" title="Switch Cyber Dark / Neo Light Theme">
        <span class="theme-switch-track">
          <i class="bi bi-sun-fill theme-ico-sun"></i>
          <i class="bi bi-moon-stars-fill theme-ico-moon"></i>
          <span class="theme-switch-thumb"></span>
        </span>
        <span class="theme-switch-label pe-1" id="loginThemeLabel">DARK</span>
      </button>
    </div>

    <div class="login-card-glass">
      <div class="login-card-border-glow"></div>

      <form method="post" class="w-100 position-relative" id="loginFormEl" autocomplete="off" style="z-index:2">
        <?= csrf_field() ?>

        <!-- Mobile Logo -->
        <div class="text-center d-lg-none mb-3">
          <img src="<?= asset('img/logo.png') ?>" alt="CAATO GYM" height="64" class="p-2 bg-white rounded-4 shadow">
        </div>

        <!-- Portal Header -->
        <div class="d-flex align-items-center justify-content-between mb-2">
          <div class="d-flex align-items-center gap-2">
            <div class="login-portal-icon">
              <i class="bi bi-shield-lock-fill"></i>
            </div>
            <div>
              <h1 class="page-title fs-2 mb-0">Command Portal</h1>
              <small class="text-muted">Authenticate to access your role console</small>
            </div>
          </div>
          <span class="badge text-bg-success"><span class="pulse-dot me-1"></span>RBAC 2026</span>
        </div>

        <hr class="my-3 opacity-25">

        <?php if ($error): ?>
          <div class="alert alert-danger py-2 px-3 small d-flex align-items-center gap-2 mb-3 rounded-3 shadow-sm">
            <i class="bi bi-exclamation-octagon-fill fs-5 flex-shrink-0"></i>
            <div><?= e($error) ?></div>
          </div>
        <?php endif; ?>
        <?php foreach ($_SESSION['flash'] ?? [] as [$t, $m]): ?>
          <div class="alert alert-<?= $t === 'error' ? 'danger' : e($t) ?> py-2 px-3 small d-flex align-items-center gap-2 mb-3 rounded-3 shadow-sm">
            <i class="bi bi-check-circle-fill fs-5 flex-shrink-0"></i>
            <div><?= e($m) ?></div>
          </div>
        <?php endforeach; unset($_SESSION['flash']); ?>

        <!-- Username Input -->
        <div class="mb-3">
          <label class="form-label small fw-bold text-uppercase text-muted d-flex justify-content-between" style="font-size:0.71rem;letter-spacing:0.07em">
            <span><i class="bi bi-person-badge me-1 text-brand"></i> Staff Username</span>
            <span id="selectedRoleTag" class="text-brand fw-bold"></span>
          </label>
          <div class="input-group login-input-group">
            <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
            <input name="username" id="loginUser" class="form-control" required autofocus placeholder="Enter your staff username" value="<?= e($_POST['username'] ?? '') ?>">
          </div>
        </div>

        <!-- Password Input -->
        <div class="mb-4">
          <label class="form-label small fw-bold text-uppercase text-muted" style="font-size:0.71rem;letter-spacing:0.07em">
            <i class="bi bi-key me-1 text-brand"></i> Security Password
          </label>
          <div class="input-group login-input-group">
            <span class="input-group-text"><i class="bi bi-shield-Keyhole"></i><i class="bi bi-lock-fill"></i></span>
            <input type="password" name="password" id="loginPw" class="form-control" required placeholder="Enter your security password">
            <button type="button" class="btn btn-outline-secondary px-3" id="togglePwBtn" title="Show / Hide Password" onclick="const p=document.getElementById('loginPw');p.type=p.type==='password'?'text':'password';this.innerHTML=p.type==='password'?'<i class=\'bi bi-eye\'></i>':'<i class=\'bi bi-eye-slash text-brand\'></i>'">
              <i class="bi bi-eye"></i>
            </button>
          </div>
        </div>

        <!-- Launch Button -->
        <button type="submit" class="btn btn-brand w-100 py-3 fs-6 fw-bold login-submit-btn">
          <i class="bi bi-rocket-takeoff-fill"></i>
          <span>Launch Operations Console</span>
          <i class="bi bi-arrow-right ms-1"></i>
        </button>

        <!-- Interactive 1-Click Role Switcher Dock -->
        <div class="login-role-dock mt-4">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <span class="small fw-bold text-uppercase text-muted" style="font-size:0.67rem;letter-spacing:0.09em">
              <i class="bi bi-lightning-charge-fill text-warning me-1"></i> Quick Role Access (1-Click Fill)
            </span>
            <small class="text-muted" style="font-size:0.66rem">5 Official Roles</small>
          </div>
          <div class="login-role-grid">
            <button type="button" class="role-quick-chip" onclick="fillRole('admin','Admin@12345','Super Admin · All 6 Branches', this)">
              <i class="bi bi-globe2 text-danger"></i>
              <div>
                <strong>Super Admin</strong>
                <small>All 6 Branches</small>
              </div>
            </button>
            <button type="button" class="role-quick-chip" onclick="fillRole('manager','Manager@12345','Branch Manager · Branch 1', this)">
              <i class="bi bi-building-fill-check text-warning"></i>
              <div>
                <strong>Manager</strong>
                <small>Branch 1 Node</small>
              </div>
            </button>
            <button type="button" class="role-quick-chip" onclick="fillRole('reception','Reception@12345','Receptionist · Front Desk', this)">
              <i class="bi bi-person-workspace text-info"></i>
              <div>
                <strong>Reception</strong>
                <small>Gate &amp; Desk</small>
              </div>
            </button>
            <button type="button" class="role-quick-chip" onclick="fillRole('accountant','Accountant@12345','Accountant · Finance & Audit', this)">
              <i class="bi bi-cash-coin text-success"></i>
              <div>
                <strong>Accountant</strong>
                <small>Finance &amp; POS</small>
              </div>
            </button>
            <button type="button" class="role-quick-chip" onclick="fillRole('trainer','Trainer@12345','Head Coach · Gym Floor', this)">
              <i class="bi bi-trophy-fill text-brand"></i>
              <div>
                <strong>Trainer</strong>
                <small>Classes &amp; BMI</small>
              </div>
            </button>
          </div>
        </div>

        <!-- Footer Security Stamp -->
        <div class="login-security-footer mt-4 pt-3 border-top d-flex align-items-center justify-content-between small text-muted" style="font-size:0.73rem">
          <span><i class="bi bi-shield-fill-check text-success me-1"></i> Brute-Force Lockout Active</span>
          <span><i class="bi bi-activity text-brand me-1"></i> Session Audit Trail</span>
        </div>
      </form>
    </div>
  </section>
</div>

<script>
// 1. Theme Switcher
const ltb = document.getElementById('loginThemeBtn'), lLbl = document.getElementById('loginThemeLabel');
const syncLoginTheme = () => {
  const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
  if (lLbl) lLbl.textContent = isDark ? 'DARK' : 'LIGHT';
};
syncLoginTheme();
ltb.onclick = () => {
  const n = document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
  document.documentElement.classList.add('theme-transition');
  document.documentElement.setAttribute('data-bs-theme', n);
  localStorage.setItem('caato-theme', n);
  syncLoginTheme();
  setTimeout(() => document.documentElement.classList.remove('theme-transition'), 400);
};

// 2. Live Digital Clock
const lClock = document.getElementById('loginLiveClock');
if (lClock) {
  setInterval(() => {
    lClock.textContent = new Date().toLocaleTimeString([], {hour: '2-digit', minute: '2-digit', second: '2-digit'});
  }, 1000);
}

// 3. 1-Click Role Credentials Filler with Smooth Pulse
function fillRole(user, pass, label, btn) {
  const uEl = document.getElementById('loginUser');
  const pEl = document.getElementById('loginPw');
  const tag = document.getElementById('selectedRoleTag');
  document.querySelectorAll('.role-quick-chip').forEach(c => c.classList.remove('active'));
  btn.classList.add('active');
  uEl.value = user;
  pEl.value = pass;
  if (tag) tag.textContent = label;
  uEl.classList.add('input-pulse');
  pEl.classList.add('input-pulse');
  setTimeout(() => {
    uEl.classList.remove('input-pulse');
    pEl.classList.remove('input-pulse');
  }, 450);
}
</script>
</body>
</html>
