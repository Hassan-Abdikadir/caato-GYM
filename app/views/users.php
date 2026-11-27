<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
  <div>
    <div class="d-flex align-items-center gap-2 mb-1">
      <h1 class="page-title">User Accounts &amp; RBAC Security</h1>
      <span class="badge badge-futuristic"><span class="pulse-dot"></span> ZERO-TRUST ACCESS CONTROL</span>
    </div>
    <small class="text-muted"><i class="bi bi-shield-lock-fill text-brand me-1"></i> Super Admins control all 6 branches; Branch Managers, Receptionists, Accountants &amp; Trainers are scoped to their facility</small>
  </div>
  <button class="btn btn-brand" onclick="uForm()">
    <i class="bi bi-person-plus-fill"></i> New User Account
  </button>
</div>

<div class="row g-2 mb-4">
  <?php
  $roleCounts = [];
  foreach ($rows as $ur) $roleCounts[$ur['role_label']] = ($roleCounts[$ur['role_label']] ?? 0) + 1;
  foreach ($roleCounts as $rLbl => $rCnt): ?>
    <div class="col-6 col-md-3 col-xl-2">
      <div class="card stat py-2 px-3">
        <div class="ico" style="width:42px;height:42px;font-size:1.25rem!important"><i class="bi bi-shield-check"></i></div>
        <div>
          <div class="num fs-5"><?= (int) $rCnt ?></div>
          <small style="font-size:0.68rem"><?= e($rLbl) ?></small>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<div class="card shadow-sm">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead>
        <tr>
          <th>Full Name</th>
          <th>Username</th>
          <th>Role Permission</th>
          <th>Assigned Facility</th>
          <th>Last Login Timestamp</th>
          <th>Status</th>
          <th class="text-end">Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td>
            <div class="fw-bold fs-6"><?= e($r['full_name']) ?></div>
            <small class="text-muted"><i class="bi bi-envelope me-1"></i><?= e($r['email']) ?></small>
          </td>
          <td><span class="badge text-bg-light border font-monospace"><i class="bi bi-person me-1"></i><?= e($r['username']) ?></span></td>
          <td><span class="badge text-bg-secondary"><?= e($r['role_label']) ?></span></td>
          <td><i class="bi bi-geo-alt-fill text-danger me-1"></i><?= e($r['branch_name'] ?: 'All Branches (Super Admin)') ?></td>
          <td><small class="text-muted"><i class="bi bi-clock me-1"></i><?= fmt_dt($r['last_login']) ?></small></td>
          <td>
            <?= badge($r['status']) ?>
            <?php if (!empty($r['locked_until']) && strtotime($r['locked_until']) > time()): ?>
              <span class="badge text-bg-danger ms-1"><i class="bi bi-lock-fill"></i> Locked</span>
            <?php endif; ?>
          </td>
          <td class="text-end">
            <button class="btn btn-sm btn-outline-secondary" onclick="uForm(<?= jsattr(["id" => $r["id"], "full_name" => $r["full_name"], "username" => $r["username"], "email" => $r["email"], "role_id" => $r["role_id"], "branch_id" => $r["branch_id"], "status" => $r["status"]]) ?>)">
              <i class="bi bi-key-fill"></i> Edit / Reset Password
            </button>
            <form method="post" class="d-inline">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?= $r['id'] ?>">
              <button class="btn btn-sm btn-outline-danger" data-confirm="Permanently DELETE user <?= e($r['username']) ?>? This cannot be undone.">
                <i class="bi bi-trash3-fill"></i>
              </button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="modal fade" id="uModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <form class="modal-content shadow-lg" method="post">
      <?= csrf_field() ?>
      <input type="hidden" name="id" id="uid">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-shield-lock-fill text-brand me-2 fs-5"></i> User Account Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body row g-3">
        <div class="col-12">
          <label class="form-label small fw-semibold">Full Legal Name *</label>
          <input name="full_name" id="ufn" class="form-control" required placeholder="Full name">
        </div>
        <div class="col-6">
          <label class="form-label small fw-semibold">Login Username *</label>
          <input name="username" id="uun" class="form-control" required autocomplete="off" placeholder="username">
        </div>
        <div class="col-6">
          <label class="form-label small fw-semibold">Email Address</label>
          <input name="email" id="uem" class="form-control" placeholder="user@caatogym.com">
        </div>
        <div class="col-6">
          <label class="form-label small fw-semibold">Permission Role *</label>
          <select name="role_id" id="uro" class="form-select">
            <?= opts($roles, '', 'id', 'label') ?>
          </select>
        </div>
        <div class="col-6">
          <label class="form-label small fw-semibold">Branch Facility <small class="text-muted">(Super Admin is all)</small></label>
          <select name="branch_id" id="ubr" class="form-select">
            <?= opts($branches, '', 'id', 'name', '— Central / All —') ?>
          </select>
        </div>
        <div class="col-6">
          <label class="form-label small fw-semibold">Account Status</label>
          <select name="status" id="ust" class="form-select">
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
          </select>
        </div>
        <div class="col-6">
          <label class="form-label small fw-semibold">Password <small class="text-muted" id="pwHint"></small></label>
          <input type="password" name="password" class="form-control" autocomplete="new-password" placeholder="min 8 characters">
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-brand"><i class="bi bi-check-circle"></i> Save User Account</button>
      </div>
    </form>
  </div>
</div>

<script>
function uForm(u){
  u = u || {};
  uid.value = u.id || '';
  ufn.value = u.full_name || '';
  uun.value = u.username || '';
  uem.value = u.email || '';
  if (u.role_id) uro.value = u.role_id;
  ubr.value = u.branch_id || '';
  ust.value = u.status || 'active';
  pwHint.textContent = u.id ? '(leave blank to keep current)' : '*';
  new bootstrap.Modal(uModal).show();
}
</script>
