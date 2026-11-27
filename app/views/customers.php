<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
  <div>
    <div class="d-flex align-items-center gap-2 mb-1">
      <h1 class="page-title">Customer Directory</h1>
      <span class="badge badge-futuristic"><span class="pulse-dot"></span> 6 BRANCHES UNIFIED</span>
    </div>
    <small class="text-muted"><i class="bi bi-shield-check text-success me-1"></i> Centralized member network — <?= (int) ($counts['total'] ?? 0) ?> profiles · <?= (int) ($counts['active'] ?? 0) ?> active passes · <?= (int) ($counts['biometric'] ?? 0) ?> biometric enrolled</small>
  </div>
  <div class="d-flex gap-2">
    <?php if (Auth::can('reports.view')): ?>
      <a href="<?= url('export', ['type' => 'customers', 'branch' => $br, 'format' => 'xlsx']) ?>" class="btn btn-outline-secondary">
        <i class="bi bi-file-earmark-excel-fill text-success"></i> Export Excel
      </a>
    <?php endif; ?>
    <?php if (Auth::can('customers.create')): ?>
      <a href="<?= url('customer_register') ?>" class="btn btn-brand">
        <i class="bi bi-person-plus-fill"></i> Register New Member
      </a>
    <?php endif; ?>
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

<div class="card mb-4 shadow-sm">
  <div class="card-body p-3 bg-body-tertiary">
    <form class="row g-2 align-items-center" method="get">
      <input type="hidden" name="page" value="customers">
      <div class="col-md-5">
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-search"></i></span>
          <input name="q" value="<?= e($q) ?>" class="form-control" placeholder="Search member name, phone, ID (CF-...) or fingerprint token…">
        </div>
      </div>
      <div class="col-md-3">
        <select name="branch" class="form-select">
          <?= opts($allBranches, $br, 'id', 'name', 'Home Facility: All 6 Branches') ?>
        </select>
      </div>
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
</div>

<div class="card shadow-sm">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead>
        <tr>
          <th style="width:58px">Photo</th>
          <th>Member Identity</th>
          <th>Contact &amp; Goal</th>
          <th>Home Facility</th>
          <th>Subscription</th>
          <th>Status</th>
          <th>Expiry &amp; Visits</th>
          <th class="text-end">Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><img class="avatar" src="<?= photo_url($r['profile_picture']) ?>" alt=""></td>
          <td>
            <a class="fw-bold d-block" href="<?= url('customer_view', ['id' => $r['id']]) ?>"><?= e($r['full_name']) ?></a>
            <small class="text-muted font-monospace">
              <?= e($r['customer_code']) ?>
              <?php if (!empty($r['fingerprint_id'])): ?>
                <span class="badge text-bg-success ms-1" title="Biometric Door Enrolled: <?= e($r['fingerprint_id']) ?>"><i class="bi bi-fingerprint"></i></span>
              <?php endif; ?>
            </small>
          </td>
          <td>
            <div class="text-nowrap small fw-semibold"><i class="bi bi-telephone me-1 text-muted"></i><?= e($r['phone']) ?></div>
            <small class="text-muted d-block text-truncate" style="max-width:170px"><i class="bi bi-bullseye text-brand me-1"></i><?= e($r['fitness_goal'] ?? 'General Fitness') ?></small>
          </td>
          <td>
            <span class="small"><i class="bi bi-geo-alt-fill text-danger me-1"></i><?= e(str_replace('CAATO FITNESS GYM – ', '', $r['reg_branch'])) ?></span>
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
            <div class="small fw-semibold"><?= fmt_date($r['end_date']) ?></div>
            <small class="text-muted"><?= (int) ($r['total_visits'] ?? 0) ?> visits</small>
          </td>
          <td class="text-end text-nowrap">
            <a class="btn btn-sm btn-whatsapp py-1 px-2" target="_blank" href="<?= e(whatsapp_url($r['phone'], "Asc {$r['full_name']} ({$r['customer_code']}), kusoo dhawow CAATO FITNESS GYM!")) ?>" title="Message on WhatsApp">
              <i class="bi bi-whatsapp"></i>
            </a>
            <a class="btn btn-sm btn-outline-secondary py-1 px-2" target="_blank" href="<?= url('customer_card', ['id' => $r['id']]) ?>" title="Digital PVC ID Card">
              <i class="bi bi-person-vcard-fill"></i>
            </a>
            <a class="btn btn-sm btn-outline-brand py-1 px-2" href="<?= url('customer_view', ['id' => $r['id']]) ?>">
              <i class="bi bi-person-bounding-box"></i> 360°
            </a>
            <?php if (Auth::can('customers.edit')): ?>
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
  <div class="card-footer bg-transparent py-3"><?= pager($pg) ?></div>
</div>
