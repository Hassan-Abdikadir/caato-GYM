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

<!-- 3D Holographic Quick Report Preset Cards -->
<div class="row g-2 mb-4">
  <?php
  $repThemes = ['brand', 'emerald', 'cyan', 'violet', 'amber', 'red'];
  $repIcons  = [
    'daily_payments'     => 'bi-cash-stack',
    'monthly_revenue'    => 'bi-graph-up-arrow',
    'customers'          => 'bi-people-fill',
    'active_expired'     => 'bi-patch-check-fill',
    'attendance'         => 'bi-fingerprint',
    'expenses'           => 'bi-receipt-cutoff',
    'employees'          => 'bi-person-badge-fill',
    'branch_performance' => 'bi-diagram-3-fill',
  ];
  $rIdx = 0;
  foreach (Report::TYPES as $k => $l):
    $th = $repThemes[$rIdx % count($repThemes)];
    $ic = $repIcons[$k] ?? 'bi-bar-chart-line-fill';
    $isSel = ($result && $f['type'] === $k);
    $rIdx++;
  ?>
    <div class="col-6 col-md-4 col-xl-3">
      <a href="<?= url('reports', ['type' => $k, 'branch' => $f['branch'], 'run' => 1]) ?>"
         class="card stat stat-pro <?= $th ?> text-decoration-none h-100 py-3 px-3 <?= $isSel ? 'border-2 border-warning' : '' ?>">
        <div class="stat-glow-orb"></div>
        <i class="bi <?= $ic ?> stat-watermark"></i>
        <div class="stat-top mb-1">
          <div class="ico" style="width:40px;height:40px;font-size:1.1rem!important"><i class="bi <?= $ic ?>"></i></div>
          <div class="stat-meta-right">
            <span class="stat-pill"><span class="stat-dot"></span> <?= $isSel ? 'Active' : 'Preset' ?></span>
          </div>
        </div>
        <div class="stat-body">
          <div class="fw-bold small text-body"><?= e($l) ?></div>
        </div>
      </a>
    </div>
  <?php endforeach; ?>
</div>

<div class="card card-bento-panel mb-4 shadow-sm">
  <i class="bi bi-sliders panel-watermark"></i>
  <div class="card-header d-flex align-items-center gap-2">
    <span class="panel-hdr-ico brand"><i class="bi bi-sliders"></i></span>
    <span>Custom Report Parameters &amp; Filters</span>
  </div>
  <div class="card-body p-3 position-relative" style="z-index:1">
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
      <?php else: ?>
      <div class="col-md-3">
        <label class="form-label small fw-semibold text-muted text-uppercase">Facility Branch</label>
        <div class="form-control bg-body-secondary fw-semibold text-truncate">
          <i class="bi bi-geo-alt-fill text-danger me-1"></i> <?= e(short_branch(Auth::user()['branch_name'] ?? '')) ?>
        </div>
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
<div class="card card-bento-panel shadow-sm">
  <i class="bi bi-table panel-watermark"></i>
  <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
    <div class="d-flex align-items-center gap-2">
      <span class="panel-hdr-ico emerald"><i class="bi bi-table"></i></span>
      <span class="fw-bold"><?= e($title) ?></span>
      <span class="stat-pill ms-1"><span class="stat-dot"></span> <?= count($rows) ?> Record(s)</span>
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
    <table class="table table-hover table-pro-matrix align-middle mb-0">
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
            <td><?= e(short_branch((string) $v)) ?></td>
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
<div class="card card-bento-panel shadow-sm p-5 text-center text-muted">
  <i class="bi bi-bar-chart-steps panel-watermark"></i>
  <i class="bi bi-bar-chart-steps fs-1 d-block mb-3 text-brand"></i>
  <h5>Select a Report Dataset Above</h5>
  <p class="small mb-0">Click any 3D report card above or configure your date range and click <strong>Run Report</strong>.</p>
</div>
<?php endif; ?>
