<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
  <div class="d-flex align-items-center gap-2">
    <h1 class="page-title">Financial &amp; Payment Console</h1>
    <span class="badge badge-futuristic"><span class="pulse-dot"></span> MULTI-BRANCH REVENUE</span>
  </div>
  <?php if (Auth::can('reports.view')): ?>
    <a href="<?= url('export', ['type' => 'daily_payments', 'branch' => $br, 'date_from' => $df, 'date_to' => $dt, 'format' => 'xlsx']) ?>" class="btn btn-outline-secondary btn-sm">
      <i class="bi bi-file-earmark-excel-fill text-success"></i> Export Payments Excel
    </a>
  <?php endif; ?>
</div>

<ul class="nav nav-tabs mb-4">
  <li class="nav-item">
    <a class="nav-link <?= $tab === 'all' ? 'active' : '' ?>" href="<?= url('payments') ?>">
      <i class="bi bi-clock-history me-1"></i> Verified Payment History
    </a>
  </li>
  <li class="nav-item">
    <a class="nav-link <?= $tab === 'pending' ? 'active' : '' ?>" href="<?= url('payments', ['tab' => 'pending']) ?>">
      <i class="bi bi-exclamation-octagon-fill text-warning me-1"></i> Outstanding Balances &amp; Unpaid
    </a>
  </li>
</ul>

<?php if ($tab === 'pending'): ?>
<div class="alert alert-warning py-3 d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 shadow-sm border-warning-subtle">
  <div class="d-flex align-items-center gap-3">
    <i class="bi bi-exclamation-triangle-fill text-warning fs-3 flex-shrink-0"></i>
    <div>
      Total outstanding balance across all listed memberships: <strong class="fs-5 text-danger"><?= money($outstanding) ?></strong>.
      <div class="small text-muted">Members with pending balances are blocked at the biometric door gate across all 6 branches until settled.</div>
    </div>
  </div>
  <form method="get" class="d-flex gap-2">
    <input type="hidden" name="page" value="payments">
    <input type="hidden" name="tab" value="pending">
    <input name="q" value="<?= e($q) ?>" class="form-control form-control-sm" placeholder="Search member or ID…">
    <?php if (Auth::isSuper()): ?>
      <select name="branch" class="form-select form-select-sm">
        <?= opts(branch_list(false), $br, 'id', 'name', 'All Branches') ?>
      </select>
    <?php endif; ?>
    <button class="btn btn-sm btn-brand"><i class="bi bi-search"></i></button>
  </form>
</div>

<div class="card shadow-sm">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead>
        <tr>
          <th>Customer</th>
          <th>Plan</th>
          <th>Registered Facility</th>
          <th>Total Price</th>
          <th>Paid</th>
          <th>Outstanding Balance</th>
          <th class="text-end">Action</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($rows as $r):
        $bal = round($r['price'] - $r['amount_paid'], 2);
        $waText = "Asc {$r['full_name']} ({$r['customer_code']}), waxaan ku xasuusinaynaa baaqiga lacagta xubinnimadaada CAATO FITNESS GYM ({$r['plan_name']}) oo ah " . money($bal) . ". Fadlan bixi si kaadhkaagu u shaqeeyo. Mahadsanid!";
      ?>
        <tr>
          <td>
            <a class="fw-bold" href="<?= url('customer_view', ['id' => $r['cid']]) ?>"><?= e($r['full_name']) ?></a>
            <small class="text-muted d-block"><i class="bi bi-upc-scan me-1"></i><?= e($r['customer_code']) ?> · <i class="bi bi-telephone me-1"></i><?= e($r['phone']) ?></small>
          </td>
          <td><span class="badge text-bg-light border"><?= e($r['plan_name']) ?></span></td>
          <td><i class="bi bi-geo-alt-fill text-danger me-1"></i><?= e($r['branch_name']) ?></td>
          <td><?= money($r['price']) ?></td>
          <td class="text-success fw-semibold"><?= money($r['amount_paid']) ?></td>
          <td class="fw-bold text-danger fs-6"><?= money($bal) ?></td>
          <td class="text-end text-nowrap">
            <a href="<?= e(whatsapp_url($r['phone'], $waText)) ?>" target="_blank" class="btn btn-sm btn-whatsapp" title="Send WhatsApp Balance Reminder">
              <i class="bi bi-whatsapp"></i> Remind
            </a>
            <?php if (Auth::can('payments.create')): ?>
              <button class="btn btn-sm btn-brand" data-pay="<?= $r['id'] ?>" data-balance="<?= $bal ?>" data-who="<?= e($r['full_name']) ?>">
                <i class="bi bi-cash-coin"></i> Collect Payment
              </button>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; if (!$rows): ?>
        <tr><td colspan="7" class="text-center text-muted py-5"><i class="bi bi-check2-circle fs-2 text-success d-block mb-2"></i>No outstanding balances found. All accounts up to date!</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php if (Auth::can('payments.create')) include ROOT . '/app/views/partials/pay_modal.php'; ?>

<?php else: ?>

<!-- Quick Method Filter Pills -->
<div class="d-flex flex-wrap gap-2 mb-3">
  <a href="<?= url('payments', array_filter(['q' => $q, 'date_from' => $df, 'date_to' => $dt, 'branch' => $br])) ?>" class="btn btn-sm <?= !$mt ? 'btn-brand' : 'btn-outline-secondary' ?>">
    <i class="bi bi-wallet2"></i> All Channels
  </a>
  <?php foreach ($methods as $m): ?>
    <a href="<?= url('payments', array_filter(['q' => $q, 'date_from' => $df, 'date_to' => $dt, 'branch' => $br, 'method' => $m['id']])) ?>" class="btn btn-sm <?= $mt == $m['id'] ? 'btn-brand' : 'btn-outline-secondary' ?>">
      <i class="bi bi-phone-flip"></i> <?= e($m['name']) ?>
    </a>
  <?php endforeach; ?>
</div>

<div class="card mb-4 shadow-sm">
  <div class="card-body p-3 bg-body-tertiary">
    <form class="row g-2 align-items-center" method="get">
      <input type="hidden" name="page" value="payments">
      <div class="col-md-3">
        <div class="input-group input-group-sm">
          <span class="input-group-text"><i class="bi bi-search"></i></span>
          <input name="q" value="<?= e($q) ?>" class="form-control" placeholder="Receipt #, reference or member name">
        </div>
      </div>
      <div class="col-md-2">
        <div class="input-group input-group-sm">
          <span class="input-group-text"><i class="bi bi-calendar-event"></i></span>
          <input type="date" name="date_from" value="<?= e($df) ?>" class="form-control">
        </div>
      </div>
      <div class="col-md-2">
        <div class="input-group input-group-sm">
          <span class="input-group-text"><i class="bi bi-calendar-check"></i></span>
          <input type="date" name="date_to" value="<?= e($dt) ?>" class="form-control">
        </div>
      </div>
      <div class="col-md-2">
        <select name="method" class="form-select form-select-sm">
          <?= opts($methods, $mt, 'id', 'name', 'Channel: Any') ?>
        </select>
      </div>
      <?php if (Auth::isSuper()): ?>
      <div class="col-md-2">
        <select name="branch" class="form-select form-select-sm">
          <?= opts(branch_list(false), $br, 'id', 'name', 'Facility: Any') ?>
        </select>
      </div>
      <?php endif; ?>
      <div class="col-md-1 d-grid">
        <button class="btn btn-sm btn-brand"><i class="bi bi-funnel-fill"></i></button>
      </div>
    </form>
  </div>
</div>

<div class="card shadow-sm">
  <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
    <span><i class="bi bi-receipt-cutoff text-brand me-2 fs-5"></i> Verified Payment Ledger</span>
    <span class="badge text-bg-success fs-6"><i class="bi bi-cash-stack me-1"></i> Filtered Total: <?= money($sum) ?></span>
  </div>
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead>
        <tr>
          <th>Receipt #</th>
          <th>Date &amp; Time</th>
          <th>Customer</th>
          <th>Plan</th>
          <th>Received At</th>
          <th>Channel &amp; Ref</th>
          <th>Amount Paid</th>
          <th class="text-end">Receipt</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><a class="fw-bold font-monospace" href="<?= url('receipt', ['id' => $r['id']]) ?>"><i class="bi bi-file-earmark-text me-1"></i><?= e($r['receipt_no']) ?></a></td>
          <td><small class="text-muted"><i class="bi bi-clock me-1"></i><?= fmt_dt($r['payment_date']) ?></small></td>
          <td>
            <a class="fw-semibold" href="<?= url('customer_view', ['id' => $r['customer_id']]) ?>"><?= e($r['full_name']) ?></a>
            <small class="text-muted d-block font-monospace"><?= e($r['customer_code']) ?></small>
          </td>
          <td><span class="badge text-bg-light border"><?= e($r['plan_name']) ?></span></td>
          <td><i class="bi bi-geo-alt-fill text-danger me-1"></i><?= e(str_replace('CAATO FITNESS GYM – ', '', $r['branch_name'])) ?></td>
          <td>
            <span class="badge text-bg-secondary"><?= e($r['method']) ?></span>
            <?= $r['reference'] ? '<br><small class="text-muted font-monospace">Ref: ' . e($r['reference']) . '</small>' : '' ?>
          </td>
          <td class="fw-bold text-brand fs-6"><?= money($r['amount']) ?></td>
          <td class="text-end">
            <a href="<?= url('receipt', ['id' => $r['id']]) ?>" class="btn btn-sm btn-outline-brand py-1 px-2" title="Print / View Receipt">
              <i class="bi bi-printer-fill"></i>
            </a>
            <a href="<?= url('receipt', ['id' => $r['id'], 'format' => 'pdf']) ?>" class="btn btn-sm btn-outline-secondary py-1 px-2" title="Download PDF">
              <i class="bi bi-file-earmark-pdf-fill text-danger"></i>
            </a>
          </td>
        </tr>
      <?php endforeach; if (!$rows): ?>
        <tr><td colspan="8" class="text-center text-muted py-5"><i class="bi bi-receipt fs-2 d-block mb-2"></i>No payment transactions found.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
  <div class="card-footer bg-transparent py-3"><?= pager($pg) ?></div>
</div>
<?php endif; ?>
