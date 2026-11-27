<?php
[$ok, $code, $msg, $lvl] = $access;
$balance = $c['ms_id'] ? round($c['ms_price'] - $c['amount_paid'], 2) : 0;
$latestMetric = $metrics[0] ?? null;
[$bmiLabel, $bmiColor] = bmi_status(isset($latestMetric['bmi']) ? (float) $latestMetric['bmi'] : null);

$waWelcome = "Asc {$c['full_name']}, kusoo dhawow CAATO FITNESS GYM! Lambarkaaga xubinnimadu waa {$c['customer_code']}. Kaadhkaagu wuxuu ka shaqaynayaa dhammaan 6-da laamood.";
$waRemind = $balance > 0
    ? "Asc {$c['full_name']} ({$c['customer_code']}), waxaan ku xasuusinaynaa baaqiga lacagta xubinnimadaada CAATO FITNESS GYM oo ah " . money($balance) . ". Fadlan bixi si kaadhkaagu u shaqeeyo. Mahadsanid!"
    : "Asc {$c['full_name']} ({$c['customer_code']}), xubinnimadaada CAATO FITNESS GYM ({$c['plan_name']}) waxay ku egtahay " . fmt_date($c['end_date']) . ". Fadlan cusboonaysii waqtigeeda. Mahadsanid!";

$totalDays = ($c['ms_id'] && $c['start_date'] && $c['end_date']) ? max(1, (int) round((strtotime($c['end_date']) - strtotime($c['start_date'])) / 86400) + 1) : 0;
$daysLeft = ($c['ms_id'] && $c['end_date']) ? max(0, (int) floor((strtotime($c['end_date']) - strtotime(date('Y-m-d'))) / 86400)) : 0;
$pctLeft = $totalDays > 0 ? min(100, max(0, (int) round(($daysLeft / $totalDays) * 100))) : 0;
?>
<?php
$cvActs = '<a class="btn btn-whatsapp btn-sm px-3 py-2" href="' . e(whatsapp_url($c['phone'], $waRemind)) . '" target="_blank"><i class="bi bi-whatsapp"></i> WhatsApp Alert</a>'
  . '<a class="btn btn-sm px-3 py-2" style="background:rgba(255,255,255,0.12);color:#fff;border:1px solid rgba(255,255,255,0.2)" href="' . url('customer_card', ['id' => $c['id']]) . '" target="_blank"><i class="bi bi-person-vcard-fill text-warning"></i> PVC ID Card</a>'
  . '<button type="button" class="btn btn-sm px-3 py-2" style="background:rgba(255,255,255,0.12);color:#fff;border:1px solid rgba(255,255,255,0.2)" data-bs-toggle="modal" data-bs-target="#coachModal"><i class="bi bi-trophy-fill text-warning"></i> Coach &amp; Goal</button>';
if (Auth::can('customers.edit')) {
  $cvActs .= '<button type="button" class="btn btn-sm px-3 py-2" style="background:rgba(255,255,255,0.12);color:#fff;border:1px solid rgba(255,255,255,0.2)" data-bs-toggle="modal" data-bs-target="#fpModal"><i class="bi bi-fingerprint text-success"></i> Biometric Token</button>'
    . '<a class="btn btn-sm px-3 py-2" style="background:rgba(255,255,255,0.12);color:#fff;border:1px solid rgba(255,255,255,0.2)" href="' . url('customer_edit', ['id' => $c['id']]) . '"><i class="bi bi-pencil-square text-info"></i> Edit Profile</a>';
}
if (Auth::can('attendance.checkin')) {
  $cvActs .= '<a class="btn btn-brand btn-sm px-3 py-2" href="' . url('attendance', ['q' => $c['customer_code']]) . '"><i class="bi bi-door-open-fill"></i> Door Check-in</a>';
}
$thirdMetric = Auth::can('payments.view')
  ? [money($balance), 'Balance Due', $balance > 0 ? 'danger' : 'success']
  : [$latestMetric ? (float) $latestMetric['weight_kg'] . ' kg' : '—', 'Current Weight', 'info'];
page_banner(
  '360° MEMBER PROFILE · ' . $c['customer_code'] . (!empty($c['fingerprint_id']) ? ' · BIOMETRIC ' . $c['fingerprint_id'] : ''),
  $c['full_name'],
  '',
  'Home Facility: ' . short_branch($c['reg_branch']) . ' · Fitness Goal: ' . ($c['fitness_goal'] ?? 'General Fitness'),
  $cvActs,
  [
    [(int) ($c['total_visits'] ?? 0), 'Total Visits', 'info'],
    [$daysLeft . 'd', 'Days Left', $daysLeft > 7 ? 'success' : 'warning'],
    $thirdMetric,
  ]
);
?>

<!-- 3D Holographic Athlete Telemetry Strip -->
<div class="row g-3 mb-4">
  <div class="col-6 col-xl-3">
    <div class="card stat stat-pro <?= $ok ? 'emerald' : 'red' ?> h-100">
      <div class="stat-glow-orb"></div>
      <i class="bi bi-patch-check-fill stat-watermark"></i>
      <div class="stat-top">
        <div class="ico"><i class="bi bi-<?= $ok ? 'unlock-fill' : 'lock-fill' ?>"></i></div>
        <div class="stat-meta-right">
          <span class="stat-pill"><span class="stat-dot"></span> Gate Status</span>
        </div>
      </div>
      <div class="stat-body">
        <div class="num fs-4"><?= e($c['plan_name'] ?: 'No Active Plan') ?></div>
        <div class="stat-title"><?= $daysLeft ?> DAYS REMAINING</div>
      </div>
      <div class="stat-foot">
        <div class="stat-spark"><div class="stat-spark-fill" style="width:<?= max(12, $pctLeft) ?>%"></div></div>
      </div>
    </div>
  </div>
  <div class="col-6 col-xl-3">
    <div class="card stat stat-pro brand h-100">
      <div class="stat-glow-orb"></div>
      <i class="bi bi-bullseye stat-watermark"></i>
      <div class="stat-top">
        <div class="ico"><i class="bi bi-bullseye"></i></div>
        <div class="stat-meta-right">
          <span class="stat-pill"><span class="stat-dot"></span> Program</span>
        </div>
      </div>
      <div class="stat-body">
        <div class="num fs-4 text-truncate"><?= e($c['fitness_goal'] ?? 'General Fitness') ?></div>
        <div class="stat-title text-truncate">COACH: <?= e(strtoupper($c['trainer_name'] ?? 'FLOOR COACH')) ?></div>
      </div>
      <div class="stat-foot">
        <div class="stat-spark"><div class="stat-spark-fill" style="width:85%"></div></div>
      </div>
    </div>
  </div>
  <div class="col-6 col-xl-3">
    <div class="card stat stat-pro amber h-100">
      <div class="stat-glow-orb"></div>
      <i class="bi bi-heart-pulse-fill stat-watermark"></i>
      <div class="stat-top">
        <div class="ico"><i class="bi bi-heart-pulse-fill"></i></div>
        <div class="stat-meta-right">
          <span class="stat-pill"><span class="stat-dot"></span> InBody / BMI</span>
        </div>
      </div>
      <div class="stat-body">
        <div class="num fs-4"><?= $latestMetric ? (float) $latestMetric['weight_kg'] . ' kg' : 'Not Logged' ?></div>
        <div class="stat-title"><?= $latestMetric && $latestMetric['bmi'] ? 'BMI ' . $latestMetric['bmi'] . ' · ' . strtoupper($bmiLabel) : 'RECORD BASELINE CHECK' ?></div>
      </div>
      <div class="stat-foot">
        <div class="stat-spark"><div class="stat-spark-fill" style="width:<?= $latestMetric ? 90 : 25 ?>%"></div></div>
      </div>
    </div>
  </div>
  <div class="col-6 col-xl-3">
    <div class="card stat stat-pro cyan h-100">
      <div class="stat-glow-orb"></div>
      <i class="bi bi-fingerprint stat-watermark"></i>
      <div class="stat-top">
        <div class="ico"><i class="bi bi-fingerprint"></i></div>
        <div class="stat-meta-right">
          <span class="stat-pill"><span class="stat-dot"></span> Attendance</span>
        </div>
      </div>
      <div class="stat-body">
        <div class="num fs-4"><?= (int) ($c['total_visits'] ?? 0) ?> Visits</div>
        <div class="stat-title"><?= !empty($c['fingerprint_id']) ? 'TOKEN: ' . e($c['fingerprint_id']) : 'QR & DESK ACCESS' ?></div>
      </div>
      <div class="stat-foot">
        <div class="stat-spark"><div class="stat-spark-fill" style="width:88%"></div></div>
      </div>
    </div>
  </div>
</div>

<div class="row g-3 mb-4">
  <!-- Identity & Biometric Card -->
  <div class="col-lg-5">
    <div class="card card-bento-panel h-100 shadow-sm">
      <i class="bi bi-person-vcard-fill panel-watermark"></i>
      <div class="card-body p-4 position-relative" style="z-index:2">
        <div class="d-flex gap-3 align-items-center">
          <img class="avatar-lg" src="<?= photo_url($c['profile_picture']) ?>" alt="">
          <div class="overflow-hidden">
            <h3 class="mb-0 fw-bold"><?= e($c['full_name']) ?></h3>
            <div class="text-muted small font-monospace"><i class="bi bi-upc-scan me-1"></i><?= e($c['customer_code']) ?> · <?= (int) ($c['total_visits'] ?? 0) ?> Total Visits</div>
            <div class="mt-2 d-flex flex-wrap gap-1">
              <?= badge($c['eff_status']) ?>
              <?= $c['status'] === 'blocked' ? badge('blocked') : '' ?>
              <span class="badge text-bg-info"><i class="bi bi-bullseye"></i> <?= e($c['fitness_goal'] ?? 'General Fitness') ?></span>
            </div>
          </div>
        </div>
        <hr class="my-3 opacity-25">
        <dl class="row small mb-0 g-2">
          <dt class="col-5 text-muted"><i class="bi bi-telephone me-1"></i> Phone</dt>
          <dd class="col-7 fw-semibold">
            <?= e($c['phone']) ?>
            <a href="<?= e(whatsapp_url($c['phone'], $waWelcome)) ?>" target="_blank" class="text-success ms-1" title="Send Welcome Message"><i class="bi bi-whatsapp"></i></a>
          </dd>
          <dt class="col-5 text-muted"><i class="bi bi-envelope me-1"></i> Email</dt>
          <dd class="col-7"><?= e($c['email'] ?: '—') ?></dd>
          <dt class="col-5 text-muted"><i class="bi bi-person me-1"></i> Gender / DOB</dt>
          <dd class="col-7"><?= ucfirst($c['gender']) ?> · <?= fmt_date($c['date_of_birth']) ?></dd>
          <dt class="col-5 text-muted"><i class="bi bi-trophy me-1 text-warning"></i> Personal Coach</dt>
          <dd class="col-7 fw-semibold"><?= e($c['trainer_name'] ?? 'Self-Guided / Floor Coach') ?></dd>
          <dt class="col-5 text-muted"><i class="bi bi-heart-pulse me-1 text-danger"></i> Medical Notes</dt>
          <dd class="col-7"><?= e($c['medical_notes'] ?: 'None reported') ?></dd>
          <dt class="col-5 text-muted"><i class="bi bi-geo-alt me-1"></i> Address</dt>
          <dd class="col-7"><?= e($c['address'] ?: '—') ?></dd>
          <dt class="col-5 text-muted"><i class="bi bi-shield-exclamation me-1"></i> Emergency</dt>
          <dd class="col-7"><?= e(trim(($c['emergency_name'] ?? '') . ' ' . ($c['emergency_phone'] ?? '')) ?: '—') ?></dd>
          <dt class="col-5 text-muted"><i class="bi bi-building me-1"></i> Home Facility</dt>
          <dd class="col-7"><span class="badge text-bg-secondary"><i class="bi bi-geo-alt-fill text-danger"></i> <?= e(short_branch($c['reg_branch'])) ?></span> · <?= fmt_date($c['registration_date']) ?></dd>
          <dt class="col-5 text-muted"><i class="bi bi-clock-history me-1"></i> Last Door Entry</dt>
          <dd class="col-7"><?= fmt_dt($c['last_attendance']) ?></dd>
        </dl>
      </div>
    </div>
  </div>

  <!-- Membership, Countdown & Access Control Card -->
  <div class="col-lg-7">
    <div class="card card-bento-panel h-100 shadow-sm">
      <i class="bi bi-award-fill panel-watermark"></i>
      <div class="card-header d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2">
          <span class="panel-hdr-ico brand"><i class="bi bi-award-fill"></i></span>
          <div>
            <div class="lh-1">Active Subscription &amp; 6-Branch Gate Clearance</div>
            <small class="text-muted fw-normal" style="font-size:0.71rem;font-family:'Outfit'">Universal Turnstile Access &amp; Billing Status</small>
          </div>
        </div>
        <?php if ($c['ms_id'] && $c['eff_status'] === 'active'): ?>
          <span class="badge text-bg-success"><?= $daysLeft ?> Days Remaining</span>
        <?php endif; ?>
      </div>
      <div class="card-body p-4 position-relative" style="z-index:2">
        <div class="access-box <?= $lvl ?> mb-3 shadow-sm">
          <div class="d-flex align-items-center gap-2 fs-6 fw-bold">
            <?= $ok ? '<i class="bi bi-unlock-fill fs-4 text-success"></i> DOOR CLEARANCE GRANTED (Universal 6-Branch Access)' : '<i class="bi bi-lock-fill fs-4 text-danger"></i> DOOR CLEARANCE RESTRICTED / LOCKED' ?>
          </div>
          <div class="mt-1 small"><?= e($msg) ?></div>
        </div>

        <?php if ($c['ms_id']): ?>
        <div class="p-3 rounded-3 bg-body-tertiary border mb-3">
          <div class="row g-2">
            <div class="col-6 col-md-4">
              <small class="text-muted d-block"><i class="bi bi-tag-fill me-1"></i> Active Tier</small>
              <strong class="fs-6"><?= e($c['plan_name']) ?></strong>
            </div>
            <div class="col-6 col-md-4">
              <small class="text-muted d-block"><i class="bi bi-cash-stack me-1"></i> Payment Status</small>
              <div><?= badge($c['payment_status']) ?> <span class="fw-semibold"><?= money($c['amount_paid']) ?></span> / <?= money($c['ms_price']) ?></div>
            </div>
            <div class="col-6 col-md-4">
              <small class="text-muted d-block"><i class="bi bi-activity me-1"></i> Gate State</small>
              <div><?= badge($c['eff_status']) ?></div>
            </div>
            <div class="col-6 col-md-4 mt-2">
              <small class="text-muted d-block"><i class="bi bi-calendar-check me-1"></i> Start Date</small>
              <span class="fw-semibold"><?= fmt_date($c['start_date']) ?></span>
            </div>
            <div class="col-6 col-md-4 mt-2">
              <small class="text-muted d-block"><i class="bi bi-calendar-x me-1"></i> Expiry Date</small>
              <span class="fw-semibold"><?= fmt_date($c['end_date']) ?></span>
            </div>
            <div class="col-6 col-md-4 mt-2">
              <small class="text-muted d-block"><i class="bi bi-wallet2 me-1"></i> Balance Due</small>
              <strong class="<?= $balance > 0 ? 'text-danger' : 'text-success' ?>"><?= money($balance) ?></strong>
            </div>
          </div>

          <?php if ($totalDays > 0): ?>
          <div class="mt-3 pt-2 border-top">
            <div class="d-flex justify-content-between small mb-1">
              <span class="text-muted">Subscription Cycle Progress</span>
              <span class="fw-bold"><?= $daysLeft ?> of <?= $totalDays ?> days left (<?= $pctLeft ?>%)</span>
            </div>
            <div class="progress" style="height:8px;border-radius:99px">
              <div class="progress-bar bg-<?= $pctLeft > 25 ? 'success' : ($pctLeft > 10 ? 'warning' : 'danger') ?>" style="width:<?= $pctLeft ?>%"></div>
            </div>
          </div>
          <?php endif; ?>
        </div>
        <?php endif; ?>

        <div class="d-flex flex-wrap gap-2 pt-1">
          <?php if (Auth::can('memberships.renew')): ?>
            <button class="btn btn-brand btn-sm" data-bs-toggle="modal" data-bs-target="#renewModal">
              <i class="bi bi-arrow-repeat"></i> Sell / Renew Plan
            </button>
          <?php endif; ?>

          <?php if ($c['ms_id'] && $balance > 0 && Auth::can('payments.create') && $c['ms_status'] !== 'cancelled'): ?>
            <button class="btn btn-accent btn-sm" data-pay="<?= $c['ms_id'] ?>" data-balance="<?= $balance ?>" data-who="<?= e($c['full_name']) ?>">
              <i class="bi bi-cash-coin"></i> Record Payment (<?= money($balance) ?>)
            </button>
          <?php endif; ?>

          <?php if ($c['ms_id'] && Auth::can('memberships.manage')): ?>
            <?php if (in_array($c['ms_status'], ['active', 'expired'], true) && $c['payment_status'] === 'paid'): ?>
              <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#extendModal">
                <i class="bi bi-calendar-plus"></i> Extend Days
              </button>
            <?php endif; ?>

            <?php foreach ([
              ['suspend', 'active', 'Freeze / Suspend', 'btn-outline-danger', 'Freeze/suspend this membership? The customer will be refused at every branch until resumed.'],
              ['resume', 'suspended', 'Resume Plan', 'btn-outline-success', 'Reactivate this membership and restore frozen days?'],
              ['cancel', 'pending', 'Cancel Unpaid', 'btn-outline-danger', 'Cancel this unpaid membership?']
            ] as [$a, $need, $lbl, $cls, $cf]):
              if ($c['ms_status'] === $need && ($a !== 'cancel' || (float) $c['amount_paid'] == 0.0)): ?>
              <form method="post" action="<?= url('membership_action') ?>" class="d-inline">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="<?= $a ?>">
                <input type="hidden" name="customer_id" value="<?= $c['id'] ?>">
                <input type="hidden" name="membership_id" value="<?= $c['ms_id'] ?>">
                <button class="btn btn-sm <?= $cls ?>" data-confirm="<?= e($cf) ?>"><?= $lbl ?></button>
              </form>
            <?php endif; endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- 2026 Body Transformation, BMI & InBody Progress Tracker -->
<div class="card card-bento-panel shadow-sm mb-4">
  <i class="bi bi-heart-pulse-fill panel-watermark"></i>
  <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
    <div class="d-flex align-items-center gap-2">
      <span class="panel-hdr-ico red"><i class="bi bi-heart-pulse-fill"></i></span>
      <div>
        <div class="lh-1">Body Transformation, BMI &amp; Fitness Progress Tracker</div>
        <small class="text-muted fw-normal" style="font-size:0.71rem;font-family:'Outfit'">InBody Weight, Height, Body Fat % &amp; Muscle Mass Telemetry</small>
      </div>
      <?php if ($latestMetric): ?>
        <span class="badge text-bg-<?= $bmiColor ?> ms-2">Current: <?= (float) $latestMetric['weight_kg'] ?> kg <?= $latestMetric['bmi'] ? '· BMI ' . $latestMetric['bmi'] . ' (' . $bmiLabel . ')' : '' ?></span>
      <?php endif; ?>
    </div>
    <button type="button" class="btn btn-sm btn-brand" data-bs-toggle="modal" data-bs-target="#metricModal">
      <i class="bi bi-plus-circle-fill"></i> Log Weight &amp; Body Check
    </button>
  </div>
  <div class="card-body position-relative" style="z-index:2">
    <?php if ($metrics): ?>
    <div class="row g-3 align-items-center">
      <div class="col-lg-5">
        <div style="height:210px"><canvas id="cBodyWeight"></canvas></div>
      </div>
      <div class="col-lg-7">
        <div class="table-responsive">
          <table class="table table-hover table-pro-matrix align-middle mb-0">
            <thead>
              <tr><th>Date</th><th>Weight</th><th>Height</th><th>BMI</th><th>Body Fat</th><th>Muscle</th><th>Waist/Chest</th><th></th></tr>
            </thead>
            <tbody>
            <?php foreach (array_slice($metrics, 0, 6) as $bm): [$bLbl, $bCol] = bmi_status($bm['bmi'] ? (float) $bm['bmi'] : null); ?>
              <tr>
                <td><small class="text-muted"><?= fmt_date($bm['recorded_date']) ?></small></td>
                <td class="fw-bold"><?= (float) $bm['weight_kg'] ?> kg</td>
                <td><?= $bm['height_cm'] ? (float) $bm['height_cm'] . ' cm' : '—' ?></td>
                <td><?= $bm['bmi'] ? '<span class="badge text-bg-' . $bCol . '">' . $bm['bmi'] . '</span>' : '—' ?></td>
                <td><?= $bm['body_fat_pct'] ? $bm['body_fat_pct'] . '%' : '—' ?></td>
                <td><?= $bm['muscle_mass_kg'] ? $bm['muscle_mass_kg'] . ' kg' : '—' ?></td>
                <td><small><?= ($bm['waist_cm'] ?: '—') . ' / ' . ($bm['chest_cm'] ?: '—') ?> cm</small></td>
                <td class="text-end">
                  <form method="post" class="d-inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="del_metric">
                    <input type="hidden" name="metric_id" value="<?= $bm['id'] ?>">
                    <button class="btn btn-sm text-danger p-0" data-confirm="Remove this body measurement?"><i class="bi bi-x-circle"></i></button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <?php else: ?>
    <div class="text-center text-muted py-3">
      <i class="bi bi-speedometer fs-2 d-block mb-1 text-brand"></i>
      <p class="small mb-2">No body weight or BMI measurements logged for <?= e($c['full_name']) ?> yet.</p>
      <button type="button" class="btn btn-sm btn-outline-brand" data-bs-toggle="modal" data-bs-target="#metricModal">
        <i class="bi bi-plus-lg"></i> Record Baseline Weight &amp; BMI
      </button>
    </div>
    <?php endif; ?>
  </div>
</div>

<!-- Membership History, Payments & Check-in Logs -->
<div class="row g-3">
  <div class="col-xl-7">
    <div class="card card-bento-panel mb-3 shadow-sm">
      <i class="bi bi-clock-history panel-watermark"></i>
      <div class="card-header">
        <div class="d-flex align-items-center gap-2">
          <span class="panel-hdr-ico brand"><i class="bi bi-clock-history"></i></span>
          <div>
            <div class="lh-1">Subscription History</div>
            <small class="text-muted fw-normal" style="font-size:0.71rem;font-family:'Outfit'">All Membership Plans Sold or Renewed</small>
          </div>
        </div>
      </div>
      <div class="table-responsive position-relative" style="z-index:2">
        <table class="table table-hover table-pro-matrix table-sm align-middle mb-0">
          <thead>
            <tr><th>Plan</th><th>Validity Period</th><th>Facility</th><th>Paid</th><th>Status</th></tr>
          </thead>
          <tbody>
          <?php foreach ($history as $h): ?>
            <tr>
              <td class="fw-semibold"><?= e($h['plan_name']) ?></td>
              <td><?= fmt_date($h['start_date']) ?> &rarr; <?= fmt_date($h['end_date']) ?></td>
              <td><span class="badge text-bg-secondary"><i class="bi bi-geo-alt"></i> <?= e(short_branch($h['branch_name'])) ?></span></td>
              <td><?= money($h['amount_paid']) ?> / <?= money($h['price']) ?></td>
              <td><?= badge($h['status'] === 'active' && $h['end_date'] < date('Y-m-d') ? 'expired' : $h['status']) ?></td>
            </tr>
          <?php endforeach; if (!$history): ?>
            <tr><td colspan="5" class="text-muted p-4 text-center">No membership history on file.</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <?php if (Auth::can('payments.view')): ?>
    <div class="card card-bento-panel shadow-sm">
      <i class="bi bi-receipt-cutoff panel-watermark"></i>
      <div class="card-header">
        <div class="d-flex align-items-center gap-2">
          <span class="panel-hdr-ico emerald"><i class="bi bi-receipt-cutoff"></i></span>
          <div>
            <div class="lh-1">Official Payment Receipts</div>
            <small class="text-muted fw-normal" style="font-size:0.71rem;font-family:'Outfit'">Verified Transactions &amp; 80mm Thermal Receipts</small>
          </div>
        </div>
      </div>
      <div class="table-responsive position-relative" style="z-index:2">
        <table class="table table-hover table-pro-matrix table-sm align-middle mb-0">
          <thead>
            <tr><th>Receipt #</th><th>Date</th><th>Facility</th><th>Method</th><th>Amount</th></tr>
          </thead>
          <tbody>
          <?php foreach ($pays as $p): ?>
            <tr>
              <td><a class="fw-semibold font-monospace" href="<?= url('receipt', ['id' => $p['id']]) ?>"><i class="bi bi-file-earmark-text me-1"></i><?= e($p['receipt_no']) ?></a></td>
              <td><small class="text-muted"><?= fmt_dt($p['payment_date']) ?></small></td>
              <td><?= e(short_branch($p['branch'])) ?></td>
              <td><span class="badge text-bg-light border"><?= e($p['method']) ?></span></td>
              <td class="fw-bold text-brand"><?= money($p['amount']) ?></td>
            </tr>
          <?php endforeach; if (!$pays): ?>
            <tr><td colspan="5" class="text-muted p-4 text-center">No payment transactions recorded.</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php endif; ?>
  </div>

  <div class="col-xl-5">
    <div class="card card-bento-panel h-100 shadow-sm">
      <i class="bi bi-fingerprint panel-watermark"></i>
      <div class="card-header">
        <div class="d-flex align-items-center gap-2">
          <span class="panel-hdr-ico cyan"><i class="bi bi-fingerprint"></i></span>
          <div>
            <div class="lh-1">Door Check-in Log (All 6 Branches)</div>
            <small class="text-muted fw-normal" style="font-size:0.71rem;font-family:'Outfit'">Cross-Facility Biometric &amp; QR Entries</small>
          </div>
        </div>
      </div>
      <div class="table-responsive position-relative" style="z-index:2">
        <table class="table table-hover table-pro-matrix table-sm mb-0 align-middle">
          <thead>
            <tr><th>Timestamp</th><th>Method</th><th>Facility</th></tr>
          </thead>
          <tbody>
          <?php foreach ($att as $a): ?>
            <tr>
              <td><span class="badge text-bg-secondary"><i class="bi bi-clock"></i> <?= fmt_dt($a['check_in_at']) ?></span></td>
              <td>
                <?php if (($a['checkin_method'] ?? '') === 'fingerprint_door'): ?>
                  <span class="badge text-bg-success"><i class="bi bi-fingerprint"></i> Biometric</span>
                <?php else: ?>
                  <span class="badge text-bg-info"><i class="bi bi-qr-code-scan"></i> QR/Desk</span>
                <?php endif; ?>
              </td>
              <td><i class="bi bi-geo-alt-fill text-danger me-1"></i> <?= e(short_branch($a['branch'])) ?></td>
            </tr>
          <?php endforeach; if (!$att): ?>
            <tr><td colspan="3" class="text-muted p-4 text-center"><i class="bi bi-door-closed fs-3 d-block mb-1"></i> No check-ins recorded yet.</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Log Body Measurement Modal -->
<div class="modal fade" id="metricModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <form class="modal-content shadow-lg" method="post">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="add_metric">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-heart-pulse-fill text-danger me-2 fs-5"></i> Log Body Measurement &amp; BMI</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body row g-3">
        <div class="col-6">
          <label class="form-label small fw-semibold">Weight (kg) *</label>
          <input type="number" step="0.1" min="20" max="350" name="weight_kg" class="form-control" required placeholder="e.g. 76.5" value="<?= e($latestMetric['weight_kg'] ?? '') ?>">
        </div>
        <div class="col-6">
          <label class="form-label small fw-semibold">Height (cm)</label>
          <input type="number" step="0.5" min="80" max="250" name="height_cm" class="form-control" placeholder="e.g. 175" value="<?= e($latestMetric['height_cm'] ?? '') ?>">
        </div>
        <div class="col-6">
          <label class="form-label small fw-semibold">Body Fat (%)</label>
          <input type="number" step="0.1" min="2" max="70" name="body_fat_pct" class="form-control" placeholder="e.g. 16.5">
        </div>
        <div class="col-6">
          <label class="form-label small fw-semibold">Muscle Mass (kg)</label>
          <input type="number" step="0.1" min="10" max="150" name="muscle_mass_kg" class="form-control" placeholder="e.g. 38.0">
        </div>
        <div class="col-6">
          <label class="form-label small fw-semibold">Waist (cm)</label>
          <input type="number" step="0.5" name="waist_cm" class="form-control" placeholder="e.g. 84">
        </div>
        <div class="col-6">
          <label class="form-label small fw-semibold">Chest (cm)</label>
          <input type="number" step="0.5" name="chest_cm" class="form-control" placeholder="e.g. 102">
        </div>
        <div class="col-6">
          <label class="form-label small fw-semibold">Measurement Date</label>
          <input type="date" name="recorded_date" value="<?= date('Y-m-d') ?>" class="form-control">
        </div>
        <div class="col-6">
          <label class="form-label small fw-semibold">Coach Notes</label>
          <input name="notes" class="form-control" placeholder="Optional assessment note">
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-brand"><i class="bi bi-check2-circle"></i> Save Body Check &amp; Calculate BMI</button>
      </div>
    </form>
  </div>
</div>

<!-- Coach, Fitness Goal & Training Notes Modal -->
<div class="modal fade" id="coachModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <form class="modal-content shadow-lg" method="post">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="update_coaching">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-trophy-fill text-warning me-2 fs-5"></i> Athlete Coaching &amp; Fitness Goal</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body row g-3">
        <div class="col-12">
          <label class="form-label small fw-semibold">Primary Fitness Objective *</label>
          <select name="fitness_goal" class="form-select">
            <?php
            $goalsList = ['General Fitness', 'Weight Loss & Fat Burn', 'Muscle Gain & Hypertrophy', 'Strength & Powerlifting', 'Boxing & Combat Conditioning', 'Cardio & Endurance', 'Rehab & Mobility'];
            $curGoal = $c['fitness_goal'] ?? 'General Fitness';
            foreach ($goalsList as $gl): ?>
              <option value="<?= e($gl) ?>" <?= $curGoal === $gl ? 'selected' : '' ?>><?= e($gl) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-12">
          <label class="form-label small fw-semibold">Assigned Personal Coach / Trainer</label>
          <select name="assigned_trainer_id" class="form-select">
            <option value="">— Self-Guided / Floor Coach —</option>
            <?php foreach (($trainers ?? []) as $tr): ?>
              <option value="<?= $tr['id'] ?>" <?= (int) ($c['assigned_trainer_id'] ?? 0) === (int) $tr['id'] ? 'selected' : '' ?>>
                <?= e($tr['full_name']) ?><?= $tr['specialization'] ? ' (' . e($tr['specialization']) . ')' : '' ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-12">
          <label class="form-label small fw-semibold">Medical, Injury or Coaching Notes</label>
          <textarea name="medical_notes" class="form-control" rows="3" placeholder="Any knee/back precautions, dietary notes or training plan..."><?= e($c['medical_notes'] ?? '') ?></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-brand"><i class="bi bi-check2-circle"></i> Save Coaching Profile</button>
      </div>
    </form>
  </div>
</div>

<!-- Biometric Door Enrollment Modal -->
<?php if (Auth::can('customers.edit')): ?>
<div class="modal fade" id="fpModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <form class="modal-content shadow-lg" onsubmit="enrollFp(event)">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-fingerprint text-success me-2 fs-5"></i> Biometric Door Token Enrollment</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-center">
        <div class="fp-scanner-pod mx-auto mb-3"><i class="bi bi-fingerprint"></i></div>
        <p class="small text-muted">Place member's finger on the enrollment reader or assign a unique biometric/RFID token for 6-branch automatic gate access.</p>
        <div class="input-group mb-2">
          <span class="input-group-text"><i class="bi bi-fingerprint"></i></span>
          <input id="enrollFpInput" class="form-control font-monospace" value="<?= e($c['fingerprint_id'] ?? '') ?>" placeholder="e.g. FP-<?= str_pad((string)$c['id'], 6, '0', STR_PAD_LEFT) ?>" required>
          <button type="button" class="btn btn-outline-secondary" onclick="document.getElementById('enrollFpInput').value='FP-<?= str_pad((string)$c['id'], 6, '0', STR_PAD_LEFT) ?>'">Auto ID</button>
        </div>
        <div id="enrollFpMsg" class="small mt-2"></div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-brand w-100"><i class="bi bi-check2-circle"></i> Save &amp; Sync Biometric Token</button>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>

<?php if (Auth::can('memberships.renew')): ?>
<div class="modal fade" id="renewModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <form class="modal-content shadow-lg" method="post" action="<?= url('membership_action') ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="renew">
      <input type="hidden" name="customer_id" value="<?= $c['id'] ?>">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-award-fill text-brand me-2 fs-5"></i> Sell / Renew Membership</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <?php if (Auth::isSuper()): ?>
          <label class="form-label small fw-semibold">Branch Facility *</label>
          <select name="branch_id" class="form-select mb-2" required>
            <?= opts(branch_list(), '', 'id', 'name', 'Select branch facility…') ?>
          </select>
        <?php endif; ?>

        <label class="form-label small fw-semibold">Membership Plan *</label>
        <select name="plan_id" id="rPlan" class="form-select mb-2" required>
          <option value="" data-price="">Select a plan…</option>
          <?php foreach ($plans as $p): ?>
            <option value="<?= $p['id'] ?>" data-price="<?= $p['price'] ?>"><?= e($p['name']) ?> – <?= money($p['price']) ?></option>
          <?php endforeach; ?>
        </select>

        <label class="form-label small fw-semibold">Start Date <small class="text-muted">(leave blank to start immediately after current expires)</small></label>
        <input type="date" name="start_date" min="<?= date('Y-m-d') ?>" class="form-control mb-3">

        <hr class="my-3 opacity-25">
        <label class="form-label small fw-semibold">Payment Amount Received Now</label>
        <div class="input-group mb-2">
          <input type="number" step="0.01" min="0" name="amount" id="rAmt" class="form-control" placeholder="0.00">
          <button type="button" class="btn btn-outline-brand" onclick="rAmt.value=rPlan.selectedOptions[0].dataset.price||''">Full Price</button>
        </div>

        <select name="method_id" class="form-select mb-2">
          <?= opts($methods, '', 'id', 'name', 'Select payment method…') ?>
        </select>
        <input name="reference" class="form-control mb-2" placeholder="Transaction reference (EVC Plus / Salaam Bank / E-Dahab)">
        
        <div class="form-check mt-3">
          <input type="checkbox" class="form-check-input" name="verified" value="1" id="rv">
          <label class="form-check-label small" for="rv">I confirm that this payment has been received and verified.</label>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-brand"><i class="bi bi-check-circle"></i> Create &amp; Activate Plan</button>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>

<?php if (Auth::can('memberships.manage') && $c['ms_id']): ?>
<div class="modal fade" id="extendModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <form class="modal-content shadow-lg" method="post" action="<?= url('membership_action') ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="extend">
      <input type="hidden" name="customer_id" value="<?= $c['id'] ?>">
      <input type="hidden" name="membership_id" value="<?= $c['ms_id'] ?>">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-calendar-plus text-brand me-2 fs-5"></i> Extend Membership Validity</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <label class="form-label small fw-semibold">Number of Extra Days to Add (1–365)</label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-calendar3"></i></span>
          <input type="number" name="days" min="1" max="365" value="7" class="form-control" required>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-brand" data-confirm="Extend this membership by the selected number of days?"><i class="bi bi-check2-circle"></i> Confirm Extension</button>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>

<?php if (Auth::can('payments.create')) include ROOT . '/app/views/partials/pay_modal.php'; ?>

<script>
<?php if ($metrics):
  $chartMetrics = array_reverse($metrics);
  $mLabels = array_map(fn($x) => date('d M', strtotime($x['recorded_date'])), $chartMetrics);
  $mWeights = array_map(fn($x) => (float) $x['weight_kg'], $chartMetrics);
?>
new Chart(document.getElementById('cBodyWeight'), {
  type: 'line',
  data: {
    labels: <?= json_encode($mLabels) ?>,
    datasets: [{
      label: 'Weight (kg)',
      data: <?= json_encode($mWeights) ?>,
      borderColor: '#ff5722',
      backgroundColor: 'rgba(255, 87, 34, 0.15)',
      fill: true,
      tension: 0.35,
      pointRadius: 4
    }]
  },
  options: {responsive: true, maintainAspectRatio: false}
});
<?php endif; ?>

async function enrollFp(e) {
  e.preventDefault();
  const fp = document.getElementById('enrollFpInput').value.trim();
  const msg = document.getElementById('enrollFpMsg');
  const body = new URLSearchParams({customer_id: <?= (int) $c['id'] ?>, fingerprint_id: fp});
  const res = await fetch(CAATO.api + '&action=enroll_fingerprint', {
    method: 'POST',
    headers: {'X-CSRF-Token': CAATO.csrf, 'Content-Type': 'application/x-www-form-urlencoded'},
    body
  });
  const j = await res.json();
  msg.className = 'small mt-2 fw-bold text-' + (j.ok ? 'success' : 'danger');
  msg.textContent = j.message;
  if (j.ok) setTimeout(() => location.reload(), 900);
}
</script>
