<?php
$custActs = '';
if (Auth::can('customers.create')) {
  $custActs .= '<a href="' . url('customer_register') . '" class="btn btn-brand btn-sm px-3 py-2"><i class="bi bi-person-plus-fill"></i> Register New Member</a>';
}
if (Auth::can('reports.view')) {
  $custActs .= '<a href="' . url('export', ['type' => 'customers', 'branch' => $br, 'format' => 'xlsx']) . '" class="btn btn-sm px-3 py-2" style="background:rgba(255,255,255,0.12);color:#fff;border:1px solid rgba(255,255,255,0.2)"><i class="bi bi-file-earmark-excel-fill text-success"></i> Export Excel</a>';
}
page_banner(
  '6 BRANCHES UNIFIED · CENTRALIZED PROFILE DATABASE',
  'Customer',
  'Directory',
  'Search, filter, and manage every member across all six CAATO FITNESS GYM branches from one unified cloud directory.',
  $custActs,
  [
    [(int) ($counts['total'] ?? 0), 'Total Members', 'info'],
    [(int) ($counts['active'] ?? 0), 'Active Passes', 'success'],
    [(int) ($counts['biometric'] ?? 0), 'Biometric IDs', 'warning'],
  ]
);
?>

<!-- 3D Bento KPI Strip -->
<div class="row g-3 mb-4">
  <div class="col-md-4">
    <div class="card stat stat-pro brand h-100">
      <div class="stat-glow-orb"></div>
      <i class="bi bi-people-fill stat-watermark"></i>
      <div class="stat-top">
        <div class="ico"><i class="bi bi-people-fill"></i></div>
        <span class="stat-pill"><span class="stat-dot"></span>DIRECTORY</span>
      </div>
      <div class="stat-body">
        <div class="num"><?= (int) ($counts['total'] ?? 0) ?></div>
        <div class="stat-title">Registered Athletes</div>
      </div>
      <div class="stat-foot">
        <span class="stat-sub">Shared 6-Branch Profiles</span>
        <div class="stat-spark"><span class="stat-spark-fill"></span></div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card stat stat-pro green h-100">
      <div class="stat-glow-orb"></div>
      <i class="bi bi-patch-check-fill stat-watermark"></i>
      <div class="stat-top">
        <div class="ico"><i class="bi bi-patch-check-fill"></i></div>
        <span class="stat-pill"><span class="stat-dot"></span>ACTIVE PASSES</span>
      </div>
      <div class="stat-body">
        <div class="num"><?= (int) ($counts['active'] ?? 0) ?></div>
        <div class="stat-title">Active Subscriptions</div>
      </div>
      <div class="stat-foot">
        <span class="stat-sub">Cleared for Biometric Gate</span>
        <div class="stat-spark"><span class="stat-spark-fill"></span></div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card stat stat-pro cyan h-100">
      <div class="stat-glow-orb"></div>
      <i class="bi bi-fingerprint stat-watermark"></i>
      <div class="stat-top">
        <div class="ico"><i class="bi bi-fingerprint"></i></div>
        <span class="stat-pill"><span class="stat-dot"></span>BIOMETRIC ID</span>
      </div>
      <div class="stat-body">
        <div class="num"><?= (int) ($counts['biometric'] ?? 0) ?></div>
        <div class="stat-title">Biometric Door Enrolled</div>
      </div>
      <div class="stat-foot">
        <span class="stat-sub">Instant Turnstile Access</span>
        <div class="stat-spark"><span class="stat-spark-fill"></span></div>
      </div>
    </div>
  </div>
</div>

<!-- Quick Status Filter Pills -->
<div class="d-flex flex-wrap gap-2 mb-3">
  <?php foreach ([
    '' => ['All Members', 'bi-people-fill'],
    'active' => ['Active Passes', 'bi-patch-check-fill text-success'],
    'expired' => ['Expired', 'bi-hourglass-bottom text-danger'],
    'pending' => ['Unpaid / Pending', 'bi-exclamation-circle-fill text-warning'],
    'suspended' => ['Suspended', 'bi-pause-circle-fill text-secondary'],
    'none' => ['No Plan', 'bi-dash-circle text-muted']
  ] as $k => [$lbl, $ic]): ?>
    <a href="<?= url('customers', array_filter(['q' => $q, 'branch' => $br, 'status' => $k])) ?>" class="btn btn-sm <?= $st === $k ? 'btn-brand' : 'btn-outline-secondary' ?>">
      <i class="bi <?= $ic ?>"></i> <?= $lbl ?>
    </a>
  <?php endforeach; ?>
</div>

<div class="card card-bento-panel shadow-sm">
  <i class="bi bi-people-fill panel-watermark"></i>
  <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
    <div class="d-flex align-items-center gap-2">
      <span class="panel-hdr-ico brand"><i class="bi bi-people-fill"></i></span>
      <div>
        <div class="lh-1">Unified Athlete Directory</div>
        <small class="text-muted fw-normal" style="font-size:0.71rem;font-family:'Outfit'">360° Profiles, Biometric Tokens, WhatsApp Reminders &amp; PVC Cards</small>
      </div>
    </div>
    <span class="badge text-bg-secondary"><?= (int) $pg['total'] ?> Record(s)</span>
  </div>

  <div class="card-body p-3 bg-body-tertiary border-bottom position-relative" style="z-index:2">
    <form class="row g-2 align-items-center" method="get">
      <input type="hidden" name="page" value="customers">
      <div class="col-md-5">
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-search"></i></span>
          <input name="q" value="<?= e($q) ?>" class="form-control" placeholder="Search member name, phone, ID (CF-...) or fingerprint token…">
        </div>
      </div>
      <?php if (Auth::isSuper()): ?>
      <div class="col-md-3">
        <select name="branch" class="form-select">
          <?= opts($allBranches, $br, 'id', 'name', 'Home Facility: All 6 Branches') ?>
        </select>
      </div>
      <?php else: ?>
      <div class="col-md-3">
        <div class="form-control bg-body-secondary fw-semibold text-truncate">
          <i class="bi bi-geo-alt-fill text-danger me-1"></i> <?= e(short_branch(Auth::user()['branch_name'] ?? '')) ?>
        </div>
      </div>
      <?php endif; ?>
      <div class="col-md-2">
        <select name="status" class="form-select">
          <option value="">Status: Any</option>
          <?php foreach (['active', 'expired', 'pending', 'suspended', 'cancelled', 'none'] as $s): ?>
            <option value="<?= $s ?>" <?= $st === $s ? 'selected' : '' ?>><?= $s === 'none' ? 'No membership' : ucfirst($s) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2 d-grid">
        <button class="btn btn-brand"><i class="bi bi-funnel-fill"></i> Filter</button>
      </div>
    </form>
  </div>

  <div class="table-responsive position-relative" style="z-index:2">
    <table class="table table-hover table-pro-matrix align-middle mb-0">
      <thead>
        <tr>
          <th class="ps-4" style="width:58px">Photo</th>
          <th>Member Identity</th>
          <th>Contact &amp; Goal</th>
          <th>Home Facility</th>
          <th>Subscription</th>
          <th>Status</th>
          <th>Expiry &amp; Visits</th>
          <th class="pe-4 text-end">Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td class="ps-4"><img class="avatar" src="<?= photo_url($r['profile_picture']) ?>" alt=""></td>
          <td>
            <a class="branch-node-title d-block text-decoration-none" href="<?= url('customer_view', ['id' => $r['id']]) ?>"><?= e($r['full_name']) ?></a>
            <small class="text-muted font-monospace">
              <?= e($r['customer_code']) ?>
              <?php if (!empty($r['fingerprint_id'])): ?>
                <span class="badge text-bg-success ms-1" title="Biometric Door Enrolled: <?= e($r['fingerprint_id']) ?>"><i class="bi bi-fingerprint"></i> <?= e($r['fingerprint_id']) ?></span>
              <?php endif; ?>
            </small>
          </td>
          <td>
            <div class="text-nowrap small fw-semibold"><i class="bi bi-telephone me-1 text-muted"></i><?= e($r['phone']) ?></div>
            <small class="text-muted d-block text-truncate" style="max-width:170px"><i class="bi bi-bullseye text-brand me-1"></i><?= e($r['fitness_goal'] ?? 'General Fitness') ?></small>
          </td>
          <td>
            <span class="small fw-semibold"><i class="bi bi-geo-alt-fill text-danger me-1"></i><?= e(short_branch($r['reg_branch'])) ?></span>
            <?php if (!empty($r['trainer_name'])): ?>
              <small class="text-muted d-block"><i class="bi bi-trophy-fill text-warning me-1"></i><?= e($r['trainer_name']) ?></small>
            <?php endif; ?>
          </td>
          <td><span class="badge text-bg-light border"><?= e($r['plan_name'] ?: '—') ?></span></td>
          <td>
            <?= badge($r['eff_status']) ?>
            <?= $r['ms_id'] && $r['payment_status'] !== 'paid' ? badge($r['payment_status']) : '' ?>
          </td>
          <td>
            <div class="small fw-bold"><?= fmt_date($r['end_date']) ?></div>
            <small class="text-muted"><?= (int) ($r['total_visits'] ?? 0) ?> visits</small>
          </td>
          <td class="pe-4 text-end text-nowrap">
            <a class="btn btn-sm btn-whatsapp py-1 px-2" target="_blank" href="<?= e(whatsapp_url($r['phone'], "Asc {$r['full_name']} ({$r['customer_code']}), kusoo dhawow CAATO FITNESS GYM!")) ?>" title="Message on WhatsApp">
              <i class="bi bi-whatsapp"></i>
            </a>
            <a class="btn btn-sm btn-outline-secondary py-1 px-2" target="_blank" href="<?= url('customer_card', ['id' => $r['id']]) ?>" title="Digital PVC ID Card">
              <i class="bi bi-person-vcard-fill"></i>
            </a>
            <a class="btn btn-sm btn-outline-brand py-1 px-2" href="<?= url('customer_view', ['id' => $r['id']]) ?>">
              <i class="bi bi-person-bounding-box"></i> 360°
            </a>
            <?php if (Auth::isSuper() || Auth::role() === 'branch_manager'): ?>
            <form method="post" action="<?= url('customer_delete') ?>" class="d-inline">
              <?= csrf_field() ?>
              <input type="hidden" name="id" value="<?= $r['id'] ?>">
              <button class="btn btn-sm btn-outline-danger py-1 px-2" data-confirm="DELETE customer <?= e($r['full_name']) ?>? This action cannot be undone!">
                <i class="bi bi-trash3-fill"></i>
              </button>
            </form>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; if (!$rows): ?>
        <tr>
          <td colspan="8" class="text-center text-muted py-5">
            <i class="bi bi-people fs-2 d-block mb-2"></i>
            No customers match your search criteria.
          </td>
        </tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
  <div class="card-footer bg-transparent py-3 position-relative" style="z-index:2"><?= pager($pg) ?></div>
</div>
