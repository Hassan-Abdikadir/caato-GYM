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
        ['pos', 'POS & Supplement Bar', 'bi-cup-straw', 'payments.view', 'NEW'],
        ['expenses', 'Operating Expenses', 'bi-receipt-cutoff', 'expenses.manage', ''],
    ],
    'GYM FLOOR & ASSETS' => [
        ['classes', 'Classes & Schedules', 'bi-calendar2-week-fill', 'trainers.view', 'NEW'],
        ['equipment', 'Equipment Inventory', 'bi-tools', 'dashboard.view', 'NEW'],
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
        <strong class="side-foot-val"><?= e(Auth::isSuper() ? 'All 6 Branches (HQ)' : (Auth::user()['branch_name'] ?? '')) ?></strong>
      </div>
    </div>
  </div>
</aside>
<div class="main">
  <header class="topbar">
    <div class="topbar-left">
      <button class="btn btn-action-icon d-lg-none" id="menuBtn" aria-label="Menu"><i class="bi bi-list"></i></button>
      <button class="btn btn-action-icon d-none d-lg-inline-grid" id="collapseBtn" title="Toggle compact sidebar"><i class="bi bi-layout-sidebar-inset"></i></button>

      <div class="topbar-context d-none d-xxl-flex">
        <span class="status-pulse-dot"></span>
        <span class="topbar-context-label"><?= e(Auth::isSuper() ? 'Central Command' : str_replace('CAATO FITNESS GYM – ', '', Auth::user()['branch_name'] ?? '')) ?></span>
      </div>

      <button type="button" class="btn btn-action-icon d-md-none" data-bs-toggle="modal" data-bs-target="#cmdPaletteModal" aria-label="Quick Search">
        <i class="bi bi-search text-brand"></i>
      </button>
      <button type="button" class="cmd-trigger-btn d-none d-md-inline-flex" data-bs-toggle="modal" data-bs-target="#cmdPaletteModal">
        <i class="bi bi-search text-brand"></i>
        <span class="cmd-trigger-text">Search members, ID or commands…</span>
        <span class="cmd-kbd">Ctrl K</span>
      </button>
    </div>

    <div class="topbar-right">
      <div class="live-clock-chip d-none d-xl-inline-flex">
        <i class="bi bi-clock text-brand"></i>
        <span id="liveClock"><?= date('H:i:s') ?></span>
      </div>

      <?php if (Auth::can('attendance.checkin')): ?>
        <a href="<?= url('attendance') ?>" class="btn btn-topbar-cta d-none d-sm-inline-flex" title="Open Biometric & QR Door Scanner">
          <i class="bi bi-fingerprint"></i>
          <span>Check-in</span>
        </a>
      <?php endif; ?>

      <div class="topbar-divider d-none d-sm-block"></div>

      <button type="button" class="theme-switch-pill" id="themeBtn" title="Toggle Obsidian Dark / Neo-Light Mode" aria-label="Toggle Theme">
        <span class="theme-switch-track">
          <i class="bi bi-sun-fill theme-ico-sun"></i>
          <i class="bi bi-moon-stars-fill theme-ico-moon"></i>
          <span class="theme-switch-thumb"></span>
        </span>
      </button>

      <div class="dropdown">
        <button class="btn btn-action-icon position-relative" data-bs-toggle="dropdown" aria-label="Notifications"><i class="bi bi-bell"></i>
          <?php if ($unread): ?><span class="badge-notif-pulse"><?= $unread > 99 ? '99+' : $unread ?></span><?php endif; ?></button>
        <div class="dropdown-menu dropdown-menu-end p-0 notif-menu shadow-lg">
          <div class="px-3 py-2 fw-semibold border-bottom d-flex align-items-center justify-content-between">
            <span><i class="bi bi-bell-fill text-danger me-1"></i> System Alerts</span>
            <?php if ($unread): ?><span class="badge rounded-pill bg-danger"><?= $unread ?> new</span><?php endif; ?>
          </div>
          <div class="notif-menu-list">
          <?php foreach ($recentN as $n): ?>
            <a class="dropdown-item notif-item <?= $n['is_read'] ? '' : 'unread' ?>" href="<?= url('notifications') ?>">
              <div class="fw-semibold text-truncate"><?= e($n['title']) ?></div>
              <small class="text-muted d-block"><?= e($n['message']) ?></small>
            </a>
          <?php endforeach; if (!$recentN): ?><div class="p-3 text-center text-muted small"><i class="bi bi-check2-circle fs-3 d-block text-success mb-1"></i>No notifications yet.</div><?php endif; ?>
          </div>
          <a class="dropdown-item text-center small py-2 border-top text-brand fw-semibold" href="<?= url('notifications') ?>">Open Notification Hub &rarr;</a>
        </div>
      </div>

      <div class="dropdown">
        <button class="btn btn-user-pill dropdown-toggle" data-bs-toggle="dropdown">
          <span class="user-avatar-badge"><?= e(strtoupper(mb_substr(Auth::user()['name'] ?? 'A', 0, 1))) ?></span>
          <span class="user-pill-name d-none d-lg-inline"><?= e(Auth::user()['name'] ?? '') ?></span>
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow-lg user-menu">
          <li class="px-3 py-2 border-bottom">
            <div class="fw-bold text-truncate"><?= e(Auth::user()['name'] ?? '') ?></div>
            <small class="text-muted text-uppercase"><?= e(ucwords(str_replace('_', ' ', Auth::role()))) ?></small>
          </li>
          <li><a class="dropdown-item" href="<?= url('dashboard') ?>"><i class="bi bi-speedometer2 me-2"></i> Operations Console</a></li>
          <li><a class="dropdown-item" href="<?= url('attendance') ?>"><i class="bi bi-fingerprint me-2"></i> Biometric Check-in</a></li>
          <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#myPwModal"><i class="bi bi-key-fill me-2 text-brand"></i> Change My Password</a></li>
          <?php if (Auth::can('customers.create')): ?><li><a class="dropdown-item" href="<?= url('customer_register') ?>"><i class="bi bi-person-plus me-2"></i> Register Customer</a></li><?php endif; ?>
          <?php if (Auth::can('settings.manage')): ?><li><a class="dropdown-item" href="<?= url('settings') ?>"><i class="bi bi-sliders me-2"></i> System Settings</a></li><?php endif; ?>
          <li><hr class="dropdown-divider"></li>
          <li><a class="dropdown-item text-danger" href="<?= url('logout') ?>" data-confirm="Sign out of CAATO FITNESS GYM?"><i class="bi bi-box-arrow-right me-2"></i> Sign out</a></li>
        </ul>
      </div>
    </div>
  </header>
  <main class="content">
