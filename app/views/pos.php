<?php
$posActs = Auth::can('pos.manage')
  ? '<button class="btn btn-brand btn-sm px-3 py-2" onclick="prodForm()"><i class="bi bi-plus-circle-fill"></i> Add Product / Restock</button>'
  : '';
page_banner(
  'INSTANT RETAIL & SHAKES · 6-BRANCH POINT OF SALE',
  'POS &amp;',
  'Supplement Bar',
  'Sell protein shakes, pre-workout shots, hydration drinks & athletic gear with automated real-time stock tracking.',
  $posActs,
  [
    [money($todayTotal), 'Today POS', 'success'],
    [money($monthTotal), 'Month POS', 'warning'],
    [count($products), 'Catalog Items', 'info'],
  ]
);
?>

<!-- 3D Holographic POS KPI Strip -->
<div class="row g-3 mb-4">
  <div class="col-md-4">
    <div class="card stat stat-pro emerald">
      <div class="stat-glow-orb"></div>
      <i class="bi bi-cup-straw stat-watermark"></i>
      <div class="stat-top">
        <div class="ico"><i class="bi bi-cup-straw"></i></div>
        <div class="stat-meta-right">
          <span class="stat-pill"><span class="stat-dot"></span> Today</span>
        </div>
      </div>
      <div class="stat-body">
        <div class="num"><?= money($todayTotal) ?></div>
        <div class="stat-title">TODAY'S POS BAR REVENUE</div>
      </div>
      <div class="stat-foot">
        <span class="stat-sub"><i class="bi bi-lightning-charge-fill"></i> Instant supplement &amp; drink sales</span>
        <div class="stat-spark"><div class="stat-spark-fill" style="width:78%"></div></div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card stat stat-pro brand">
      <div class="stat-glow-orb"></div>
      <i class="bi bi-graph-up-arrow stat-watermark"></i>
      <div class="stat-top">
        <div class="ico"><i class="bi bi-graph-up-arrow"></i></div>
        <div class="stat-meta-right">
          <span class="stat-pill"><span class="stat-dot"></span> <?= date('M Y') ?></span>
        </div>
      </div>
      <div class="stat-body">
        <div class="num"><?= money($monthTotal) ?></div>
        <div class="stat-title">MONTHLY POS BAR REVENUE</div>
      </div>
      <div class="stat-foot">
        <span class="stat-sub"><i class="bi bi-phone-vibrate-fill"></i> EVC Plus · Salaam Bank · E-Dahab</span>
        <div class="stat-spark"><div class="stat-spark-fill" style="width:88%"></div></div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card stat stat-pro cyan">
      <div class="stat-glow-orb"></div>
      <i class="bi bi-box-seam-fill stat-watermark"></i>
      <div class="stat-top">
        <div class="ico"><i class="bi bi-box-seam-fill"></i></div>
        <div class="stat-meta-right">
          <span class="stat-pill"><span class="stat-dot"></span> Live Stock</span>
        </div>
      </div>
      <div class="stat-body">
        <div class="num"><?= count($products) ?> SKUs</div>
        <div class="stat-title">ACTIVE PRODUCT CATALOG</div>
      </div>
      <div class="stat-foot">
        <span class="stat-sub"><i class="bi bi-check2-circle"></i> Real-time inventory deduction</span>
        <div class="stat-spark"><div class="stat-spark-fill" style="width:94%"></div></div>
      </div>
    </div>
  </div>
</div>

<!-- 3D Holographic Product Catalog Grid -->
<div class="row g-3 mb-4">
<?php foreach ($products as $p):
  $catMap = [
    'Supplements'    => ['ico' => 'bi-lightning-charge-fill', 'theme' => 'amber'],
    'Beverages'      => ['ico' => 'bi-droplet-fill',          'theme' => 'cyan'],
    'Gear & Apparel' => ['ico' => 'bi-bag-heart-fill',        'theme' => 'brand'],
    'Snacks & Bars'  => ['ico' => 'bi-box2-heart-fill',       'theme' => 'violet'],
  ];
  $cm = $catMap[$p['category']] ?? ['ico' => 'bi-box-seam-fill', 'theme' => 'emerald'];
  $stk = (int) $p['stock_qty'];
  $stkPct = min(100, max(8, (int) round(($stk / 100) * 100)));
?>
  <div class="col-md-6 col-lg-4 col-xl-3">
    <div class="card stat stat-pro <?= $cm['theme'] ?> h-100">
      <div class="stat-glow-orb"></div>
      <i class="bi <?= $cm['ico'] ?> stat-watermark"></i>
      <div class="stat-top">
        <div class="ico"><i class="bi <?= $cm['ico'] ?>"></i></div>
        <div class="stat-meta-right">
          <span class="stat-pill"><span class="stat-dot"></span> <?= e($p['category']) ?></span>
          <span class="badge text-bg-<?= $stk > 10 ? 'success' : ($stk > 0 ? 'warning' : 'danger') ?>">
            <?= $stk ?> in stock
          </span>
        </div>
      </div>

      <div class="stat-body my-1">
        <div class="fw-bold fs-6 mb-1" style="line-height:1.25"><?= e($p['name']) ?></div>
        <small class="text-muted font-monospace d-block mb-2"><?= e($p['sku']) ?></small>
        <div class="num"><?= money($p['price']) ?></div>
      </div>

      <div class="stat-foot mt-auto">
        <div class="stat-spark mb-2"><div class="stat-spark-fill" style="width:<?= $stkPct ?>%"></div></div>
        <div class="d-flex gap-2">
          <?php if (Auth::can('pos.create') && $p['status'] === 'active' && $stk > 0): ?>
            <button class="btn btn-sm btn-brand flex-grow-1" onclick="sellModal(<?= jsattr($p) ?>)">
              <i class="bi bi-cart-check-fill"></i> Quick Sell
            </button>
          <?php elseif ($stk <= 0): ?>
            <button class="btn btn-sm btn-secondary flex-grow-1" disabled>Out of Stock</button>
          <?php else: ?>
            <span class="badge text-bg-secondary w-100 py-2">View Only</span>
          <?php endif; ?>
          <?php if (Auth::can('pos.manage')): ?>
            <button class="btn btn-sm btn-outline-secondary" onclick="prodForm(<?= jsattr($p) ?>)" title="Edit / Restock">
              <i class="bi bi-pencil-square"></i>
            </button>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
<?php endforeach; ?>
</div>

<!-- Recent POS Sales Table -->
<div class="card card-bento-panel shadow-sm">
  <i class="bi bi-receipt panel-watermark"></i>
  <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
    <div class="d-flex align-items-center gap-2">
      <span class="panel-hdr-ico brand"><i class="bi bi-receipt"></i></span>
      <span>Recent POS Bar Transactions</span>
    </div>
    <?php if (Auth::isSuper()): ?>
    <form method="get" class="d-flex gap-2">
      <input type="hidden" name="page" value="pos">
      <select name="branch" class="form-select form-select-sm" onchange="this.form.submit()">
        <?= opts(branch_list(false), $br, 'id', 'name', 'All 6 Branches') ?>
      </select>
    </form>
    <?php else: ?>
      <span class="gate-visit-pill active"><i class="bi bi-geo-alt-fill text-danger"></i> <?= e(short_branch(Auth::user()['branch_name'] ?? '')) ?> Scope</span>
    <?php endif; ?>
  </div>
  <div class="table-responsive">
    <table class="table table-hover table-pro-matrix align-middle mb-0">
      <thead>
        <tr>
          <th>Sale #</th>
          <th>Timestamp</th>
          <th>Item Sold</th>
          <th>Qty</th>
          <th>Total</th>
          <th>Channel &amp; Ref</th>
          <th>Facility</th>
          <th>Cashier</th>
          <?php if (Auth::can('pos.manage')): ?><th class="text-end">Void</th><?php endif; ?>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($sales as $s): ?>
        <tr>
          <td class="font-monospace fw-bold small"><?= e($s['sale_no']) ?></td>
          <td><small class="text-muted"><?= fmt_dt($s['sold_at']) ?></small></td>
          <td class="fw-semibold"><?= e($s['product_name']) ?> <small class="text-muted">(<?= e($s['category']) ?>)</small></td>
          <td><span class="badge text-bg-light border">x<?= (int) $s['qty'] ?></span></td>
          <td><span class="matrix-rev-month"><?= money($s['total_amount']) ?></span></td>
          <td>
            <span class="badge text-bg-secondary"><?= e($s['method_name']) ?></span>
            <?= $s['reference'] ? '<small class="text-muted font-monospace ms-1">' . e($s['reference']) . '</small>' : '' ?>
          </td>
          <td><span class="gate-visit-pill"><i class="bi bi-geo-alt-fill text-danger"></i> <?= e(short_branch($s['branch_name'])) ?></span></td>
          <td><small class="text-muted"><?= e($s['seller'] ?: 'Staff') ?></small></td>
          <?php if (Auth::can('pos.manage')): ?>
          <td class="text-end">
            <form method="post" class="d-inline">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="delete_sale">
              <input type="hidden" name="id" value="<?= $s['id'] ?>">
              <button class="btn btn-sm btn-outline-danger py-0 px-2" data-confirm="Void this POS sale and restore stock?"><i class="bi bi-trash3"></i></button>
            </form>
          </td>
          <?php endif; ?>
        </tr>
      <?php endforeach; if (!$sales): ?>
        <tr><td colspan="9" class="text-center text-muted py-5"><i class="bi bi-cup-straw fs-2 d-block mb-2"></i>No POS bar sales recorded yet.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Quick Sell Modal -->
<div class="modal fade" id="posSellModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <form class="modal-content shadow-lg" method="post">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="sell">
      <input type="hidden" name="product_id" id="spId">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-cart-check-fill text-brand me-2 fs-5"></i> Instant POS Checkout</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="p-3 rounded-3 bg-body-tertiary border mb-3 d-flex justify-content-between align-items-center">
          <div>
            <small class="text-muted d-block">PRODUCT ITEM</small>
            <strong class="fs-5" id="spName"></strong>
          </div>
          <div class="text-end">
            <small class="text-muted d-block">TOTAL DUE</small>
            <strong class="fs-3 text-brand" style="font-family:'Barlow Condensed'" id="spTotal"></strong>
          </div>
        </div>

        <?php if (Auth::isSuper()): ?>
          <label class="form-label small fw-semibold">Facility Branch *</label>
          <select name="branch_id" class="form-select mb-2" required>
            <?= opts($branches, '', 'id', 'name') ?>
          </select>
        <?php endif; ?>

        <div class="row g-2 mb-2">
          <div class="col-5">
            <label class="form-label small fw-semibold">Quantity *</label>
            <input type="number" name="qty" id="spQty" min="1" value="1" class="form-control" required oninput="calcPosTotal()">
          </div>
          <div class="col-7">
            <label class="form-label small fw-semibold">Payment Channel *</label>
            <select name="method_id" class="form-select" required>
              <?= opts($methods, '', 'id', 'name') ?>
            </select>
          </div>
        </div>

        <label class="form-label small fw-semibold">Transaction Reference (EVC Plus / Salaam Bank / E-Dahab) *</label>
        <input name="reference" class="form-control" required placeholder="Enter transaction ref or mobile number">
      </div>
      <div class="modal-footer">
        <button class="btn btn-brand w-100 py-2"><i class="bi bi-check2-circle"></i> Complete POS Sale</button>
      </div>
    </form>
  </div>
</div>

<!-- Add / Edit Product Modal -->
<?php if (Auth::can('pos.manage')): ?>
<div class="modal fade" id="posProdModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <form class="modal-content shadow-lg" method="post">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="save_product">
      <input type="hidden" name="id" id="ppId">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-box-seam-fill text-brand me-2 fs-5"></i> POS Product &amp; Stock</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body row g-3">
        <div class="col-12">
          <label class="form-label small fw-semibold">Product Name *</label>
          <input name="name" id="ppName" class="form-control" required placeholder="e.g. Whey Protein Shake (1 Scoop)">
        </div>
        <div class="col-6">
          <label class="form-label small fw-semibold">Category</label>
          <select name="category" id="ppCat" class="form-select">
            <option>Supplements</option>
            <option>Beverages</option>
            <option>Gear &amp; Apparel</option>
            <option>Snacks &amp; Bars</option>
          </select>
        </div>
        <div class="col-6">
          <label class="form-label small fw-semibold">Unit Price *</label>
          <input type="number" step="0.01" min="0.1" name="price" id="ppPrice" class="form-control" required>
        </div>
        <div class="col-6">
          <label class="form-label small fw-semibold">Available Stock Qty *</label>
          <input type="number" min="0" name="stock_qty" id="ppStock" class="form-control" required>
        </div>
        <div class="col-6">
          <label class="form-label small fw-semibold">Status</label>
          <select name="status" id="ppStatus" class="form-select">
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-brand"><i class="bi bi-check-circle"></i> Save Product Inventory</button>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>

<script>
let curUnitPrice = 0;
function sellModal(p) {
  curUnitPrice = parseFloat(p.price) || 0;
  document.getElementById('spId').value = p.id;
  document.getElementById('spName').textContent = p.name;
  document.getElementById('spQty').value = 1;
  document.getElementById('spQty').max = p.stock_qty;
  calcPosTotal();
  new bootstrap.Modal(document.getElementById('posSellModal')).show();
}
function calcPosTotal() {
  const q = Math.max(1, parseInt(document.getElementById('spQty').value || '1', 10));
  document.getElementById('spTotal').textContent = CAATO.currency + (curUnitPrice * q).toFixed(2);
}
function prodForm(p) {
  p = p || {};
  document.getElementById('ppId').value = p.id || '';
  document.getElementById('ppName').value = p.name || '';
  document.getElementById('ppCat').value = p.category || 'Supplements';
  document.getElementById('ppPrice').value = p.price || '';
  document.getElementById('ppStock').value = p.stock_qty ?? 50;
  document.getElementById('ppStatus').value = p.status || 'active';
  new bootstrap.Modal(document.getElementById('posProdModal')).show();
}
</script>
