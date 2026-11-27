<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
  <div>
    <div class="d-flex align-items-center gap-2 mb-1">
      <h1 class="page-title">Biometric &amp; QR Door Console</h1>
      <span class="badge badge-futuristic"><span class="pulse-dot"></span> UNIVERSAL 6-BRANCH GATE</span>
    </div>
    <small class="text-muted"><i class="bi bi-broadcast text-danger me-1"></i> Fingerprint Biometric Terminal, Optical Camera QR Scanner &amp; Instant Cross-Branch Verification</small>
  </div>
  <div class="d-flex gap-2">
    <button type="button" class="btn btn-outline-secondary btn-sm" id="soundToggleBtn" onclick="toggleAudio()">
      <i class="bi bi-volume-up-fill text-success"></i> Door Audio: ON
    </button>
    <button type="button" class="btn btn-outline-brand btn-sm" onclick="toggleKiosk()">
      <i class="bi bi-arrows-fullscreen"></i> Fullscreen Kiosk
    </button>
  </div>
</div>

<!-- 6-Branch Today's Attendance Telemetry Bar -->
<div class="row g-2 mb-4">
<?php foreach ($stats as $s): ?>
  <div class="col-6 col-md-4 col-xl-2">
    <div class="card stat py-2 px-3">
      <div class="ico" style="width:46px;height:46px;font-size:1.35rem!important"><i class="bi bi-door-open-fill"></i></div>
      <div class="overflow-hidden">
        <div class="num fs-4"><?= (int) $s['attendance_today'] ?></div>
        <small class="text-truncate" style="font-size:0.72rem"><?= e(str_replace('CAATO FITNESS GYM – ', '', $s['name'])) ?></small>
      </div>
    </div>
  </div>
<?php endforeach; ?>
</div>

<?php if ($canCheckin): ?>
<div class="card mb-4 shadow-md border-primary-subtle" id="kioskContainer">
  <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2 pb-0 border-bottom-0">
    <ul class="nav nav-tabs border-bottom-0" role="tablist">
      <li class="nav-item">
        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabSearch" type="button">
          <i class="bi bi-upc-scan text-brand me-1"></i> Instant Search &amp; USB Scanner
        </button>
      </li>
      <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabBiometric" type="button" onclick="setTimeout(()=>document.getElementById('fpInput').focus(),150)">
          <i class="bi bi-fingerprint text-success me-1"></i> Biometric Fingerprint Door Terminal
        </button>
      </li>
      <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabCameraQr" type="button">
          <i class="bi bi-camera-video-fill text-info me-1"></i> Live Camera QR Scanner
        </button>
      </li>
    </ul>

    <div class="d-flex align-items-center gap-2 pb-2">
      <span class="small text-muted fw-bold text-uppercase" style="font-size:0.72rem"><i class="bi bi-geo-alt-fill text-danger"></i> Active Gate:</span>
      <?php if (Auth::isSuper()): ?>
        <select id="branchSel" class="form-select form-select-sm fw-bold" style="min-width:210px"><?= opts($branches, '', 'id', 'name') ?></select>
      <?php else: ?>
        <input type="hidden" id="branchSel" value="<?= (int) Auth::branchId() ?>">
        <span class="badge text-bg-dark py-2 px-3"><span class="pulse-dot me-1"></span> <?= e(str_replace('CAATO FITNESS GYM – ', '', Auth::user()['branch_name'])) ?></span>
      <?php endif; ?>
    </div>
  </div>

  <div class="card-body p-4 border-top">
    <div class="tab-content">
      <!-- TAB 1: Instant Member Search & USB Barcode/QR Reader -->
      <div class="tab-pane fade show active" id="tabSearch">
        <label class="form-label fw-bold text-uppercase small text-muted" style="letter-spacing:0.06em">
          <i class="bi bi-qr-code-scan me-1 text-brand"></i> Scan Member QR Card or Type Name, Phone, Member ID (CF-...) or Fingerprint Token
        </label>
        <div class="input-group input-group-lg shadow-sm">
          <span class="input-group-text bg-body-tertiary border-end-0">
            <i class="bi bi-search text-brand fs-4"></i>
          </span>
          <input id="q" class="form-control border-start-0 ps-1 fs-5" autocomplete="off" autofocus placeholder="Scan card or type member name / phone / CF-000001…" value="<?= e($initialQ) ?>">
          <button class="btn btn-outline-secondary" type="button" onclick="qEl.value='';box.innerHTML='';qEl.focus()"><i class="bi bi-x-lg"></i></button>
        </div>
        <div id="results" class="row g-3 mt-2"></div>
      </div>

      <!-- TAB 2: Biometric Fingerprint Door Hardware Terminal & Simulator -->
      <div class="tab-pane fade" id="tabBiometric">
        <div class="row g-4 align-items-center">
          <div class="col-lg-5 text-center border-end-lg">
            <div class="fp-scanner-pod mx-auto mb-3" id="fpPod">
              <i class="bi bi-fingerprint" id="fpIcon"></i>
            </div>
            <h4 class="fw-bold mb-1" id="fpStatusTitle">BIOMETRIC TERMINAL READY</h4>
            <p class="text-muted small mb-3" id="fpStatusSub">Place enrolled finger on reader or scan member token / ID below to trigger electromagnetic door lock.</p>

            <form id="fpForm" onsubmit="triggerBiometricDoor(event)" class="mx-auto" style="max-width:360px">
              <div class="input-group input-group-lg mb-2 shadow-sm">
                <span class="input-group-text"><i class="bi bi-fingerprint text-brand"></i></span>
                <input id="fpInput" class="form-control font-monospace" placeholder="FP Token, CF-ID or Phone…" autocomplete="off">
                <button class="btn btn-brand" type="submit"><i class="bi bi-unlock-fill"></i> Verify</button>
              </div>
              <small class="text-muted d-block">Supports USB/Serial HID Biometric Readers &amp; RFID/QR Tokens</small>
            </form>
          </div>

          <div class="col-lg-7">
            <div id="fpResultHud" class="p-4 rounded-4 border bg-body-tertiary text-center">
              <i class="bi bi-shield-lock fs-1 text-muted d-block mb-2"></i>
              <div class="fw-bold text-muted">DOOR LOCK STATUS: <span class="badge text-bg-secondary">LOCKED (STANDBY)</span></div>
              <small class="text-muted">Awaiting member fingerprint verification…</small>
            </div>
          </div>
        </div>
      </div>

      <!-- TAB 3: Live Optical Camera QR Code Scanner -->
      <div class="tab-pane fade" id="tabCameraQr">
        <div class="row g-4 align-items-center">
          <div class="col-lg-5 text-center">
            <div class="position-relative mx-auto rounded-4 overflow-hidden border border-2 border-brand shadow-md" style="max-width:340px;height:255px;background:#090d16">
              <video id="qrVideo" autoplay playsinline muted style="width:100%;height:100%;object-fit:cover"></video>
              <canvas id="qrCanvas" class="d-none"></canvas>
              <div id="qrOverlay" class="position-absolute inset-0 d-flex flex-column align-items-center justify-content-center text-white p-3" style="inset:0;background:rgba(9,13,22,0.75)">
                <i class="bi bi-qr-code-scan display-4 text-brand mb-2"></i>
                <span class="small fw-semibold">Hold Member Digital/Printed QR Card in front of camera</span>
              </div>
            </div>
            <div class="d-flex justify-content-center gap-2 mt-3">
              <button type="button" class="btn btn-brand btn-sm" id="btnStartQrCam" onclick="startQrCamera()">
                <i class="bi bi-camera-video-fill"></i> Start Optical QR Camera
              </button>
              <button type="button" class="btn btn-outline-danger btn-sm d-none" id="btnStopQrCam" onclick="stopQrCamera()">
                <i class="bi bi-stop-circle-fill"></i> Stop Camera
              </button>
            </div>
          </div>
          <div class="col-lg-7">
            <div id="qrScanHud" class="p-4 rounded-4 border bg-body-tertiary">
              <h5 class="fw-bold mb-1"><i class="bi bi-cpu text-brand me-1"></i> Optical QR Verification Feed</h5>
              <p class="text-muted small mb-0">Start the camera and point it at any CAATO Membership Card QR code. The door will automatically verify payment &amp; expiry and unlock on valid match.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- Attendance History & Live Door Log -->
<div class="card shadow-sm">
  <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
    <div>
      <i class="bi bi-clock-history text-brand me-2 fs-5"></i>
      <span>Verified Cross-Branch Door Access Log</span>
    </div>
    <div class="d-flex align-items-center gap-2">
      <span class="badge text-bg-success"><span class="pulse-dot me-1"></span> 1 Check-in / Member / Day Rule Active</span>
    </div>
  </div>
  <div class="card-body border-bottom bg-body-tertiary p-3">
    <form class="row g-2 align-items-center" method="get">
      <input type="hidden" name="page" value="attendance">
      <div class="col-md-3">
        <div class="input-group input-group-sm">
          <span class="input-group-text"><i class="bi bi-person"></i></span>
          <input name="hq" value="<?= e($hq) ?>" class="form-control" placeholder="Search member name, ID or FP token">
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
      <?php if (Auth::isSuper()): ?>
      <div class="col-md-3">
        <select name="branch" class="form-select form-select-sm">
          <?= opts(branch_list(false), $br, 'id', 'name', 'All 6 Branches (Unified)') ?>
        </select>
      </div>
      <?php endif; ?>
      <div class="col-md-2 d-grid">
        <button class="btn btn-sm btn-brand"><i class="bi bi-funnel-fill"></i> Filter Log</button>
      </div>
    </form>
  </div>
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead>
        <tr>
          <th>Check-in Time</th>
          <th>Member Profile</th>
          <th>Verification Method</th>
          <th>Checked In Facility</th>
          <th>Home Facility</th>
          <?php if ($canCheckin): ?><th class="text-end">Action</th><?php endif; ?>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><span class="badge text-bg-secondary"><i class="bi bi-clock"></i> <?= fmt_dt($r['check_in_at']) ?></span></td>
          <td>
            <div class="d-flex align-items-center gap-2">
              <img src="<?= photo_url($r['profile_picture']) ?>" class="avatar" style="width:40px;height:40px">
              <div>
                <a class="fw-bold" href="<?= url('customer_view', ['id' => $r['cid']]) ?>"><?= e($r['full_name']) ?></a>
                <small class="text-muted d-block font-monospace"><?= e($r['customer_code']) ?><?= $r['fingerprint_id'] ? ' · <i class="bi bi-fingerprint text-success"></i> ' . e($r['fingerprint_id']) : '' ?></small>
              </div>
            </div>
          </td>
          <td>
            <?php if (($r['checkin_method'] ?? '') === 'fingerprint_door'): ?>
              <span class="badge text-bg-success"><i class="bi bi-fingerprint"></i> Biometric Door</span>
            <?php else: ?>
              <span class="badge text-bg-info"><i class="bi bi-qr-code-scan"></i> QR / Reception</span>
            <?php endif; ?>
          </td>
          <td><i class="bi bi-geo-alt-fill text-danger me-1"></i> <?= e($r['visit_branch']) ?></td>
          <td>
            <?= e($r['reg_branch']) ?>
            <?= $r['branch_id'] != $r['registered_branch_id'] ? '<span class="badge text-bg-info ms-1"><i class="bi bi-airplane-engines"></i> Cross-Branch Guest</span>' : '' ?>
          </td>
          <?php if ($canCheckin): ?>
          <td class="text-end">
            <form method="post" class="d-inline">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="delete_log">
              <input type="hidden" name="id" value="<?= $r['id'] ?>">
              <button class="btn btn-sm btn-outline-danger py-1 px-2" data-confirm="Delete this check-in record?">
                <i class="bi bi-trash3"></i>
              </button>
            </form>
          </td>
          <?php endif; ?>
        </tr>
      <?php endforeach; if (!$rows): ?>
        <tr><td colspan="6" class="text-center text-muted py-5"><i class="bi bi-inbox fs-2 d-block mb-2"></i>No attendance logs found in this period.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
  <div class="card-footer bg-transparent py-3"><?= pager($pg) ?></div>
</div>

<?php if ($canCheckin): ?>
<script>
const qEl = document.getElementById('q'), box = document.getElementById('results'), bSel = document.getElementById('branchSel');
const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'}[c]));
const badge = {active: 'success', paid: 'success', pending: 'warning', partial: 'warning', expired: 'danger', unpaid: 'danger', suspended: 'secondary', cancelled: 'danger', none: 'secondary'};
let timer, last = [], audioEnabled = true;

function playSound(type) {
  if (audioEnabled && window.caatoAudio) window.caatoAudio.play(type);
}

function toggleAudio() {
  audioEnabled = !audioEnabled;
  const btn = document.getElementById('soundToggleBtn');
  btn.innerHTML = audioEnabled
    ? '<i class="bi bi-volume-up-fill text-success"></i> Door Audio: ON'
    : '<i class="bi bi-volume-mute-fill text-muted"></i> Door Audio: MUTED';
}

function toggleKiosk() {
  const el = document.getElementById('kioskContainer');
  if (!document.fullscreenElement) {
    el.requestFullscreen && el.requestFullscreen();
  } else {
    document.exitFullscreen && document.exitFullscreen();
  }
}

function card(r) {
  return `<div class="col-lg-6"><div class="card result-card shadow-sm h-100"><div class="card-body p-3">
   <div class="d-flex gap-3 align-items-center">
     <div style="position:relative">
       <img class="avatar-lg" src="${esc(r.photo)}" style="width:96px;height:96px;border:3px solid ${r.allowed ? '#10b981' : '#f43f5e'}">
     </div>
     <div class="flex-grow-1 overflow-hidden">
       <div class="d-flex align-items-center justify-content-between">
         <h5 class="mb-0 fw-bold text-truncate"><a href="${esc(r.profile)}" class="text-decoration-none">${esc(r.name)}</a></h5>
         ${r.fingerprint_id ? `<span class="badge text-bg-success"><i class="bi bi-fingerprint"></i> ${esc(r.fingerprint_id)}</span>` : ''}
       </div>
       <div class="text-muted small mb-1"><i class="bi bi-upc-scan me-1"></i>${esc(r.code)} · <i class="bi bi-telephone me-1 ms-1"></i>${esc(r.phone)}</div>
       <div class="small text-muted"><i class="bi bi-geo-alt me-1 text-danger"></i>Home Branch: <strong>${esc(r.reg_branch)}</strong></div>
       <div class="small mt-1 d-flex flex-wrap gap-1 align-items-center">
         <span class="badge text-bg-info"><i class="bi bi-tag-fill me-1"></i>${esc(r.plan)}</span>
         <span class="badge text-bg-${badge[r.payment] || 'secondary'}">${esc(r.payment)}</span>
         <span class="badge text-bg-${badge[r.status] || 'secondary'}">${esc(r.status === 'none' ? 'no membership' : r.status)}</span>
       </div>
       <div class="small text-muted mt-1" style="font-size:0.78rem"><i class="bi bi-calendar-range me-1"></i>${esc(r.start)} &rarr; ${esc(r.end)} · Last visit: ${esc(r.last)}</div>
     </div>
   </div>
   <div class="access-box ${esc(r.level)} mt-3 py-2 fw-semibold" id="msg-${r.id}">${esc(r.message)}</div>
   <div class="d-flex gap-2 mt-2">
     <button class="btn ${r.allowed ? 'btn-brand' : 'btn-secondary'} btn-lg flex-grow-1 py-2" ${r.allowed ? '' : 'disabled'} onclick="checkin(${r.id}, this, 'qr_scan')">
       <i class="bi bi-door-open-fill"></i> Unlock Door &amp; Check In
     </button>
     <a href="${esc(r.profile)}" class="btn btn-outline-secondary btn-lg py-2" title="Open Member Profile"><i class="bi bi-person-lines-fill"></i></a>
   </div>
  </div></div></div>`;
}

async function search() {
  const v = qEl.value.trim();
  if (v.length < 2) { box.innerHTML = ''; last = []; return; }
  const res = await fetch(CAATO.api + '&action=search&q=' + encodeURIComponent(v));
  const j = await res.json();
  last = j.results || [];
  box.innerHTML = last.length ? last.map(card).join('') : '<div class="col-12 text-center text-muted py-4"><i class="bi bi-person-x fs-2 d-block mb-1"></i>No member found. Check spelling or <a href="<?= url('customer_register') ?>" class="fw-bold">register a new customer</a>.</div>';
  return last;
}

async function checkin(id, btn, method = 'qr_scan') {
  if (btn) btn.disabled = true;
  const body = new URLSearchParams({customer_id: id, branch_id: bSel.value, method});
  const res = await fetch(CAATO.api + '&action=checkin', {
    method: 'POST',
    headers: {'X-CSRF-Token': CAATO.csrf, 'Content-Type': 'application/x-www-form-urlencoded'},
    body
  });
  const j = await res.json();
  playSound(j.ok ? 'unlock' : 'denied');
  const m = document.getElementById('msg-' + id);
  if (m) {
    m.className = 'access-box mt-3 py-2 fw-semibold ' + (j.level || (j.ok ? 'success' : 'danger'));
    m.innerHTML = (j.ok ? '<i class="bi bi-unlock-fill me-1"></i> ' : '<i class="bi bi-lock-fill me-1"></i> ') + esc(j.message);
  }
  if (j.ok && btn) {
    btn.innerHTML = '<i class="bi bi-check2-circle"></i> Door Unlocked — Checked In!';
    setTimeout(() => location.reload(), 1600);
  }
  return j;
}

// Biometric Door Terminal Handler
async function triggerBiometricDoor(e) {
  if (e) e.preventDefault();
  const inp = document.getElementById('fpInput');
  const token = inp.value.trim();
  if (!token) return;
  const pod = document.getElementById('fpPod');
  const hud = document.getElementById('fpResultHud');
  const title = document.getElementById('fpStatusTitle');

  const body = new URLSearchParams({fingerprint_token: token, branch_id: bSel.value});
  const res = await fetch(CAATO.api + '&action=door_fingerprint', {
    method: 'POST',
    headers: {'X-CSRF-Token': CAATO.csrf, 'Content-Type': 'application/x-www-form-urlencoded'},
    body
  });
  const j = await res.json();
  playSound(j.ok ? 'unlock' : 'denied');
  inp.value = '';
  inp.focus();

  pod.className = 'fp-scanner-pod mx-auto mb-3 ' + (j.ok ? 'unlocked' : 'denied');
  title.textContent = j.ok ? 'DOOR UNLOCKED · ACCESS GRANTED' : 'DOOR LOCKED · ACCESS DENIED';
  title.className = 'fw-bold mb-1 text-' + (j.ok ? 'success' : 'danger');

  const c = j.customer;
  hud.innerHTML = `
    <div class="d-flex flex-column flex-md-row align-items-center gap-3 text-start">
      ${c ? `<img src="${esc(c.photo)}" class="avatar-lg" style="width:92px;height:92px;border-color:${j.ok ? '#10b981' : '#f43f5e'}">` : `<div class="p-3 rounded-circle bg-danger bg-opacity-10 text-danger"><i class="bi bi-shield-x fs-1"></i></div>`}
      <div class="flex-grow-1">
        <div class="d-flex align-items-center gap-2 mb-1">
          <span class="badge text-bg-${j.ok ? 'success' : 'danger'} fs-6"><i class="bi bi-${j.ok ? 'unlock-fill' : 'lock-fill'}"></i> ${j.ok ? 'ALBAABKA WAA LA FURAY' : 'ALBAABKA WAA XIRAN YAHAY'}</span>
        </div>
        ${c ? `<h4 class="fw-bold mb-0">${esc(c.name)} <small class="text-muted font-monospace">(${esc(c.code)})</small></h4>
               <div class="small text-muted">Plan: <strong>${esc(c.plan || '—')}</strong> · Expires: <strong>${esc(c.end_date || '—')}</strong></div>` : ''}
        <div class="access-box ${esc(j.level || (j.ok ? 'success' : 'danger'))} mt-2 py-2">${esc(j.message)}</div>
        ${j.can_renew && j.renew_url ? `<a href="${esc(j.renew_url)}" class="btn btn-sm btn-brand mt-2"><i class="bi bi-arrow-repeat"></i> Renew / Pay Membership Now</a>` : ''}
      </div>
    </div>
  `;
}

// Optical Camera QR Code Scanner (using BarcodeDetector API or fallback canvas frame scan)
let qrStream = null, qrLoop = null;
async function startQrCamera() {
  const video = document.getElementById('qrVideo');
  const overlay = document.getElementById('qrOverlay');
  const hud = document.getElementById('qrScanHud');
  try {
    qrStream = await navigator.mediaDevices.getUserMedia({video: {facingMode: 'environment'}});
    video.srcObject = qrStream;
    overlay.classList.add('d-none');
    document.getElementById('btnStartQrCam').classList.add('d-none');
    document.getElementById('btnStopQrCam').classList.remove('d-none');

    if ('BarcodeDetector' in window) {
      const detector = new BarcodeDetector({formats: ['qr_code', 'code_128']});
      qrLoop = setInterval(async () => {
        if (video.readyState === video.HAVE_ENOUGH_DATA) {
          try {
            const codes = await detector.detect(video);
            if (codes.length > 0 && codes[0].rawValue) {
              const code = codes[0].rawValue.trim();
              stopQrCamera();
              document.getElementById('fpInput').value = code;
              await triggerBiometricDoor();
              hud.innerHTML = document.getElementById('fpResultHud').innerHTML;
            }
          } catch (err) {}
        }
      }, 450);
    } else {
      hud.innerHTML = '<div class="alert alert-info mb-0 small"><i class="bi bi-info-circle me-1"></i> Camera preview active. Your browser uses USB/Keyboard wedge mode for instant QR scan — use the search or biometric box while scanning.</div>';
    }
  } catch (e) {
    hud.innerHTML = `<div class="alert alert-danger mb-0 small"><i class="bi bi-exclamation-triangle me-1"></i> Could not access camera: ${esc(e.message)}</div>`;
  }
}

function stopQrCamera() {
  if (qrLoop) { clearInterval(qrLoop); qrLoop = null; }
  if (qrStream) { qrStream.getTracks().forEach(t => t.stop()); qrStream = null; }
  const overlay = document.getElementById('qrOverlay');
  if (overlay) overlay.classList.remove('d-none');
  document.getElementById('btnStartQrCam').classList.remove('d-none');
  document.getElementById('btnStopQrCam').classList.add('d-none');
}

qEl.addEventListener('input', () => { clearTimeout(timer); timer = setTimeout(search, 220); });
qEl.addEventListener('keydown', async e => {
  if (e.key !== 'Enter') return;
  e.preventDefault();
  clearTimeout(timer);
  const r = await search();
  if (r && r.length === 1 && (r[0].code.toLowerCase() === qEl.value.trim().toLowerCase() || (r[0].fingerprint_id && r[0].fingerprint_id.toLowerCase() === qEl.value.trim().toLowerCase())) && r[0].allowed) {
    checkin(r[0].id, box.querySelector('button'), 'qr_scan');
  }
});
if (qEl.value) search();
</script>
<?php endif; ?>
