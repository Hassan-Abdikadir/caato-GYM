<?php
Auth::need('customers.view');
$c = Customer::find((int) req('id'));
if (!$c) redirect('customers');
$gymPhone = setting('gym_phone', '+252 61 / 063 CAATO GYM');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>PVC Membership Card – <?= e($c['customer_code']) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&family=Barlow+Condensed:wght@700;800&display=swap" rel="stylesheet">
<style>
  body {
    background: #090d16;
    color: #f8fafc;
    padding: 36px 16px;
    font-family: 'Outfit', sans-serif;
  }
  .cards-wrapper {
    display: flex;
    flex-wrap: wrap;
    gap: 28px;
    justify-content: center;
    align-items: center;
  }
  .pvc-card {
    width: 440px;
    height: 270px;
    border-radius: 20px;
    position: relative;
    overflow: hidden;
    box-shadow: 0 24px 50px rgba(0, 0, 0, 0.55);
    border: 1px solid rgba(255, 87, 34, 0.4);
    page-break-inside: avoid;
  }
  .pvc-front {
    background:
      radial-gradient(circle at 85% 15%, rgba(255, 87, 34, 0.28) 0%, transparent 45%),
      radial-gradient(circle at 10% 90%, rgba(225, 29, 72, 0.22) 0%, transparent 45%),
      linear-gradient(135deg, #130804 0%, #1d0c06 55%, #0c1220 100%);
    color: #ffffff;
  }
  .pvc-back {
    background: linear-gradient(135deg, #0f172a 0%, #090d16 100%);
    color: #e2e8f0;
  }
  .pvc-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 18px 10px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  }
  .pvc-logo-box {
    display: flex;
    align-items: center;
    gap: 10px;
  }
  .pvc-logo-box img {
    height: 44px;
    background: #fff;
    padding: 3px;
    border-radius: 9px;
  }
  .pvc-title {
    font-family: 'Barlow Condensed', sans-serif;
    font-size: 1.45rem;
    font-weight: 800;
    letter-spacing: 0.04em;
    line-height: 1;
  }
  .pvc-sub {
    font-size: 0.65rem;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: #ff9e80;
    font-weight: 700;
  }
  .pvc-body {
    display: flex;
    gap: 14px;
    padding: 14px 18px;
    align-items: center;
  }
  .pvc-photo {
    width: 100px;
    height: 118px;
    object-fit: cover;
    border-radius: 14px;
    border: 2.5px solid #ff5722;
    box-shadow: 0 6px 18px rgba(255, 87, 34, 0.35);
    flex-shrink: 0;
  }
  .pvc-info {
    flex: 1;
    font-size: 0.82rem;
    overflow: hidden;
  }
  .pvc-name {
    font-family: 'Barlow Condensed', sans-serif;
    font-size: 1.55rem;
    font-weight: 800;
    color: #ffffff;
    line-height: 1.05;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  .pvc-meta {
    margin-top: 6px;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 4px 8px;
    font-size: 0.75rem;
  }
  .pvc-meta small {
    color: #94a3b8;
    display: block;
    font-size: 0.62rem;
    text-transform: uppercase;
    letter-spacing: 0.06em;
  }
  .pvc-meta strong {
    color: #f8fafc;
  }
  .pvc-qr {
    background: #ffffff;
    padding: 6px;
    border-radius: 12px;
    flex-shrink: 0;
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.35);
  }
  .pvc-bottom-bar {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 26px;
    background: linear-gradient(90deg, #ff5722, #e11d48);
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 18px;
    font-size: 0.68rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
  }
  @media print {
    body { background: #ffffff; color: #000; padding: 0; }
    .no-print { display: none !important; }
    .pvc-card { box-shadow: none; border: 1px solid #333; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
  }
</style>
</head>
<body>
<div class="cards-wrapper">
  <!-- FRONT SIDE -->
  <div class="pvc-card pvc-front">
    <div class="pvc-header">
      <div class="pvc-logo-box">
        <img src="<?= asset('img/logo.png') ?>" alt="CAATO">
        <div>
          <div class="pvc-title">CAATO FITNESS GYM</div>
          <div class="pvc-sub">Universal 6-Branch Access Pass</div>
        </div>
      </div>
      <span class="badge rounded-pill" style="background:rgba(16,185,129,0.2);color:#34d399;border:1px solid rgba(16,185,129,0.4);font-size:0.68rem">
        <i class="bi bi-fingerprint"></i> VIP PASS
      </span>
    </div>

    <div class="pvc-body">
      <img class="pvc-photo" src="<?= photo_url($c['profile_picture']) ?>" alt="">
      <div class="pvc-info">
        <div class="pvc-name"><?= e($c['full_name']) ?></div>
        <div class="text-warning font-monospace fw-bold" style="font-size:0.85rem"><?= e($c['customer_code']) ?></div>
        <div class="pvc-meta">
          <div><small>Plan Tier</small><strong><?= e($c['plan_name'] ?: 'Member') ?></strong></div>
          <div><small>Valid Until</small><strong><?= fmt_date($c['end_date']) ?></strong></div>
          <div><small>Home Node</small><strong><?= e(short_branch($c['reg_branch'])) ?></strong></div>
          <div><small>Phone</small><strong><?= e($c['phone']) ?></strong></div>
        </div>
      </div>
      <div class="pvc-qr">
        <img src="https://api.qrserver.com/v1/create-qr-code/?size=110x110&margin=0&data=<?= urlencode($c['customer_code']) ?>" width="82" height="82" alt="QR">
      </div>
    </div>

    <div class="pvc-bottom-bar">
      <span>ONE MEMBERSHIP · SIX FACILITIES</span>
      <span><?= !empty($c['fingerprint_id']) ? 'BIOMETRIC: ' . e($c['fingerprint_id']) : 'QR & BIOMETRIC READY' ?></span>
    </div>
  </div>

  <!-- BACK SIDE -->
  <div class="pvc-card pvc-back">
    <div class="pvc-header" style="background:rgba(255,255,255,0.03)">
      <span class="fw-bold small text-uppercase" style="letter-spacing:0.08em;color:#ff7043"><i class="bi bi-shield-check me-1"></i> Member Card Terms &amp; Verification</span>
      <span class="font-monospace small text-white-50"><?= e($c['customer_code']) ?></span>
    </div>
    <div class="p-3 px-4" style="font-size:0.77rem;line-height:1.55">
      <ul class="mb-2 ps-3 text-white-50">
        <li>This digital &amp; PVC card grants entry to <strong class="text-white">all 6 CAATO FITNESS GYM branches</strong> while subscription is active and fully paid.</li>
        <li>Present this QR code or scan your enrolled fingerprint at the entrance turnstile on every visit.</li>
        <li>Membership is strictly personal and non-transferable.</li>
      </ul>
      <div class="p-2 px-3 rounded-3 mt-2 d-flex justify-content-between align-items-center" style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.08)">
        <div>
          <small class="text-white-50 d-block" style="font-size:0.65rem">EMERGENCY CONTACT</small>
          <strong><?= e(trim(($c['emergency_name'] ?? '') . ' ' . ($c['emergency_phone'] ?? '')) ?: 'On File at Reception') ?></strong>
        </div>
        <div class="text-end">
          <small class="text-white-50 d-block" style="font-size:0.65rem">FITNESS GOAL</small>
          <strong class="text-warning"><?= e($c['fitness_goal'] ?? 'General Fitness') ?></strong>
        </div>
      </div>
    </div>
    <div class="pvc-bottom-bar" style="background:#1e293b">
      <span>CAATO CLOUD OS v4.0</span>
      <span><?= e($gymPhone) ?></span>
    </div>
  </div>
</div>

<div class="text-center no-print mt-4 d-flex gap-2 justify-content-center">
  <button onclick="window.print()" class="btn btn-warning fw-bold px-4" style="background:linear-gradient(135deg,#ff5722,#e11d48);color:#fff;border:0">
    <i class="bi bi-printer-fill"></i> Print Dual-Sided PVC ID Card
  </button>
  <a href="<?= url('customer_view', ['id' => $c['id']]) ?>" class="btn btn-outline-light">
    <i class="bi bi-arrow-left"></i> Back to 360° Profile
  </a>
</div>
</body>
</html>
