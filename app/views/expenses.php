<?php
page_banner(
  'EXPENDITURE LEDGER · FACILITY COST CONTROL',
  'Operating',
  'Expenses',
  'Record rent, utilities, equipment maintenance, salaries & daily operating expenditures across all 6 branches.',
  '',
  [
    [money($total), 'Period Total', 'danger'],
    [(int) ($pg['total'] ?? 0), 'Expense Entries', 'warning'],
  ]
);
?>

<?php if (!empty($catBreakdown)): ?>
<div class="row g-3 mb-4">
<?php
$expThemes = ['amber', 'red', 'violet', 'cyan', 'brand', 'emerald'];
foreach ($catBreakdown as $idx => $cb):
  $th = $expThemes[$idx % count($expThemes)];
  $pct = $total > 0 ? min(100, max(12, (int) round(($cb['total'] / $total) * 100))) : 40;
?>
  <div class="col-6 col-md-4 col-xl-2">
    <div class="card stat stat-pro <?= $th ?> h-100">
      <div class="stat-glow-orb"></div>
      <i class="bi bi-pie-chart-fill stat-watermark"></i>
      <div class="stat-top">
        <div class="ico" style="width:42px;height:42px;font-size:1.15rem!important"><i class="bi bi-pie-chart-fill"></i></div>
        <div class="stat-meta-right">
          <span class="stat-pill"><span class="stat-dot"></span> <?= (int) $cb['cnt'] ?>x</span>
        </div>
      </div>
      <div class="stat-body">
        <div class="num fs-4"><?= money($cb['total']) ?></div>
        <div class="stat-title text-truncate"><?= e($cb['category']) ?></div>
      </div>
      <div class="stat-foot">
        <div class="stat-spark"><div class="stat-spark-fill" style="width:<?= $pct ?>%"></div></div>
      </div>
    </div>
  </div>
<?php endforeach; ?>
</div>
<?php endif; ?>

<div class="card card-bento-panel mb-4 shadow-sm">
  <i class="bi bi-plus-circle-fill panel-watermark"></i>
  <div class="card-header d-flex align-items-center gap-2">
    <span class="panel-hdr-ico brand"><i class="bi bi-plus-circle-fill"></i></span>
    <span>Record New Facility Expense</span>
  </div>
  <div class="card-body p-3 position-relative" style="z-index:1">
    <form class="row g-2 align-items-end" method="post">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="add">
      <?php if (Auth::isSuper()): ?>
      <div class="col-md-2">
        <label class="form-label small fw-semibold text-muted text-uppercase">Branch</label>
        <select name="branch_id" class="form-select" required>
          <?= opts($branches, '', 'id', 'name', 'Facility…') ?>
        </select>
      </div>
      <?php else: ?>
      <div class="col-md-2">
        <label class="form-label small fw-semibold text-muted text-uppercase">Branch</label>
        <div class="form-control bg-body-secondary fw-semibold text-truncate">
          <i class="bi bi-geo-alt-fill text-danger me-1"></i> <?= e(short_branch(Auth::user()['branch_name'] ?? '')) ?>
        </div>
      </div>
      <?php endif; ?>
      <div class="col-md-2">
        <label class="form-label small fw-semibold text-muted text-uppercase">Category</label>
        <input name="category" list="cats" class="form-control" placeholder="Category" required>
        <datalist id="cats">
          <option>Rent</option><option>Electricity</option><option>Water</option>
          <option>Equipment</option><option>Maintenance</option><option>Salaries</option>
          <option>Cleaning</option><option>Marketing</option><option>Internet &amp; Software</option>
        </datalist>
      </div>
      <div class="col-md-3">
        <label class="form-label small fw-semibold text-muted text-uppercase">Description</label>
        <input name="description" class="form-control" placeholder="Description of expense">
      </div>
      <div class="col-md-2">
        <label class="form-label small fw-semibold text-muted text-uppercase">Amount</label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-cash"></i></span>
          <input type="number" step="0.01" min="0.01" name="amount" class="form-control" placeholder="0.00" required>
        </div>
      </div>
      <div class="col-md-2">
        <label class="form-label small fw-semibold text-muted text-uppercase">Date</label>
        <input type="date" name="expense_date" value="<?= date('Y-m-d') ?>" class="form-control" required>
      </div>
      <div class="col-md-1 d-grid">
        <button class="btn btn-brand"><i class="bi bi-plus-lg"></i> Add</button>
      </div>
    </form>
  </div>
</div>

<div class="card card-bento-panel shadow-sm">
  <i class="bi bi-receipt-cutoff panel-watermark"></i>
  <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
    <div class="d-flex align-items-center gap-2">
      <span class="panel-hdr-ico amber"><i class="bi bi-receipt-cutoff"></i></span>
      <span>Expense Ledger</span>
      <span class="badge text-bg-danger ms-2"><i class="bi bi-cash-stack me-1"></i> Period Total: <?= money($total) ?></span>
    </div>
    <form class="d-flex gap-2 align-items-center" method="get">
      <input type="hidden" name="page" value="expenses">
      <input type="date" name="date_from" value="<?= e($df) ?>" class="form-control form-control-sm">
      <input type="date" name="date_to" value="<?= e($dt) ?>" class="form-control form-control-sm">
      <?php if (Auth::isSuper()): ?>
        <select name="branch" class="form-select form-select-sm">
          <?= opts(branch_list(false), $br, 'id', 'name', 'All branches') ?>
        </select>
      <?php endif; ?>
      <button class="btn btn-sm btn-brand"><i class="bi bi-funnel-fill"></i> Filter</button>
    </form>
  </div>
  <div class="table-responsive">
    <table class="table table-hover table-pro-matrix align-middle mb-0">
      <thead>
        <tr>
          <th>Date</th>
          <th>Facility</th>
          <th>Category</th>
          <th>Description</th>
          <th>Amount</th>
          <th>Recorded By</th>
          <th class="text-end">Action</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><small class="text-muted"><i class="bi bi-calendar3 me-1"></i><?= fmt_date($r['expense_date']) ?></small></td>
          <td><span class="gate-visit-pill"><i class="bi bi-geo-alt-fill text-danger"></i> <?= e(short_branch($r['branch_name'])) ?></span></td>
          <td><span class="badge text-bg-light border"><?= e($r['category']) ?></span></td>
          <td><?= e($r['description'] ?: '—') ?></td>
          <td class="fw-bold text-danger fs-6"><?= money($r['amount']) ?></td>
          <td><small class="text-muted"><i class="bi bi-person me-1"></i><?= e($r['by_name']) ?></small></td>
          <td class="text-end">
            <form method="post" class="d-inline">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?= $r['id'] ?>">
              <button class="btn btn-sm btn-outline-danger" data-confirm="Delete this expense record?">
                <i class="bi bi-trash-fill"></i>
              </button>
            </form>
          </td>
        </tr>
      <?php endforeach; if (!$rows): ?>
        <tr><td colspan="7" class="text-center text-muted py-5"><i class="bi bi-receipt fs-2 d-block mb-2"></i>No expense records found in this timeframe.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
  <div class="card-footer bg-transparent py-3"><?= pager($pg) ?></div>
</div>
