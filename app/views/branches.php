<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
  <div>
    <div class="d-flex align-items-center gap-2 mb-1">
      <h1 class="page-title">6-Branch Facility Network</h1>
      <span class="badge badge-futuristic"><span class="pulse-dot"></span> CENTRALIZED ENTERPRISE NODES</span>
    </div>
    <small class="text-muted"><i class="bi bi-diagram-3-fill text-brand me-1"></i> Manage all CAATO FITNESS GYM branches, local managers, door traffic &amp; monthly revenue</small>
  </div>
  <button class="btn btn-brand" onclick="bForm()">
    <i class="bi bi-building-add"></i> Add Facility Node
  </button>
</div>

<!-- 2026 Bento Branch Node Cards -->
<div class="row g-3 mb-4">
<?php foreach ($stats as $s): $x = $ex[$s['id']] ?? []; ?>
  <div class="col-md-6 col-xl-4">
    <div class="card h-100 shadow-sm" style="border-top:4px solid <?= $s['status'] === 'active' ? '#ff5722' : '#64748b' ?>">
      <div class="card-body p-4 d-flex flex-column">
        <div class="d-flex justify-content-between align-items-start mb-2">
          <div>
            <span class="badge text-bg-dark font-monospace mb-1"><?= e($s['code']) ?></span>
            <h5 class="fw-bold mb-0"><?= e($s['name']) ?></h5>
          </div>
          <?= badge($s['status']) ?>
        </div>

        <div class="small text-muted mb-3">
          <div><i class="bi bi-person-badge me-1 text-brand"></i> Manager: <strong><?= e($s['manager'] ?: 'Unassigned') ?></strong></div>
          <?php if (!empty($x['address'])): ?><div><i class="bi bi-geo-alt me-1 text-danger"></i> <?= e($x['address']) ?></div><?php endif; ?>
          <?php if (!empty($x['phone'])): ?><div><i class="bi bi-telephone me-1"></i> <?= e($x['phone']) ?></div><?php endif; ?>
        </div>

        <div class="row g-2 text-center mb-3">
          <div class="col-4">
            <div class="p-2 rounded-3 bg-body-tertiary border">
              <div class="fw-bold fs-5 text-success"><?= (int) $s['active_members'] ?></div>
              <small class="text-muted d-block" style="font-size:0.65rem">ACTIVE</small>
            </div>
          </div>
          <div class="col-4">
            <div class="p-2 rounded-3 bg-body-tertiary border">
              <div class="fw-bold fs-5 text-info"><?= (int) $s['attendance_today'] ?></div>
              <small class="text-muted d-block" style="font-size:0.65rem">TODAY VISITS</small>
            </div>
          </div>
          <div class="col-4">
            <div class="p-2 rounded-3 bg-body-tertiary border">
              <div class="fw-bold fs-6 text-brand"><?= money($s['revenue_month']) ?></div>
              <small class="text-muted d-block" style="font-size:0.65rem">MO. REVENUE</small>
            </div>
          </div>
        </div>

        <div class="mt-auto d-flex flex-wrap gap-1">
          <a class="btn btn-sm btn-outline-brand flex-grow-1" href="<?= url('dashboard', ['branch' => $s['id']]) ?>">
            <i class="bi bi-speedometer2"></i> Console
          </a>
          <a class="btn btn-sm btn-outline-secondary" href="<?= url('reports', ['type' => 'branch_performance', 'branch' => $s['id']]) ?>" title="Branch Analytics">
            <i class="bi bi-bar-chart"></i>
          </a>
          <a class="btn btn-sm btn-outline-secondary" href="<?= url('employees', ['branch' => $s['id']]) ?>" title="Branch Staff (<?= (int)$s['employees'] ?>)">
            <i class="bi bi-people"></i>
          </a>
          <button class="btn btn-sm btn-outline-secondary" onclick="bForm(<?= jsattr(["id" => $s["id"], "code" => $s["code"], "name" => $s["name"], "address" => $x["address"] ?? '', "phone" => $x["phone"] ?? '', "email" => $x["email"] ?? '']) ?>)" title="Edit Branch">
            <i class="bi bi-pencil-square"></i>
          </button>
          <form method="post" class="d-inline">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="toggle">
            <input type="hidden" name="id" value="<?= $s['id'] ?>">
            <button class="btn btn-sm btn-outline-danger" data-confirm="<?= $s['status'] === 'active' ? 'Deactivate this branch? Its staff will not be able to sign in and it will stop accepting check-ins.' : 'Activate this branch?' ?>">
              <?= $s['status'] === 'active' ? '<i class="bi bi-slash-circle"></i>' : '<i class="bi bi-check-circle"></i>' ?>
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
<?php endforeach; ?>
</div>

<div class="modal fade" id="bModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <form class="modal-content shadow-lg" method="post">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="save">
      <input type="hidden" name="id" id="bid">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-building text-brand me-2 fs-5"></i> Facility Node Configuration</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body row g-3">
        <div class="col-4">
          <label class="form-label small fw-semibold">Branch Code *</label>
          <input name="code" id="bcode" class="form-control font-monospace" required maxlength="20" placeholder="e.g. B6">
        </div>
        <div class="col-8">
          <label class="form-label small fw-semibold">Branch Name *</label>
          <input name="name" id="bname" class="form-control" required placeholder="CAATO FITNESS GYM – Branch">
        </div>
        <div class="col-12">
          <label class="form-label small fw-semibold">Physical Address</label>
          <input name="address" id="baddr" class="form-control" placeholder="Street, district or landmarks">
        </div>
        <div class="col-6">
          <label class="form-label small fw-semibold">Branch Contact Phone</label>
          <input name="phone" id="bphone" class="form-control" placeholder="+252 ...">
        </div>
        <div class="col-6">
          <label class="form-label small fw-semibold">Branch Email</label>
          <input name="email" id="bemail" class="form-control" placeholder="branch@caatogym.com">
        </div>
        <div class="col-12 small text-muted">
          <i class="bi bi-info-circle me-1"></i> Branch managers can be designated under the <strong>Access &amp; RBAC Users</strong> console.
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-brand"><i class="bi bi-check-circle"></i> Save Facility Details</button>
      </div>
    </form>
  </div>
</div>

<script>
function bForm(b){
  b = b || {};
  bid.value = b.id || '';
  bcode.value = b.code || '';
  bname.value = b.name || '';
  baddr.value = b.address || '';
  bphone.value = b.phone || '';
  bemail.value = b.email || '';
  new bootstrap.Modal(bModal).show();
}
</script>
