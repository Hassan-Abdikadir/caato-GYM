<?php
Auth::need('pos.view');
$methods = DB::all("SELECT * FROM payment_methods WHERE status='active' ORDER BY id");

if (is_post()) {
    try {
        $a = req('action');
        if ($a === 'sell') {
            Auth::need('pos.create');
            $pid = (int) req('product_id');
            $qty = max(1, (int) req('qty', 1));
            $methodId = (int) req('method_id');
            $branchId = Auth::resolveBranch(req('branch_id'));
            $ref = trim(req('reference'));

            $prod = DB::one("SELECT * FROM pos_products WHERE id=? AND status='active'", [$pid]);
            if (!$prod) throw new RuntimeException('Choose an active product.');
            if ((int) $prod['stock_qty'] < $qty) throw new RuntimeException("Insufficient stock for {$prod['name']} (Only {$prod['stock_qty']} left).");
            $method = DB::one("SELECT * FROM payment_methods WHERE id=? AND status='active'", [$methodId]);
            if (!$method) throw new RuntimeException('Select a valid payment channel.');
            if ($method['requires_reference'] && $ref === '') throw new RuntimeException($method['name'] . ' requires a transaction reference.');

            $total = round((float) $prod['price'] * $qty, 2);
            DB::tx(function () use ($prod, $qty, $total, $methodId, $branchId, $ref) {
                $sid = DB::insert('pos_sales', [
                    'sale_no' => 'TMP' . bin2hex(random_bytes(6)),
                    'product_id' => $prod['id'],
                    'branch_id' => $branchId,
                    'method_id' => $methodId,
                    'qty' => $qty,
                    'unit_price' => $prod['price'],
                    'total_amount' => $total,
                    'reference' => $ref ?: null,
                    'sold_at' => date('Y-m-d H:i:s'),
                    'sold_by' => Auth::id(),
                ]);
                DB::update('pos_sales', ['sale_no' => 'POS-' . date('Ymd') . '-' . str_pad((string) $sid, 5, '0', STR_PAD_LEFT)], 'id=?', [$sid]);
                DB::exec('UPDATE pos_products SET stock_qty = GREATEST(0, stock_qty - ?) WHERE id=?', [$qty, $prod['id']]);
                audit('pos_sale', 'pos_sale', $sid, "{$qty}x {$prod['name']} (" . money($total) . ')');
            });
            flash('success', "POS Sale recorded: {$qty}x {$prod['name']} (" . money($total) . ').');
        } elseif ($a === 'save_product') {
            Auth::need('pos.manage');
            $id = (int) req('id');
            $name = req('name');
            $cat = req('category', 'Supplements');
            $price = (float) req('price');
            $stock = max(0, (int) req('stock_qty', 0));
            if ($name === '' || $price <= 0) throw new RuntimeException('Product name and a valid price are required.');
            $d = ['name' => $name, 'category' => $cat, 'price' => $price, 'stock_qty' => $stock, 'status' => req('status') === 'inactive' ? 'inactive' : 'active'];
            if ($id) {
                DB::update('pos_products', $d, 'id=?', [$id]);
            } else {
                $d['sku'] = 'POS-' . strtoupper(bin2hex(random_bytes(3)));
                $id = DB::insert('pos_products', $d);
                DB::update('pos_products', ['sku' => 'POS-' . str_pad((string) $id, 3, '0', STR_PAD_LEFT)], 'id=?', [$id]);
            }
            audit('pos_product_save', 'pos_product', $id, "$name @ $price");
            flash('success', 'POS Product inventory saved.');
        } elseif ($a === 'delete_sale') {
            Auth::need('pos.manage');
            $sid = (int) req('id');
            $s = DB::one('SELECT * FROM pos_sales WHERE id=?', [$sid]);
            if ($s) {
                if (!Auth::isSuper() && (int) $s['branch_id'] !== (int) Auth::branchId()) {
                    throw new RuntimeException('You can only void POS sales from your own branch.');
                }
                DB::exec('UPDATE pos_products SET stock_qty = stock_qty + ? WHERE id=?', [$s['qty'], $s['product_id']]);
                DB::exec('DELETE FROM pos_sales WHERE id=?', [$sid]);
                flash('success', 'POS sale voided and stock restored.');
            }
        }
    } catch (RuntimeException $e) {
        flash('error', $e->getMessage());
    }
    redirect('pos');
}

$br = (int) req('branch');
[$sc, $sp] = scope_branch('s.branch_id', $br);
$products = DB::all('SELECT * FROM pos_products ORDER BY status, category, name');
$sales = DB::all("SELECT s.*, p.name AS product_name, p.sku, p.category, b.name AS branch_name, pm.name AS method_name, u.full_name AS seller
                  FROM pos_sales s JOIN pos_products p ON p.id=s.product_id JOIN branches b ON b.id=s.branch_id
                  JOIN payment_methods pm ON pm.id=s.method_id LEFT JOIN users u ON u.id=s.sold_by
                  WHERE 1=1 $sc ORDER BY s.id DESC LIMIT 40", $sp);
$todayTotal = (float) DB::val("SELECT COALESCE(SUM(s.total_amount),0) FROM pos_sales s WHERE DATE(s.sold_at)=CURDATE() $sc", $sp);
$monthTotal = (float) DB::val("SELECT COALESCE(SUM(s.total_amount),0) FROM pos_sales s WHERE s.sold_at>=DATE_FORMAT(CURDATE(),'%Y-%m-01') $sc", $sp);

render('pos', [
    'pageTitle' => 'POS & Supplement Bar',
    'products' => $products,
    'sales' => $sales,
    'methods' => $methods,
    'branches' => branch_list(),
    'br' => $br,
    'todayTotal' => $todayTotal,
    'monthTotal' => $monthTotal,
]);
