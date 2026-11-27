<?php
page_banner(
  'ENTERPRISE ANALYTICS & EXPORT · PDF & EXCEL (.XLSX)',
  'Reports &amp;',
  'Data Intelligence',
  'Generate real-time financial, attendance, membership, POS supplement bar & equipment reports across all 6 branches.',
  '',
  [
    [count(Report::TYPES), 'Report Models', 'info'],
    ['PDF & XLSX', 'Export Ready', 'success'],
  ]
);
?>

<!-- Quick Report Preset Pills -->
<div class="d-flex flex-wrap gap-2 mb-3">
  <?php foreach (Report::TYPES as $k => $l): ?>
    <a href="<?= url('reports', ['type' => $k, 'branch' => $f['branch'], 'run' => 1]) ?>" class="btn btn-sm <?= ($result && $f['type'] === $k) ? 'btn-brand' : 'btn-outline-secondary' ?>">
      <?= e($l) ?>
    </a>
  <?php endforeach; ?>
</div>

<div class="card mb-4 shadow-sm">
  <div class="card-header"><i class="bi bi-sliders text-brand me-2 fs-5"></i> Custom Report Parameters</div>
  <div class="card-body p-3 bg-body-tertiary">
    <form class="row g-2 align-items-end" method="get">
      <input type="hidden" name="page" value="reports">
      <input type="hidden" name="run" value="1">
      <div class="col-md-3">
        <label class="form-label small fw-semibold text-muted text-uppercase">Report Dataset</label>
        <select name="type" class="form-select">
          <?php foreach (Report::TYPES as $k => $l): ?>
            <option value="<?= $k ?>" <?= $f['type'] === $k ? 'selected' : '' ?>><?= e($l) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <?php if (Auth::isSuper()): ?>
      <div class="col-md-3">
        <label class="form-label small fw-semibold text-muted text-uppercase">Facility Branch</label>
        <select name="branch" class="form-select">
          <?= opts(branch_list(false), $f['branch'], 'id', 'name', 'All 6 Branches Combined') ?>
        </select>
      </div>
      <?php endif; ?>
      <div class="col-md-2">
        <label class="form-label small fw-semibold text-muted text-uppercase">Date From</label>
        <input type="date" name="date_from" value="<?= e($f['date_from']) ?>" class="form-control">
      </div>
      <div class="col-md-2">
        <label class="form-label small fw-semibold text-muted text-uppercase">Date To</label>
        <input type="date" name="date_to" value="<?= e($f['date_to']) ?>" class="form-control">
      </div>
      <div class="col-md-2">
        <label class="form-label small fw-semibold text-muted text-uppercase">Plan Tier</label>
        <select name="plan" class="form-select">
          <?= opts($plans, $f['plan'], 'id', 'name', 'Any Plan') ?>
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label small fw-semibold text-muted text-uppercase">Payment Status</label>
        <select name="pstatus" class="form-select">
          <option value="">Any Status</option>
          <?php foreach (['paid', 'partial', 'unpaid'] as $s): ?>
            <option value="<?= $s ?>" <?= $f['pstatus'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2 d-grid">
        <button class="btn btn-brand"><i class="bi bi-play-circle-fill"></i> Run Report</button>
      </div>
    </form>
  </div>
</div>

<?php if ($result): [$title, $cols, $rows] = $result; ?>
<div class="card shadow-sm">
  <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
    <div>
      <i class="bi bi-table text-brand me-2 fs-5"></i>
      <span class="fw-bold"><?= e($title) ?></span>
      <span class="badge text-bg-secondary ms-2"><?= count($rows) ?> Record(s)</span>
    </div>
    <div class="d-flex gap-2">
      <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()">
        <i class="bi bi-printer"></i> Print
      </button>
      <a class="btn btn-sm btn-outline-danger" href="<?= url('export') ?>&<?= e($qs) ?>&format=pdf">
        <i class="bi bi-file-earmark-pdf-fill"></i> Download PDF
      </a>
      <a class="btn btn-sm btn-outline-success" href="<?= url('export') ?>&<?= e($qs) ?>&format=xlsx">
        <i class="bi bi-file-earmark-excel-fill"></i> Download Excel (.xlsx)
      </a>
    </div>
  </div>
  <div class="table-responsive">
    <table class="table table-hover table-striped align-middle mb-0">
      <thead>
        <tr>
          <?php foreach ($cols as $c): ?>
            <th><?= e($c) ?></th>
          <?php endforeach; ?>
        </tr>
      </thead>
      <tbody>
      <?php foreach (array_slice($rows, 0, 500) as $r): ?>
        <tr>
          <?php foreach ($r as $v): ?>
            <td><?= e($v) ?></td>
          <?php endforeach; ?>
        </tr>
      <?php endforeach; if (!$rows): ?>
        <tr>
          <td colspan="<?= count($cols) ?>" class="text-center text-muted py-5">
            <i class="bi bi-clipboard-x fs-2 d-block mb-2"></i>
            No records matched the selected filters.
          </td>
        </tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
  <?php if (count($rows) > 500): ?>
    <div class="card-footer small text-muted py-2">
      <i class="bi bi-info-circle me-1"></i> Displaying first 500 rows. Use PDF or Excel export to download the complete dataset.
    </div>
  <?php endif; ?>
</div>
<?php else: ?>
<div class="card shadow-sm p-5 text-center text-muted">
  <i class="bi bi-bar-chart-steps fs-1 d-block mb-3 text-brand"></i>
  <h5>Select a Report Dataset Above</h5>
  <p class="small mb-0">Click any quick-report preset button above or configure your date range and click <strong>Run Report</strong>.</p>
</div>
<?php endif; ?>
