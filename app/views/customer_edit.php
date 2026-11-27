<?php
page_banner(
  'PROFILE CONFIGURATION · ' . $c['customer_code'],
  'Edit Member',
  $c['full_name'],
  'Update personal contact info, fitness goal, assigned personal coach, biometric door token & account clearance.',
  '<a class="btn btn-sm px-3 py-2" style="background:rgba(255,255,255,0.12);color:#fff;border:1px solid rgba(255,255,255,0.2)" href="' . url('customer_view', ['id' => $id]) . '"><i class="bi bi-arrow-left"></i> Back to 360° Profile</a>'
);
?>

<form method="post" enctype="multipart/form-data" class="card shadow-sm">
  <?= csrf_field() ?>
  <div class="card-header"><i class="bi bi-pencil-square text-brand me-2 fs-5"></i> Customer Account Details</div>
  <div class="card-body p-4">
    <?php include ROOT . '/app/views/partials/customer_fields.php'; ?>
    <?php if (Auth::can('memberships.manage')): ?>
      <div class="mt-4 col-md-4 px-0">
        <label class="form-label small fw-semibold text-uppercase text-muted">Account Access Status</label>
        <select name="status" class="form-select">
          <option value="active" <?= $c['status'] === 'active' ? 'selected' : '' ?>>Active (Standard)</option>
          <option value="blocked" <?= $c['status'] === 'blocked' ? 'selected' : '' ?>>Blocked (Refuse entry at all 6 branches)</option>
        </select>
      </div>
    <?php endif; ?>
  </div>
  <div class="card-footer bg-transparent p-3 d-flex gap-2">
    <button class="btn btn-brand"><i class="bi bi-check-circle"></i> Save Profile Changes</button>
    <a class="btn btn-outline-secondary" href="<?= url('customer_view', ['id' => $id]) ?>">Cancel</a>
  </div>
</form>
