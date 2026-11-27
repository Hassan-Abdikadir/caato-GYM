<?php
$msActs = ($tab === 'plans' && Auth::can('plans.manage'))
  ? '<button class="btn btn-brand btn-sm px-3 py-2" onclick="planForm()"><i class="bi bi-plus-circle-fill"></i> Create New Plan Tier</button>'
  : (Auth::can('customers.create') ? '<a href="' . url('customer_register') . '" class="btn btn-brand btn-sm px-3 py-2"><i class="bi bi-person-plus-fill"></i> Sell New Membership</a>' : '');
$activePlansCnt = count(array_filter($plans, fn($x) => $x['status'] === 'active'));
$totalActiveSubs = array_sum(array_map(fn($x) => (int) ($x['active_count'] ?? 0), $plans));
page_banner(
  'SUBSCRIPTION ENGINE · 6-BRANCH UNIVERSAL PASSES',
  'Memberships &amp;',
  'Plan Tiers',
  'Manage member subscriptions, renewal lifecycles, validity countdowns & pricing plans across all six facilities.',
  $msActs,
  [
    [$activePlansCnt, 'Active Plans', 'warning'],
    [(int) ($pg['total'] ?? $totalActiveSubs), 'Subscriptions', 'info'],
  ]
);
?>

<!-- 3D Holographic KPI Summary Strip -->
<div class="row g-3 mb-4">
  <div class="col-md-4">
    <div class="card stat stat-pro brand">
      <div class="stat-glow-orb"></div>
      <i class="bi bi-tags-fill stat-watermark"></i>
      <div class="stat-top">
        <div class="ico"><i class="bi bi-tags-fill"></i></div>
        <div class="stat-meta-right">
          <span class="stat-pill"><span class="stat-dot"></span> Pricing Engine</span>
        </div>
      </div>
      <div class="stat-body">
        <div class="num"><?= $activePlansCnt ?> / <?= count($plans) ?></div>
        <div class="stat-title">ACTIVE PRICING TIERS</div>
      </div>
      <div class="stat-foot">
        <span class="stat-sub"><i class="bi bi-diagram-3-fill"></i> Universal 6-branch pass plans</span>
        <div class="stat-spark"><div class="stat-spark-fill" style="width:92%"></div></div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card stat stat-pro emerald">
      <div class="stat-glow-orb"></div>
      <i class="bi bi-patch-check-fill stat-watermark"></i>
      <div class="stat-top">
        <div class="ico"><i class="bi bi-patch-check-fill"></i></div>
        <div class="stat-meta-right">
          <span class="stat-pill"><span class="stat-dot"></span> Verified Passes</span>
        </div>
      </div>
      <div class="stat-body">
        <div class="num"><?= (int) $totalActiveSubs ?></div>
        <div class="stat-title">ACTIVE SUBSCRIBERS</div>
      </div>
      <div class="stat-foot">
        <span class="stat-sub"><i class="bi bi-fingerprint"></i> Authorized for biometric gate access</span>
        <div class="stat-spark"><div class="stat-spark-fill" style="width:86%"></div></div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card stat stat-pro cyan">
      <div class="stat-glow-orb"></div>
      <i class="bi bi-card-checklist stat-watermark"></i>
      <div class="stat-top">
        <div class="ico"><i class="bi bi-card-checklist"></i></div>
        <div class="stat-meta-right">
          <span class="stat-pill"><span class="stat-dot"></span> Cloud Registry</span>
        </div>
      </div>
      <div class="stat-body">
        <div class="num"><?= (int) ($pg['total'] ?? count($rows)) ?></div>
        <div class="stat-title">FILTERED SUBSCRIPTIONS</div>
      </div>
      <div class="stat-foot">
        <span class="stat-sub"><i class="bi bi-clock-history"></i> Full lifecycle &amp; renewal tracking</span>
        <div class="stat-spark"><div class="stat-spark-fill" style="width:100%"></div></div>
      </div>
    </div>
  </div>
</div>

<ul class="nav nav-tabs mb-4">
  <li class="nav-item">
    <a class="nav-link <?= $tab === 'list' ? 'active' : '' ?>" href="<?= url('memberships') ?>">
      <i class="bi bi-list-stars me-1"></i> Member Subscriptions
    </a>
  </li>
  <li class="nav-item">
    <a class="nav-link <?= $tab === 'plans' ? 'active' : '' ?>" href="<?= url('memberships', ['tab' => 'plans']) ?>">
      <i class="bi bi-tags-fill me-1"></i> Membership Plans &amp; Pricing Tiers
    </a>
  </li>
</ul>

<?php if ($tab === 'plans'): ?>
<!-- 2026 3D Holographic Bento Pricing Tier Cards -->
<div class="row g-3 mb-4">
<?php
$tierThemes = ['brand', 'emerald', 'cyan', 'violet', 'amber', 'red'];
$tierIcons  = ['daily' => 'bi-lightning-charge-fill', 'weekly' => 'bi-calendar-week-fill', 'monthly' => 'bi-gem', 'quarterly' => 'bi-trophy-fill', 'semiannual' => 'bi-award-fill', 'annual' => 'bi-stars'];
foreach ($plans as $idx => $p):
  $th = $p['status'] === 'active' ? $tierThemes[$idx % count($tierThemes)] : 'red';
  $tIcon = $tierIcons[$p['type']] ?? 'bi-patch-check-fill';
  $subCnt = (int) ($p['active_count'] ?? 0);
  $subPct = $totalActiveSubs > 0 ? min(100, max(16, (int) round(($subCnt / $totalActiveSubs) * 100))) : 35;
?>
  <div class="col-md-6 col-xl-4">
    <div class="card stat stat-pro <?= $th ?> h-100">
      <div class="stat-glow-orb"></div>
      <i class="bi <?= $tIcon ?> stat-watermark"></i>
      <div class="stat-top">
        <div class="ico"><i class="bi <?= $tIcon ?>"></i></div>
        <div class="stat-meta-right">
          <span class="stat-pill"><span class="stat-dot"></span> <?= strtoupper(e($p['type'])) ?> · <?= (int) $p['duration_days'] ?>D</span>
          <?= badge($p['status']) ?>
        </div>
      </div>

      <div class="stat-body my-2">
        <div class="d-flex align-items-baseline justify-content-between flex-wrap gap-2">
          <div>
            <div class="stat-title mb-1"><?= e($p['name']) ?></div>
            <div class="num"><?= money($p['price']) ?> <small class="text-muted fw-normal" style="font-size:0.85rem">/ <?= (int) $p['duration_days'] ?> days</small></div>
          </div>
        </div>
      </div>

      <div class="stat-foot mt-auto">
        <div class="d-flex justify-content-between align-items-center w-100">
          <span class="stat-sub"><i class="bi bi-people-fill"></i> Active Subscribers</span>
          <strong class="text-success small"><?= $subCnt ?> members</strong>
        </div>
        <div class="stat-spark"><div class="stat-spark-fill" style="width:<?= $subPct ?>%"></div></div>
        <?php if (Auth::can('plans.manage')): ?>
        <div class="d-flex gap-2 mt-2 pt-1">
          <button class="btn btn-sm btn-outline-brand flex-grow-1" onclick="planForm(<?= jsattr($p) ?>)">
            <i class="bi bi-pencil-square"></i> Edit Price / Days
          </button>
          <form method="post" class="d-inline">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="toggle_plan">
            <input type="hidden" name="id" value="<?= $p['id'] ?>">
            <button class="btn btn-sm btn-outline-danger" data-confirm="Change the status of the <?= e($p['name']) ?> plan?" title="Toggle Active/Inactive">
              <?= $p['status'] === 'active' ? '<i class="bi bi-slash-circle"></i>' : '<i class="bi bi-check-circle"></i>' ?>
            </button>
          </form>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
<?php endforeach; ?>
</div>

<div class="modal fade" id="planModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <form class="modal-content shadow-lg" method="post">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="save_plan">
      <input type="hidden" name="id" id="pid">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-tag-fill text-brand me-2 fs-5"></i> Membership Plan Tier</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body row g-3">
        <div class="col-12">
          <label class="form-label small fw-semibold">Plan Name *</label>
          <input name="name" id="pname" class="form-control" required placeholder="e.g. Monthly VIP">
        </div>
        <div class="col-6">
          <label class="form-label small fw-semibold">Billing Frequency</label>
          <select name="type" id="ptype" class="form-select">
            <?php foreach (['daily', 'weekly', 'monthly', 'quarterly', 'semiannual', 'annual', 'custom'] as $t): ?>
              <option value="<?= $t ?>"><?= ucfirst($t) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-6">
          <label class="form-label small fw-semibold">Duration (Days) *</label>
          <input type="number" name="duration_days" id="pdays" min="1" class="form-control" required placeholder="30">
        </div>
        <div class="col-12">
          <label class="form-label small fw-semibold">Price *</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
            <input type="number" step="0.01" min="0" name="price" id="pprice" class="form-control" required placeholder="0.00">
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-brand"><i class="bi bi-check-circle"></i> Save Membership Plan</button>
      </div>
    </form>
  </div>
</div>

<script>
function planForm(p){
  p = p || {};
  pid.value = p.id || '';
  pname.value = p.name || '';
  ptype.value = p.type || 'monthly';
  pdays.value = p.duration_days || 30;
  pprice.value = p.price || '';
  new bootstrap.Modal(planModal).show();
}
</script>

<?php else: ?>

<!-- 3D Bento Filter Bar -->
<div class="card card-bento-panel mb-4 shadow-sm">
  <i class="bi bi-funnel-fill panel-watermark"></i>
  <div class="card-body p-3 position-relative" style="z-index:1">
    <form class="row g-2 align-items-center" method="get">
      <input type="hidden" name="page" value="memberships">
      <div class="col-md-3">
        <div class="input-group input-group-sm">
          <span class="input-group-text"><i class="bi bi-person"></i></span>
          <input name="q" value="<?= e($q) ?>" class="form-control" placeholder="Customer name, ID or phone">
        </div>
      </div>
      <div class="col-md-2">
        <select name="status" class="form-select form-select-sm">
          <option value="">Status: Any</option>
          <?php foreach (['active', 'pending', 'expired', 'suspended', 'cancelled'] as $s): ?>
            <option value="<?= $s ?>" <?= $st === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2">
        <select name="pstatus" class="form-select form-select-sm">
          <option value="">Payment: Any</option>
          <?php foreach (['paid', 'partial', 'unpaid'] as $s): ?>
            <option value="<?= $s ?>" <?= $ps === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2">
        <select name="plan" class="form-select form-select-sm">
          <?= opts($plans, $pl, 'id', 'name', 'Plan: Any') ?>
        </select>
      </div>
      <?php if (Auth::isSuper()): ?>
      <div class="col-md-2">
        <select name="branch" class="form-select form-select-sm">
          <?= opts(branch_list(false), $br, 'id', 'name', 'Facility: All') ?>
        </select>
      </div>
      <?php else: ?>
      <div class="col-md-2">
        <div class="form-control form-control-sm bg-body-secondary fw-semibold text-truncate">
          <i class="bi bi-geo-alt-fill text-danger me-1"></i> <?= e(short_branch(Auth::user()['branch_name'] ?? '')) ?>
        </div>
      </div>
      <?php endif; ?>
      <div class="col-md-1 d-grid">
        <button class="btn btn-sm btn-brand" title="Filter"><i class="bi bi-funnel-fill"></i></button>
      </div>
    </form>
  </div>
</div>

<!-- 3D Bento Subscriptions Matrix -->
<div class="card card-bento-panel shadow-sm">
  <i class="bi bi-card-checklist panel-watermark"></i>
  <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
    <div class="d-flex align-items-center gap-2">
      <span class="panel-hdr-ico brand"><i class="bi bi-card-checklist"></i></span>
      <span>Active &amp; Historical Subscriptions Matrix</span>
    </div>
    <span class="stat-pill"><span class="stat-dot"></span> <?= (int) ($pg['total'] ?? count($rows)) ?> Records</span>
  </div>
  <div class="table-responsive">
    <table class="table table-hover table-pro-matrix align-middle mb-0">
      <thead>
        <tr>
          <th>Customer Profile</th>
          <th>Plan Tier</th>
          <th>Validity &amp; Countdown</th>
          <th>Origin Branch</th>
          <th>Paid / Price</th>
          <th>Payment</th>
          <th>Gate Status</th>
          <th class="text-end">Action</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($rows as $r):
        $bal = round($r['price'] - $r['amount_paid'], 2);
        $dLeft = max(0, (int) floor((strtotime($r['end_date']) - strtotime(date('Y-m-d'))) / 86400));
      ?>
        <tr>
          <td>
            <div class="d-flex align-items-center gap-2">
              <img src="<?= photo_url($r['profile_picture']) ?>" class="avatar" style="width:38px;height:38px">
              <div>
                <a class="fw-bold" href="<?= url('customer_view', ['id' => $r['customer_id']]) ?>"><?= e($r['full_name']) ?></a>
                <small class="text-muted d-block font-monospace"><?= e($r['customer_code']) ?></small>
              </div>
            </div>
          </td>
          <td><span class="badge text-bg-light border fw-semibold"><i class="bi bi-tag-fill text-brand me-1"></i><?= e($r['plan_name']) ?></span></td>
          <td>
            <small class="d-block fw-semibold"><i class="bi bi-calendar3 me-1"></i><?= fmt_date($r['start_date']) ?> &rarr; <?= fmt_date($r['end_date']) ?></small>
            <?php if ($r['eff'] === 'active'): ?>
              <small class="text-success fw-bold"><i class="bi bi-hourglass-split"></i> <?= $dLeft ?> days remaining</small>
            <?php endif; ?>
          </td>
          <td><span class="gate-visit-pill"><i class="bi bi-geo-alt-fill text-danger"></i> <?= e(short_branch($r['branch_name'])) ?></span></td>
          <td>
            <span class="matrix-rev-month"><?= money($r['amount_paid']) ?></span>
            <span class="text-muted small"> / <?= money($r['price']) ?></span>
          </td>
          <td><?= badge($r['payment_status']) ?></td>
          <td><?= badge($r['eff']) ?></td>
          <td class="text-end text-nowrap">
            <?php if ($bal > 0 && Auth::can('payments.create') && $r['status'] !== 'cancelled'): ?>
              <button class="btn btn-sm btn-brand py-1 px-2" data-pay="<?= $r['id'] ?>" data-balance="<?= $bal ?>" data-who="<?= e($r['full_name']) ?>">
                <i class="bi bi-cash-coin"></i> Pay <?= money($bal) ?>
              </button>
            <?php endif; ?>
            <a href="<?= url('customer_view', ['id' => $r['customer_id']]) ?>" class="btn btn-sm btn-outline-secondary py-1 px-2">
              <i class="bi bi-arrow-right"></i>
            </a>
          </td>
        </tr>
      <?php endforeach; if (!$rows): ?>
        <tr><td colspan="8" class="text-center text-muted py-5"><i class="bi bi-card-checklist fs-2 d-block mb-2"></i>No membership subscriptions found.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
  <div class="card-footer bg-transparent py-3"><?= pager($pg) ?></div>
</div>
<?php if (Auth::can('payments.create')) include ROOT . '/app/views/partials/pay_modal.php'; ?>
<?php endif; ?>
