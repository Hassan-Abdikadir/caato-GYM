<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
  <div class="d-flex align-items-center gap-2">
    <h1 class="page-title">System Settings &amp; Security</h1>
    <span class="badge badge-futuristic"><span class="pulse-dot"></span> GLOBAL CONFIGURATION</span>
  </div>
</div>

<ul class="nav nav-tabs mb-4">
  <?php 
  $tabs = [
    'general' => ['Organization Profile', 'bi-sliders'],
    'methods' => ['Payment Channels', 'bi-credit-card-2-front-fill'],
    'smtp' => ['Email SMTP Gateway', 'bi-envelope-at-fill'],
    'audit' => ['Security Audit Log', 'bi-shield-check']
  ];
  foreach ($tabs as $k => [$l, $ic]): ?>
    <li class="nav-item">
      <a class="nav-link <?= $tab === $k ? 'active' : '' ?>" href="<?= url('settings', ['tab' => $k]) ?>">
        <i class="bi <?= $ic ?> me-1"></i> <?= $l ?>
      </a>
    </li>
  <?php endforeach; ?>
</ul>

<?php if ($tab === 'general'): ?>
<form method="post" class="card shadow-sm" style="max-width:740px">
  <?= csrf_field() ?>
  <input type="hidden" name="action" value="general">
  <div class="card-header"><i class="bi bi-sliders text-brand me-2 fs-5"></i> Core Organization Profile</div>
  <div class="card-body p-4 row g-3">
    <div class="col-6">
      <label class="form-label small fw-semibold">Gym / Enterprise Name</label>
      <input name="gym_name" value="<?= e(setting('gym_name', 'CAATO FITNESS GYM')) ?>" class="form-control" required>
    </div>
    <div class="col-6">
      <label class="form-label small fw-semibold">Official WhatsApp Support Number</label>
      <div class="input-group">
        <span class="input-group-text"><i class="bi bi-whatsapp text-success"></i></span>
        <input name="gym_whatsapp" value="<?= e(setting('gym_whatsapp')) ?>" class="form-control" placeholder="e.g. 252612345678">
      </div>
    </div>
    <div class="col-4">
      <label class="form-label small fw-semibold">Currency Symbol</label>
      <input name="currency" value="<?= e(setting('currency', '$')) ?>" class="form-control" maxlength="5" required>
    </div>
    <div class="col-8">
      <label class="form-label small fw-semibold">"Expiring Soon" Warning Window (Days)</label>
      <input type="number" name="expiry_warning_days" min="1" max="60" value="<?= e(setting('expiry_warning_days', '7')) ?>" class="form-control" required>
    </div>
    <div class="col-12">
      <label class="form-label small fw-semibold">Head Office Phone</label>
      <input name="gym_phone" value="<?= e(setting('gym_phone')) ?>" class="form-control">
    </div>
    <div class="col-12">
      <label class="form-label small fw-semibold">Head Office Physical Address</label>
      <input name="gym_address" value="<?= e(setting('gym_address')) ?>" class="form-control">
    </div>
    <div class="col-12 pt-2">
      <button class="btn btn-brand"><i class="bi bi-check-circle"></i> Save General Settings</button>
      <small class="text-muted ms-2">Configure membership pricing in <strong>Memberships &rarr; Plans &amp; Pricing</strong>.</small>
    </div>
  </div>
</form>

<?php elseif ($tab === 'smtp'): ?>
<div class="row g-3">
  <div class="col-lg-7">
    <form method="post" class="card shadow-sm h-100">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="smtp">
      <input type="hidden" name="tab" value="smtp">
      <div class="card-header"><i class="bi bi-envelope-at-fill text-brand me-2 fs-5"></i> SMTP Outbound Email Gateway</div>
      <div class="card-body p-4 row g-3">
        <div class="col-8">
          <label class="form-label small fw-semibold">SMTP Host</label>
          <input name="smtp_host" value="<?= e(setting('smtp_host')) ?>" class="form-control" placeholder="e.g. smtp.gmail.com">
        </div>
        <div class="col-4">
          <label class="form-label small fw-semibold">Port</label>
          <input name="smtp_port" value="<?= e(setting('smtp_port', '587')) ?>" class="form-control" placeholder="587">
        </div>
        <div class="col-12">
          <label class="form-label small fw-semibold">Sender 'From' Address</label>
          <input name="smtp_from" value="<?= e(setting('smtp_from')) ?>" class="form-control" placeholder="noreply@caatogym.com" type="email">
        </div>
        <div class="col-12">
          <label class="form-label small fw-semibold">SMTP Username</label>
          <input name="smtp_user" value="<?= e(setting('smtp_user')) ?>" class="form-control" autocomplete="off">
        </div>
        <div class="col-12">
          <label class="form-label small fw-semibold">SMTP Password <small class="text-muted">(leave blank to keep current)</small></label>
          <input type="password" name="smtp_pass" value="" class="form-control" autocomplete="new-password">
        </div>
        <div class="col-12 pt-2">
          <div class="alert alert-info py-2 mb-3 small d-flex align-items-center gap-2">
            <i class="bi bi-lightbulb-fill text-warning fs-5 flex-shrink-0"></i>
            <div>Recommended: Gmail host <code>smtp.gmail.com</code> with port <code>587</code> and an <strong>App Password</strong>.</div>
          </div>
          <button class="btn btn-brand"><i class="bi bi-check-circle"></i> Save SMTP Configuration</button>
        </div>
      </div>
    </form>
  </div>

  <div class="col-lg-5">
    <form method="post" class="card shadow-sm">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="smtp_test">
      <input type="hidden" name="tab" value="smtp">
      <div class="card-header"><i class="bi bi-send-check-fill text-success me-2 fs-5"></i> Verify SMTP Gateway</div>
      <div class="card-body p-4">
        <p class="small text-muted">Send a live test email to verify that welcome messages will reach newly registered customers.</p>
        <label class="form-label small fw-semibold">Recipient Email Address *</label>
        <input type="email" name="test_email" class="form-control mb-3" required placeholder="yourname@gmail.com">
        <button class="btn btn-outline-brand w-100"><i class="bi bi-send-fill"></i> Dispatch Test Email</button>
      </div>
    </form>
  </div>
</div>

<?php elseif ($tab === 'methods'): ?>
<div class="row g-3">
  <div class="col-lg-7">
    <div class="card shadow-sm h-100">
      <div class="card-header"><i class="bi bi-credit-card-2-front-fill text-brand me-2 fs-5"></i> Configured Payment Channels</div>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead>
            <tr><th>Channel</th><th>Requires Reference</th><th>Status</th><th class="text-end">Action</th></tr>
          </thead>
          <tbody>
          <?php foreach ($methods as $m): ?>
            <tr>
              <td class="fw-bold"><i class="bi bi-wallet2 text-brand me-2"></i><?= e($m['name']) ?></td>
              <td><?= $m['requires_reference'] ? '<span class="badge text-bg-warning">Yes (Mandatory)</span>' : '<span class="badge text-bg-light border">Optional</span>' ?></td>
              <td><?= badge($m['status']) ?></td>
              <td class="text-end">
                <form method="post">
                  <?= csrf_field() ?>
                  <input type="hidden" name="tab" value="methods">
                  <input type="hidden" name="action" value="method_toggle">
                  <input type="hidden" name="id" value="<?= $m['id'] ?>">
                  <button class="btn btn-sm btn-outline-secondary">
                    <?= $m['status'] === 'active' ? '<i class="bi bi-slash-circle"></i> Disable' : '<i class="bi bi-check-circle"></i> Enable' ?>
                  </button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="col-lg-5">
    <form method="post" class="card shadow-sm">
      <?= csrf_field() ?>
      <input type="hidden" name="tab" value="methods">
      <input type="hidden" name="action" value="method_add">
      <div class="card-header"><i class="bi bi-plus-circle-fill text-brand me-2 fs-5"></i> Add Payment Channel</div>
      <div class="card-body p-4">
        <label class="form-label small fw-semibold">Payment Channel Name *</label>
        <input name="name" class="form-control mb-3" placeholder="e.g. Premier Wallet, Zaad, Cash" required>
        <div class="form-check mb-4">
          <input type="checkbox" name="requires_reference" value="1" class="form-check-input" id="rr" checked>
          <label for="rr" class="form-check-label small fw-semibold">Require staff to record a transaction reference</label>
        </div>
        <button class="btn btn-brand w-100 py-2"><i class="bi bi-plus-lg"></i> Add Payment Channel</button>
      </div>
    </form>
  </div>
</div>

<?php else: ?>
<div class="card shadow-sm">
  <div class="card-header"><i class="bi bi-shield-check text-brand me-2 fs-5"></i> System Audit &amp; Security Event Trail</div>
  <div class="table-responsive">
    <table class="table table-sm table-hover align-middle mb-0">
      <thead>
        <tr>
          <th>Timestamp</th>
          <th>Staff User</th>
          <th>Facility</th>
          <th>Action Event</th>
          <th>Details</th>
          <th>IP Address</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($logs as $l): ?>
        <tr>
          <td class="text-nowrap small text-muted"><i class="bi bi-clock me-1"></i><?= fmt_dt($l['created_at']) ?></td>
          <td class="fw-semibold"><?= e($l['username'] ?: 'system') ?></td>
          <td><span class="badge text-bg-light border"><?= e($l['branch_code'] ?: 'HQ') ?></span></td>
          <td><code class="text-brand fw-bold"><?= e($l['action']) ?></code></td>
          <td class="small"><?= e($l['details']) ?></td>
          <td><small class="font-monospace text-muted"><?= e($l['ip_address']) ?></small></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <div class="card-footer bg-transparent py-3"><?= pager($pg) ?></div>
</div>
<?php endif; ?>
