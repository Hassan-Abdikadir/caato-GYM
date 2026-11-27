<?php
page_banner(
  'UNIVERSAL 6-BRANCH PASS · NEW MEMBER ONBOARDING',
  'Register',
  'New Member',
  'Enroll a new athlete once — instantly synchronized with biometric door access, digital PVC card & all 6 CAATO facilities.',
  '<a href="' . url('customers') . '" class="btn btn-sm px-3 py-2" style="background:rgba(255,255,255,0.12);color:#fff;border:1px solid rgba(255,255,255,0.2)"><i class="bi bi-arrow-left"></i> Member Directory</a>',
  [
    [count($plans), 'Active Plans', 'warning'],
    [count($methods), 'Pay Channels', 'success'],
    ['6 Branches', 'Universal Pass', 'info'],
  ]
);
?>

<!-- 3D Holographic Front-Desk Onboarding Workflow Strip -->
<div class="row g-3 mb-4">
  <div class="col-md-4">
    <div class="card stat stat-pro brand h-100">
      <div class="stat-glow-orb"></div>
      <i class="bi bi-person-bounding-box stat-watermark"></i>
      <div class="stat-top">
        <div class="ico"><i class="bi bi-person-bounding-box"></i></div>
        <div class="stat-meta-right">
          <span class="stat-pill"><span class="stat-dot"></span> Step 01</span>
        </div>
      </div>
      <div class="stat-body">
        <div class="num fs-4">Identity &amp; Biometrics</div>
        <div class="stat-title">PHOTO · FINGERPRINT · COACH</div>
      </div>
      <div class="stat-foot">
        <span class="stat-sub"><i class="bi bi-camera-video-fill"></i> Live webcam &amp; USB biometric token</span>
        <div class="stat-spark"><div class="stat-spark-fill" style="width:100%"></div></div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card stat stat-pro emerald h-100">
      <div class="stat-glow-orb"></div>
      <i class="bi bi-award-fill stat-watermark"></i>
      <div class="stat-top">
        <div class="ico"><i class="bi bi-award-fill"></i></div>
        <div class="stat-meta-right">
          <span class="stat-pill"><span class="stat-dot"></span> Step 02</span>
        </div>
      </div>
      <div class="stat-body">
        <div class="num fs-4">Plan &amp; Checkout</div>
        <div class="stat-title">EVC PLUS · SALAAM · E-DAHAB</div>
      </div>
      <div class="stat-foot">
        <span class="stat-sub"><i class="bi bi-shield-check"></i> Instant receipt &amp; gate activation</span>
        <div class="stat-spark"><div class="stat-spark-fill" style="width:90%"></div></div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card stat stat-pro cyan h-100">
      <div class="stat-glow-orb"></div>
      <i class="bi bi-person-vcard-fill stat-watermark"></i>
      <div class="stat-top">
        <div class="ico"><i class="bi bi-person-vcard-fill"></i></div>
        <div class="stat-meta-right">
          <span class="stat-pill"><span class="stat-dot"></span> Step 03</span>
        </div>
      </div>
      <div class="stat-body">
        <div class="num fs-4">Digital PVC &amp; QR</div>
        <div class="stat-title">6-BRANCH TURNSTILE CLEARANCE</div>
      </div>
      <div class="stat-foot">
        <span class="stat-sub"><i class="bi bi-qr-code-scan"></i> Printable ID card &amp; WhatsApp welcome</span>
        <div class="stat-spark"><div class="stat-spark-fill" style="width:95%"></div></div>
      </div>
    </div>
  </div>
</div>

<form method="post" enctype="multipart/form-data">
<?= csrf_field() ?>
<div class="row g-3">
  <div class="col-lg-7">
    <div class="card card-bento-panel shadow-sm h-100">
      <i class="bi bi-person-plus-fill panel-watermark"></i>
      <div class="card-header">
        <div class="d-flex align-items-center gap-2">
          <span class="panel-hdr-ico brand"><i class="bi bi-person-lines-fill"></i></span>
          <div>
            <div class="lh-1">Personal, Biometric &amp; Athletic Profile</div>
            <small class="text-muted fw-normal" style="font-size:0.71rem;font-family:'Outfit'">Identity, Fingerprint Token, Personal Coach &amp; Webcam Capture</small>
          </div>
        </div>
        <span class="badge text-bg-secondary">Step 1 of 2</span>
      </div>
      <div class="card-body p-4 position-relative" style="z-index:2">
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
            <div>Registering at Home Facility: <strong><?= e(short_branch(Auth::user()['branch_name'])) ?></strong></div>
          </div>
        <?php endif; ?>
        <?php include ROOT . '/app/views/partials/customer_fields.php'; ?>
      </div>
    </div>
  </div>

  <div class="col-lg-5">
    <!-- Optional Baseline Body Metrics Card -->
    <div class="card card-bento-panel shadow-sm mb-3">
      <i class="bi bi-heart-pulse-fill panel-watermark"></i>
      <div class="card-header">
        <div class="d-flex align-items-center gap-2">
          <span class="panel-hdr-ico red"><i class="bi bi-heart-pulse-fill"></i></span>
          <div>
            <div class="lh-1">Baseline Body Metrics <small class="text-muted fw-normal">(Optional)</small></div>
            <small class="text-muted fw-normal" style="font-size:0.71rem;font-family:'Outfit'">Auto-calculates starting BMI on registration</small>
          </div>
        </div>
      </div>
      <div class="card-body p-3 position-relative" style="z-index:2">
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
    <div class="card card-bento-panel shadow-sm mb-3">
      <i class="bi bi-award-fill panel-watermark"></i>
      <div class="card-header">
        <div class="d-flex align-items-center gap-2">
          <span class="panel-hdr-ico emerald"><i class="bi bi-award-fill"></i></span>
          <div>
            <div class="lh-1">Membership &amp; Payment <small class="text-muted fw-normal">(Optional)</small></div>
            <small class="text-muted fw-normal" style="font-size:0.71rem;font-family:'Outfit'">Assign Plan Tier &amp; Collect Initial Payment</small>
          </div>
        </div>
        <span class="badge text-bg-secondary">Step 2 of 2</span>
      </div>
      <div class="card-body p-4 position-relative" style="z-index:2">
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
