<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
  <div class="d-flex align-items-center gap-2">
    <h1 class="page-title">Notification Hub</h1>
    <span class="badge badge-futuristic"><span class="pulse-dot"></span> REALTIME ALERTS</span>
  </div>
  <div class="ms-auto d-flex gap-2">
    <form method="post">
      <?= csrf_field() ?>
      <button class="btn btn-brand btn-sm">
        <i class="bi bi-check2-all"></i> Mark All Read
      </button>
    </form>
    <form method="post">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="clear_read">
      <button class="btn btn-outline-danger btn-sm" data-confirm="Clear all read notifications?">
        <i class="bi bi-trash3"></i> Clear Read
      </button>
    </form>
  </div>
</div>

<div class="mb-4 d-flex flex-wrap gap-2">
  <?php foreach ([
    '' => ['All Notifications', 'bi-bell'],
    'membership_expired' => ['Expired Plans', 'bi-x-octagon-fill text-danger'],
    'expiring_soon' => ['Expiring Soon', 'bi-alarm-fill text-warning'],
    'payment_success' => ['Payment Received', 'bi-check-circle-fill text-success'],
    'membership_renewal' => ['Renewals', 'bi-arrow-repeat text-info'],
    'payment_pending' => ['Pending Balances', 'bi-exclamation-circle-fill text-danger']
  ] as $k => [$l, $ic]): ?>
    <a class="btn btn-sm <?= $type === $k ? 'btn-brand' : 'btn-outline-secondary' ?>" href="<?= url('notifications', $k ? ['type' => $k] : []) ?>">
      <i class="bi <?= $ic ?> me-1"></i> <?= $l ?>
    </a>
  <?php endforeach; ?>
</div>

<div class="card shadow-sm">
  <div class="list-group list-group-flush">
    <?php foreach ($rows as $n):
      $ic = [
        'membership_expired' => 'bi-x-octagon-fill text-danger',
        'expiring_soon' => 'bi-alarm-fill text-warning',
        'payment_success' => 'bi-check-circle-fill text-success',
        'membership_renewal' => 'bi-arrow-repeat text-info',
        'payment_pending' => 'bi-exclamation-circle-fill text-danger'
      ][$n['type']] ?? 'bi-bell-fill text-brand';
    ?>
      <div class="list-group-item bg-transparent d-flex gap-3 align-items-start p-3 <?= $n['is_read'] ? '' : 'border-start border-brand border-4 bg-body-tertiary' ?>">
        <div class="p-2 rounded-3 bg-body-secondary mt-1">
          <i class="bi <?= $ic ?> fs-4"></i>
        </div>
        <div class="flex-grow-1">
          <div class="fw-bold fs-6"><?= e($n['title']) ?></div>
          <div class="text-muted small mt-1"><?= e($n['message']) ?></div>
        </div>
        <div class="text-end small text-muted text-nowrap">
          <i class="bi bi-clock me-1"></i><?= fmt_dt($n['created_at']) ?>
          <?php if ($n['customer_id']): ?>
            <div class="mt-1">
              <a class="btn btn-sm btn-outline-brand py-0 px-2" href="<?= url('customer_view', ['id' => $n['customer_id']]) ?>">
                Profile &rarr;
              </a>
            </div>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; if (!$rows): ?>
      <div class="p-5 text-muted text-center">
        <i class="bi bi-bell-slash fs-1 d-block mb-2 text-secondary opacity-50"></i>
        <h5>No notifications found</h5>
        <p class="small mb-0">You're completely up to date.</p>
      </div>
    <?php endif; ?>
  </div>
  <div class="card-footer bg-transparent py-3"><?= pager($pg) ?></div>
</div>
