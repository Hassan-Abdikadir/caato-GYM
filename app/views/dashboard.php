<?php
$hour = (int) date('G');
$greet = $hour < 12 ? 'Subax Wanaagsan / Good Morning' : ($hour < 17 ? 'Galab Wanaagsan / Good Afternoon' : 'Fiid Wanaagsan / Good Evening');
$roleLabel = ucwords(str_replace('_', ' ', Auth::role()));
$totalCust = max(1, (int) $S['customers']);
$malePct = (int) round(($S['male'] / $totalCust) * 100);
$femalePct = (int) round(($S['female'] / $totalCust) * 100);
$bioPct = (int) round(($S['biometric'] / $totalCust) * 100);
?>
<!-- 2026 Cyber Hero Command Deck -->
<div class="cyber-hero mb-4">
  <div class="row g-3 align-items-center position-relative" style="z-index:2">
    <div class="col-xl-7">
      <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill mb-2" style="background:rgba(255,87,34,0.18);border:1px solid rgba(255,87,34,0.4);font-size:0.73rem;font-weight:700;letter-spacing:0.08em;color:#ffab91">
        <span class="pulse-dot"></span> <?= e(strtoupper($roleLabel)) ?> TELEMETRY · <?= date('l, d M Y') ?>
      </div>
      <h1 class="display-6 fw-bold text-white mb-1" style="font-family:'Barlow Condensed',sans-serif;letter-spacing:0.02em">
        <?= e($greet) ?>, <span style="color:#ff7043"><?= e(Auth::user()['name'] ?? 'Commander') ?></span>
      </h1>
      <p class="mb-3 text-white-50 small" style="max-width:600px">
        <i class="bi bi-geo-alt-fill text-danger me-1"></i>
        <?= Auth::isSuper() ? ($B ? 'Filtered Facility Node View' : 'Enterprise Central Command — All 6 Branches Synchronized Live') : e(Auth::user()['branch_name']) ?>
      </p>
      <div class="d-flex flex-wrap gap-2">
        <?php if (Auth::can('attendance.checkin')): ?>
          <a href="<?= url('attendance') ?>" class="btn btn-brand btn-sm px-3 py-2">
            <i class="bi bi-fingerprint"></i> Biometric Door Console
          </a>
        <?php endif; ?>
        <?php if (Auth::can('customers.create')): ?>
          <a href="<?= url('customer_register') ?>" class="btn btn-sm px-3 py-2" style="background:rgba(255,255,255,0.12);color:#fff;border:1px solid rgba(255,255,255,0.2)">
            <i class="bi bi-person-plus-fill text-warning"></i> Register Member
          </a>
        <?php endif; ?>
        <?php if (Auth::can('payments.view')): ?>
          <a href="<?= url('pos') ?>" class="btn btn-sm px-3 py-2" style="background:rgba(255,255,255,0.12);color:#fff;border:1px solid rgba(255,255,255,0.2)">
            <i class="bi bi-cup-straw text-info"></i> POS Supplement Bar
          </a>
        <?php endif; ?>
        <?php if (Auth::can('trainers.view')): ?>
          <a href="<?= url('classes') ?>" class="btn btn-sm px-3 py-2" style="background:rgba(255,255,255,0.12);color:#fff;border:1px solid rgba(255,255,255,0.2)">
            <i class="bi bi-calendar2-week text-danger"></i> Workout Classes
          </a>
        <?php endif; ?>
        <?php if (Auth::can('reports.view')): ?>
          <a href="<?= url('reports') ?>" class="btn btn-sm px-3 py-2" style="background:rgba(255,255,255,0.12);color:#fff;border:1px solid rgba(255,255,255,0.2)">
            <i class="bi bi-file-earmark-bar-graph text-success"></i> Intelligence Reports
          </a>
        <?php endif; ?>
      </div>
    </div>

    <div class="col-xl-5">
      <div class="p-3 rounded-4" style="background:rgba(15,23,42,0.65);border:1px solid rgba(255,255,255,0.12);backdrop-filter:blur(12px)">
        <?php if (Auth::isSuper()): ?>
        <form method="get" class="mb-3">
          <input type="hidden" name="page" value="dashboard">
          <label class="form-label text-white-50 small fw-bold text-uppercase mb-1" style="font-size:0.68rem;letter-spacing:0.08em">
            <i class="bi bi-diagram-3-fill text-brand me-1"></i> Active Facility Scope Filter
          </label>
          <select name="branch" class="form-select form-select-sm bg-dark text-white border-secondary fw-semibold" onchange="this.form.submit()">
            <?= opts(branch_list(false), $B, 'id', 'name', '🌐 All 6 Branches (Unified Enterprise)') ?>
          </select>
        </form>
        <?php endif; ?>
        <div class="row g-2 text-center">
          <div class="col-4">
            <div class="p-2 rounded-3" style="background:rgba(255,255,255,0.05)">
              <div class="fw-bold fs-4 text-success" style="font-family:'Barlow Condensed'"><?= (int) $S['today'] ?></div>
              <small class="text-white-50 d-block" style="font-size:0.68rem">TODAY VISITS</small>
            </div>
          </div>
          <div class="col-4">
            <div class="p-2 rounded-3" style="background:rgba(255,255,255,0.05)">
              <div class="fw-bold fs-4 text-info" style="font-family:'Barlow Condensed'"><?= (int) $S['active'] ?></div>
              <small class="text-white-50 d-block" style="font-size:0.68rem">ACTIVE MEMBERS</small>
            </div>
          </div>
          <div class="col-4">
            <div class="p-2 rounded-3" style="background:rgba(255,255,255,0.05)">
              <div class="fw-bold fs-4 text-warning" style="font-family:'Barlow Condensed'"><?= $showMoney ? money($S['today_rev']) : (int) $S['classes'] ?></div>
              <small class="text-white-50 d-block" style="font-size:0.68rem"><?= $showMoney ? 'TODAY REVENUE' : 'ACTIVE CLASSES' ?></small>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php
$cards = [
  ['Total Registered', $S['customers'], 'bi-people-fill', 'brand', url('customers')],
  ['Active Memberships', $S['active'], 'bi-patch-check-fill', 'green', url('customers', ['status' => 'active'])],
  ['Expired Accounts', $S['expired'], 'bi-hourglass-bottom', 'red', url('customers', ['status' => 'expired'])],
  ["Today's Door Check-ins", $S['today'] . ' (' . $S['month_visits'] . ' mo)', 'bi-fingerprint', 'cyan', url('attendance')],
];
if ($showMoney) {
  $cards[] = ['Monthly Gross Revenue', money($S['month']), 'bi-graph-up-arrow', 'emerald', url('payments')];
  $cards[] = ['Monthly Net Profit', money($S['net_month']), 'bi-safe2-fill', $S['net_month'] >= 0 ? 'violet' : 'red', url('reports', ['type' => 'branch_performance'])];
  $cards[] = ['Monthly Expenses', money($S['expenses_month']), 'bi-receipt-cutoff', 'amber', url('expenses')];
  $cards[] = ['Cumulative Revenue', money($S['revenue']), 'bi-cash-stack', 'green', url('payments')];
}
$cards[] = ['Unpaid / Pending', $S['pending_n'] . ($showMoney ? ' · ' . money($S['pending_amt']) : ''), 'bi-exclamation-octagon-fill', 'red', url('payments', ['tab' => 'pending'])];
$cards[] = ['Expiring in ' . $days . ' Days', $S['expiring'], 'bi-alarm-fill', 'amber', url('notifications', ['type' => 'expiring_soon'])];
$cards[] = ['Staff & Coaches', $S['employees'] . ' Staff · ' . $S['trainers'] . ' Coaches', 'bi-person-badge-fill', 'cyan', url('trainers')];
$cards[] = ['Facility Nodes & Gear', $S['branches'] . ' Branches' . ($S['eq_alert'] ? ' · ' . $S['eq_alert'] . ' Gear Alert' : ''), 'bi-diagram-3-fill', $S['eq_alert'] ? 'red' : 'brand', url('equipment')];
?>

<!-- Bento KPI Grid -->
<div class="row g-3 mb-4">
<?php foreach ($cards as [$l, $v, $i, $c, $link]): ?>
  <div class="col-6 col-xl-3 col-lg-4">
    <a href="<?= e($link) ?>" class="card stat <?= $c ?> text-decoration-none h-100">
      <div class="ico"><i class="bi <?= $i ?>"></i></div>
      <div class="overflow-hidden">
        <div class="num text-truncate"><?= e($v) ?></div>
        <small class="text-truncate"><?= e($l) ?></small>
      </div>
    </a>
  </div>
<?php endforeach; ?>
</div>

<!-- Payment Channel Split (EVC Plus / Salaam Bank / E-Dahab) & Demographics Strip -->
<div class="row g-3 mb-4">
  <?php if ($showMoney && !empty($channelSplit)): ?>
  <div class="col-xl-7">
    <div class="card h-100 shadow-sm">
      <div class="card-header d-flex align-items-center justify-content-between">
        <span><i class="bi bi-wallet2 text-brand me-2 fs-5"></i> Monthly Payment Channel Telemetry (Memberships + POS)</span>
        <span class="badge text-bg-success"><?= date('F Y') ?></span>
      </div>
      <div class="card-body p-3">
        <div class="row g-2">
          <?php
          $chColors = ['brand', 'emerald', 'cyan', 'violet'];
          foreach ($channelSplit as $idx => $ch):
            $pct = $S['month'] > 0 ? (int) round(((float) $ch['month_amount'] / $S['month']) * 100) : 0;
            $col = $chColors[$idx % count($chColors)];
          ?>
            <div class="col-md-4">
              <a href="<?= url('payments', ['method' => $ch['id']]) ?>" class="p-3 rounded-4 border bg-body-tertiary d-block text-decoration-none h-100">
                <div class="d-flex justify-content-between align-items-center mb-1">
                  <span class="fw-bold text-ink"><?= e($ch['name']) ?></span>
                  <span class="badge text-bg-secondary"><?= $pct ?>%</span>
                </div>
                <div class="fs-3 fw-bold text-brand" style="font-family:'Barlow Condensed'"><?= money($ch['month_amount']) ?></div>
                <small class="text-muted d-block mb-2"><?= (int) $ch['month_txns'] ?> verified txns this month</small>
                <div class="progress" style="height:6px;border-radius:99px">
                  <div class="progress-bar" style="width:<?= $pct ?>%;background:var(--brand-gradient)"></div>
                </div>
              </a>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <div class="col-xl-<?= ($showMoney && !empty($channelSplit)) ? '5' : '12' ?>">
    <div class="card h-100 shadow-sm">
      <div class="card-header d-flex align-items-center justify-content-between">
        <span><i class="bi bi-bar-chart-steps text-info me-2 fs-5"></i> Member Demographics &amp; Biometric Readiness</span>
        <span class="badge text-bg-info"><?= (int) $S['customers'] ?> Total</span>
      </div>
      <div class="card-body p-3 d-flex flex-column justify-content-center">
        <div class="mb-3">
          <div class="d-flex justify-content-between small fw-semibold mb-1">
            <span><i class="bi bi-gender-male text-info me-1"></i> Male: <?= (int) $S['male'] ?> (<?= $malePct ?>%)</span>
            <span><i class="bi bi-gender-female text-danger me-1"></i> Female: <?= (int) $S['female'] ?> (<?= $femalePct ?>%)</span>
          </div>
          <div class="progress" style="height:10px;border-radius:99px">
            <div class="progress-bar bg-info" style="width:<?= $malePct ?>%"></div>
            <div class="progress-bar bg-danger" style="width:<?= $femalePct ?>%"></div>
          </div>
        </div>
        <div>
          <div class="d-flex justify-content-between small fw-semibold mb-1">
            <span><i class="bi bi-fingerprint text-success me-1"></i> Biometric Door Enrolled</span>
            <span class="text-success"><?= (int) $S['biometric'] ?> / <?= (int) $S['customers'] ?> (<?= $bioPct ?>%)</span>
          </div>
          <div class="progress" style="height:10px;border-radius:99px">
            <div class="progress-bar bg-success" style="width:<?= $bioPct ?>%"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php if (Auth::isSuper() && count($branchStats) > 1): ?>
<!-- 6-Branch Live Telemetry Matrix -->
<div class="card shadow-sm mb-4">
  <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
    <div>
      <i class="bi bi-buildings-fill text-brand me-2 fs-5"></i>
      <span>6-Branch Live Telemetry &amp; Performance Matrix</span>
    </div>
    <a href="<?= url('branches') ?>" class="btn btn-sm btn-outline-brand py-1">Manage Facilities &rarr;</a>
  </div>
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead>
        <tr>
          <th>Facility Node</th>
          <th>Branch Manager</th>
          <th>Registered</th>
          <th>Active Members</th>
          <th>Door Visits Today</th>
          <th>Visits (Month)</th>
          <?php if ($showMoney): ?><th>Revenue (Month)</th><th>Cumulative</th><?php endif; ?>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($branchStats as $bs): ?>
        <tr>
          <td>
            <a href="<?= url('dashboard', ['branch' => $bs['id']]) ?>" class="fw-bold text-decoration-none">
              <span class="badge text-bg-dark me-1 font-monospace"><?= e($bs['code']) ?></span>
              <?= e(str_replace('CAATO FITNESS GYM – ', '', $bs['name'])) ?>
            </a>
          </td>
          <td><small class="text-muted"><?= e($bs['manager'] ?: 'Unassigned') ?></small></td>
          <td><span class="fw-semibold"><?= (int) $bs['customers'] ?></span></td>
          <td><span class="badge text-bg-success"><?= (int) $bs['active_members'] ?> active</span></td>
          <td>
            <span class="fw-bold <?= $bs['attendance_today'] > 0 ? 'text-info' : 'text-muted' ?>">
              <i class="bi bi-fingerprint me-1"></i><?= (int) $bs['attendance_today'] ?>
            </span>
          </td>
          <td><?= (int) $bs['attendance_month'] ?></td>
          <?php if ($showMoney): ?>
            <td class="fw-bold text-brand"><?= money($bs['revenue_month']) ?></td>
            <td class="text-muted small fw-semibold"><?= money($bs['revenue_total']) ?></td>
          <?php endif; ?>
          <td><?= badge($bs['status']) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<!-- Visual Intelligence Charts -->
<div class="row g-3 mb-4">
  <?php if ($showMoney): ?>
  <div class="col-lg-6">
    <div class="card h-100">
      <div class="card-header">
        <span><i class="bi bi-bar-chart-fill text-brand me-2 fs-5"></i> Gross Revenue (Memberships + POS) vs Expenses</span>
        <span class="badge text-bg-success">12-Month Flow</span>
      </div>
      <div class="card-body"><div class="chart-box"><canvas id="cRevenue"></canvas></div></div>
    </div>
  </div>
  <?php endif; ?>

  <div class="col-lg-6">
    <div class="card h-100">
      <div class="card-header">
        <span><i class="bi bi-activity text-danger me-2 fs-5"></i> Biometric &amp; QR Attendance Velocity (14 Days)</span>
        <span class="badge text-bg-info">Door Traffic</span>
      </div>
      <div class="card-body"><div class="chart-box"><canvas id="cAtt"></canvas></div></div>
    </div>
  </div>

  <div class="col-lg-6">
    <div class="card h-100">
      <div class="card-header">
        <span><i class="bi bi-person-lines-fill text-info me-2 fs-5"></i> Monthly Member Acquisition</span>
      </div>
      <div class="card-body"><div class="chart-box"><canvas id="cReg"></canvas></div></div>
    </div>
  </div>

  <div class="col-lg-6">
    <div class="card h-100">
      <div class="card-header">
        <span><i class="bi bi-pie-chart-fill text-warning me-2 fs-5"></i> Subscription Tier Distribution</span>
      </div>
      <div class="card-body"><div class="chart-box"><canvas id="cPlan"></canvas></div></div>
    </div>
  </div>

  <?php if (Auth::isSuper()): ?>
  <div class="col-lg-6">
    <div class="card h-100">
      <div class="card-header"><i class="bi bi-buildings-fill text-warning me-2 fs-5"></i> 6-Branch Monthly Revenue vs Visits</div>
      <div class="card-body"><div class="chart-box"><canvas id="cBranch"></canvas></div></div>
    </div>
  </div>
  <?php if ($showMoney): ?>
  <div class="col-lg-6">
    <div class="card h-100">
      <div class="card-header"><i class="bi bi-cash-coin text-success me-2 fs-5"></i> Cumulative Revenue Share by Branch</div>
      <div class="card-body"><div class="chart-box"><canvas id="cBranchRev"></canvas></div></div>
    </div>
  </div>
  <?php endif; endif; ?>
</div>

<!-- Gym Floor Operations: Group Classes Timetable & Equipment / POS Stock Alerts -->
<div class="row g-3 mb-4">
  <div class="col-lg-6">
    <div class="card h-100 shadow-sm">
      <div class="card-header d-flex align-items-center justify-content-between">
        <span><i class="bi bi-calendar2-week-fill text-brand me-2 fs-5"></i> Active Group Workout Classes</span>
        <a href="<?= url('classes') ?>" class="btn btn-sm btn-outline-brand py-1">Timetable &rarr;</a>
      </div>
      <div class="table-responsive">
        <table class="table table-hover table-sm mb-0 align-middle">
          <thead><tr><th>Class &amp; Studio</th><th>Days &amp; Time</th><th>Coach</th><th>Node</th></tr></thead>
          <tbody>
          <?php foreach ($activeClasses as $ac): ?>
            <tr>
              <td>
                <div class="fw-bold"><?= e($ac['title']) ?></div>
                <small class="text-muted"><?= e($ac['category']) ?> · <?= e($ac['room']) ?></small>
              </td>
              <td>
                <span class="badge text-bg-light border"><?= e($ac['day_of_week']) ?></span>
                <small class="d-block text-brand fw-semibold"><?= date('h:i A', strtotime($ac['start_time'])) ?> – <?= date('h:i A', strtotime($ac['end_time'])) ?></small>
              </td>
              <td><small class="fw-semibold"><i class="bi bi-trophy-fill text-warning me-1"></i><?= e($ac['trainer_name'] ?: 'Floor Coach') ?></small></td>
              <td><span class="badge text-bg-dark font-monospace"><?= e($ac['branch_code']) ?></span></td>
            </tr>
          <?php endforeach; if (!$activeClasses): ?>
            <tr><td colspan="4" class="text-muted p-4 text-center"><i class="bi bi-calendar2-check fs-3 d-block mb-1"></i> No group classes scheduled yet. <a href="<?= url('classes') ?>">Schedule classes</a>.</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="col-lg-6">
    <div class="card h-100 shadow-sm">
      <div class="card-header d-flex align-items-center justify-content-between">
        <span><i class="bi bi-tools text-warning me-2 fs-5"></i> Equipment Alerts &amp; POS Low-Stock Monitor</span>
        <div class="d-flex gap-1">
          <a href="<?= url('equipment') ?>" class="btn btn-sm btn-outline-secondary py-1">Gear</a>
          <a href="<?= url('pos') ?>" class="btn btn-sm btn-outline-secondary py-1">POS</a>
        </div>
      </div>
      <div class="card-body p-3">
        <?php if (!$eqAlertList && !$lowStockList): ?>
          <div class="text-center text-muted py-4">
            <i class="bi bi-shield-check text-success fs-2 d-block mb-1"></i>
            <strong>All Gym Equipment Operational &amp; POS Bar Stock Healthy</strong>
            <div class="small">Zero maintenance or inventory warnings across active facilities.</div>
          </div>
        <?php else: ?>
          <?php foreach ($eqAlertList as $eq): ?>
            <div class="p-2 px-3 mb-2 rounded-3 border d-flex justify-content-between align-items-center bg-body-tertiary">
              <div>
                <span class="badge text-bg-dark font-monospace me-1"><?= e($eq['branch_code']) ?></span>
                <strong><?= e($eq['name']) ?></strong>
                <small class="text-muted d-block"><?= e($eq['code']) ?> · <?= e($eq['notes'] ?: 'Requires technician attention') ?></small>
              </div>
              <?= badge($eq['condition_status']) ?>
            </div>
          <?php endforeach; ?>
          <?php foreach ($lowStockList as $ls): ?>
            <div class="p-2 px-3 mb-2 rounded-3 border d-flex justify-content-between align-items-center bg-body-tertiary">
              <div>
                <span class="badge text-bg-info me-1">POS</span>
                <strong><?= e($ls['name']) ?></strong>
                <small class="text-muted d-block"><?= e($ls['sku']) ?> · <?= e($ls['category']) ?></small>
              </div>
              <span class="badge text-bg-<?= $ls['stock_qty'] == 0 ? 'danger' : 'warning' ?>"><?= (int) $ls['stock_qty'] ?> left</span>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- Expiring Alerts (with 1-Click WhatsApp) & Live Door Check-ins -->
<div class="row g-3">
  <div class="col-lg-6">
    <div class="card h-100">
      <div class="card-header d-flex align-items-center justify-content-between">
        <span><i class="bi bi-hourglass-split text-danger me-2 fs-5"></i> Memberships Expiring Soon</span>
        <span class="badge text-bg-warning">Next <?= $days ?> Days</span>
      </div>
      <div class="table-responsive">
        <table class="table table-hover table-sm mb-0 align-middle">
          <thead><tr><th>Member</th><th>Plan</th><th>Expires</th><th class="text-end">Remind</th></tr></thead>
          <tbody>
          <?php foreach ($expiring as $r):
            $waMsg = "Asc {$r['full_name']}, xubinnimadaada CAATO FITNESS GYM ({$r['plan']}) waxay ku egtahay " . fmt_date($r['end_date']) . ". Fadlan cusboonaysii si aad u sii wadato tababarkaaga. Mahadsanid!";
          ?>
            <tr>
              <td>
                <div class="d-flex align-items-center gap-2">
                  <img src="<?= photo_url($r['profile_picture']) ?>" class="avatar" style="width:36px;height:36px">
                  <div>
                    <a class="fw-semibold" href="<?= url('customer_view', ['id' => $r['id']]) ?>"><?= e($r['full_name']) ?></a>
                    <small class="text-muted d-block font-monospace"><?= e($r['customer_code']) ?></small>
                  </div>
                </div>
              </td>
              <td><span class="badge text-bg-light border"><?= e($r['plan']) ?></span></td>
              <td><span class="badge text-bg-warning"><?= fmt_date($r['end_date']) ?></span></td>
              <td class="text-end">
                <a href="<?= e(whatsapp_url($r['phone'], $waMsg)) ?>" target="_blank" class="btn btn-sm btn-whatsapp py-1 px-2" title="Send WhatsApp Expiry Reminder">
                  <i class="bi bi-whatsapp"></i>
                </a>
              </td>
            </tr>
          <?php endforeach; if (!$expiring): ?>
            <tr><td colspan="4" class="text-muted p-4 text-center"><i class="bi bi-shield-check text-success fs-3 d-block mb-1"></i> Zero memberships expiring in the next <?= $days ?> days.</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="col-lg-6">
    <div class="card h-100">
      <div class="card-header d-flex align-items-center justify-content-between">
        <span><i class="bi bi-fingerprint text-info me-2 fs-5"></i> Recent Biometric &amp; Door Check-ins</span>
        <a href="<?= url('attendance') ?>" class="btn btn-sm btn-outline-brand py-1">Open Door Console &rarr;</a>
      </div>
      <div class="table-responsive">
        <table class="table table-hover table-sm mb-0 align-middle">
          <thead><tr><th>Member</th><th>Facility Node</th><th>Method</th><th>Time</th></tr></thead>
          <tbody>
          <?php foreach ($recent as $r): ?>
            <tr>
              <td>
                <div class="d-flex align-items-center gap-2">
                  <img src="<?= photo_url($r['profile_picture']) ?>" class="avatar" style="width:36px;height:36px">
                  <div>
                    <a class="fw-semibold" href="<?= url('customer_view', ['id' => $r['id']]) ?>"><?= e($r['full_name']) ?></a>
                    <small class="text-muted d-block font-monospace"><?= e($r['customer_code']) ?></small>
                  </div>
                </div>
              </td>
              <td>
                <span class="badge text-bg-secondary"><i class="bi bi-geo-alt"></i> <?= e(str_replace('CAATO FITNESS GYM – ', '', $r['branch'])) ?></span>
                <?= $r['branch'] !== $r['reg_branch'] ? '<span class="badge text-bg-info ms-1">Visiting</span>' : '' ?>
              </td>
              <td>
                <?php if (($r['checkin_method'] ?? '') === 'fingerprint_door'): ?>
                  <span class="badge text-bg-success"><i class="bi bi-fingerprint"></i> Biometric</span>
                <?php else: ?>
                  <span class="badge text-bg-info"><i class="bi bi-qr-code-scan"></i> QR/Desk</span>
                <?php endif; ?>
              </td>
              <td><small class="text-muted"><?= fmt_dt($r['check_in_at']) ?></small></td>
            </tr>
          <?php endforeach; if (!$recent): ?>
            <tr><td colspan="4" class="text-muted p-4 text-center"><i class="bi bi-clock-history fs-3 d-block mb-1"></i> No door check-ins recorded yet today.</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script>
const D = <?= json_encode($charts) ?>, C = window.chartColors;
const mk = (id, type, labels, sets, opts = {}) => {
  const el = document.getElementById(id);
  if (!el) return;
  new Chart(el, {
    type,
    data: {labels, datasets: sets},
    options: Object.assign({
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: sets.length > 1 || type === 'doughnut',
          labels: { boxWidth: 12, padding: 14 }
        }
      }
    }, opts)
  });
};

mk('cRevenue', 'bar', D.months, [
  {label: 'Gross Revenue (Memberships + POS)', data: D.revenue, backgroundColor: '#10b981', borderRadius: 8},
  {label: 'Operating Expenses', data: D.expenses, backgroundColor: '#f43f5e', borderRadius: 8}
]);
mk('cAtt', 'line', D.days, [{
  label: 'Door Check-ins',
  data: D.attendance,
  borderColor: C[0],
  backgroundColor: 'rgba(255, 87, 34, 0.16)',
  fill: true,
  tension: 0.36,
  pointRadius: 4,
  pointHoverRadius: 6
}]);
mk('cReg', 'bar', D.months, [{label: 'New Members', data: D.registrations, backgroundColor: C[2], borderRadius: 8}]);
mk('cPlan', 'doughnut', D.plans.labels, [{data: D.plans.data, backgroundColor: C, borderWidth: 2}]);
mk('cBranch', 'bar', D.branches.labels, [
  {label: 'Monthly Revenue', data: D.branches.revenue, backgroundColor: C[0], borderRadius: 8, yAxisID: 'y'},
  {label: 'Monthly Visits', data: D.branches.visits, backgroundColor: C[2], borderRadius: 8, yAxisID: 'y1'}
], {
  scales: {
    y: {position: 'left'},
    y1: {position: 'right', grid: {drawOnChartArea: false}}
  }
});
mk('cBranchRev', 'bar', D.branches.labels, [{label: 'Total Revenue', data: D.branches.total, backgroundColor: C[3], borderRadius: 8}]);
</script>
