<?php
page_banner(
  'UNIVERSAL 6-BRANCH PASS · NEW MEMBER ONBOARDING',
  'Register',
  'New Member',
  'Enroll a new athlete once — instantly synchronized with biometric door access, digital PVC card & all 6 CAATO facilities.',
  '<a href="' . url('customers') . '" class="btn btn-sm px-3 py-2" style="background:rgba(255,255,255,0.12);color:#fff;border:1px solid rgba(255,255,255,0.2)"><i class="bi bi-arrow-left"></i> Member Directory</a>'
);
?>

<form method="post" enctype="multipart/form-data">
<?= csrf_field() ?>
<div class="row g-3">
  <div class="col-lg-7">
    <div class="card shadow-sm h-100">
      <div class="card-header">
        <span><i class="bi bi-person-lines-fill text-brand me-2 fs-5"></i> Personal, Biometric &amp; Athletic Profile</span>
        <span class="badge text-bg-secondary">Step 1 of 2</span>
      </div>
      <div class="card-body p-4">
        <?php if (Auth::isSuper()): ?>
          <div class="mb-3">
            <label class="form-label fw-semibold small text-muted text-uppercase">Registering Facility Branch *</label>
            <select name="branch_id" class="form-select" required>
              <?= opts(branch_list(), $v['branch_id'] ?? '', 'id', 'name', 'Select branch facility…') ?>
            </select>
          </div>
        <?php else: ?>
          <div class="alert alert-light border py-2 small mb-3 d-flex align-items-center gap-2">
            <i class="bi bi-geo-alt-fill text-danger fs-5"></i>
            <div>Registering at Home Facility: <strong><?= e(Auth::user()['branch_name']) ?></strong></div>
          </div>
        <?php endif; ?>
        <?php include ROOT . '/app/views/partials/customer_fields.php'; ?>
      </div>
    </div>
  </div>

  <div class="col-lg-5">
    <!-- Optional Baseline Body Metrics Card -->
    <div class="card shadow-sm mb-3">
      <div class="card-header">
        <span><i class="bi bi-heart-pulse-fill text-danger me-2 fs-5"></i> Baseline Body Metrics <small class="text-muted fw-normal">(Optional)</small></span>
      </div>
      <div class="card-body p-3">
        <div class="row g-2">
          <div class="col-6">
            <label class="form-label small fw-semibold">Starting Weight (kg)</label>
            <div class="input-group input-group-sm">
              <input type="number" step="0.1" min="25" max="300" name="init_weight_kg" class="form-control" placeholder="e.g. 78.5" value="<?= e($v['init_weight_kg'] ?? '') ?>">
              <span class="input-group-text">kg</span>
            </div>
          </div>
          <div class="col-6">
            <label class="form-label small fw-semibold">Height (cm)</label>
            <div class="input-group input-group-sm">
              <input type="number" step="0.5" min="100" max="240" name="init_height_cm" class="form-control" placeholder="e.g. 176" value="<?= e($v['init_height_cm'] ?? '') ?>">
              <span class="input-group-text">cm</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Subscription & Immediate Payment Card -->
    <div class="card shadow-sm mb-3">
      <div class="card-header">
        <span><i class="bi bi-award-fill text-brand me-2 fs-5"></i> Membership &amp; Payment <small class="text-muted fw-normal ms-1">(Optional)</small></span>
        <span class="badge text-bg-secondary">Step 2 of 2</span>
      </div>
      <div class="card-body p-4">
        <label class="form-label small fw-semibold">Membership Plan</label>
        <select name="plan_id" id="planSel" class="form-select mb-3">
          <option value="" data-price="">No plan yet (Register profile only)</option>
          <?php foreach ($plans as $p): ?>
            <option value="<?= $p['id'] ?>" data-price="<?= $p['price'] ?>" <?= ($v['plan_id'] ?? '') == $p['id'] ? 'selected' : '' ?>>
              <?= e($p['name']) ?> – <?= money($p['price']) ?> (<?= $p['duration_days'] ?> days)
            </option>
          <?php endforeach; ?>
        </select>

        <label class="form-label small fw-semibold">Membership Start Date <small class="text-muted">(blank = today)</small></label>
        <div class="input-group mb-1">
          <span class="input-group-text"><i class="bi bi-calendar-event"></i></span>
          <input type="date" name="start_date" id="start_date" min="<?= date('Y-m-d') ?>" class="form-control" value="<?= e($v['start_date'] ?? '') ?>">
        </div>
        <small class="text-danger fw-semibold d-block mb-3" style="font-size:0.75rem">
          <i class="bi bi-shield-lock me-1"></i> Taariikhdii hore lama ogola (Only today or future dates accepted).
        </small>

        <hr class="my-3 opacity-25">
        <label class="form-label small fw-semibold">Amount Received Now</label>
        <div class="input-group mb-2">
          <span class="input-group-text"><i class="bi bi-cash"></i></span>
          <input type="number" step="0.01" min="0" name="amount" id="amt" class="form-control" placeholder="0.00" value="<?= e($v['amount'] ?? '') ?>">
          <button type="button" class="btn btn-outline-brand" id="fullBtn">Full Price</button>
        </div>

        <label class="form-label small fw-semibold mt-2">Payment Method</label>
        <select name="method_id" class="form-select mb-2">
          <option value="">Select payment channel…</option>
          <?php foreach ($methods as $m): ?>
            <option value="<?= $m['id'] ?>" <?= ($v['method_id'] ?? '') == $m['id'] ? 'selected' : '' ?>><?= e($m['name']) ?></option>
          <?php endforeach; ?>
        </select>

        <input name="reference" class="form-control mb-2" placeholder="Transaction reference (EVC Plus / Salaam Bank / E-Dahab)" value="<?= e($v['reference'] ?? '') ?>">

        <div class="form-check mb-3 mt-3">
          <input class="form-check-input" type="checkbox" name="verified" value="1" id="ver">
          <label for="ver" class="form-check-label small fw-semibold">I have received and verified this payment.</label>
        </div>

        <div class="alert alert-warning small py-2 mb-0 d-flex align-items-center gap-2">
          <i class="bi bi-info-circle-fill fs-5 text-warning flex-shrink-0"></i>
          <div>Membership becomes <strong>Active</strong> immediately once the full balance is paid.</div>
        </div>
      </div>
    </div>

    <button class="btn btn-brand btn-lg w-100 py-3 shadow-md">
      <i class="bi bi-person-check-fill"></i> Complete Member Registration
    </button>
  </div>
</div>
</form>

<script>
const planSel = document.getElementById('planSel'), amtEl = document.getElementById('amt');
document.getElementById('fullBtn').onclick = () => {
  const o = planSel.selectedOptions[0];
  amtEl.value = o.dataset.price || '';
};
planSel.addEventListener('change', () => {
  const o = planSel.selectedOptions[0];
  if (o && o.dataset.price && !amtEl.value) amtEl.value = o.dataset.price;
});
</script>
