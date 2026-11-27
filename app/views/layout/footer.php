    <footer class="mt-5 pt-3 border-top d-flex flex-wrap align-items-center justify-content-between gap-2 small text-muted no-print">
      <div class="d-flex align-items-center gap-2">
        <span class="panel-hdr-ico brand" style="width:26px;height:26px;font-size:0.78rem;border-radius:7px"><i class="bi bi-shield-check"></i></span>
        <span><strong><?= e(setting('gym_name', 'CAATO FITNESS GYM')) ?></strong> · 6-Branch Enterprise Cloud OS v4.0</span>
      </div>
      <div class="d-flex align-items-center gap-3">
        <span><span class="pulse-dot me-1"></span> Biometric &amp; QR Gate Engine Active</span>
        <span class="font-monospace"><?= e(ucwords(str_replace('_', ' ', Auth::role()))) ?></span>
      </div>
    </footer>
  </main>
</div>

<!-- Global Ctrl+K Command Palette Modal -->
<div class="modal fade" id="cmdPaletteModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content card-bento-panel shadow-lg overflow-hidden">
      <i class="bi bi-command panel-watermark"></i>
      <div class="p-3 border-bottom bg-body-tertiary position-relative" style="z-index:2">
        <div class="input-group input-group-lg align-items-center">
          <span class="panel-hdr-ico brand ms-1 me-2" style="width:38px;height:38px;font-size:1.05rem"><i class="bi bi-search"></i></span>
          <input type="text" id="cmdSearchInput" class="form-control border-0 bg-transparent shadow-none" placeholder="Search member by name, phone, ID (CF-...) or fingerprint..." autocomplete="off">
          <button type="button" class="btn-close align-self-center me-2" data-bs-dismiss="modal"></button>
        </div>
      </div>
      <div id="cmdSearchResults" class="position-relative" style="max-height:320px;overflow-y:auto;z-index:2"></div>
      <div class="p-3 bg-body-tertiary border-top position-relative" style="z-index:2">
        <div class="small text-muted fw-bold text-uppercase mb-2" style="font-size:0.7rem;letter-spacing:0.06em">3D Quick Jump Actions</div>
        <div class="d-flex flex-wrap gap-2">
          <?php if (Auth::can('attendance.view')): ?><a href="<?= url('attendance') ?>" class="btn btn-sm btn-outline-brand"><i class="bi bi-fingerprint"></i> Door Check-in</a><?php endif; ?>
          <?php if (Auth::can('customers.create')): ?><a href="<?= url('customer_register') ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-person-plus"></i> New Member</a><?php endif; ?>
          <?php if (Auth::can('payments.view')): ?><a href="<?= url('payments', ['tab' => 'pending']) ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-cash-coin"></i> Unpaid Balances</a><?php endif; ?>
          <?php if (Auth::can('pos.view')): ?><a href="<?= url('pos') ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-cup-straw"></i> POS Bar</a><?php endif; ?>
          <?php if (Auth::can('reports.view')): ?><a href="<?= url('reports') ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-graph-up-arrow"></i> Reports</a><?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Self-Service Change Password Modal -->
<div class="modal fade" id="myPwModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <form class="modal-content card-bento-panel shadow-lg" onsubmit="changeMyPassword(event)">
      <i class="bi bi-key-fill panel-watermark"></i>
      <div class="modal-header position-relative" style="z-index:2">
        <h5 class="modal-title d-flex align-items-center gap-2">
          <span class="panel-hdr-ico brand"><i class="bi bi-key-fill"></i></span>
          <span>Change My Account Password</span>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body position-relative" style="z-index:2">
        <label class="form-label small fw-semibold">Current Password *</label>
        <input type="password" id="cpOld" class="form-control mb-3" required autocomplete="current-password">
        <label class="form-label small fw-semibold">New Password (min 8 chars) *</label>
        <input type="password" id="cpNew" minlength="8" class="form-control mb-2" required autocomplete="new-password">
        <div id="cpMsg" class="small mt-2"></div>
      </div>
      <div class="modal-footer position-relative" style="z-index:2">
        <button class="btn btn-brand w-100"><i class="bi bi-shield-check"></i> Update Password</button>
      </div>
    </form>
  </div>
</div>

<div class="toast-container position-fixed top-0 end-0 p-3" id="toasts">
<?php foreach ($_SESSION['flash'] ?? [] as [$t, $m]): ?>
  <div class="toast align-items-center text-bg-<?= $t === 'error' ? 'danger' : e($t) ?> border-0" role="alert" data-bs-delay="6000"><div class="d-flex"><div class="toast-body"><?= e($m) ?></div><button class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div></div>
<?php endforeach; unset($_SESSION['flash']); ?>
</div>
<div class="modal fade" id="confirmModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content shadow-lg">
  <div class="modal-header"><h5 class="modal-title"><i class="bi bi-shield-exclamation text-warning me-2 fs-4"></i> Please confirm</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
  <div class="modal-body fs-6 py-4" id="confirmText"></div>
  <div class="modal-footer"><button class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button class="btn btn-brand" id="confirmOk"><i class="bi bi-check2-circle"></i> Yes, continue</button></div>
</div></div></div>
<script src="<?= asset('js/app.js') ?>"></script>
</body></html>
