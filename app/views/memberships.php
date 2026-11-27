<?php
$msActs = ($tab === 'plans' && Auth::can('plans.manage'))
  ? '<button class="btn btn-brand btn-sm px-3 py-2" onclick="planForm()"><i class="bi bi-plus-circle-fill"></i> Create New Plan Tier</button>'
  : (Auth::can('customers.create') ? '<a href="' . url('customer_register') . '" class="btn btn-brand btn-sm px-3 py-2"><i class="bi bi-person-plus-fill"></i> Sell New Membership</a>' : '');
$activePlansCnt = count(array_filter($plans, fn($x) => $x['status'] === 'active'));
page_banner(
  'SUBSCRIPTION ENGINE · 6-BRANCH UNIVERSAL PASSES',
  'Memberships &amp;',
  'Plan Tiers',
  'Manage member subscriptions, renewal lifecycles, validity countdowns & pricing plans across all six facilities.',
  $msActs,
  [
    [$activePlansCnt, 'Active Plans', 'warning'],
    [(int) ($pg['total'] ?? 0), 'Subscriptions', 'info'],
  ]
);
?>

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
<!-- 2026 Bento Pricing Tier Cards -->
<div class="row g-3 mb-4">
<?php foreach ($plans as $p): ?>
  <div class="col-md-6 col-xl-4">
    <div class="card h-100 shadow-sm position-relative" style="border-top:4px solid <?= $p['status'] === 'active' ? '#ff5722' : '#64748b' ?>">
      <div class="card-body p-4 d-flex flex-column">
        <div class="d-flex justify-content-between align-items-start mb-2">
          <div>
            <span class="badge text-bg-light border text-uppercase mb-1"><?= e($p['type']) ?></span>
            <h4 class="fw-bold mb-0"><?= e($p['name']) ?></h4>
          </div>
          <?= badge($p['status']) ?>
        </div>
        <div class="my-3">
          <span class="display-5 fw-bold text-brand" style="font-family:'Barlow Condensed'"><?= money($p['price']) ?></span>
          <span class="text-muted small">/ <?= (int) $p['duration_days'] ?> days</span>
        </div>
        <div class="p-2 px-3 rounded-3 bg-body-tertiary border small d-flex justify-content-between align-items-center mb-3">
          <span class="text-muted"><i class="bi bi-people-fill me-1"></i> Active Subscribers</span>
          <strong class="text-success"><?= (int) ($p['active_count'] ?? 0) ?> members</strong>
        </div>
        <?php if (Auth::can('plans.manage')): ?>
        <div class="mt-auto d-flex gap-2">
          <button class="btn btn-sm btn-outline-brand flex-grow-1" onclick="planForm(<?= jsattr($p) ?>)">
            <i class="bi bi-pencil-square"></i> Edit Price / Days
          </button>
          <form method="post" class="d-inline">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="toggle_plan">
            <input type="hidden" name="id" value="<?= $p['id'] ?>">
            <button class="btn btn-sm btn-outline-danger" data-confirm="Change the status of the <?= e($p['name']) ?> plan?">
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

<div class="card mb-4 shadow-sm">
  <div class="card-body p-3 bg-body-tertiary">
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
      <?php endif; ?>
      <div class="col-md-1 d-grid">
        <button class="btn btn-sm btn-brand" title="Filter"><i class="bi bi-funnel-fill"></i></button>
      </div>
    </form>
  </div>
</div>

<div class="card shadow-sm">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
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
          <td><span class="badge text-bg-light border fw-semibold"><?= e($r['plan_name']) ?></span></td>
          <td>
            <small class="d-block fw-semibold"><i class="bi bi-calendar3 me-1"></i><?= fmt_date($r['start_date']) ?> &rarr; <?= fmt_date($r['end_date']) ?></small>
            <?php if ($r['eff'] === 'active'): ?>
              <small class="text-success fw-bold"><?= $dLeft ?> days remaining</small>
            <?php endif; ?>
          </td>
          <td><i class="bi bi-geo-alt-fill text-danger me-1"></i><?= e(str_replace('CAATO FITNESS GYM – ', '', $r['branch_name'])) ?></td>
          <td><span class="fw-semibold"><?= money($r['amount_paid']) ?></span> / <?= money($r['price']) ?></td>
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
