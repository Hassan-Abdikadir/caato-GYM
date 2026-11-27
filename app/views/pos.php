<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
  <div>
    <div class="d-flex align-items-center gap-2 mb-1">
      <h1 class="page-title">POS &amp; Supplement Bar</h1>
      <span class="badge badge-futuristic"><span class="pulse-dot"></span> INSTANT RETAIL &amp; SHAKES</span>
    </div>
    <small class="text-muted"><i class="bi bi-cup-straw text-brand me-1"></i> Protein Shakes, Pre-Workout, Hydration &amp; Athletic Gear Point-of-Sale across all 6 facilities</small>
  </div>
  <div class="d-flex gap-2 align-items-center">
    <span class="badge text-bg-success fs-6 py-2 px-3">Today POS: <?= money($todayTotal) ?></span>
    <span class="badge text-bg-info fs-6 py-2 px-3">Month POS: <?= money($monthTotal) ?></span>
    <?php if (Auth::can('plans.manage')): ?>
      <button class="btn btn-brand" onclick="prodForm()">
        <i class="bi bi-plus-circle-fill"></i> Add Product / Restock
      </button>
    <?php endif; ?>
  </div>
</div>

<!-- Product Catalog Grid -->
<div class="row g-3 mb-4">
<?php foreach ($products as $p):
  $iconMap = ['Supplements' => 'bi-lightning-charge-fill text-warning', 'Beverages' => 'bi-droplet-fill text-info', 'Gear & Apparel' => 'bi-bag-heart-fill text-danger'];
  $ic = $iconMap[$p['category']] ?? 'bi-box-seam-fill text-brand';
?>
  <div class="col-md-6 col-lg-4 col-xl-3">
    <div class="card h-100 shadow-sm">
      <div class="card-body p-3 d-flex flex-column">
        <div class="d-flex justify-content-between align-items-start mb-2">
          <span class="badge text-bg-light border"><i class="bi <?= $ic ?> me-1"></i><?= e($p['category']) ?></span>
          <span class="badge text-bg-<?= $p['stock_qty'] > 10 ? 'success' : ($p['stock_qty'] > 0 ? 'warning' : 'danger') ?>">
            <?= (int) $p['stock_qty'] ?> in stock
          </span>
        </div>
        <h5 class="fw-bold mb-1"><?= e($p['name']) ?></h5>
        <small class="text-muted font-monospace mb-2"><?= e($p['sku']) ?></small>
        <div class="fs-3 fw-bold text-brand mb-3" style="font-family:'Barlow Condensed'"><?= money($p['price']) ?></div>

        <div class="mt-auto d-flex gap-2">
          <?php if (Auth::can('payments.create') && $p['status'] === 'active' && $p['stock_qty'] > 0): ?>
            <button class="btn btn-sm btn-brand flex-grow-1" onclick="sellModal(<?= jsattr($p) ?>)">
              <i class="bi bi-cart-check-fill"></i> Quick Sell
            </button>
          <?php else: ?>
            <button class="btn btn-sm btn-secondary flex-grow-1" disabled>Out of Stock</button>
          <?php endif; ?>
          <?php if (Auth::can('plans.manage')): ?>
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
<div class="card shadow-sm">
  <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
    <span><i class="bi bi-receipt text-brand me-2 fs-5"></i> Recent POS Bar Transactions</span>
    <?php if (Auth::isSuper()): ?>
    <form method="get" class="d-flex gap-2">
      <input type="hidden" name="page" value="pos">
      <select name="branch" class="form-select form-select-sm" onchange="this.form.submit()">
        <?= opts(branch_list(false), $br, 'id', 'name', 'All 6 Branches') ?>
      </select>
    </form>
    <?php endif; ?>
  </div>
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
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
          <?php if (Auth::can('plans.manage')): ?><th class="text-end">Void</th><?php endif; ?>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($sales as $s): ?>
        <tr>
          <td class="font-monospace fw-bold small"><?= e($s['sale_no']) ?></td>
          <td><small class="text-muted"><?= fmt_dt($s['sold_at']) ?></small></td>
          <td class="fw-semibold"><?= e($s['product_name']) ?> <small class="text-muted">(<?= e($s['category']) ?>)</small></td>
          <td><span class="badge text-bg-light border">x<?= (int) $s['qty'] ?></span></td>
          <td class="fw-bold text-brand"><?= money($s['total_amount']) ?></td>
          <td>
            <span class="badge text-bg-secondary"><?= e($s['method_name']) ?></span>
            <?= $s['reference'] ? '<small class="text-muted font-monospace ms-1">' . e($s['reference']) . '</small>' : '' ?>
          </td>
          <td><i class="bi bi-geo-alt-fill text-danger me-1"></i><?= e(str_replace('CAATO FITNESS GYM – ', '', $s['branch_name'])) ?></td>
          <td><small class="text-muted"><?= e($s['seller'] ?: 'Staff') ?></small></td>
          <?php if (Auth::can('plans.manage')): ?>
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
<?php if (Auth::can('plans.manage')): ?>
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
