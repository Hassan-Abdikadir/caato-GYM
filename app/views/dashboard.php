<?php
$hour = (int) date('G');
$greet = $hour < 12 ? 'Subax Wanaagsan / Good Morning' : ($hour < 17 ? 'Galab Wanaagsan / Good Afternoon' : 'Fiid Wanaagsan / Good Evening');
$role = Auth::role();
$roleLabel = ucwords(str_replace('_', ' ', $role));
$totalCust = max(1, (int) $S['customers']);
$malePct = (int) round(($S['male'] / $totalCust) * 100);
$femalePct = (int) round(($S['female'] / $totalCust) * 100);
$bioPct = (int) round(($S['biometric'] / $totalCust) * 100);

// Role-specific console subtitle & badge
$myBrShort = short_branch(Auth::user()['branch_name'] ?? '');
$roleBanners = [
    'super_admin'    => ['ENTERPRISE CENTRAL COMMAND · ALL 6 BRANCHES', 'Full multi-branch executive oversight: financials, biometric doors, staff, inventory & analytics.'],
    'branch_manager' => ['BRANCH MANAGER COMMAND · ' . strtoupper($myBrShort ?: 'ASSIGNED FACILITY'), 'Complete facility oversight for your assigned branch: revenue, staff, biometric gate, equipment & classes.'],
    'receptionist'   => ['FRONT-DESK & GATE CONSOLE · ' . strtoupper($myBrShort ?: 'ASSIGNED FACILITY'), 'Member registration, biometric & QR door check-in, subscription renewals & POS supplement sales.'],
    'accountant'     => ['FINANCIAL & TREASURY LEDGER · ' . strtoupper($myBrShort ?: 'ASSIGNED FACILITY'), 'Verified revenue auditing, EVC Plus / Salaam Bank / E-Dahab channels, unpaid balances & operating expenses.'],
    'trainer'        => ['COACHING & GYM FLOOR DECK · ' . strtoupper($myBrShort ?: 'ASSIGNED FACILITY'), 'Athlete attendance, group workout classes timetable, body transformation & gym floor equipment status.'],
];
[$roleBadge, $roleSub] = $roleBanners[$role] ?? [$roleLabel, 'CAATO OS Operations Console'];
?>
<!-- 2026 Role-Personalized Cyber Hero Command Deck -->
<div class="cyber-hero mb-4">
  <div class="row g-3 align-items-center position-relative" style="z-index:2">
    <div class="col-xl-7">
      <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill mb-2" style="background:rgba(255,87,34,0.18);border:1px solid rgba(255,87,34,0.4);font-size:0.72rem;font-weight:700;letter-spacing:0.08em;color:#ffab91">
        <span class="pulse-dot"></span> <?= e($roleBadge) ?> · <?= date('D, d M Y') ?>
      </div>
      <h1 class="display-6 fw-bold text-white mb-1" style="font-family:'Barlow Condensed',sans-serif;letter-spacing:0.02em">
        <?= e($greet) ?>, <span style="color:#ff7043"><?= e(Auth::user()['name'] ?? 'Commander') ?></span>
      </h1>
      <p class="mb-3 text-white-50 small" style="max-width:620px">
        <i class="bi bi-shield-check text-success me-1"></i> <?= e($roleSub) ?>
      </p>
      <div class="d-flex flex-wrap gap-2">
        <?php if (Auth::can('attendance.checkin')): ?>
          <a href="<?= url('attendance') ?>" class="btn btn-brand btn-sm px-3 py-2">
            <i class="bi bi-fingerprint"></i> Biometric Door Check-in
          </a>
        <?php elseif (Auth::can('attendance.view')): ?>
          <a href="<?= url('attendance') ?>" class="btn btn-brand btn-sm px-3 py-2">
            <i class="bi bi-clock-history"></i> View Attendance Log
          </a>
        <?php endif; ?>

        <?php if (Auth::can('customers.create')): ?>
          <a href="<?= url('customer_register') ?>" class="btn btn-sm px-3 py-2" style="background:rgba(255,255,255,0.12);color:#fff;border:1px solid rgba(255,255,255,0.2)">
            <i class="bi bi-person-plus-fill text-warning"></i> Register Member
          </a>
        <?php endif; ?>

        <?php if (Auth::can('payments.view')): ?>
          <a href="<?= url('payments', ['tab' => 'pending']) ?>" class="btn btn-sm px-3 py-2" style="background:rgba(255,255,255,0.12);color:#fff;border:1px solid rgba(255,255,255,0.2)">
            <i class="bi bi-cash-coin text-success"></i> Collect Payments
          </a>
        <?php endif; ?>

        <?php if (Auth::can('pos.view')): ?>
          <a href="<?= url('pos') ?>" class="btn btn-sm px-3 py-2" style="background:rgba(255,255,255,0.12);color:#fff;border:1px solid rgba(255,255,255,0.2)">
            <i class="bi bi-cup-straw text-info"></i> POS Supplement Bar
          </a>
        <?php endif; ?>

        <?php if (Auth::can('expenses.manage')): ?>
          <a href="<?= url('expenses') ?>" class="btn btn-sm px-3 py-2" style="background:rgba(255,255,255,0.12);color:#fff;border:1px solid rgba(255,255,255,0.2)">
            <i class="bi bi-receipt-cutoff text-danger"></i> Log Expense
          </a>
        <?php endif; ?>

        <?php if (Auth::can('classes.view')): ?>
          <a href="<?= url('classes') ?>" class="btn btn-sm px-3 py-2" style="background:rgba(255,255,255,0.12);color:#fff;border:1px solid rgba(255,255,255,0.2)">
            <i class="bi bi-calendar2-week text-warning"></i> Workout Classes
          </a>
        <?php endif; ?>

        <?php if (Auth::can('reports.view')): ?>
          <a href="<?= url('reports') ?>" class="btn btn-sm px-3 py-2" style="background:rgba(255,255,255,0.12);color:#fff;border:1px solid rgba(255,255,255,0.2)">
            <i class="bi bi-file-earmark-bar-graph text-info"></i> Financial Reports
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
        <?php else: ?>
        <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom border-secondary border-opacity-25">
          <div class="d-flex align-items-center gap-2">
            <span class="branch-code-tile" style="width:32px;height:26px;font-size:0.72rem"><?= e($myBranch['code'] ?? (Auth::user()['branch_code'] ?? 'B1')) ?></span>
            <div>
              <span class="small text-white-50 fw-bold text-uppercase d-block" style="font-size:0.62rem;letter-spacing:0.08em">Assigned Facility Node</span>
              <strong class="text-white small"><?= e($myBrShort ?: 'Branch 1') ?></strong>
            </div>
          </div>
          <span class="badge text-bg-success"><i class="bi bi-broadcast-pin me-1"></i>LIVE NODE</span>
        </div>
        <?php endif; ?>

        <div class="row g-2 text-center">
          <?php if ($role === 'accountant'): ?>
            <div class="col-4">
              <div class="p-2 rounded-3" style="background:rgba(255,255,255,0.05)">
                <div class="fw-bold fs-4 text-success" style="font-family:'Barlow Condensed'"><?= money($S['today_rev']) ?></div>
                <small class="text-white-50 d-block" style="font-size:0.65rem">TODAY REVENUE</small>
              </div>
            </div>
            <div class="col-4">
              <div class="p-2 rounded-3" style="background:rgba(255,255,255,0.05)">
                <div class="fw-bold fs-4 text-info" style="font-family:'Barlow Condensed'"><?= money($S['net_month']) ?></div>
                <small class="text-white-50 d-block" style="font-size:0.65rem">MONTH NET PROFIT</small>
              </div>
            </div>
            <div class="col-4">
              <div class="p-2 rounded-3" style="background:rgba(255,255,255,0.05)">
                <div class="fw-bold fs-4 text-danger" style="font-family:'Barlow Condensed'"><?= money($S['pending_amt']) ?></div>
                <small class="text-white-50 d-block" style="font-size:0.65rem">UNPAID DUE</small>
              </div>
            </div>
          <?php elseif ($role === 'trainer'): ?>
            <div class="col-4">
              <div class="p-2 rounded-3" style="background:rgba(255,255,255,0.05)">
                <div class="fw-bold fs-4 text-success" style="font-family:'Barlow Condensed'"><?= (int) $S['today'] ?></div>
                <small class="text-white-50 d-block" style="font-size:0.65rem">TODAY ATHLETES</small>
              </div>
            </div>
            <div class="col-4">
              <div class="p-2 rounded-3" style="background:rgba(255,255,255,0.05)">
                <div class="fw-bold fs-4 text-info" style="font-family:'Barlow Condensed'"><?= (int) $S['classes'] ?></div>
                <small class="text-white-50 d-block" style="font-size:0.65rem">GROUP CLASSES</small>
              </div>
            </div>
            <div class="col-4">
              <div class="p-2 rounded-3" style="background:rgba(255,255,255,0.05)">
                <div class="fw-bold fs-4 text-warning" style="font-family:'Barlow Condensed'"><?= (int) $S['trainers'] ?></div>
                <small class="text-white-50 d-block" style="font-size:0.65rem">BRANCH COACHES</small>
              </div>
            </div>
          <?php else: ?>
            <div class="col-4">
              <div class="p-2 rounded-3" style="background:rgba(255,255,255,0.05)">
                <div class="fw-bold fs-4 text-success" style="font-family:'Barlow Condensed'"><?= (int) $S['today'] ?></div>
                <small class="text-white-50 d-block" style="font-size:0.65rem">TODAY VISITS</small>
              </div>
            </div>
            <div class="col-4">
              <div class="p-2 rounded-3" style="background:rgba(255,255,255,0.05)">
                <div class="fw-bold fs-4 text-info" style="font-family:'Barlow Condensed'"><?= (int) $S['active'] ?></div>
                <small class="text-white-50 d-block" style="font-size:0.65rem">ACTIVE MEMBERS</small>
              </div>
            </div>
            <div class="col-4">
              <div class="p-2 rounded-3" style="background:rgba(255,255,255,0.05)">
                <div class="fw-bold fs-4 text-warning" style="font-family:'Barlow Condensed'"><?= money($S['today_rev']) ?></div>
                <small class="text-white-50 d-block" style="font-size:0.65rem">TODAY COLLECTED</small>
              </div>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<?php
// Role-Strict Bento KPI Cards: Every role ONLY sees the KPIs relevant and permitted for them!
$cards = [];
if ($role === 'super_admin') {
    $cards = [
        ['Total Registered', $S['customers'], 'bi-people-fill', 'brand', url('customers'), 'ALL 6 BRANCHES', $S['biometric'] . ' Biometric IDs'],
        ['Active Memberships', $S['active'], 'bi-patch-check-fill', 'green', url('customers', ['status' => 'active']), 'VERIFIED ACCESS', 'Universal Gate Pass'],
        ['Expired Accounts', $S['expired'], 'bi-hourglass-bottom', 'red', url('customers', ['status' => 'expired']), 'RENEWAL QUEUE', 'Door Entry Blocked'],
        ["Today's Door Check-ins", $S['today'], 'bi-fingerprint', 'cyan', url('attendance'), 'LIVE GATE TRAFFIC', $S['month_visits'] . ' Visits This Month'],
        ['Monthly Gross Revenue', money($S['month']), 'bi-graph-up-arrow', 'emerald', url('payments'), strtoupper(date('M Y')) . ' FLOW', 'Memberships + POS Bar'],
        ['Monthly Net Profit', money($S['net_month']), 'bi-safe2-fill', $S['net_month'] >= 0 ? 'violet' : 'red', url('reports', ['type' => 'branch_performance']), 'NET P&L MARGIN', 'Gross Minus Expenses'],
        ['Monthly Expenses', money($S['expenses_month']), 'bi-receipt-cutoff', 'amber', url('expenses'), 'COST CONTROL', '6-Branch Operations'],
        ['Cumulative Revenue', money($S['revenue']), 'bi-cash-stack', 'green', url('payments'), 'ALL-TIME LEDGER', 'Verified Collections'],
        ['Unpaid / Pending', money($S['pending_amt']), 'bi-exclamation-octagon-fill', 'red', url('payments', ['tab' => 'pending']), $S['pending_n'] . ' ACCOUNTS DUE', 'Awaiting Settlement'],
        ['Expiring in ' . $days . ' Days', $S['expiring'], 'bi-alarm-fill', 'amber', url('notifications', ['type' => 'expiring_soon']), 'RETENTION ALERT', '1-Click WhatsApp Ready'],
        ['Staff & Coaches', $S['employees'] + $S['trainers'], 'bi-person-badge-fill', 'cyan', url('employees'), 'ACTIVE WORKFORCE', $S['employees'] . ' Staff · ' . $S['trainers'] . ' Coaches'],
        ['6-Branch Fleet & Gear', $S['branches'] . ' Nodes', 'bi-diagram-3-fill', $S['eq_alert'] ? 'red' : 'brand', url('branches'), 'FACILITY NETWORK', $S['eq_alert'] ? $S['eq_alert'] . ' Gear Alert' : '100% Operational'],
    ];
} elseif ($role === 'branch_manager') {
    $cards = [
        ['Branch Members', $S['customers'], 'bi-people-fill', 'brand', url('customers'), 'HOME FACILITY', $S['biometric'] . ' Biometric Enrolled'],
        ['Active Memberships', $S['active'], 'bi-patch-check-fill', 'green', url('customers', ['status' => 'active']), 'GATE CLEARED', 'Active Subscriptions'],
        ['Expired Memberships', $S['expired'], 'bi-hourglass-bottom', 'red', url('customers', ['status' => 'expired']), 'LAPSED PLANS', 'Follow-up Required'],
        ["Today's Door Visits", $S['today'], 'bi-fingerprint', 'cyan', url('attendance'), 'BRANCH TURNSTILE', $S['month_visits'] . ' Visits This Month'],
        ['Branch Month Revenue', money($S['month']), 'bi-graph-up-arrow', 'emerald', url('payments'), strtoupper(date('M Y')) . ' REVENUE', 'Plans + POS Bar'],
        ['Branch Net Profit', money($S['net_month']), 'bi-safe2-fill', $S['net_month'] >= 0 ? 'violet' : 'red', url('reports', ['type' => 'branch_performance']), 'BRANCH NET P&L', 'After Operating Costs'],
        ['Branch Expenses', money($S['expenses_month']), 'bi-receipt-cutoff', 'amber', url('expenses'), 'EXPENDITURES', 'Logged This Month'],
        ['Unpaid Balances', money($S['pending_amt']), 'bi-exclamation-octagon-fill', 'red', url('payments', ['tab' => 'pending']), $S['pending_n'] . ' PENDING', 'Blocked at Gate'],
        ['Expiring in ' . $days . ' Days', $S['expiring'], 'bi-alarm-fill', 'amber', url('notifications', ['type' => 'expiring_soon']), 'EXPIRY WINDOW', 'Send WhatsApp Alert'],
        ['Branch Staff', $S['employees'], 'bi-person-badge-fill', 'cyan', url('employees'), 'HR & PAYROLL', 'Assigned Team Members'],
        ['Coaches & Classes', $S['classes'] . ' Classes', 'bi-trophy-fill', 'violet', url('classes'), $S['trainers'] . ' COACHES', 'Studio Timetable'],
        ['Equipment Status', $S['eq_alert'] ? $S['eq_alert'] . ' Alert' : 'Healthy', 'bi-tools', $S['eq_alert'] ? 'red' : 'green', url('equipment'), 'HARDWARE FLEET', $S['eq_alert'] ? 'Needs Service' : 'All Operational'],
    ];
} elseif ($role === 'receptionist') {
    $cards = [
        ["Today's Door Check-ins", $S['today'], 'bi-fingerprint', 'cyan', url('attendance'), 'LIVE TURNSTILE', $S['month_visits'] . ' Monthly Visits'],
        ['Active Branch Members', $S['active'], 'bi-patch-check-fill', 'green', url('customers', ['status' => 'active']), 'CLEAR FOR ENTRY', 'Paid & Active Passes'],
        ['Expired (Need Renewal)', $S['expired'], 'bi-hourglass-bottom', 'red', url('customers', ['status' => 'expired']), 'RENEW AT DESK', 'Entry Refused'],
        ['Expiring in ' . $days . ' Days', $S['expiring'], 'bi-alarm-fill', 'amber', url('notifications', ['type' => 'expiring_soon']), 'SOON EXPIRING', 'Remind at Reception'],
        ['Unpaid Desk Balances', money($S['pending_amt']), 'bi-exclamation-octagon-fill', 'red', url('payments', ['tab' => 'pending']), $S['pending_n'] . ' MEMBERS DUE', 'Collect Balance Now'],
        ["Today's Desk Collection", money($S['today_rev']), 'bi-cash-coin', 'emerald', url('payments'), 'TODAY RECEIPTS', 'Plans + POS Shakes'],
        ['Biometric Enrolled', $S['biometric'], 'bi-upc-scan', 'violet', url('customers'), 'DOOR TOKENS', 'Out of ' . $S['customers'] . ' Members'],
        ['Workout Classes', $S['classes'], 'bi-calendar2-week-fill', 'brand', url('classes'), 'STUDIO SCHEDULE', $S['trainers'] . ' Active Coaches'],
    ];
} elseif ($role === 'accountant') {
    $cards = [
        ["Today's Gross Revenue", money($S['today_rev']), 'bi-cash-coin', 'emerald', url('payments'), 'DAILY TREASURY', 'Verified Today'],
        ['Monthly Gross Revenue', money($S['month']), 'bi-graph-up-arrow', 'green', url('payments'), strtoupper(date('M Y')) . ' GROSS', 'Subscriptions + POS'],
        ['Monthly Expenses', money($S['expenses_month']), 'bi-receipt-cutoff', 'amber', url('expenses'), 'COST LEDGER', 'Facility Outflows'],
        ['Monthly Net Profit', money($S['net_month']), 'bi-safe2-fill', $S['net_month'] >= 0 ? 'violet' : 'red', url('reports', ['type' => 'branch_performance']), 'NET SURPLUS', 'Gross Minus Expenses'],
        ['Cumulative Revenue', money($S['revenue']), 'bi-cash-stack', 'cyan', url('payments'), 'TOTAL AUDITED', 'All-Time Collections'],
        ['POS Bar Monthly Sales', money($S['pos_month']), 'bi-cup-straw', 'brand', url('pos'), 'RETAIL & SHAKES', 'Supplement Bar Flow'],
        ['Outstanding Unpaid Due', money($S['pending_amt']), 'bi-exclamation-octagon-fill', 'red', url('payments', ['tab' => 'pending']), $S['pending_n'] . ' ACCOUNTS', 'Receivables Pending'],
        ['Active Paid Members', $S['active'], 'bi-patch-check-fill', 'green', url('memberships'), 'PAID SUBSCRIBERS', 'Verified Accounts'],
    ];
} else {
    $cards = [
        ["Today's Athlete Visits", $S['today'], 'bi-fingerprint', 'cyan', url('attendance'), 'FLOOR CHECK-INS', $S['month_visits'] . ' Monthly Visits'],
        ['Active Branch Athletes', $S['active'], 'bi-patch-check-fill', 'green', url('customers', ['status' => 'active']), 'TRAINING PASSES', 'Cleared for Gym Floor'],
        ['Total Athlete Profiles', $S['customers'], 'bi-people-fill', 'brand', url('customers'), 'ATHLETE ROSTER', $S['biometric'] . ' Biometric Enrolled'],
        ['1-on-1 Coached Trainees', $S['assigned_trainees'] ?? 0, 'bi-person-check-fill', 'amber', url('customers'), 'PERSONAL COACHING', 'Assigned to Trainers'],
        ['InBody & BMI Checks', $S['bmi_checks'] ?? 0, 'bi-heart-pulse-fill', 'red', url('customers'), 'BODY METRICS', 'Weight & Body Fat Logs'],
        ['Group Workout Classes', $S['classes'], 'bi-calendar2-week-fill', 'violet', url('classes'), 'STUDIO SESSIONS', 'Weekly Timetable'],
        ['Coaching Staff', $S['trainers'], 'bi-trophy-fill', 'emerald', url('trainers'), 'ATHLETIC TEAM', 'Active Floor Coaches'],
        ['Gym Floor Equipment', $S['eq_alert'] ? $S['eq_alert'] . ' Alert' : '100% Ready', 'bi-tools', $S['eq_alert'] ? 'red' : 'green', url('equipment'), 'MACHINES & RACKS', $S['eq_alert'] ? 'Maintenance Logged' : 'All Operational'],
    ];
}
?>

<!-- 2026 God-Level 3D Bento KPI Grid -->
<div class="row g-3 mb-4">
<?php foreach ($cards as [$l, $v, $i, $c, $link, $tag, $sub]): ?>
  <div class="col-6 col-xl-3 col-lg-4">
    <a href="<?= e($link) ?>" class="card stat stat-pro <?= $c ?> text-decoration-none h-100">
      <div class="stat-glow-orb"></div>
      <i class="bi <?= $i ?> stat-watermark"></i>

      <div class="stat-top">
        <div class="ico">
          <i class="bi <?= $i ?>"></i>
        </div>
        <div class="stat-meta-right">
          <span class="stat-pill"><span class="stat-dot"></span><?= e($tag) ?></span>
          <span class="stat-arrow"><i class="bi bi-arrow-up-right"></i></span>
        </div>
      </div>

      <div class="stat-body">
        <div class="num text-truncate"><?= e($v) ?></div>
        <div class="stat-title text-truncate"><?= e($l) ?></div>
      </div>

      <div class="stat-foot">
        <span class="stat-sub text-truncate"><?= e($sub) ?></span>
        <div class="stat-spark"><span class="stat-spark-fill"></span></div>
      </div>
    </a>
  </div>
<?php endforeach; ?>
</div>

<?php if (in_array($role, ['super_admin', 'branch_manager', 'accountant', 'receptionist'], true)): ?>
<!-- Financial Payment Channel Split (For Super Admin, Branch Manager, Accountant & Receptionist) -->
<div class="row g-3 mb-4">
  <?php if (!empty($channelSplit)): ?>
  <div class="col-xl-7">
    <div class="card card-bento-panel h-100">
      <i class="bi bi-wallet2 panel-watermark"></i>
      <div class="card-header d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2">
          <span class="panel-hdr-ico brand"><i class="bi bi-wallet2"></i></span>
          <div>
            <div class="lh-1">Monthly Payment Channel Telemetry</div>
            <small class="text-muted fw-normal" style="font-size:0.72rem;font-family:'Outfit'">Verified Memberships + POS Supplement Bar</small>
          </div>
        </div>
        <span class="badge text-bg-success"><?= date('F Y') ?></span>
      </div>
      <div class="card-body p-3 position-relative" style="z-index:2">
        <div class="row g-3">
          <?php
          $chPalette = [
            ['brand', 'bi-phone-vibrate-fill', 'MOBILE MONEY'],
            ['emerald', 'bi-bank2', 'BANK GATEWAY'],
            ['cyan', 'bi-wallet-fill', 'DIGITAL WALLET'],
            ['violet', 'bi-credit-card-2-front-fill', 'PAYMENT CHANNEL'],
          ];
          foreach ($channelSplit as $idx => $ch):
            $pct = $S['month'] > 0 ? (int) round(((float) $ch['month_amount'] / $S['month']) * 100) : 0;
            [$cTheme, $cIcon, $cType] = $chPalette[$idx % count($chPalette)];
          ?>
            <div class="col-md-4">
              <a href="<?= url('payments', ['method' => $ch['id']]) ?>" class="channel-pro-card <?= $cTheme ?> text-decoration-none h-100">
                <i class="bi <?= $cIcon ?> channel-watermark"></i>
                <div class="d-flex justify-content-between align-items-start mb-2">
                  <div class="channel-ico"><i class="bi <?= $cIcon ?>"></i></div>
                  <span class="channel-pct-pill"><?= $pct ?>% Share</span>
                </div>
                <small class="channel-type"><?= e($cType) ?></small>
                <div class="channel-name"><?= e($ch['name']) ?></div>
                <div class="channel-amt"><?= money($ch['month_amount']) ?></div>
                <div class="d-flex justify-content-between align-items-center small text-muted mb-2" style="font-size:0.72rem">
                  <span><i class="bi bi-shield-check text-success me-1"></i><?= (int) $ch['month_txns'] ?> verified txns</span>
                  <i class="bi bi-arrow-up-right channel-go"></i>
                </div>
                <div class="stat-spark"><span class="stat-spark-fill" style="width:<?= max(12, $pct) ?>%"></span></div>
              </a>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <div class="col-xl-<?= !empty($channelSplit) ? '5' : '12' ?>">
    <div class="card card-bento-panel h-100">
      <i class="bi bi-fingerprint panel-watermark"></i>
      <div class="card-header d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2">
          <span class="panel-hdr-ico cyan"><i class="bi bi-bar-chart-steps"></i></span>
          <div>
            <div class="lh-1">Demographics &amp; Biometric Readiness</div>
            <small class="text-muted fw-normal" style="font-size:0.72rem;font-family:'Outfit'">Athlete Gender Split &amp; Turnstile Enrollment</small>
          </div>
        </div>
        <span class="badge text-bg-info"><?= (int) $S['customers'] ?> Athletes</span>
      </div>
      <div class="card-body p-3 d-flex flex-column justify-content-between position-relative" style="z-index:2">
        <div class="demo-pod mb-3">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="d-flex align-items-center gap-2">
              <span class="demo-ico bg-info bg-opacity-10 text-info"><i class="bi bi-gender-male"></i></span>
              <div>
                <small class="text-muted d-block" style="font-size:0.65rem;font-weight:700">MALE ATHLETES</small>
                <strong class="fs-6"><?= (int) $S['male'] ?> <span class="text-info">(<?= $malePct ?>%)</span></strong>
              </div>
            </div>
            <div class="d-flex align-items-center gap-2 text-end">
              <div>
                <small class="text-muted d-block" style="font-size:0.65rem;font-weight:700">FEMALE ATHLETES</small>
                <strong class="fs-6"><?= (int) $S['female'] ?> <span class="text-danger">(<?= $femalePct ?>%)</span></strong>
              </div>
              <span class="demo-ico bg-danger bg-opacity-10 text-danger"><i class="bi bi-gender-female"></i></span>
            </div>
          </div>
          <div class="progress" style="height:10px;border-radius:99px;background:rgba(148,163,184,0.14)">
            <div class="progress-bar bg-info" style="width:<?= $malePct ?>%"></div>
            <div class="progress-bar bg-danger" style="width:<?= $femalePct ?>%"></div>
          </div>
        </div>

        <div class="demo-pod">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="d-flex align-items-center gap-2">
              <span class="demo-ico bg-success bg-opacity-10 text-success"><i class="bi bi-fingerprint"></i></span>
              <div>
                <small class="text-muted d-block" style="font-size:0.65rem;font-weight:700">BIOMETRIC TURNSTILE ENROLLED</small>
                <strong class="fs-6"><?= (int) $S['biometric'] ?> of <?= (int) $S['customers'] ?> Enrolled</strong>
              </div>
            </div>
            <span class="badge text-bg-success fs-6"><?= $bioPct ?>%</span>
          </div>
          <div class="progress" style="height:10px;border-radius:99px;background:rgba(16,185,129,0.12)">
            <div class="progress-bar bg-success" style="width:<?= max(8, $bioPct) ?>%"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php elseif ($role === 'trainer'): ?>
<!-- 3D Athlete Fitness Goals & Demographics Telemetry (For Trainer) -->
<div class="row g-3 mb-4">
  <div class="col-xl-7">
    <div class="card card-bento-panel h-100">
      <i class="bi bi-bullseye panel-watermark"></i>
      <div class="card-header d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2">
          <span class="panel-hdr-ico brand"><i class="bi bi-bullseye"></i></span>
          <div>
            <div class="lh-1">Athlete Fitness Goals &amp; Program Split</div>
            <small class="text-muted fw-normal" style="font-size:0.72rem;font-family:'Outfit'">Training Objectives Across <?= e($myBrShort ?: 'Branch') ?> Athletes</small>
          </div>
        </div>
        <span class="badge text-bg-warning"><i class="bi bi-trophy-fill me-1"></i><?= (int) ($S['assigned_trainees'] ?? 0) ?> Coached</span>
      </div>
      <div class="card-body p-3 position-relative" style="z-index:2">
        <div class="row g-3">
          <?php
          $gPalette = [
            ['brand',   'bi-fire',                  'FAT BURN & CONDITIONING'],
            ['emerald', 'bi-lightning-charge-fill', 'HYPERTROPHY & MUSCLE'],
            ['cyan',    'bi-heart-pulse-fill',      'CARDIO & ENDURANCE'],
            ['violet',  'bi-trophy-fill',           'ATHLETIC PERFORMANCE'],
          ];
          $goalsToRender = !empty($goalSplit) ? array_slice($goalSplit, 0, 3) : [
            ['goal' => 'Weight Loss & Fat Burn', 'cnt' => 0],
            ['goal' => 'Muscle Gain & Strength', 'cnt' => 0],
            ['goal' => 'General Fitness',        'cnt' => 0],
          ];
          foreach ($goalsToRender as $idx => $gs):
            $gCnt = (int) $gs['cnt'];
            $gPct = $S['customers'] > 0 ? (int) round(($gCnt / $totalCust) * 100) : 0;
            [$gTheme, $gIcon, $gType] = $gPalette[$idx % count($gPalette)];
          ?>
            <div class="col-md-4">
              <a href="<?= url('customers', ['q' => $gs['goal']]) ?>" class="channel-pro-card <?= $gTheme ?> text-decoration-none h-100">
                <i class="bi <?= $gIcon ?> channel-watermark"></i>
                <div class="d-flex justify-content-between align-items-start mb-2">
                  <div class="channel-ico"><i class="bi <?= $gIcon ?>"></i></div>
                  <span class="channel-pct-pill"><?= $gPct ?>% Share</span>
                </div>
                <small class="channel-type"><?= e($gType) ?></small>
                <div class="channel-name text-truncate"><?= e($gs['goal']) ?></div>
                <div class="channel-amt"><?= $gCnt ?> Athletes</div>
                <div class="d-flex justify-content-between align-items-center small text-muted mb-2" style="font-size:0.72rem">
                  <span><i class="bi bi-check2-circle text-success me-1"></i>Active program track</span>
                  <i class="bi bi-arrow-up-right channel-go"></i>
                </div>
                <div class="stat-spark"><span class="stat-spark-fill" style="width:<?= max(16, $gPct) ?>%"></span></div>
              </a>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>

  <div class="col-xl-5">
    <div class="card card-bento-panel h-100">
      <i class="bi bi-fingerprint panel-watermark"></i>
      <div class="card-header d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2">
          <span class="panel-hdr-ico cyan"><i class="bi bi-bar-chart-steps"></i></span>
          <div>
            <div class="lh-1">Demographics &amp; Biometric Readiness</div>
            <small class="text-muted fw-normal" style="font-size:0.72rem;font-family:'Outfit'">Athlete Gender Split &amp; Turnstile Enrollment</small>
          </div>
        </div>
        <span class="badge text-bg-info"><?= (int) $S['customers'] ?> Athletes</span>
      </div>
      <div class="card-body p-3 d-flex flex-column justify-content-between position-relative" style="z-index:2">
        <div class="demo-pod mb-3">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="d-flex align-items-center gap-2">
              <span class="demo-ico bg-info bg-opacity-10 text-info"><i class="bi bi-gender-male"></i></span>
              <div>
                <small class="text-muted d-block" style="font-size:0.65rem;font-weight:700">MALE ATHLETES</small>
                <strong class="fs-6"><?= (int) $S['male'] ?> <span class="text-info">(<?= $malePct ?>%)</span></strong>
              </div>
            </div>
            <div class="d-flex align-items-center gap-2 text-end">
              <div>
                <small class="text-muted d-block" style="font-size:0.65rem;font-weight:700">FEMALE ATHLETES</small>
                <strong class="fs-6"><?= (int) $S['female'] ?> <span class="text-danger">(<?= $femalePct ?>%)</span></strong>
              </div>
              <span class="demo-ico bg-danger bg-opacity-10 text-danger"><i class="bi bi-gender-female"></i></span>
            </div>
          </div>
          <div class="progress" style="height:10px;border-radius:99px;background:rgba(148,163,184,0.14)">
            <div class="progress-bar bg-info" style="width:<?= $malePct ?>%"></div>
            <div class="progress-bar bg-danger" style="width:<?= $femalePct ?>%"></div>
          </div>
        </div>

        <div class="demo-pod">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="d-flex align-items-center gap-2">
              <span class="demo-ico bg-success bg-opacity-10 text-success"><i class="bi bi-fingerprint"></i></span>
              <div>
                <small class="text-muted d-block" style="font-size:0.65rem;font-weight:700">BIOMETRIC TURNSTILE ENROLLED</small>
                <strong class="fs-6"><?= (int) $S['biometric'] ?> of <?= (int) $S['customers'] ?> Enrolled</strong>
              </div>
            </div>
            <span class="badge text-bg-success fs-6"><?= $bioPct ?>%</span>
          </div>
          <div class="progress" style="height:10px;border-radius:99px;background:rgba(16,185,129,0.12)">
            <div class="progress-bar bg-success" style="width:<?= max(8, $bioPct) ?>%"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<?php if ($role === 'super_admin' && count($branchStats) > 1): ?>
<!-- 6-Branch Live Telemetry Matrix (Strictly Super Admin Only) -->
<div class="card card-bento-panel mb-4">
  <i class="bi bi-diagram-3-fill panel-watermark"></i>
  <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
    <div class="d-flex align-items-center gap-2">
      <span class="panel-hdr-ico brand"><i class="bi bi-buildings-fill"></i></span>
      <div>
        <div class="lh-1">6-Branch Live Telemetry &amp; Performance Matrix</div>
        <small class="text-muted fw-normal" style="font-size:0.72rem;font-family:'Outfit'">Real-time facility comparison: managers, active passes, biometric gate traffic &amp; revenue</small>
      </div>
    </div>
    <a href="<?= url('branches') ?>" class="btn btn-sm btn-outline-brand py-1 px-3"><i class="bi bi-gear-wide-connected"></i> Manage Facilities &rarr;</a>
  </div>
  <div class="table-responsive position-relative" style="z-index:2">
    <table class="table table-hover table-pro-matrix align-middle mb-0">
      <thead>
        <tr>
          <th class="ps-4">Facility Node</th>
          <th>Branch Manager</th>
          <th>Registered</th>
          <th>Active Passes</th>
          <th>Today Gate</th>
          <th>Month Visits</th>
          <th>Month Revenue</th>
          <th>Cumulative</th>
          <th class="pe-4 text-end">Status</th>
        </tr>
      </thead>
      <tbody>
      <?php
      $maxBrRev = max(1, max(array_map(fn($x) => (float) $x['revenue_month'], $branchStats)));
      foreach ($branchStats as $bs):
        $brRevPct = (int) round(((float) $bs['revenue_month'] / $maxBrRev) * 100);
      ?>
        <tr>
          <td class="ps-4">
            <a href="<?= url('dashboard', ['branch' => $bs['id']]) ?>" class="branch-node-link">
              <span class="branch-code-tile"><?= e($bs['code']) ?></span>
              <div class="branch-node-text">
                <span class="branch-node-title"><?= e(short_branch($bs['name'])) ?></span>
                <span class="branch-node-sub">CAATO Facility Node</span>
              </div>
            </a>
          </td>
          <td>
            <div class="d-inline-flex align-items-center gap-2 text-nowrap">
              <span class="manager-mini-avatar"><i class="bi bi-person-badge-fill"></i></span>
              <span class="matrix-manager-name <?= $bs['manager'] ? '' : 'text-muted' ?>"><?= e($bs['manager'] ?: 'Unassigned') ?></span>
            </div>
          </td>
          <td class="text-nowrap">
            <span class="matrix-num"><?= (int) $bs['customers'] ?></span>
            <span class="matrix-unit">members</span>
          </td>
          <td class="text-nowrap">
            <span class="badge text-bg-success"><i class="bi bi-patch-check-fill me-1"></i><?= (int) $bs['active_members'] ?> active</span>
          </td>
          <td class="text-nowrap">
            <span class="gate-visit-pill <?= $bs['attendance_today'] > 0 ? 'active' : '' ?>">
              <i class="bi bi-fingerprint"></i> <?= (int) $bs['attendance_today'] ?> today
            </span>
          </td>
          <td class="text-nowrap">
            <span class="matrix-num"><?= (int) $bs['attendance_month'] ?></span>
            <span class="matrix-unit">visits</span>
          </td>
          <td class="text-nowrap" style="min-width:125px">
            <div class="matrix-rev-month"><?= money($bs['revenue_month']) ?></div>
            <div class="stat-spark mt-1" style="height:3.5px"><span class="stat-spark-fill" style="width:<?= max(12, $brRevPct) ?>%"></span></div>
          </td>
          <td class="text-nowrap">
            <span class="matrix-rev-total"><?= money($bs['revenue_total']) ?></span>
          </td>
          <td class="pe-4 text-end text-nowrap"><?= badge($bs['status']) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php elseif ($role === 'branch_manager'): ?>
<!-- Branch Manager Dedicated 3D Command Panels: Branch Workforce & Live Branch Revenue Ledger -->
<div class="row g-3 mb-4">
  <div class="col-xl-6">
    <div class="card card-bento-panel h-100">
      <i class="bi bi-person-badge-fill panel-watermark"></i>
      <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div class="d-flex align-items-center gap-2">
          <span class="panel-hdr-ico cyan"><i class="bi bi-person-badge-fill"></i></span>
          <div>
            <div class="lh-1"><?= e($myBrShort ?: 'Branch') ?> Workforce &amp; Floor Team</div>
            <small class="text-muted fw-normal" style="font-size:0.71rem;font-family:'Outfit'">Receptionists, Accountants &amp; Coaches assigned to your facility</small>
          </div>
        </div>
        <div class="d-flex gap-1">
          <a href="<?= url('employees') ?>" class="btn btn-sm btn-outline-brand py-1">Manage Staff &rarr;</a>
          <a href="<?= url('trainers') ?>" class="btn btn-sm btn-outline-secondary py-1">Coaches</a>
        </div>
      </div>
      <div class="table-responsive position-relative" style="z-index:2">
        <table class="table table-hover table-pro-matrix align-middle mb-0">
          <thead>
            <tr>
              <th>Staff Member</th>
              <th>Role / Position</th>
              <th>Login</th>
              <th>Status</th>
              <th class="text-end">Contact</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($branchTeam as $bt): ?>
            <tr>
              <td>
                <div class="fw-bold"><?= e($bt['full_name']) ?></div>
                <small class="text-muted"><i class="bi bi-telephone me-1"></i><?= e($bt['phone']) ?></small>
              </td>
              <td>
                <div class="fw-semibold small"><?= e($bt['position']) ?></div>
                <span class="badge text-bg-light border" style="font-size:0.65rem"><?= e($bt['role_label']) ?></span>
              </td>
              <td>
                <?php if (!empty($bt['username'])): ?>
                  <span class="badge text-bg-light border font-monospace"><i class="bi bi-person-check-fill text-success me-1"></i><?= e($bt['username']) ?></span>
                <?php else: ?>
                  <span class="text-muted small">—</span>
                <?php endif; ?>
              </td>
              <td><?= badge($bt['status']) ?></td>
              <td class="text-end">
                <a href="<?= e(whatsapp_url($bt['phone'], "Asc {$bt['full_name']}, fariin ka socota Manager-ka " . ($myBrShort ?: 'CAATO GYM'))) ?>" target="_blank" class="btn btn-sm btn-whatsapp py-1 px-2">
                  <i class="bi bi-whatsapp"></i>
                </a>
              </td>
            </tr>
          <?php endforeach; if (!$branchTeam): ?>
            <tr><td colspan="5" class="text-center text-muted py-4"><i class="bi bi-people fs-3 d-block mb-1"></i>No branch staff registered yet.</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="col-xl-6">
    <div class="card card-bento-panel h-100">
      <i class="bi bi-receipt-cutoff panel-watermark"></i>
      <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div class="d-flex align-items-center gap-2">
          <span class="panel-hdr-ico emerald"><i class="bi bi-receipt-cutoff"></i></span>
          <div>
            <div class="lh-1">Recent <?= e($myBrShort ?: 'Branch') ?> Verified Receipts</div>
            <small class="text-muted fw-normal" style="font-size:0.71rem;font-family:'Outfit'">Latest Membership &amp; Gateway Collections at Your Facility</small>
          </div>
        </div>
        <div class="d-flex gap-1">
          <a href="<?= url('payments') ?>" class="btn btn-sm btn-outline-brand py-1">All Payments &rarr;</a>
          <a href="<?= url('pos') ?>" class="btn btn-sm btn-outline-secondary py-1">POS Bar</a>
        </div>
      </div>
      <div class="table-responsive position-relative" style="z-index:2">
        <table class="table table-hover table-pro-matrix align-middle mb-0">
          <thead>
            <tr>
              <th>Receipt #</th>
              <th>Member</th>
              <th>Channel</th>
              <th>Amount</th>
              <th class="text-end">Time</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($branchTxns as $tx): ?>
            <tr>
              <td><a href="<?= url('receipt', ['id' => $tx['id']]) ?>" class="fw-bold font-monospace small"><i class="bi bi-receipt me-1"></i><?= e($tx['ref_no']) ?></a></td>
              <td class="fw-semibold"><?= e($tx['title']) ?></td>
              <td><span class="badge text-bg-secondary"><?= e($tx['channel']) ?></span></td>
              <td><span class="matrix-rev-month"><?= money($tx['amount']) ?></span></td>
              <td class="text-end"><small class="text-muted"><?= fmt_dt($tx['dt']) ?></small></td>
            </tr>
          <?php endforeach; if (!$branchTxns): ?>
            <tr><td colspan="5" class="text-center text-muted py-4"><i class="bi bi-cash-stack fs-3 d-block mb-1"></i>No branch payments recorded yet.</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<?php elseif ($role === 'trainer'): ?>
<!-- Trainer Dedicated 3D Coaching Command Matrix: Branch Athletes & InBody Transformation Logs -->
<div class="row g-3 mb-4">
  <div class="col-xl-6">
    <div class="card card-bento-panel h-100">
      <i class="bi bi-trophy-fill panel-watermark"></i>
      <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div class="d-flex align-items-center gap-2">
          <span class="panel-hdr-ico amber"><i class="bi bi-trophy-fill"></i></span>
          <div>
            <div class="lh-1"><?= e($myBrShort ?: 'Branch') ?> Athletes &amp; Personal Coaching Roster</div>
            <small class="text-muted fw-normal" style="font-size:0.71rem;font-family:'Outfit'">Fitness Goals, Assigned Coaches &amp; Latest Weight/BMI</small>
          </div>
        </div>
        <a href="<?= url('customers') ?>" class="btn btn-sm btn-outline-brand py-1">All Athletes &rarr;</a>
      </div>
      <div class="table-responsive position-relative" style="z-index:2">
        <table class="table table-hover table-pro-matrix align-middle mb-0">
          <thead>
            <tr>
              <th>Athlete</th>
              <th>Fitness Goal</th>
              <th>Coach</th>
              <th>Weight / BMI</th>
              <th class="text-end">360° / Chat</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($trainerAthletes as $ta):
            [$taBmiLbl, $taBmiCol] = bmi_status(!empty($ta['last_bmi']) ? (float) $ta['last_bmi'] : null);
          ?>
            <tr>
              <td>
                <div class="d-flex align-items-center gap-2">
                  <img src="<?= photo_url($ta['profile_picture']) ?>" class="avatar" style="width:36px;height:36px">
                  <div>
                    <a class="fw-bold" href="<?= url('customer_view', ['id' => $ta['id']]) ?>"><?= e($ta['full_name']) ?></a>
                    <small class="text-muted d-block font-monospace"><?= e($ta['customer_code']) ?></small>
                  </div>
                </div>
              </td>
              <td><span class="badge text-bg-light border"><i class="bi bi-bullseye text-brand me-1"></i><?= e($ta['fitness_goal'] ?: 'General Fitness') ?></span></td>
              <td><small class="fw-semibold"><i class="bi bi-trophy-fill text-warning me-1"></i><?= e($ta['trainer_name'] ?: 'Floor Coach') ?></small></td>
              <td>
                <?php if (!empty($ta['last_weight'])): ?>
                  <span class="fw-bold"><?= (float) $ta['last_weight'] ?> kg</span>
                  <?php if (!empty($ta['last_bmi'])): ?>
                    <span class="badge text-bg-<?= $taBmiCol ?> ms-1">BMI <?= (float) $ta['last_bmi'] ?></span>
                  <?php endif; ?>
                <?php else: ?>
                  <span class="text-muted small">No check yet</span>
                <?php endif; ?>
              </td>
              <td class="text-end text-nowrap">
                <a href="<?= e(whatsapp_url($ta['phone'], "Asc {$ta['full_name']}, waa tababarahaaga CAATO FITNESS GYM! Sidee u socdaa tababarkaagu?")) ?>" target="_blank" class="btn btn-sm btn-whatsapp py-1 px-2" title="WhatsApp Athlete">
                  <i class="bi bi-whatsapp"></i>
                </a>
                <a href="<?= url('customer_view', ['id' => $ta['id']]) ?>" class="btn btn-sm btn-outline-brand py-1 px-2" title="Open 360° Fitness Dossier">
                  <i class="bi bi-heart-pulse-fill"></i> 360°
                </a>
              </td>
            </tr>
          <?php endforeach; if (!$trainerAthletes): ?>
            <tr><td colspan="5" class="text-center text-muted py-4"><i class="bi bi-people fs-3 d-block mb-1"></i>No athletes registered in this branch yet.</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="col-xl-6">
    <div class="card card-bento-panel h-100">
      <i class="bi bi-heart-pulse-fill panel-watermark"></i>
      <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div class="d-flex align-items-center gap-2">
          <span class="panel-hdr-ico red"><i class="bi bi-heart-pulse-fill"></i></span>
          <div>
            <div class="lh-1">Recent InBody Weight &amp; BMI Transformation Log</div>
            <small class="text-muted fw-normal" style="font-size:0.71rem;font-family:'Outfit'">Latest Athlete Body Composition Assessments</small>
          </div>
        </div>
        <a href="<?= url('customers') ?>" class="btn btn-sm btn-outline-brand py-1"><i class="bi bi-plus-circle"></i> Log Assessment</a>
      </div>
      <div class="table-responsive position-relative" style="z-index:2">
        <table class="table table-hover table-pro-matrix align-middle mb-0">
          <thead>
            <tr>
              <th>Athlete</th>
              <th>Weight</th>
              <th>BMI Status</th>
              <th>Fat / Muscle</th>
              <th class="text-end">Date</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($recentMetrics as $rm):
            [$rmLbl, $rmCol] = bmi_status(!empty($rm['bmi']) ? (float) $rm['bmi'] : null);
          ?>
            <tr>
              <td>
                <div class="d-flex align-items-center gap-2">
                  <img src="<?= photo_url($rm['profile_picture']) ?>" class="avatar" style="width:34px;height:34px">
                  <div>
                    <a class="fw-bold" href="<?= url('customer_view', ['id' => $rm['cid']]) ?>"><?= e($rm['full_name']) ?></a>
                    <small class="text-muted d-block font-monospace"><?= e($rm['customer_code']) ?></small>
                  </div>
                </div>
              </td>
              <td><span class="matrix-num"><?= (float) $rm['weight_kg'] ?></span><span class="matrix-unit">kg</span></td>
              <td>
                <?php if (!empty($rm['bmi'])): ?>
                  <span class="badge text-bg-<?= $rmCol ?>"><?= (float) $rm['bmi'] ?> · <?= e($rmLbl) ?></span>
                <?php else: ?>
                  <span class="text-muted small">—</span>
                <?php endif; ?>
              </td>
              <td>
                <small class="fw-semibold">
                  <?= $rm['body_fat_pct'] ? (float) $rm['body_fat_pct'] . '% fat' : '—' ?>
                  <?= $rm['muscle_mass_kg'] ? ' · ' . (float) $rm['muscle_mass_kg'] . 'kg mus' : '' ?>
                </small>
              </td>
              <td class="text-end"><small class="text-muted"><?= fmt_date($rm['recorded_date']) ?></small></td>
            </tr>
          <?php endforeach; if (!$recentMetrics): ?>
            <tr><td colspan="5" class="text-center text-muted py-4"><i class="bi bi-speedometer2 fs-3 d-block mb-1"></i>No InBody or BMI assessments recorded yet. Open any athlete's <strong>360° Profile</strong> to log weight &amp; BMI.</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<?php elseif ($role === 'receptionist'): ?>
<!-- Receptionist Dedicated 3D Front-Desk Matrix: Unpaid Desk Balances Queue & Newly Enrolled Athletes -->
<div class="row g-3 mb-4">
  <div class="col-xl-6">
    <div class="card card-bento-panel h-100">
      <i class="bi bi-cash-coin panel-watermark"></i>
      <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div class="d-flex align-items-center gap-2">
          <span class="panel-hdr-ico red"><i class="bi bi-exclamation-octagon-fill"></i></span>
          <div>
            <div class="lh-1">Unpaid Desk Balances &amp; Gate Hold Queue</div>
            <small class="text-muted fw-normal" style="font-size:0.71rem;font-family:'Outfit'">Members blocked at the door turnstile until balance is collected</small>
          </div>
        </div>
        <a href="<?= url('payments', ['tab' => 'pending']) ?>" class="btn btn-sm btn-outline-brand py-1">Collect All &rarr;</a>
      </div>
      <div class="table-responsive position-relative" style="z-index:2">
        <table class="table table-hover table-pro-matrix align-middle mb-0">
          <thead>
            <tr>
              <th>Member</th>
              <th>Plan</th>
              <th>Paid</th>
              <th>Balance Due</th>
              <th class="text-end">Desk Action</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($deskPending as $dp):
            $dpBal = round((float) $dp['price'] - (float) $dp['amount_paid'], 2);
            $dpWa  = "Asc {$dp['full_name']} ({$dp['customer_code']}), xasuusin ka socota Reception-ka CAATO FITNESS GYM: baaqiga xubinnimadaada ({$dp['plan_name']}) waa " . money($dpBal) . ". Fadlan bixi si albaabka biometric-gu kuugu furmo.";
          ?>
            <tr>
              <td>
                <div class="d-flex align-items-center gap-2">
                  <img src="<?= photo_url($dp['profile_picture']) ?>" class="avatar" style="width:36px;height:36px">
                  <div>
                    <a class="fw-bold" href="<?= url('customer_view', ['id' => $dp['cid']]) ?>"><?= e($dp['full_name']) ?></a>
                    <small class="text-muted d-block font-monospace"><?= e($dp['customer_code']) ?></small>
                  </div>
                </div>
              </td>
              <td><span class="badge text-bg-light border"><?= e($dp['plan_name']) ?></span></td>
              <td class="text-success fw-semibold"><?= money($dp['amount_paid']) ?></td>
              <td><span class="fw-bold text-danger"><?= money($dpBal) ?></span></td>
              <td class="text-end text-nowrap">
                <a href="<?= e(whatsapp_url($dp['phone'], $dpWa)) ?>" target="_blank" class="btn btn-sm btn-whatsapp py-1 px-2" title="Send WhatsApp Balance Reminder">
                  <i class="bi bi-whatsapp"></i>
                </a>
                <a href="<?= url('customer_view', ['id' => $dp['cid']]) ?>" class="btn btn-sm btn-brand py-1 px-2">
                  <i class="bi bi-cash-coin"></i> Collect
                </a>
              </td>
            </tr>
          <?php endforeach; if (!$deskPending): ?>
            <tr><td colspan="5" class="text-center text-muted py-4"><i class="bi bi-check2-circle text-success fs-3 d-block mb-1"></i>Zero unpaid desk balances at <?= e($myBrShort ?: 'your branch') ?>! All members are cleared.</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="col-xl-6">
    <div class="card card-bento-panel h-100">
      <i class="bi bi-person-vcard-fill panel-watermark"></i>
      <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div class="d-flex align-items-center gap-2">
          <span class="panel-hdr-ico cyan"><i class="bi bi-person-vcard-fill"></i></span>
          <div>
            <div class="lh-1">Latest Enrolled Members &amp; Digital PVC Cards</div>
            <small class="text-muted fw-normal" style="font-size:0.71rem;font-family:'Outfit'">Recent front-desk registrations, biometric tokens &amp; QR cards</small>
          </div>
        </div>
        <a href="<?= url('customer_register') ?>" class="btn btn-sm btn-brand py-1"><i class="bi bi-person-plus-fill"></i> Enroll New</a>
      </div>
      <div class="table-responsive position-relative" style="z-index:2">
        <table class="table table-hover table-pro-matrix align-middle mb-0">
          <thead>
            <tr>
              <th>Member</th>
              <th>Plan Tier</th>
              <th>Biometric Gate</th>
              <th>Status</th>
              <th class="text-end">Card / 360°</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($deskRecentMembers as $dm): ?>
            <tr>
              <td>
                <div class="d-flex align-items-center gap-2">
                  <img src="<?= photo_url($dm['profile_picture']) ?>" class="avatar" style="width:36px;height:36px">
                  <div>
                    <a class="fw-bold" href="<?= url('customer_view', ['id' => $dm['id']]) ?>"><?= e($dm['full_name']) ?></a>
                    <small class="text-muted d-block font-monospace"><?= e($dm['customer_code']) ?></small>
                  </div>
                </div>
              </td>
              <td><span class="badge text-bg-light border"><?= e($dm['plan_name'] ?: 'No Plan') ?></span></td>
              <td>
                <?php if (!empty($dm['fingerprint_id'])): ?>
                  <span class="badge text-bg-success"><i class="bi bi-fingerprint"></i> <?= e($dm['fingerprint_id']) ?></span>
                <?php else: ?>
                  <a href="<?= url('customer_view', ['id' => $dm['id']]) ?>" class="badge text-bg-warning text-decoration-none"><i class="bi bi-plus-circle"></i> Enroll FP</a>
                <?php endif; ?>
              </td>
              <td><?= badge($dm['ms_status'] ?: 'none') ?></td>
              <td class="text-end text-nowrap">
                <a href="<?= url('customer_card', ['id' => $dm['id']]) ?>" target="_blank" class="btn btn-sm btn-outline-secondary py-1 px-2" title="Print Digital PVC ID Card">
                  <i class="bi bi-person-vcard-fill"></i>
                </a>
                <a href="<?= url('customer_view', ['id' => $dm['id']]) ?>" class="btn btn-sm btn-outline-brand py-1 px-2" title="Open 360° Profile">
                  360°
                </a>
              </td>
            </tr>
          <?php endforeach; if (!$deskRecentMembers): ?>
            <tr><td colspan="5" class="text-center text-muted py-4"><i class="bi bi-person-plus fs-3 d-block mb-1"></i>No members enrolled yet. Click <strong>Enroll New</strong> to register your first member.</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<?php elseif ($role === 'accountant'): ?>
<!-- Accountant Dedicated 3D Treasury & Expenditure Audit Centerpiece Matrix -->
<div class="row g-3 mb-4">
  <div class="col-xl-6">
    <div class="card card-bento-panel h-100">
      <i class="bi bi-receipt-cutoff panel-watermark"></i>
      <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div class="d-flex align-items-center gap-2">
          <span class="panel-hdr-ico emerald"><i class="bi bi-receipt-cutoff"></i></span>
          <div>
            <div class="lh-1">Verified Gateway Receipts &amp; Audit Trail</div>
            <small class="text-muted fw-normal" style="font-size:0.71rem;font-family:'Outfit'">EVC Plus, Salaam Bank &amp; E-Dahab Reference-Verified Collections</small>
          </div>
        </div>
        <div class="d-flex gap-1">
          <a href="<?= url('payments') ?>" class="btn btn-sm btn-outline-brand py-1">Full Ledger &rarr;</a>
          <a href="<?= url('reports', ['type' => 'daily_payments', 'run' => 1]) ?>" class="btn btn-sm btn-outline-secondary py-1">Export</a>
        </div>
      </div>
      <div class="table-responsive position-relative" style="z-index:2">
        <table class="table table-hover table-pro-matrix align-middle mb-0">
          <thead>
            <tr>
              <th>Receipt #</th>
              <th>Member</th>
              <th>Channel &amp; Ref</th>
              <th>Amount</th>
              <th class="text-end">Receipt</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($accRecentPayments as $ap): ?>
            <tr>
              <td><a href="<?= url('receipt', ['id' => $ap['id']]) ?>" class="fw-bold font-monospace small"><i class="bi bi-file-earmark-text me-1"></i><?= e($ap['receipt_no']) ?></a></td>
              <td>
                <a class="fw-semibold" href="<?= url('customer_view', ['id' => $ap['cid']]) ?>"><?= e($ap['full_name']) ?></a>
                <small class="text-muted d-block font-monospace"><?= e($ap['customer_code']) ?></small>
              </td>
              <td>
                <span class="badge text-bg-secondary"><?= e($ap['method_name']) ?></span>
                <?= $ap['reference'] ? '<small class="text-muted font-monospace d-block">Ref: ' . e($ap['reference']) . '</small>' : '' ?>
              </td>
              <td><span class="matrix-rev-month"><?= money($ap['amount']) ?></span></td>
              <td class="text-end text-nowrap">
                <a href="<?= url('receipt', ['id' => $ap['id']]) ?>" class="btn btn-sm btn-outline-brand py-1 px-2" title="View / Print Receipt">
                  <i class="bi bi-printer-fill"></i>
                </a>
                <a href="<?= url('receipt', ['id' => $ap['id'], 'format' => 'pdf']) ?>" class="btn btn-sm btn-outline-secondary py-1 px-2" title="Download PDF">
                  <i class="bi bi-file-earmark-pdf-fill text-danger"></i>
                </a>
              </td>
            </tr>
          <?php endforeach; if (!$accRecentPayments): ?>
            <tr><td colspan="5" class="text-center text-muted py-4"><i class="bi bi-receipt fs-3 d-block mb-1"></i>No verified payment transactions recorded yet.</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="col-xl-6">
    <div class="card card-bento-panel h-100">
      <i class="bi bi-safe2-fill panel-watermark"></i>
      <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div class="d-flex align-items-center gap-2">
          <span class="panel-hdr-ico amber"><i class="bi bi-safe2-fill"></i></span>
          <div>
            <div class="lh-1">Operating Expenses &amp; Cost Outflow Matrix</div>
            <small class="text-muted fw-normal" style="font-size:0.71rem;font-family:'Outfit'">Facility Rent, Utilities, Maintenance &amp; Payroll Outflows</small>
          </div>
        </div>
        <a href="<?= url('expenses') ?>" class="btn btn-sm btn-brand py-1"><i class="bi bi-plus-circle-fill"></i> Log Expense</a>
      </div>
      <div class="table-responsive position-relative" style="z-index:2">
        <table class="table table-hover table-pro-matrix align-middle mb-0">
          <thead>
            <tr>
              <th>Date</th>
              <th>Category</th>
              <th>Description</th>
              <th>Logged By</th>
              <th class="text-end">Amount</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($accRecentExpenses as $ax): ?>
            <tr>
              <td><small class="text-muted"><i class="bi bi-calendar3 me-1"></i><?= fmt_date($ax['expense_date']) ?></small></td>
              <td><span class="badge text-bg-light border"><?= e($ax['category']) ?></span></td>
              <td class="text-truncate" style="max-width:170px"><?= e($ax['description'] ?: '—') ?></td>
              <td><small class="text-muted"><?= e($ax['by_name'] ?: 'Staff') ?></small></td>
              <td class="text-end"><span class="fw-bold text-danger"><?= money($ax['amount']) ?></span></td>
            </tr>
          <?php endforeach; if (!$accRecentExpenses): ?>
            <tr><td colspan="5" class="text-center text-muted py-4"><i class="bi bi-pie-chart fs-3 d-block mb-1"></i>No operating expenses logged yet. Click <strong>Log Expense</strong> to record costs.</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- Role-Scoped Visual Intelligence Charts -->
<div class="row g-3 mb-4">
  <?php if (in_array($role, ['super_admin', 'branch_manager', 'accountant'], true)): ?>
  <div class="col-lg-6">
    <div class="card card-bento-panel h-100">
      <i class="bi bi-bar-chart-fill panel-watermark"></i>
      <div class="card-header">
        <div class="d-flex align-items-center gap-2">
          <span class="panel-hdr-ico emerald"><i class="bi bi-bar-chart-fill"></i></span>
          <div>
            <div class="lh-1">Gross Revenue vs Operating Expenses</div>
            <small class="text-muted fw-normal" style="font-size:0.71rem;font-family:'Outfit'">12-Month Financial Trajectory (Memberships + POS Bar)</small>
          </div>
        </div>
        <span class="badge text-bg-success">12-Month Flow</span>
      </div>
      <div class="card-body position-relative" style="z-index:2"><div class="chart-box"><canvas id="cRevenue"></canvas></div></div>
    </div>
  </div>
  <?php endif; ?>

  <?php if (Auth::can('attendance.view')): ?>
  <div class="col-lg-6">
    <div class="card card-bento-panel h-100">
      <i class="bi bi-activity panel-watermark"></i>
      <div class="card-header">
        <div class="d-flex align-items-center gap-2">
          <span class="panel-hdr-ico red"><i class="bi bi-activity"></i></span>
          <div>
            <div class="lh-1">Biometric &amp; QR Attendance Velocity</div>
            <small class="text-muted fw-normal" style="font-size:0.71rem;font-family:'Outfit'">14-Day Turnstile Check-in Volume</small>
          </div>
        </div>
        <span class="badge text-bg-info">Door Traffic</span>
      </div>
      <div class="card-body position-relative" style="z-index:2"><div class="chart-box"><canvas id="cAtt"></canvas></div></div>
    </div>
  </div>
  <?php endif; ?>

  <?php if (in_array($role, ['super_admin', 'branch_manager', 'receptionist'], true)): ?>
  <div class="col-lg-6">
    <div class="card card-bento-panel h-100">
      <i class="bi bi-person-lines-fill panel-watermark"></i>
      <div class="card-header">
        <div class="d-flex align-items-center gap-2">
          <span class="panel-hdr-ico cyan"><i class="bi bi-person-lines-fill"></i></span>
          <div>
            <div class="lh-1">Monthly Member Acquisition</div>
            <small class="text-muted fw-normal" style="font-size:0.71rem;font-family:'Outfit'">New Athlete Onboarding Velocity</small>
          </div>
        </div>
        <span class="badge text-bg-info">Growth</span>
      </div>
      <div class="card-body position-relative" style="z-index:2"><div class="chart-box"><canvas id="cReg"></canvas></div></div>
    </div>
  </div>
  <?php endif; ?>

  <div class="col-lg-6">
    <div class="card card-bento-panel h-100">
      <i class="bi bi-pie-chart-fill panel-watermark"></i>
      <div class="card-header">
        <div class="d-flex align-items-center gap-2">
          <span class="panel-hdr-ico amber"><i class="bi bi-pie-chart-fill"></i></span>
          <div>
            <div class="lh-1">Subscription Tier Distribution</div>
            <small class="text-muted fw-normal" style="font-size:0.71rem;font-family:'Outfit'">Popularity Across Membership Plans</small>
          </div>
        </div>
        <span class="badge text-bg-warning">Plans Mix</span>
      </div>
      <div class="card-body position-relative" style="z-index:2"><div class="chart-box"><canvas id="cPlan"></canvas></div></div>
    </div>
  </div>

  <?php if ($role === 'super_admin'): ?>
  <div class="col-lg-6">
    <div class="card card-bento-panel h-100">
      <i class="bi bi-buildings-fill panel-watermark"></i>
      <div class="card-header">
        <div class="d-flex align-items-center gap-2">
          <span class="panel-hdr-ico violet"><i class="bi bi-buildings-fill"></i></span>
          <div>
            <div class="lh-1">6-Branch Monthly Revenue vs Visits</div>
            <small class="text-muted fw-normal" style="font-size:0.71rem;font-family:'Outfit'">Cross-Facility Financial &amp; Attendance Correlation</small>
          </div>
        </div>
      </div>
      <div class="card-body position-relative" style="z-index:2"><div class="chart-box"><canvas id="cBranch"></canvas></div></div>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="card card-bento-panel h-100">
      <i class="bi bi-cash-coin panel-watermark"></i>
      <div class="card-header">
        <div class="d-flex align-items-center gap-2">
          <span class="panel-hdr-ico emerald"><i class="bi bi-cash-coin"></i></span>
          <div>
            <div class="lh-1">Cumulative Revenue Share by Branch</div>
            <small class="text-muted fw-normal" style="font-size:0.71rem;font-family:'Outfit'">All-Time Revenue Contribution per Facility Node</small>
          </div>
        </div>
      </div>
      <div class="card-body position-relative" style="z-index:2"><div class="chart-box"><canvas id="cBranchRev"></canvas></div></div>
    </div>
  </div>
  <?php endif; ?>
</div>

<?php if (Auth::can('classes.view') || Auth::can('equipment.view') || Auth::can('pos.view')): ?>
<!-- Gym Floor Operations: Group Classes Timetable & Equipment / POS Stock Alerts -->
<div class="row g-3 mb-4">
  <?php if (Auth::can('classes.view')): ?>
  <div class="col-lg-<?= (Auth::can('equipment.view') || Auth::can('pos.view')) ? '6' : '12' ?>">
    <div class="card card-bento-panel h-100">
      <i class="bi bi-calendar2-week-fill panel-watermark"></i>
      <div class="card-header d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2">
          <span class="panel-hdr-ico brand"><i class="bi bi-calendar2-week-fill"></i></span>
          <div>
            <div class="lh-1">Active Group Workout Classes</div>
            <small class="text-muted fw-normal" style="font-size:0.71rem;font-family:'Outfit'">Scheduled Studio &amp; Floor Sessions</small>
          </div>
        </div>
        <a href="<?= url('classes') ?>" class="btn btn-sm btn-outline-brand py-1">Timetable &rarr;</a>
      </div>
      <div class="table-responsive position-relative" style="z-index:2">
        <table class="table table-hover table-pro-matrix mb-0 align-middle">
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
              <td><span class="branch-code-tile" style="width:32px;height:26px;font-size:0.72rem"><?= e($ac['branch_code']) ?></span></td>
            </tr>
          <?php endforeach; if (!$activeClasses): ?>
            <tr><td colspan="4" class="text-muted p-4 text-center"><i class="bi bi-calendar2-check fs-3 d-block mb-1"></i> No group classes scheduled yet.</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <?php if (Auth::can('equipment.view') || Auth::can('pos.view')): ?>
  <div class="col-lg-<?= Auth::can('classes.view') ? '6' : '12' ?>">
    <div class="card card-bento-panel h-100">
      <i class="bi bi-tools panel-watermark"></i>
      <div class="card-header d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2">
          <span class="panel-hdr-ico amber"><i class="bi bi-tools"></i></span>
          <div>
            <div class="lh-1"><?= Auth::can('equipment.view') ? 'Equipment Alerts' : 'Inventory Monitor' ?><?= Auth::can('pos.view') ? ' &amp; POS Stock' : '' ?></div>
            <small class="text-muted fw-normal" style="font-size:0.71rem;font-family:'Outfit'">Hardware Maintenance &amp; Supplement Bar Alerts</small>
          </div>
        </div>
        <div class="d-flex gap-1">
          <?php if (Auth::can('equipment.view')): ?><a href="<?= url('equipment') ?>" class="btn btn-sm btn-outline-secondary py-1">Gear</a><?php endif; ?>
          <?php if (Auth::can('pos.view')): ?><a href="<?= url('pos') ?>" class="btn btn-sm btn-outline-secondary py-1">POS</a><?php endif; ?>
        </div>
      </div>
      <div class="card-body p-3 position-relative" style="z-index:2">
        <?php
        $showEq = Auth::can('equipment.view') ? $eqAlertList : [];
        $showLs = Auth::can('pos.view') ? $lowStockList : [];
        if (!$showEq && !$showLs): ?>
          <div class="text-center text-muted py-4">
            <i class="bi bi-shield-check text-success fs-2 d-block mb-1"></i>
            <strong>All Monitored Assets &amp; Inventory Healthy</strong>
            <div class="small">Zero warnings across your authorized scope.</div>
          </div>
        <?php else: ?>
          <?php foreach ($showEq as $eq): ?>
            <div class="p-2 px-3 mb-2 rounded-3 border d-flex justify-content-between align-items-center bg-body-tertiary">
              <div>
                <span class="badge text-bg-dark font-monospace me-1"><?= e($eq['branch_code']) ?></span>
                <strong><?= e($eq['name']) ?></strong>
                <small class="text-muted d-block"><?= e($eq['code']) ?> · <?= e($eq['notes'] ?: 'Requires technician attention') ?></small>
              </div>
              <?= badge($eq['condition_status']) ?>
            </div>
          <?php endforeach; ?>
          <?php foreach ($showLs as $ls): ?>
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
  <?php endif; ?>
</div>
<?php endif; ?>

<!-- Expiring Alerts (with 1-Click WhatsApp) & Live Door Check-ins / Accountant Receivables -->
<div class="row g-3">
  <div class="col-lg-<?= (Auth::can('attendance.view') || $role === 'accountant') ? '6' : '12' ?>">
    <div class="card card-bento-panel h-100">
      <i class="bi bi-hourglass-split panel-watermark"></i>
      <div class="card-header d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2">
          <span class="panel-hdr-ico red"><i class="bi bi-hourglass-split"></i></span>
          <div>
            <div class="lh-1">Memberships Expiring Soon</div>
            <small class="text-muted fw-normal" style="font-size:0.71rem;font-family:'Outfit'">1-Click Direct WhatsApp Renewal Alerts</small>
          </div>
        </div>
        <span class="badge text-bg-warning">Next <?= $days ?> Days</span>
      </div>
      <div class="table-responsive position-relative" style="z-index:2">
        <table class="table table-hover table-pro-matrix mb-0 align-middle">
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

  <?php if (Auth::can('attendance.view')): ?>
  <div class="col-lg-6">
    <div class="card card-bento-panel h-100">
      <i class="bi bi-fingerprint panel-watermark"></i>
      <div class="card-header d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2">
          <span class="panel-hdr-ico cyan"><i class="bi bi-fingerprint"></i></span>
          <div>
            <div class="lh-1">Recent Biometric &amp; Door Check-ins</div>
            <small class="text-muted fw-normal" style="font-size:0.71rem;font-family:'Outfit'">Live Turnstile Verification Feed</small>
          </div>
        </div>
        <a href="<?= url('attendance') ?>" class="btn btn-sm btn-outline-brand py-1">Open Door Console &rarr;</a>
      </div>
      <div class="table-responsive position-relative" style="z-index:2">
        <table class="table table-hover table-pro-matrix mb-0 align-middle">
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
                <span class="gate-visit-pill"><i class="bi bi-geo-alt-fill text-danger"></i> <?= e(short_branch($r['branch'])) ?></span>
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
  <?php elseif ($role === 'accountant'): ?>
  <div class="col-lg-6">
    <div class="card card-bento-panel h-100">
      <i class="bi bi-exclamation-octagon-fill panel-watermark"></i>
      <div class="card-header d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2">
          <span class="panel-hdr-ico amber"><i class="bi bi-exclamation-octagon-fill"></i></span>
          <div>
            <div class="lh-1">Outstanding Receivables &amp; Unpaid Accounts</div>
            <small class="text-muted fw-normal" style="font-size:0.71rem;font-family:'Outfit'">Pending Subscription Balances Requiring Settlement</small>
          </div>
        </div>
        <a href="<?= url('payments', ['tab' => 'pending']) ?>" class="btn btn-sm btn-outline-brand py-1">Receivables &rarr;</a>
      </div>
      <div class="table-responsive position-relative" style="z-index:2">
        <table class="table table-hover table-pro-matrix mb-0 align-middle">
          <thead><tr><th>Member</th><th>Plan</th><th>Balance Due</th><th class="text-end">Collect</th></tr></thead>
          <tbody>
          <?php foreach ($deskPending as $dp):
            $dpBal = round((float) $dp['price'] - (float) $dp['amount_paid'], 2);
            $dpWa  = "Asc {$dp['full_name']} ({$dp['customer_code']}), xasuusin ka socota Waaxda Maaliyadda CAATO FITNESS GYM: baaqiga xubinnimadaada ({$dp['plan_name']}) waa " . money($dpBal) . ". Fadlan bixi si kaadhkaagu u shaqeeyo.";
          ?>
            <tr>
              <td>
                <div class="d-flex align-items-center gap-2">
                  <img src="<?= photo_url($dp['profile_picture']) ?>" class="avatar" style="width:36px;height:36px">
                  <div>
                    <a class="fw-semibold" href="<?= url('customer_view', ['id' => $dp['cid']]) ?>"><?= e($dp['full_name']) ?></a>
                    <small class="text-muted d-block font-monospace"><?= e($dp['customer_code']) ?></small>
                  </div>
                </div>
              </td>
              <td><span class="badge text-bg-light border"><?= e($dp['plan_name']) ?></span></td>
              <td><span class="fw-bold text-danger"><?= money($dpBal) ?></span></td>
              <td class="text-end text-nowrap">
                <a href="<?= e(whatsapp_url($dp['phone'], $dpWa)) ?>" target="_blank" class="btn btn-sm btn-whatsapp py-1 px-2" title="Send WhatsApp Balance Reminder">
                  <i class="bi bi-whatsapp"></i>
                </a>
                <a href="<?= url('customer_view', ['id' => $dp['cid']]) ?>" class="btn btn-sm btn-brand py-1 px-2">
                  <i class="bi bi-cash-coin"></i> Settle
                </a>
              </td>
            </tr>
          <?php endforeach; if (!$deskPending): ?>
            <tr><td colspan="4" class="text-muted p-4 text-center"><i class="bi bi-check2-circle text-success fs-3 d-block mb-1"></i> Zero outstanding receivables. All accounts settled!</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <?php endif; ?>
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
