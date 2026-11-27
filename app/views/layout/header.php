<?php
$navGroups = [
    'CORE OPERATIONS' => [
        ['dashboard', 'Operations Console', 'bi-speedometer2', 'dashboard.view', ''],
        ['attendance', 'Biometric & QR Check-in', 'bi-fingerprint', 'attendance.view', 'LIVE'],
        ['customers', 'Customer Directory', 'bi-people-fill', 'customers.view', ''],
        ['customer_register', 'Register Member', 'bi-person-plus-fill', 'customers.create', ''],
    ],
    'BILLING & COMMERCE' => [
        ['memberships', 'Memberships & Plans', 'bi-award-fill', 'memberships.view', ''],
        ['payments', 'Payments & Receipts', 'bi-cash-coin', 'payments.view', ''],
        ['pos', 'POS & Supplement Bar', 'bi-cup-straw', 'pos.view', 'NEW'],
        ['expenses', 'Operating Expenses', 'bi-receipt-cutoff', 'expenses.manage', ''],
    ],
    'GYM FLOOR & ASSETS' => [
        ['classes', 'Classes & Schedules', 'bi-calendar2-week-fill', 'classes.view', 'NEW'],
        ['equipment', 'Equipment Inventory', 'bi-tools', 'equipment.view', 'NEW'],
        ['trainers', 'Coaches & Trainers', 'bi-trophy-fill', 'trainers.view', ''],
    ],
    'ENTERPRISE & ADMIN' => [
        ['branches', '6-Branch Network', 'bi-diagram-3-fill', 'branches.manage', '6'],
        ['employees', 'Staff & Workforce', 'bi-person-badge-fill', 'employees.manage', ''],
        ['reports', 'Reports & Analytics', 'bi-graph-up-arrow', 'reports.view', ''],
        ['users', 'Access & RBAC Users', 'bi-shield-lock-fill', 'users.manage', ''],
        ['settings', 'System Settings', 'bi-sliders', 'settings.manage', ''],
    ],
];
[$nsc, $nsp] = Auth::isSuper() ? ['', []] : [' AND (branch_id IS NULL OR branch_id=?)', [Auth::branchId() ?? 0]];
$unread = (int) DB::val('SELECT COUNT(*) FROM notifications WHERE is_read=0' . $nsc, $nsp);
$recentN = DB::all('SELECT * FROM notifications WHERE 1=1' . $nsc . ' ORDER BY id DESC LIMIT 6', $nsp);
$pageTitle = $pageTitle ?? 'CAATO FITNESS GYM';
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle) ?> · CAATO FITNESS GYM</title>
<link rel="icon" href="<?= asset('img/favicon.png') ?>">
<script>
(function(){
  const t = localStorage.getItem('caato-theme') || 'dark';
  document.documentElement.setAttribute('data-bs-theme', t);
})();
</script>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Barlow+Condensed:wght@600;700;800&family=Barlow:wght@400;500;600&display=swap" rel="stylesheet">
<link href="<?= asset('css/app.css') ?>" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>window.CAATO = {csrf: <?= json_encode(csrf_token()) ?>, api: <?= json_encode(url('api')) ?>, currency: <?= json_encode(setting('currency', '$')) ?>};</script>
</head>
<body>
<aside class="sidebar" id="sidebar">
  <div class="sidebar-brand-wrapper">
    <a class="brand brand-pro-card" href="<?= url('dashboard') ?>">
      <span class="logo-tile">
        <img src="<?= asset('img/logo.png') ?>" alt="CAATO FITNESS GYM">
      </span>
      <div class="brand-text-block">
        <div class="brand-title-row">
          <span class="brand-title">CAATO</span>
          <span class="brand-os-pill">OS</span>
        </div>
        <span class="brand-sub">FITNESS GYM</span>
        <span class="brand-status-tag"><span class="pulse-dot"></span> 6-BRANCH CLOUD v4.0</span>
      </div>
    </a>
  </div>
  <nav class="sidebar-nav">
    <?php foreach ($navGroups as $groupTitle => $items):
      $visible = array_filter($items, fn($it) => Auth::can($it[3]));
      if (!$visible) continue;
    ?>
      <div class="nav-section-title"><?= e($groupTitle) ?></div>
      <?php foreach ($visible as [$p, $label, $icon, $perm, $tag]):
        $isActive = PAGE === $p || (in_array(PAGE, ['customer_view', 'customer_edit'], true) && $p === 'customers');
        $tagClass = $tag === 'LIVE' ? 'nav-badge-live' : ($tag === 'NEW' ? 'nav-badge-new' : 'nav-badge-count');
      ?>
        <a class="nav-link <?= $isActive ? 'active' : '' ?>" href="<?= url($p) ?>" title="<?= e($label) ?>">
          <span class="nav-icon-wrap"><i class="bi <?= $icon ?>"></i></span>
          <span class="nav-label"><?= e($label) ?></span>
          <?php if ($tag !== ''): ?><span class="nav-badge <?= $tagClass ?>"><?= e($tag) ?></span><?php endif; ?>
        </a>
      <?php endforeach; ?>
    <?php endforeach; ?>
  </nav>
  <div class="side-foot">
    <div class="side-foot-chip">
      <span class="side-foot-ico"><i class="bi bi-geo-alt-fill"></i></span>
      <div class="side-foot-info">
        <span class="side-foot-label">ACTIVE FACILITY NODE</span>
        <strong class="side-foot-val"><?= e(Auth::isSuper() ? 'All 6 Branches (HQ)' : short_branch(Auth::user()['branch_name'] ?? '')) ?></strong>
      </div>
    </div>
  </div>
</aside>
<?php
$tbRole = Auth::role();
$tbRoleLabel = ucwords(str_replace('_', ' ', $tbRole));
$tbBrCode = Auth::isSuper() ? 'HQ' : (Auth::user()['branch_code'] ?? 'B1');
$tbBrName = Auth::isSuper() ? 'Central Command' : short_branch(Auth::user()['branch_name'] ?? 'Branch 1');
$tbRoleColors = [
    'super_admin'    => 'brand',
    'branch_manager' => 'cyan',
    'receptionist'   => 'emerald',
    'accountant'     => 'violet',
    'trainer'        => 'amber',
];
$tbRoleTheme = $tbRoleColors[$tbRole] ?? 'brand';
?>
<div class="main">
  <header class="topbar">
    <div class="topbar-glow-line"></div>
    <div class="topbar-left">
      <button class="btn btn-action-icon pro-icon-btn d-lg-none" id="menuBtn" aria-label="Menu">
        <i class="bi bi-list"></i>
      </button>
      <button class="btn btn-action-icon pro-icon-btn d-none d-lg-inline-grid" id="collapseBtn" title="Toggle compact sidebar">
        <i class="bi bi-layout-sidebar-inset"></i>
      </button>

      <!-- 3D Facility & Section Context Pod -->
      <a href="<?= url('dashboard') ?>" class="topbar-context-pro d-none d-xl-inline-flex text-decoration-none" title="Active Facility & Console Scope">
        <span class="topbar-node-badge <?= $tbRoleTheme ?>"><?= e($tbBrCode) ?></span>
        <div class="topbar-context-stack">
          <span class="topbar-context-top">
            <span class="status-pulse-dot"></span>
            <?= e($tbBrName) ?>
          </span>
          <span class="topbar-context-sub"><?= e($pageTitle) ?></span>
        </div>
      </a>

      <!-- Mobile Quick Search Trigger -->
      <button type="button" class="btn btn-action-icon pro-icon-btn d-md-none" data-bs-toggle="modal" data-bs-target="#cmdPaletteModal" aria-label="Quick Search">
        <i class="bi bi-search text-brand"></i>
      </button>

      <!-- 3D Holographic Command Search Bar -->
      <button type="button" class="cmd-trigger-btn pro-search-bar d-none d-md-inline-flex" data-bs-toggle="modal" data-bs-target="#cmdPaletteModal">
        <span class="cmd-ico-pod"><i class="bi bi-search"></i></span>
        <span class="cmd-trigger-text">Search members, CF-ID or commands…</span>
        <span class="cmd-kbd"><i class="bi bi-command me-1"></i>Ctrl K</span>
      </button>
    </div>

    <div class="topbar-right">
      <!-- 3D Live Telemetry Clock & Date Pod -->
      <div class="live-clock-chip pro-clock-pod d-none d-xxl-inline-flex" title="Live Server & Turnstile Sync Time">
        <span class="clock-ico-pod"><i class="bi bi-clock-fill"></i></span>
        <span id="liveClock" class="clock-time-val"><?= date('H:i:s') ?></span>
        <span class="clock-date-pill"><?= strtoupper(date('d M')) ?></span>
      </div>

      <!-- Quick Action 3D Pills (Role-Aware) -->
      <?php if (Auth::can('customers.create')): ?>
        <a href="<?= url('customer_register') ?>" class="btn-topbar-quick emerald d-none d-xl-inline-flex" title="Enroll New Member">
          <span class="quick-ico"><i class="bi bi-person-plus-fill"></i></span>
          <span>Enroll</span>
        </a>
      <?php endif; ?>

      <?php if (Auth::can('pos.view')): ?>
        <a href="<?= url('pos') ?>" class="btn-topbar-quick cyan d-none d-xxl-inline-flex" title="Open POS & Supplement Bar">
          <span class="quick-ico"><i class="bi bi-cup-straw"></i></span>
          <span>POS</span>
        </a>
      <?php endif; ?>

      <?php if (Auth::can('attendance.checkin')): ?>
        <a href="<?= url('attendance') ?>" class="btn btn-topbar-cta d-none d-sm-inline-flex" title="Open Biometric & QR Door Scanner">
          <span class="cta-ico-ring"><i class="bi bi-fingerprint"></i></span>
          <span>Gate Check-in</span>
        </a>
      <?php endif; ?>

      <div class="topbar-divider d-none d-sm-block"></div>

      <!-- 3D Theme Switch Pill -->
      <button type="button" class="theme-switch-pill" id="themeBtn" title="Toggle Obsidian Dark / Neo-Light Mode" aria-label="Toggle Theme">
        <span class="theme-switch-track">
          <i class="bi bi-sun-fill theme-ico-sun"></i>
          <i class="bi bi-moon-stars-fill theme-ico-moon"></i>
          <span class="theme-switch-thumb"></span>
        </span>
      </button>

      <!-- 3D Notification Bell & Holographic Alert Menu -->
      <div class="dropdown">
        <button class="btn btn-action-icon pro-icon-btn position-relative <?= $unread ? 'has-alerts' : '' ?>" data-bs-toggle="dropdown" aria-label="Notifications" title="System Notifications & Expiry Alerts">
          <i class="bi bi-bell-fill"></i>
          <?php if ($unread): ?><span class="badge-notif-pulse"><?= $unread > 99 ? '99+' : $unread ?></span><?php endif; ?>
        </button>
        <div class="dropdown-menu dropdown-menu-end p-0 notif-menu shadow-lg">
          <div class="notif-menu-hdr d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2">
              <span class="panel-hdr-ico brand" style="width:32px;height:32px;font-size:0.95rem"><i class="bi bi-bell-fill"></i></span>
              <div>
                <div class="fw-bold small lh-1">Realtime Alert Hub</div>
                <small class="text-muted" style="font-size:0.68rem">Expiry, Renewal &amp; Gateway Events</small>
              </div>
            </div>
            <?php if ($unread): ?><span class="badge rounded-pill bg-danger"><?= $unread ?> unread</span><?php endif; ?>
          </div>
          <div class="notif-menu-list">
          <?php foreach ($recentN as $n):
            $nIco = [
              'membership_expired' => 'bi-x-octagon-fill text-danger',
              'expiring_soon'      => 'bi-alarm-fill text-warning',
              'payment_success'    => 'bi-check-circle-fill text-success',
              'membership_renewal' => 'bi-arrow-repeat text-info',
              'payment_pending'    => 'bi-exclamation-circle-fill text-danger',
            ][$n['type']] ?? 'bi-bell-fill text-brand';
          ?>
            <a class="dropdown-item notif-item d-flex align-items-start gap-2 <?= $n['is_read'] ? '' : 'unread' ?>" href="<?= url('notifications') ?>">
              <i class="bi <?= $nIco ?> mt-1"></i>
              <div class="overflow-hidden">
                <div class="fw-semibold text-truncate small"><?= e($n['title']) ?></div>
                <small class="text-muted d-block text-truncate"><?= e($n['message']) ?></small>
              </div>
            </a>
          <?php endforeach; if (!$recentN): ?><div class="p-4 text-center text-muted small"><i class="bi bi-check2-circle fs-3 d-block text-success mb-1"></i>All caught up! Zero pending alerts.</div><?php endif; ?>
          </div>
          <a class="dropdown-item text-center small py-2 border-top text-brand fw-semibold" href="<?= url('notifications') ?>">Open Full Notification Hub &rarr;</a>
        </div>
      </div>

      <!-- 3D Executive User Profile Pod -->
      <div class="dropdown">
        <button class="btn btn-user-pill pro-user-pill dropdown-toggle" data-bs-toggle="dropdown">
          <span class="user-avatar-badge <?= $tbRoleTheme ?>">
            <?= e(strtoupper(mb_substr(Auth::user()['name'] ?? 'A', 0, 1))) ?>
            <span class="avatar-online-dot"></span>
          </span>
          <span class="user-pill-stack d-none d-lg-flex">
            <span class="user-pill-name"><?= e(Auth::user()['name'] ?? '') ?></span>
            <span class="user-pill-role"><?= e(strtoupper($tbRoleLabel)) ?> · <?= e($tbBrCode) ?></span>
          </span>
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow-lg user-menu p-2">
          <li class="user-menu-hero p-3 mb-2 rounded-3">
            <div class="d-flex align-items-center gap-2">
              <span class="user-avatar-badge <?= $tbRoleTheme ?>" style="width:38px;height:38px;font-size:1rem">
                <?= e(strtoupper(mb_substr(Auth::user()['name'] ?? 'A', 0, 1))) ?>
              </span>
              <div class="overflow-hidden">
                <div class="fw-bold text-truncate"><?= e(Auth::user()['name'] ?? '') ?></div>
                <div class="d-flex align-items-center gap-1 mt-1">
                  <span class="badge text-bg-dark font-monospace" style="font-size:0.65rem"><?= e($tbBrCode) ?></span>
                  <span class="badge text-bg-secondary" style="font-size:0.65rem"><?= e($tbRoleLabel) ?></span>
                </div>
              </div>
            </div>
          </li>
          <li><a class="dropdown-item rounded-2 py-2" href="<?= url('dashboard') ?>"><i class="bi bi-speedometer2 me-2 text-brand"></i> Operations Console</a></li>
          <?php if (Auth::can('attendance.view')): ?><li><a class="dropdown-item rounded-2 py-2" href="<?= url('attendance') ?>"><i class="bi bi-fingerprint me-2 text-success"></i> Biometric Gate Check-in</a></li><?php endif; ?>
          <?php if (Auth::can('customers.create')): ?><li><a class="dropdown-item rounded-2 py-2" href="<?= url('customer_register') ?>"><i class="bi bi-person-plus-fill me-2 text-info"></i> Enroll New Member</a></li><?php endif; ?>
          <li><a class="dropdown-item rounded-2 py-2" href="#" data-bs-toggle="modal" data-bs-target="#myPwModal"><i class="bi bi-key-fill me-2 text-warning"></i> Change My Password</a></li>
          <?php if (Auth::can('settings.manage')): ?><li><a class="dropdown-item rounded-2 py-2" href="<?= url('settings') ?>"><i class="bi bi-sliders me-2 text-secondary"></i> System Settings</a></li><?php endif; ?>
          <li><hr class="dropdown-divider my-1"></li>
          <li><a class="dropdown-item rounded-2 py-2 text-danger fw-semibold" href="<?= url('logout') ?>" data-confirm="Sign out of CAATO FITNESS GYM?"><i class="bi bi-box-arrow-right me-2"></i> Sign Out Console</a></li>
        </ul>
      </div>
    </div>
  </header>
  <main class="content">
