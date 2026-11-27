<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
  <div>
    <div class="d-flex align-items-center gap-2 mb-1">
      <h1 class="page-title">Staff &amp; Workforce</h1>
      <span class="badge badge-futuristic"><span class="pulse-dot"></span> HR &amp; PAYROLL LEDGER</span>
    </div>
    <small class="text-muted"><i class="bi bi-person-badge-fill text-brand me-1"></i> Manage receptionists, accountants, trainers &amp; system login credentials across 6 branches</small>
  </div>
  <div class="d-flex gap-2">
    <?php if (Auth::can('reports.view')): ?>
      <a href="<?= url('export', ['type' => 'employees', 'branch' => $br, 'format' => 'xlsx']) ?>" class="btn btn-outline-secondary">
        <i class="bi bi-file-earmark-excel-fill text-success"></i> Export Payroll Excel
      </a>
    <?php endif; ?>
    <button class="btn btn-brand" onclick="eForm()">
      <i class="bi bi-person-plus-fill"></i> Add Staff Member
    </button>
  </div>
</div>

<div class="card mb-4 shadow-sm">
  <div class="card-body p-3 bg-body-tertiary">
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

<div class="card shadow-sm">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
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
            <small class="text-muted"><i class="bi bi-telephone me-1"></i><?= e($r['phone']) ?> · <?= e($r['email']) ?></small>
          </td>
          <td>
            <div class="fw-semibold"><?= e($r['position']) ?></div>
            <small class="text-muted"><?= e($r['role_label']) ?></small>
          </td>
          <td><i class="bi bi-geo-alt-fill text-danger me-1"></i><?= e($r['branch_name']) ?></td>
          <td class="fw-semibold text-brand"><?= money($r['salary']) ?></td>
          <td><small class="text-muted"><i class="bi bi-calendar3 me-1"></i><?= fmt_date($r['joining_date']) ?></small></td>
          <td>
            <?php if ($r['username']): ?>
              <span class="badge text-bg-light border font-monospace"><i class="bi bi-person-check me-1"></i><?= e($r['username']) ?></span>
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
                <?= $r['status'] === 'active' ? '<i class="bi bi-person-x"></i> Deactivate' : '<i class="bi bi-person-check"></i> Activate' ?>
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
