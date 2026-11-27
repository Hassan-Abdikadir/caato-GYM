<?php
$eqActs = $canManage ? '<button class="btn btn-brand btn-sm px-3 py-2" onclick="eqForm()"><i class="bi bi-plus-circle-fill"></i> Register Equipment Asset</button>' : '';
$opCnt = count(array_filter($rows, fn($x) => $x['condition_status'] === 'operational'));
$alCnt = count($rows) - $opCnt;
$totalUnits = array_sum(array_map(fn($x) => (int) ($x['quantity'] ?? 1), $rows));
page_banner(
  '6-BRANCH HARDWARE FLEET · PREVENTIVE MAINTENANCE',
  'Equipment &amp;',
  'Asset Inventory',
  'Track commercial treadmills, racks, cables, free weights & preventive maintenance schedules across every branch.',
  $eqActs,
  [
    [count($rows), 'Total Assets', 'info'],
    [$opCnt, 'Operational', 'success'],
    [$alCnt, 'Needs Service', $alCnt > 0 ? 'danger' : 'warning'],
  ]
);
?>

<!-- 3D Holographic Equipment KPI Strip -->
<div class="row g-3 mb-4">
  <div class="col-md-4">
    <div class="card stat stat-pro cyan">
      <div class="stat-glow-orb"></div>
      <i class="bi bi-tools stat-watermark"></i>
      <div class="stat-top">
        <div class="ico"><i class="bi bi-tools"></i></div>
        <div class="stat-meta-right">
          <span class="stat-pill"><span class="stat-dot"></span> Fleet Total</span>
        </div>
      </div>
      <div class="stat-body">
        <div class="num"><?= count($rows) ?> Models · <?= (int) $totalUnits ?> Units</div>
        <div class="stat-title">REGISTERED GYM MACHINES</div>
      </div>
      <div class="stat-foot">
        <span class="stat-sub"><i class="bi bi-building-gear"></i> Cardio · Strength · Free Weights</span>
        <div class="stat-spark"><div class="stat-spark-fill" style="width:94%"></div></div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card stat stat-pro emerald">
      <div class="stat-glow-orb"></div>
      <i class="bi bi-check-circle-fill stat-watermark"></i>
      <div class="stat-top">
        <div class="ico"><i class="bi bi-check-circle-fill"></i></div>
        <div class="stat-meta-right">
          <span class="stat-pill"><span class="stat-dot"></span> Floor Ready</span>
        </div>
      </div>
      <div class="stat-body">
        <div class="num"><?= $opCnt ?> Operational</div>
        <div class="stat-title">ACTIVE FLOOR HARDWARE</div>
      </div>
      <div class="stat-foot">
        <span class="stat-sub"><i class="bi bi-shield-check"></i> Passes safety &amp; calibration checks</span>
        <div class="stat-spark"><div class="stat-spark-fill" style="width:<?= count($rows) ? round(($opCnt / count($rows)) * 100) : 100 ?>%"></div></div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card stat stat-pro <?= $alCnt > 0 ? 'red' : 'amber' ?>">
      <div class="stat-glow-orb"></div>
      <i class="bi bi-wrench-adjustable-circle-fill stat-watermark"></i>
      <div class="stat-top">
        <div class="ico"><i class="bi bi-wrench-adjustable-circle-fill"></i></div>
        <div class="stat-meta-right">
          <span class="stat-pill"><span class="stat-dot"></span> Maintenance</span>
        </div>
      </div>
      <div class="stat-body">
        <div class="num"><?= $alCnt ?> Alerts</div>
        <div class="stat-title">UNDER SERVICE / REPAIR</div>
      </div>
      <div class="stat-foot">
        <span class="stat-sub"><i class="bi bi-calendar-check"></i> Preventive service schedule tracked</span>
        <div class="stat-spark"><div class="stat-spark-fill" style="width:<?= $alCnt > 0 ? 60 : 18 ?>%"></div></div>
      </div>
    </div>
  </div>
</div>

<div class="d-flex flex-wrap gap-2 mb-3">
  <?php foreach ([
    '' => ['All Equipment', 'bi-grid-fill'],
    'operational' => ['Operational', 'bi-check-circle-fill text-success'],
    'maintenance' => ['Under Maintenance', 'bi-wrench-adjustable-circle-fill text-warning'],
    'out_of_order' => ['Out of Order', 'bi-exclamation-octagon-fill text-danger'],
  ] as $k => [$lbl, $ic]): ?>
    <a href="<?= url('equipment', array_filter(['branch' => $br, 'status' => $k])) ?>" class="btn btn-sm <?= $st === $k ? 'btn-brand' : 'btn-outline-secondary' ?>">
      <i class="bi <?= $ic ?>"></i> <?= $lbl ?>
    </a>
  <?php endforeach; ?>

  <?php if (Auth::isSuper()): ?>
    <form method="get" class="ms-auto">
      <input type="hidden" name="page" value="equipment">
      <select name="branch" class="form-select form-select-sm" onchange="this.form.submit()">
        <?= opts(branch_list(false), $br, 'id', 'name', 'All 6 Branches') ?>
      </select>
    </form>
  <?php else: ?>
    <span class="gate-visit-pill active ms-auto"><i class="bi bi-geo-alt-fill text-danger"></i> <?= e(short_branch(Auth::user()['branch_name'] ?? '')) ?> Fleet</span>
  <?php endif; ?>
</div>

<div class="card card-bento-panel shadow-sm">
  <i class="bi bi-tools panel-watermark"></i>
  <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
    <div class="d-flex align-items-center gap-2">
      <span class="panel-hdr-ico cyan"><i class="bi bi-tools"></i></span>
      <span>6-Branch Hardware &amp; Equipment Matrix</span>
    </div>
    <span class="stat-pill"><span class="stat-dot"></span> <?= count($rows) ?> Listed Assets</span>
  </div>
  <div class="table-responsive">
    <table class="table table-hover table-pro-matrix align-middle mb-0">
      <thead>
        <tr>
          <th>Asset Code</th>
          <th>Machine / Equipment Name</th>
          <th>Category</th>
          <th>Facility Node</th>
          <th>Qty</th>
          <th>Condition</th>
          <th>Last / Next Service</th>
          <?php if ($canManage): ?><th class="text-end">Actions</th><?php endif; ?>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td class="font-monospace fw-bold small"><?= e($r['code']) ?></td>
          <td>
            <div class="fw-bold"><?= e($r['name']) ?></div>
            <?php if ($r['notes']): ?><small class="text-muted"><?= e($r['notes']) ?></small><?php endif; ?>
          </td>
          <td><span class="badge text-bg-light border"><?= e($r['category']) ?></span></td>
          <td><span class="gate-visit-pill"><i class="bi bi-geo-alt-fill text-danger"></i> <?= e(short_branch($r['branch_name'])) ?></span></td>
          <td><span class="matrix-num"><?= (int) $r['quantity'] ?></span><span class="matrix-unit">units</span></td>
          <td><?= badge($r['condition_status']) ?></td>
          <td>
            <small class="d-block text-muted">Last: <?= fmt_date($r['last_service_date']) ?></small>
            <small class="d-block fw-semibold <?= ($r['next_service_date'] && $r['next_service_date'] <= date('Y-m-d')) ? 'text-danger' : 'text-info' ?>">
              Next: <?= fmt_date($r['next_service_date']) ?>
            </small>
          </td>
          <?php if ($canManage): ?>
          <td class="text-end text-nowrap">
            <button class="btn btn-sm btn-outline-secondary" onclick="eqForm(<?= jsattr($r) ?>)">
              <i class="bi bi-pencil-square"></i> Edit
            </button>
            <form method="post" class="d-inline">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?= $r['id'] ?>">
              <button class="btn btn-sm btn-outline-danger" data-confirm="Delete equipment asset <?= e($r['name']) ?>?">
                <i class="bi bi-trash3-fill"></i>
              </button>
            </form>
          </td>
          <?php endif; ?>
        </tr>
      <?php endforeach; if (!$rows): ?>
        <tr>
          <td colspan="8" class="text-center text-muted py-5">
            <i class="bi bi-tools fs-2 d-block mb-2"></i>
            No gym equipment logged yet. Register your treadmills, racks, benches &amp; machines above.
          </td>
        </tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php if ($canManage): ?>
<div class="modal fade" id="eqModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <form class="modal-content shadow-lg" method="post">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="save">
      <input type="hidden" name="id" id="eqId">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-tools text-brand me-2 fs-5"></i> Gym Equipment Asset</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body row g-3">
        <div class="col-8">
          <label class="form-label small fw-semibold">Equipment / Machine Name *</label>
          <input name="name" id="eqName" class="form-control" required placeholder="e.g. LifeFitness Commercial Treadmill T5">
        </div>
        <div class="col-4">
          <label class="form-label small fw-semibold">Quantity *</label>
          <input type="number" name="quantity" id="eqQty" min="1" value="1" class="form-control" required>
        </div>
        <div class="col-6">
          <label class="form-label small fw-semibold">Category</label>
          <select name="category" id="eqCat" class="form-select">
            <option>Cardio Machines</option>
            <option>Strength Machines</option>
            <option>Free Weights &amp; Racks</option>
            <option>Cables &amp; Functional</option>
            <option>Biometric &amp; Door Gates</option>
          </select>
        </div>
        <div class="col-6">
          <label class="form-label small fw-semibold">Facility Branch *</label>
          <select name="branch_id" id="eqBr" class="form-select" required>
            <?= opts($branches, '', 'id', 'name') ?>
          </select>
        </div>
        <div class="col-4">
          <label class="form-label small fw-semibold">Condition Status</label>
          <select name="condition_status" id="eqCond" class="form-select">
            <option value="operational">Operational</option>
            <option value="maintenance">Under Maintenance</option>
            <option value="out_of_order">Out of Order</option>
          </select>
        </div>
        <div class="col-4">
          <label class="form-label small fw-semibold">Last Service Date</label>
          <input type="date" name="last_service_date" id="eqLast" class="form-control">
        </div>
        <div class="col-4">
          <label class="form-label small fw-semibold">Next Service Due</label>
          <input type="date" name="next_service_date" id="eqNext" class="form-control">
        </div>
        <div class="col-12">
          <label class="form-label small fw-semibold">Maintenance / Technician Notes</label>
          <input name="notes" id="eqNotes" class="form-control" placeholder="Serial number, belt status or service note">
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-brand"><i class="bi bi-check-circle"></i> Save Equipment Asset</button>
      </div>
    </form>
  </div>
</div>

<script>
function eqForm(e) {
  e = e || {};
  document.getElementById('eqId').value = e.id || '';
  document.getElementById('eqName').value = e.name || '';
  document.getElementById('eqQty').value = e.quantity || 1;
  document.getElementById('eqCat').value = e.category || 'Strength Machines';
  if (e.branch_id) document.getElementById('eqBr').value = e.branch_id;
  document.getElementById('eqCond').value = e.condition_status || 'operational';
  document.getElementById('eqLast').value = e.last_service_date || '';
  document.getElementById('eqNext').value = e.next_service_date || '';
  document.getElementById('eqNotes').value = e.notes || '';
  new bootstrap.Modal(document.getElementById('eqModal')).show();
}
</script>
<?php endif; ?>
