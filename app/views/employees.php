<?php
$empActs = '';
if (Auth::can('reports.view')) {
  $empActs .= '<a href="' . url('export', ['type' => 'employees', 'branch' => $br, 'format' => 'xlsx']) . '" class="btn btn-sm px-3 py-2" style="background:rgba(255,255,255,0.12);color:#fff;border:1px solid rgba(255,255,255,0.2)"><i class="bi bi-file-earmark-excel-fill text-success"></i> Export Payroll Excel</a>';
}
$empActs .= '<button class="btn btn-brand btn-sm px-3 py-2" onclick="eForm()"><i class="bi bi-person-plus-fill"></i> Add Staff Member</button>';
$totPayroll = array_sum(array_map(fn($x) => (float) ($x['salary'] ?? 0), $rows));
$withLogin  = count(array_filter($rows, fn($x) => !empty($x['username'])));
page_banner(
  'HR & PAYROLL LEDGER · WORKFORCE OPERATIONS',
  'Staff &amp;',
  'Workforce',
  'Manage receptionists, accountants, coaches, monthly payroll & branch login credentials across all 6 facilities.',
  $empActs,
  [
    [(int) ($pg['total'] ?? 0), 'Total Staff', 'info'],
    [count($branches), 'Active Branches', 'success'],
  ]
);
?>

<!-- 3D Holographic Workforce KPI Strip -->
<div class="row g-3 mb-4">
  <div class="col-md-4">
    <div class="card stat stat-pro cyan">
      <div class="stat-glow-orb"></div>
      <i class="bi bi-person-badge-fill stat-watermark"></i>
      <div class="stat-top">
        <div class="ico"><i class="bi bi-person-badge-fill"></i></div>
        <div class="stat-meta-right">
          <span class="stat-pill"><span class="stat-dot"></span> HR Directory</span>
        </div>
      </div>
      <div class="stat-body">
        <div class="num"><?= (int) ($pg['total'] ?? count($rows)) ?> Staff</div>
        <div class="stat-title">TOTAL REGISTERED WORKFORCE</div>
      </div>
      <div class="stat-foot">
        <span class="stat-sub"><i class="bi bi-building-check"></i> Deployed across 6 CAATO facilities</span>
        <div class="stat-spark"><div class="stat-spark-fill" style="width:90%"></div></div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card stat stat-pro brand">
      <div class="stat-glow-orb"></div>
      <i class="bi bi-cash-coin stat-watermark"></i>
      <div class="stat-top">
        <div class="ico"><i class="bi bi-cash-coin"></i></div>
        <div class="stat-meta-right">
          <span class="stat-pill"><span class="stat-dot"></span> Monthly Payroll</span>
        </div>
      </div>
      <div class="stat-body">
        <div class="num"><?= money($totPayroll) ?></div>
        <div class="stat-title">PAGE MONTHLY PAYROLL</div>
      </div>
      <div class="stat-foot">
        <span class="stat-sub"><i class="bi bi-file-earmark-spreadsheet"></i> Exportable to Excel payroll sheet</span>
        <div class="stat-spark"><div class="stat-spark-fill" style="width:84%"></div></div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card stat stat-pro emerald">
      <div class="stat-glow-orb"></div>
      <i class="bi bi-shield-lock-fill stat-watermark"></i>
      <div class="stat-top">
        <div class="ico"><i class="bi bi-shield-lock-fill"></i></div>
        <div class="stat-meta-right">
          <span class="stat-pill"><span class="stat-dot"></span> RBAC Accounts</span>
        </div>
      </div>
      <div class="stat-body">
        <div class="num"><?= $withLogin ?> Linked</div>
        <div class="stat-title">STAFF WITH SYSTEM LOGINS</div>
      </div>
      <div class="stat-foot">
        <span class="stat-sub"><i class="bi bi-key-fill"></i> Role-restricted console access</span>
        <div class="stat-spark"><div class="stat-spark-fill" style="width:88%"></div></div>
      </div>
    </div>
  </div>
</div>

<div class="card card-bento-panel mb-4 shadow-sm">
  <i class="bi bi-funnel-fill panel-watermark"></i>
  <div class="card-body p-3 position-relative" style="z-index:1">
    <form class="row g-2 align-items-center" method="get">
      <input type="hidden" name="page" value="employees">
      <div class="col-md-3">
        <div class="input-group input-group-sm">
          <span class="input-group-text"><i class="bi bi-search"></i></span>
          <input name="q" value="<?= e($q) ?>" class="form-control" placeholder="Search name or phone">
        </div>
      </div>
      <?php if (Auth::isSuper()): ?>
      <div class="col-md-3">
        <select name="branch" class="form-select form-select-sm">
          <?= opts(branch_list(false), $br, 'id', 'name', 'Facility: All Branches') ?>
        </select>
      </div>
      <?php else: ?>
      <div class="col-md-3">
        <div class="form-control form-control-sm bg-body-secondary fw-semibold text-truncate">
          <i class="bi bi-geo-alt-fill text-danger me-1"></i> <?= e(short_branch(Auth::user()['branch_name'] ?? '')) ?>
        </div>
      </div>
      <?php endif; ?>
      <div class="col-md-2">
        <select name="role" class="form-select form-select-sm">
          <?= opts($roles, $rl, 'id', 'label', 'Role: Any') ?>
        </select>
      </div>
      <div class="col-md-2">
        <select name="status" class="form-select form-select-sm">
          <option value="">Status: Any</option>
          <option value="active" <?= $st === 'active' ? 'selected' : '' ?>>Active</option>
          <option value="inactive" <?= $st === 'inactive' ? 'selected' : '' ?>>Inactive</option>
        </select>
      </div>
      <div class="col-md-2 d-grid">
        <button class="btn btn-sm btn-brand"><i class="bi bi-funnel-fill"></i> Filter</button>
      </div>
    </form>
  </div>
</div>

<div class="card card-bento-panel shadow-sm">
  <i class="bi bi-person-badge-fill panel-watermark"></i>
  <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
    <div class="d-flex align-items-center gap-2">
      <span class="panel-hdr-ico cyan"><i class="bi bi-person-badge-fill"></i></span>
      <span>Staff &amp; Workforce Payroll Matrix</span>
    </div>
    <span class="stat-pill"><span class="stat-dot"></span> <?= (int) ($pg['total'] ?? count($rows)) ?> Employees</span>
  </div>
  <div class="table-responsive">
    <table class="table table-hover table-pro-matrix align-middle mb-0">
      <thead>
        <tr>
          <th>Employee Details</th>
          <th>Designation &amp; Role</th>
          <th>Assigned Facility</th>
          <th>Monthly Salary</th>
          <th>Joined Date</th>
          <th>System Login</th>
          <th>Status</th>
          <th class="text-end">Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td>
            <div class="fw-bold fs-6"><?= e($r['full_name']) ?></div>
            <small class="text-muted"><i class="bi bi-telephone me-1"></i><?= e($r['phone']) ?><?= $r['email'] ? ' · ' . e($r['email']) : '' ?></small>
          </td>
          <td>
            <div class="fw-semibold"><?= e($r['position']) ?></div>
            <small class="text-muted"><i class="bi bi-shield-check me-1 text-brand"></i><?= e($r['role_label']) ?></small>
          </td>
          <td><span class="gate-visit-pill"><i class="bi bi-geo-alt-fill text-danger"></i> <?= e(short_branch($r['branch_name'])) ?></span></td>
          <td><span class="matrix-rev-month"><?= money($r['salary']) ?></span></td>
          <td><small class="text-muted"><i class="bi bi-calendar3 me-1"></i><?= fmt_date($r['joining_date']) ?></small></td>
          <td>
            <?php if ($r['username']): ?>
              <span class="badge text-bg-light border font-monospace"><i class="bi bi-person-check-fill text-success me-1"></i><?= e($r['username']) ?></span>
            <?php else: ?>
              <span class="text-muted small">No account</span>
            <?php endif; ?>
          </td>
          <td><?= badge($r['status']) ?></td>
          <td class="text-nowrap text-end">
            <a href="<?= e(whatsapp_url($r['phone'], "Asc {$r['full_name']}, fariin ka socota maamulka CAATO FITNESS GYM.")) ?>" target="_blank" class="btn btn-sm btn-whatsapp" title="WhatsApp Staff">
              <i class="bi bi-whatsapp"></i>
            </a>
            <button class="btn btn-sm btn-outline-secondary" onclick="eForm(<?= jsattr($r) ?>)">
              <i class="bi bi-pencil-square"></i> Edit
            </button>
            <form method="post" class="d-inline">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="toggle">
              <input type="hidden" name="id" value="<?= $r['id'] ?>">
              <button class="btn btn-sm btn-outline-danger" data-confirm="Change this employee's status? Their login is affected too.">
                <?= $r['status'] === 'active' ? '<i class="bi bi-person-x"></i>' : '<i class="bi bi-person-check"></i>' ?>
              </button>
            </form>
            <form method="post" class="d-inline">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?= $r['id'] ?>">
              <button class="btn btn-sm btn-outline-danger" data-confirm="Permanently DELETE employee <?= e($r['full_name']) ?>? This cannot be undone.">
                <i class="bi bi-trash3-fill"></i>
              </button>
            </form>
          </td>
        </tr>
      <?php endforeach; if (!$rows): ?>
        <tr><td colspan="8" class="text-center text-muted py-5"><i class="bi bi-people fs-2 d-block mb-2"></i>No employees registered yet.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
  <div class="card-footer bg-transparent py-3"><?= pager($pg) ?></div>
</div>

<div class="modal fade" id="eModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <form class="modal-content shadow-lg" method="post">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="save">
      <input type="hidden" name="id" id="eid">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-person-badge-fill text-brand me-2 fs-5"></i> Staff Member Profile</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body row g-3">
        <div class="col-md-6">
          <label class="form-label small fw-semibold">Full Legal Name *</label>
          <input name="full_name" id="efn" class="form-control" required placeholder="Full name">
        </div>
        <div class="col-md-3">
          <label class="form-label small fw-semibold">Phone Number *</label>
          <input name="phone" id="eph" class="form-control" required placeholder="Phone">
        </div>
        <div class="col-md-3">
          <label class="form-label small fw-semibold">Email Address</label>
          <input name="email" id="eem" class="form-control" placeholder="Email">
        </div>
        <div class="col-md-4">
          <label class="form-label small fw-semibold">Job Title / Position *</label>
          <input name="position" id="epo" class="form-control" required placeholder="e.g. Head Coach, Receptionist">
        </div>
        <div class="col-md-4">
          <label class="form-label small fw-semibold">System Permission Role *</label>
          <select name="role_id" id="ero" class="form-select" required>
            <?= opts($roles, '', 'id', 'label') ?>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label small fw-semibold">Assigned Facility *</label>
          <select name="branch_id" id="ebr" class="form-select" required>
            <?= opts($branches, '', 'id', 'name') ?>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label small fw-semibold">Monthly Salary</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-cash"></i></span>
            <input type="number" step="0.01" min="0" name="salary" id="esa" class="form-control" placeholder="0.00">
          </div>
        </div>
        <div class="col-md-4">
          <label class="form-label small fw-semibold">Joining Date *</label>
          <input type="date" name="joining_date" id="ejo" class="form-control" required>
        </div>
        <div class="col-md-4">
          <label class="form-label small fw-semibold">Account Status</label>
          <select name="status" id="est" class="form-select">
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
          </select>
        </div>
        <div class="col-12" id="loginBox">
          <hr class="my-3 opacity-25">
          <div class="form-check mb-2">
            <input type="checkbox" class="form-check-input" name="create_login" value="1" id="cl">
            <label class="form-check-label fw-semibold" for="cl">Enable system login access for this employee</label>
          </div>
          <div class="row g-2">
            <div class="col-md-6">
              <input name="username" class="form-control" placeholder="System login username">
            </div>
            <div class="col-md-6">
              <input type="password" name="password" class="form-control" placeholder="Password (min 8 chars)" autocomplete="new-password">
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-brand"><i class="bi bi-check-circle"></i> Save Employee Profile</button>
      </div>
    </form>
  </div>
</div>

<script>
function eForm(e){
  e = e || {};
  eid.value = e.id || '';
  efn.value = e.full_name || '';
  eph.value = e.phone || '';
  eem.value = e.email || '';
  epo.value = e.position || '';
  if (e.role_id) ero.value = e.role_id;
  if (e.branch_id) ebr.value = e.branch_id;
  esa.value = e.salary || '';
  ejo.value = e.joining_date || '<?= date('Y-m-d') ?>';
  est.value = e.status || 'active';
  loginBox.style.display = e.user_id ? 'none' : '';
  new bootstrap.Modal(eModal).show();
}
</script>
