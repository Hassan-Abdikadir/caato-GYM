<?php
$activeUsr = count(array_filter($rows, fn($x) => $x['status'] === 'active'));
page_banner(
  'ZERO-TRUST ACCESS CONTROL · ROLE-BASED PERMISSIONS',
  'Access &amp;',
  'RBAC Users',
  'Super Admins command all 6 branches; Branch Managers, Receptionists, Accountants & Trainers are securely scoped.',
  '<button class="btn btn-brand btn-sm px-3 py-2" onclick="uForm()"><i class="bi bi-person-plus-fill"></i> New User Account</button>',
  [
    [count($rows), 'Total Accounts', 'info'],
    [$activeUsr, 'Active Logins', 'success'],
    [count($roles), 'RBAC Roles', 'warning'],
  ]
);
?>

<!-- 3D Holographic Role Breakdown Cards -->
<div class="row g-3 mb-4">
  <?php
  $roleCounts = [];
  foreach ($rows as $ur) $roleCounts[$ur['role_label']] = ($roleCounts[$ur['role_label']] ?? 0) + 1;
  $rThemes = ['brand', 'cyan', 'emerald', 'violet', 'amber', 'red'];
  $rIcons  = [
    'Super Admin'    => 'bi-shield-shaded',
    'Branch Manager' => 'bi-building-fill-gear',
    'Receptionist'   => 'bi-person-workspace',
    'Accountant'     => 'bi-safe2-fill',
    'Trainer'        => 'bi-trophy-fill',
  ];
  $rIdx = 0;
  foreach ($roleCounts as $rLbl => $rCnt):
    $th = $rThemes[$rIdx % count($rThemes)];
    $ic = $rIcons[$rLbl] ?? 'bi-shield-check';
    $pct = count($rows) > 0 ? min(100, max(18, (int) round(($rCnt / count($rows)) * 100))) : 40;
    $rIdx++;
  ?>
    <div class="col-6 col-md-4 col-xl">
      <div class="card stat stat-pro <?= $th ?> h-100">
        <div class="stat-glow-orb"></div>
        <i class="bi <?= $ic ?> stat-watermark"></i>
        <div class="stat-top">
          <div class="ico" style="width:44px;height:44px;font-size:1.2rem!important"><i class="bi <?= $ic ?>"></i></div>
          <div class="stat-meta-right">
            <span class="stat-pill"><span class="stat-dot"></span> RBAC</span>
          </div>
        </div>
        <div class="stat-body">
          <div class="num fs-3"><?= (int) $rCnt ?></div>
          <div class="stat-title"><?= e($rLbl) ?></div>
        </div>
        <div class="stat-foot">
          <div class="stat-spark"><div class="stat-spark-fill" style="width:<?= $pct ?>%"></div></div>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<div class="card card-bento-panel shadow-sm">
  <i class="bi bi-shield-lock-fill panel-watermark"></i>
  <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
    <div class="d-flex align-items-center gap-2">
      <span class="panel-hdr-ico brand"><i class="bi bi-shield-lock-fill"></i></span>
      <span>System Users &amp; RBAC Security Matrix</span>
    </div>
    <span class="stat-pill"><span class="stat-dot"></span> <?= count($rows) ?> Active &amp; Scoped Accounts</span>
  </div>
  <div class="table-responsive">
    <table class="table table-hover table-pro-matrix align-middle mb-0">
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
          <td><span class="badge text-bg-light border font-monospace"><i class="bi bi-person-fill-lock text-brand me-1"></i><?= e($r['username']) ?></span></td>
          <td><span class="badge text-bg-secondary"><?= e($r['role_label']) ?></span></td>
          <td>
            <span class="gate-visit-pill">
              <i class="bi bi-geo-alt-fill text-danger"></i>
              <?= e($r['branch_name'] ? short_branch($r['branch_name']) : 'All 6 Branches (Super Admin)') ?>
            </span>
          </td>
          <td><small class="text-muted"><i class="bi bi-clock me-1"></i><?= fmt_dt($r['last_login']) ?></small></td>
          <td>
            <?= badge($r['status']) ?>
            <?php if (!empty($r['locked_until']) && strtotime($r['locked_until']) > time()): ?>
              <span class="badge text-bg-danger ms-1"><i class="bi bi-lock-fill"></i> Locked</span>
            <?php endif; ?>
          </td>
          <td class="text-end text-nowrap">
            <button class="btn btn-sm btn-outline-brand" onclick="uForm(<?= jsattr(["id" => $r["id"], "full_name" => $r["full_name"], "username" => $r["username"], "email" => $r["email"], "role_id" => $r["role_id"], "branch_id" => $r["branch_id"], "status" => $r["status"]]) ?>)">
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
