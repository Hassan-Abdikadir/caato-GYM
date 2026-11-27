<?php
Auth::need('payments.view');
$r = Payment::receipt((int) req('id'));
if (!$r) { flash('error', 'Receipt not found.'); redirect('payments'); }
if (req('format') === 'pdf') {
    $pdf = new Pdf(false);
    $pdf->header('Payment Receipt', $r['receipt_no']);
    $y = $pdf->y;
    $lines = [['Receipt No', $r['receipt_no']], ['Date', fmt_dt($r['payment_date'])], ['Customer', $r['full_name'] . ' (' . $r['customer_code'] . ')'], ['Phone', $r['phone']],
        ['Membership plan', $r['plan_name']], ['Period', fmt_date($r['start_date']) . ' - ' . fmt_date($r['end_date'])], ['Received at', $r['branch_name']],
        ['Payment method', $r['method_name'] . ($r['reference'] ? ' (ref ' . $r['reference'] . ')' : '')], ['Received by', $r['received_name'] ?: '-']];
    foreach ($lines as [$k, $v]) { $pdf->text(40, $y, $k, 10, true, '#873D00'); $pdf->text(170, $y, (string) $v, 11); $pdf->hline(40, 555, $y - 6); $y -= 26; }
    $pdf->rect(40, $y - 50, 515, 60, '#F5F5F5');
    $pdf->text(55, $y - 16, 'AMOUNT PAID', 10, true, '#873D00'); $pdf->text(55, $y - 40, money($r['amount']), 22, true, '#C52B43');
    $pdf->text(300, $y - 16, 'Plan price: ' . money($r['price']), 10); $pdf->text(300, $y - 32, 'Total paid so far: ' . money($r['amount_paid']), 10);
    $pdf->text(300, $y - 48, 'Balance: ' . money($r['price'] - $r['amount_paid']), 10, true);
    $pdf->text(40, $y - 90, 'Thank you for training with CAATO FITNESS GYM. Your membership is valid at all branches.', 9, false, '#777777');
    $pdf->output($r['receipt_no'] . '.pdf');
}
$mode = req('mode', 'standard');
?>
<!DOCTYPE html><html><head><meta charset="utf-8"><title>Receipt <?= e($r['receipt_no']) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&family=Barlow+Condensed:wght@700;800&display=swap" rel="stylesheet">
<link href="<?= asset('css/app.css') ?>" rel="stylesheet">
<style>
  body { background:#090d16; padding:28px 16px; font-family:'Outfit',sans-serif; }
  .rc { max-width:580px; margin:auto; background:#fff; border-radius:20px; overflow:hidden; border:1px solid #e2e8f0; color:#1e293b; box-shadow:0 20px 50px rgba(0,0,0,0.45); }
  .thermal-rc { max-width:320px; margin:auto; background:#fff; color:#000; padding:18px 14px; font-family:monospace; font-size:12.5px; border-radius:8px; box-shadow:0 15px 40px rgba(0,0,0,0.45); }
  .thermal-rc hr { border-top: 1px dashed #000; opacity: 0.7; }
  @media print { body{background:#fff;padding:0} .rc,.thermal-rc{border:0;box-shadow:none} .no-print{display:none!important} }
</style></head><body>

<div class="text-center mb-3 no-print d-flex justify-content-center gap-2">
  <a href="<?= url('receipt', ['id' => $r['id'], 'mode' => 'standard']) ?>" class="btn btn-sm <?= $mode !== 'thermal' ? 'btn-brand' : 'btn-outline-light' ?>">
    <i class="bi bi-file-earmark-richtext"></i> Standard Receipt
  </a>
  <a href="<?= url('receipt', ['id' => $r['id'], 'mode' => 'thermal']) ?>" class="btn btn-sm <?= $mode === 'thermal' ? 'btn-brand' : 'btn-outline-light' ?>">
    <i class="bi bi-receipt"></i> 80mm POS Thermal Format
  </a>
</div>

<?php if ($mode === 'thermal'): ?>
<div class="thermal-rc">
  <div class="text-center fw-bold">
    <div style="font-size:16px">CAATO FITNESS GYM</div>
    <div>Universal 6-Branch Receipt</div>
    <div><?= e($r['branch_name']) ?></div>
  </div>
  <hr>
  <div>RECEIPT: <strong><?= e($r['receipt_no']) ?></strong></div>
  <div>DATE: <?= fmt_dt($r['payment_date']) ?></div>
  <div>MEMBER: <?= e($r['full_name']) ?> (<?= e($r['customer_code']) ?>)</div>
  <div>PHONE: <?= e($r['phone']) ?></div>
  <hr>
  <div>PLAN: <strong><?= e($r['plan_name']) ?></strong></div>
  <div>VALID: <?= fmt_date($r['start_date']) ?> -> <?= fmt_date($r['end_date']) ?></div>
  <div>CHANNEL: <?= e($r['method_name']) ?> <?= $r['reference'] ? '(' . e($r['reference']) . ')' : '' ?></div>
  <hr>
  <div class="d-flex justify-content-between fw-bold" style="font-size:15px">
    <span>PAID NOW:</span>
    <span><?= money($r['amount']) ?></span>
  </div>
  <div class="d-flex justify-content-between">
    <span>PLAN PRICE:</span>
    <span><?= money($r['price']) ?></span>
  </div>
  <div class="d-flex justify-content-between">
    <span>BALANCE DUE:</span>
    <span><?= money($r['price'] - $r['amount_paid']) ?></span>
  </div>
  <hr>
  <div class="text-center">
    <img src="https://api.qrserver.com/v1/create-qr-code/?size=95x95&margin=0&data=<?= urlencode($r['receipt_no']) ?>" width="85" height="85" alt="QR">
    <div class="mt-1">Served by: <?= e($r['received_name'] ?: 'Staff') ?></div>
    <div class="mt-1">Valid Across All 6 CAATO Branches!</div>
  </div>
</div>
<?php else: ?>
<div class="rc">
  <div style="background:linear-gradient(135deg, #160803 0%, #2b0d05 100%);border-bottom:4px solid #ff5722;color:#fff;padding:18px 24px;display:flex;gap:14px;align-items:center;justify-content:space-between">
    <div class="d-flex gap-3 align-items-center">
      <img src="<?= asset('img/logo.png') ?>" height="56" style="background:#fff;border-radius:10px;padding:3px" alt="">
      <div>
        <div class="brand-font fs-3 fw-bold">CAATO FITNESS GYM</div>
        <small style="opacity:0.85">Universal 6-Branch Official Payment Receipt</small>
      </div>
    </div>
    <span class="badge rounded-pill" style="background:rgba(16,185,129,0.2);color:#34d399;border:1px solid rgba(16,185,129,0.4)">VERIFIED</span>
  </div>
  <div class="p-4">
    <table class="table table-sm mb-3">
      <?php foreach ([
        ['Receipt No', $r['receipt_no']],
        ['Timestamp', fmt_dt($r['payment_date'])],
        ['Member', $r['full_name'] . ' (' . $r['customer_code'] . ') · ' . $r['phone']],
        ['Subscription Tier', $r['plan_name'] . ' · ' . fmt_date($r['start_date']) . ' → ' . fmt_date($r['end_date'])],
        ['Facility Node', $r['branch_name']],
        ['Payment Channel', $r['method_name'] . ($r['reference'] ? ' · Ref: ' . $r['reference'] : '')],
        ['Cashier / Staff', $r['received_name'] ?: '—']
      ] as [$k, $v]): ?>
      <tr><th class="text-muted" style="width:32%"><?= $k ?></th><td class="fw-semibold"><?= e($v) ?></td></tr>
      <?php endforeach; ?>
    </table>
    <div class="p-3 rounded-3 d-flex justify-content-between align-items-center" style="background:#f8fafc;border:1px solid #e2e8f0">
      <div>
        <small class="text-muted text-uppercase fw-bold" style="font-size:0.74rem">Amount Paid</small>
        <div class="brand-font" style="font-size:2.35rem;color:#ff5722;font-weight:800;line-height:1"><?= money($r['amount']) ?></div>
      </div>
      <div class="text-end small">
        Plan Price: <strong><?= money($r['price']) ?></strong><br>
        Total Paid: <strong class="text-success"><?= money($r['amount_paid']) ?></strong><br>
        <strong class="<?= ($r['price'] - $r['amount_paid']) > 0 ? 'text-danger' : 'text-success' ?>">Balance: <?= money($r['price'] - $r['amount_paid']) ?></strong>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<div class="text-center mt-4 no-print d-flex flex-wrap gap-2 justify-content-center">
  <button class="btn btn-brand" onclick="window.print()"><i class="bi bi-printer-fill"></i> Print Receipt</button>
  <a class="btn btn-outline-light" href="<?= url('receipt', ['id' => $r['id'], 'format' => 'pdf']) ?>"><i class="bi bi-file-earmark-pdf-fill text-danger"></i> Download PDF</a>
  <a class="btn btn-whatsapp" target="_blank" href="<?= e(whatsapp_url($r['phone'], "Asc {$r['full_name']}, waad ku mahadsan tahay lacag bixintaada CAATO FITNESS GYM! Rasiidh #: {$r['receipt_no']}, Qadarka: " . money($r['amount']) . " ({$r['plan_name']}). Xubinnimadaadu waxay ku egtahay " . fmt_date($r['end_date']) . ".")) ?>">
    <i class="bi bi-whatsapp"></i> Send WhatsApp Receipt
  </a>
  <a class="btn btn-outline-light" href="<?= url('customer_view', ['id' => $r['customer_id']]) ?>"><i class="bi bi-arrow-left"></i> Member Profile</a>
</div>
</body></html>
