<?php
$totBrRev = array_sum(array_map(fn($x) => (float) $x['revenue_month'], $stats));
$totBrAct = array_sum(array_map(fn($x) => (int) $x['active_members'], $stats));
$totBrVis = array_sum(array_map(fn($x) => (int) $x['attendance_today'], $stats));
page_banner(
  'CENTRALIZED ENTERPRISE NODES · 6 UNIFIED FACILITIES',
  '6-Branch',
  'Facility Network',
  'Monitor and configure all CAATO FITNESS GYM branches, local branch managers, door traffic & monthly revenue.',
  '<button class="btn btn-brand btn-sm px-3 py-2" onclick="bForm()"><i class="bi bi-building-add"></i> Add Facility Node</button>',
  [
    [count($stats), 'Facility Nodes', 'info'],
    [$totBrAct, 'Active Members', 'success'],
    [money($totBrRev), 'Month Revenue', 'warning'],
  ]
);
?>

<!-- 3D Holographic Network KPI Strip -->
<div class="row g-3 mb-4">
  <div class="col-md-4">
    <div class="card stat stat-pro brand">
      <div class="stat-glow-orb"></div>
      <i class="bi bi-diagram-3-fill stat-watermark"></i>
      <div class="stat-top">
        <div class="ico"><i class="bi bi-diagram-3-fill"></i></div>
        <div class="stat-meta-right">
          <span class="stat-pill"><span class="stat-dot"></span> Cloud Sync</span>
        </div>
      </div>
      <div class="stat-body">
        <div class="num"><?= count($stats) ?> Facilities</div>
        <div class="stat-title">UNIFIED BRANCH NETWORK</div>
      </div>
      <div class="stat-foot">
        <span class="stat-sub"><i class="bi bi-fingerprint"></i> Single membership valid at all gates</span>
        <div class="stat-spark"><div class="stat-spark-fill" style="width:100%"></div></div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card stat stat-pro emerald">
      <div class="stat-glow-orb"></div>
      <i class="bi bi-people-fill stat-watermark"></i>
      <div class="stat-top">
        <div class="ico"><i class="bi bi-people-fill"></i></div>
        <div class="stat-meta-right">
          <span class="stat-pill"><span class="stat-dot"></span> Network Pass</span>
        </div>
      </div>
      <div class="stat-body">
        <div class="num"><?= (int) $totBrAct ?> Active · <?= (int) $totBrVis ?> Today</div>
        <div class="stat-title">ACTIVE MEMBERS &amp; TODAY TRAFFIC</div>
      </div>
      <div class="stat-foot">
        <span class="stat-sub"><i class="bi bi-activity"></i> Live cross-branch door check-ins</span>
        <div class="stat-spark"><div class="stat-spark-fill" style="width:86%"></div></div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card stat stat-pro cyan">
      <div class="stat-glow-orb"></div>
      <i class="bi bi-cash-stack stat-watermark"></i>
      <div class="stat-top">
        <div class="ico"><i class="bi bi-cash-stack"></i></div>
        <div class="stat-meta-right">
          <span class="stat-pill"><span class="stat-dot"></span> <?= date('M Y') ?></span>
        </div>
      </div>
      <div class="stat-body">
        <div class="num"><?= money($totBrRev) ?></div>
        <div class="stat-title">COMBINED MONTHLY REVENUE</div>
      </div>
      <div class="stat-foot">
        <span class="stat-sub"><i class="bi bi-shield-check"></i> Verified across all 6 facility ledgers</span>
        <div class="stat-spark"><div class="stat-spark-fill" style="width:92%"></div></div>
      </div>
    </div>
  </div>
</div>

<!-- 2026 3D Holographic Bento Branch Node Cards -->
<div class="row g-3 mb-4">
<?php
$brThemes = ['brand', 'emerald', 'cyan', 'violet', 'amber', 'green'];
foreach ($stats as $idx => $s):
  $x = $ex[$s['id']] ?? [];
  $th = $s['status'] === 'active' ? $brThemes[$idx % count($brThemes)] : 'red';
  $revPct = $totBrRev > 0 ? min(100, max(16, (int) round(($s['revenue_month'] / $totBrRev) * 100))) : 55;
?>
  <div class="col-md-6 col-xl-4">
    <div class="card stat stat-pro <?= $th ?> h-100">
      <div class="stat-glow-orb"></div>
      <i class="bi bi-building-fill-check stat-watermark"></i>
      <div class="stat-top">
        <div class="d-flex align-items-center gap-2">
          <div class="ico"><i class="bi bi-building"></i></div>
          <div>
            <span class="badge text-bg-dark font-monospace mb-1"><?= e($s['code']) ?></span>
            <h5 class="fw-bold mb-0"><?= e(short_branch($s['name'])) ?></h5>
          </div>
        </div>
        <div class="stat-meta-right">
          <?= badge($s['status']) ?>
        </div>
      </div>

      <div class="stat-body my-1">
        <div class="small text-muted mb-3">
          <div class="mb-1"><i class="bi bi-person-badge-fill me-1 text-brand"></i> Manager: <strong class="text-body"><?= e($s['manager'] ?: 'Unassigned') ?></strong></div>
          <?php if (!empty($x['address'])): ?><div class="text-truncate"><i class="bi bi-geo-alt-fill me-1 text-danger"></i> <?= e($x['address']) ?></div><?php endif; ?>
          <?php if (!empty($x['phone'])): ?><div><i class="bi bi-telephone-fill me-1 text-info"></i> <?= e($x['phone']) ?></div><?php endif; ?>
        </div>

        <div class="row g-2 text-center">
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
      </div>

      <div class="stat-foot mt-auto">
        <div class="stat-spark mb-2"><div class="stat-spark-fill" style="width:<?= $revPct ?>%"></div></div>
        <div class="d-flex flex-wrap gap-1">
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
