<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Sign in · CAATO FITNESS GYM</title>
<link rel="icon" href="<?= asset('img/favicon.png') ?>">
<script>document.documentElement.setAttribute('data-bs-theme', localStorage.getItem('caato-theme') || 'dark');</script>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Barlow+Condensed:wght@600;700;800&family=Barlow:wght@400;500;600&display=swap" rel="stylesheet">
<link href="<?= asset('css/app.css') ?>" rel="stylesheet">
</head>
<body>
<div class="login-wrap">
  <section class="login-art">
    <span class="logo-tile">
      <img src="<?= asset('img/logo.png') ?>" alt="CAATO FITNESS GYM">
    </span>
    <div class="d-inline-flex align-items-center gap-2 mb-2 px-3 py-1 rounded-pill" style="background:rgba(255,87,34,0.16);border:1px solid rgba(255,87,34,0.38);font-size:0.75rem;font-weight:700;letter-spacing:0.08em;color:#ffab91;z-index:2">
      <span class="pulse-dot"></span> CAATO ENTERPRISE OS v4.0 · 2026 EDITION
    </div>
    <h2>One Membership.<br>Six Elite Facilities.</h2>
    <p>Unified cloud biometric access, real-time revenue intelligence, body transformation analytics &amp; multi-branch operations.</p>

    <div class="login-feature-grid">
      <div class="login-feature-pill">
        <i class="bi bi-fingerprint"></i>
        <span>Biometric &amp; QR Door</span>
      </div>
      <div class="login-feature-pill">
        <i class="bi bi-diagram-3-fill"></i>
        <span>6-Branch Cloud Sync</span>
      </div>
      <div class="login-feature-pill">
        <i class="bi bi-shield-check"></i>
        <span>Zero-Trust Billing</span>
      </div>
    </div>
  </section>

  <section class="login-form">
    <button type="button" class="theme-switch-pill position-absolute top-0 end-0 m-4" id="loginThemeBtn" title="Toggle Dark/Light Theme">
      <span class="theme-switch-track">
        <i class="bi bi-sun-fill theme-ico-sun"></i>
        <i class="bi bi-moon-stars-fill theme-ico-moon"></i>
        <span class="theme-switch-thumb"></span>
      </span>
      <span class="theme-switch-label" id="loginThemeLabel">DARK</span>
    </button>

    <div class="login-card-glass">
      <form method="post" class="w-100" autocomplete="off">
        <?= csrf_field() ?>
        <div class="text-center d-lg-none mb-4">
          <img src="<?= asset('img/logo.png') ?>" alt="CAATO GYM" height="72" class="p-2 bg-white rounded-3 shadow-sm">
        </div>
        <div class="d-flex align-items-center gap-2 mb-1">
          <h1 class="page-title fs-2 mb-0">Staff Console</h1>
          <span class="badge badge-futuristic ms-auto"><i class="bi bi-shield-lock-fill"></i> RBAC 2026</span>
        </div>
        <p class="text-muted small mb-4">Authenticate to access CAATO 6-Branch Command Center</p>
        
        <?php if ($error): ?>
          <div class="alert alert-danger py-2 small d-flex align-items-center gap-2 mb-3 rounded-3">
            <i class="bi bi-exclamation-triangle-fill fs-5"></i>
            <div><?= e($error) ?></div>
          </div>
        <?php endif; ?>
        <?php foreach ($_SESSION['flash'] ?? [] as [$t, $m]): ?>
          <div class="alert alert-<?= $t === 'error' ? 'danger' : e($t) ?> py-2 small mb-3 rounded-3"><?= e($m) ?></div>
        <?php endforeach; unset($_SESSION['flash']); ?>

        <div class="mb-3">
          <label class="form-label small fw-semibold text-uppercase text-muted" style="font-size:0.74rem;letter-spacing:0.05em">Staff Username</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
            <input name="username" class="form-control" required autofocus placeholder="Enter username" value="<?= e($_POST['username'] ?? '') ?>">
          </div>
        </div>

        <div class="mb-4">
          <label class="form-label small fw-semibold text-uppercase text-muted" style="font-size:0.74rem;letter-spacing:0.05em">Security Password</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-key-fill"></i></span>
            <input type="password" name="password" id="loginPw" class="form-control" required placeholder="Enter password">
            <button type="button" class="btn btn-outline-secondary" onclick="const p=document.getElementById('loginPw');p.type=p.type==='password'?'text':'password';this.innerHTML=p.type==='password'?'<i class=\'bi bi-eye\'></i>':'<i class=\'bi bi-eye-slash\'></i>'">
              <i class="bi bi-eye"></i>
            </button>
          </div>
        </div>

        <button class="btn btn-brand w-100 py-2 fs-6 fw-bold shadow-md">
          <i class="bi bi-box-arrow-in-right"></i> Launch Operations Console
        </button>

        <div class="text-center mt-4 pt-2 border-top small text-muted" style="font-size:0.76rem">
          <i class="bi bi-lock-fill text-success me-1"></i> Protected by Brute-Force Lockout &amp; Session Audit
        </div>
      </form>
    </div>
  </section>
</div>
<script>
const ltb = document.getElementById('loginThemeBtn'), lLbl = document.getElementById('loginThemeLabel');
const syncLoginTheme = () => {
  const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
  if (lLbl) lLbl.textContent = isDark ? 'CYBER DARK' : 'NEO LIGHT';
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
</script>
</body>
</html>
