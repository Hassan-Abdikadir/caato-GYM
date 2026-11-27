<?php $payMethods = DB::all("SELECT * FROM payment_methods WHERE status='active' ORDER BY id"); ?>
<div class="modal fade" id="payModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <form class="modal-content shadow-lg" method="post" id="payForm" action="<?= url('payment_save') ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="membership_id">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-cash-coin text-success me-2 fs-4"></i> Record Payment</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="p-3 mb-3 rounded-3 bg-body-tertiary border">
          <small class="text-muted d-block text-uppercase">Member &amp; Outstanding Balance</small>
          <strong class="fs-6 text-brand" id="payWho"></strong>
        </div>

        <?php if (Auth::isSuper()): ?>
          <label class="form-label small fw-semibold">Branch Facility Receiving Payment *</label>
          <select name="branch_id" class="form-select mb-2" required>
            <?= opts(branch_list(), '', 'id', 'name', 'Select branch facility…') ?>
          </select>
        <?php endif; ?>

        <label class="form-label small fw-semibold">Amount to Pay *</label>
        <div class="input-group mb-2">
          <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
          <input type="number" step="0.01" min="0.01" name="amount" class="form-control" required placeholder="0.00">
        </div>

        <label class="form-label small fw-semibold">Payment Method *</label>
        <select name="method_id" class="form-select mb-2" required>
          <?= opts($payMethods, '', 'id', 'name', 'Select payment method…') ?>
        </select>

        <label class="form-label small fw-semibold">Transaction Reference (EVC Plus / Salaam Bank / E-Dahab)</label>
        <input name="reference" class="form-control mb-2" placeholder="e.g. TXN-99882211 — EVC / Salaam / E-Dahab">

        <label class="form-label small fw-semibold">Notes / Memo</label>
        <input name="notes" class="form-control mb-3" placeholder="Optional notes">

        <div class="form-check">
          <input class="form-check-input" type="checkbox" name="verified" value="1" id="verPay" required>
          <label class="form-check-label small" for="verPay">I confirm that this payment has been collected and verified.</label>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-brand w-100 py-2">
          <i class="bi bi-printer-fill"></i> Save Payment &amp; Generate Receipt
        </button>
      </div>
    </form>
  </div>
</div>
