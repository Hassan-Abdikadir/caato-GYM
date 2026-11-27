<?php
Auth::need('memberships.view');
if (is_post()) {
    Auth::need('plans.manage');
    try {
        $a = req('action');
        if ($a === 'save_plan') {
            $id = (int) req('id'); $name = req('name'); $type = req('type'); $days = (int) req('duration_days'); $price = (float) req('price');
            if (!in_array($type, ['daily', 'weekly', 'monthly', 'quarterly', 'semiannual', 'annual', 'custom'], true)) throw new RuntimeException('Choose a plan type.');
            if ($name === '' || $days < 1 || $days > 3660 || $price < 0) throw new RuntimeException('Enter a name, a duration in days and a valid price.');
            $d = ['name' => $name, 'type' => $type, 'duration_days' => $days, 'price' => $price];
            if ($id) { DB::update('membership_plans', $d, 'id=?', [$id]); audit('plan_update', 'plan', $id, "$name @ $price"); }
            else { $id = DB::insert('membership_plans', $d); audit('plan_create', 'plan', $id, "$name @ $price"); }
            flash('success', 'Plan saved. New sales use the new price; existing memberships keep the price they were sold at.');
        } elseif ($a === 'toggle_plan') {
            DB::exec("UPDATE membership_plans SET status=IF(status='active','inactive','active') WHERE id=?", [(int) req('id')]);
            audit('plan_toggle', 'plan', (int) req('id')); flash('success', 'Plan status changed.');
        }
    } catch (PDOException $e) { flash('error', 'A plan with that name already exists.'); }
    catch (RuntimeException $e) { flash('error', $e->getMessage()); }
    redirect('memberships', ['tab' => 'plans']);
}
$tab = req('tab', 'list') === 'plans' ? 'plans' : 'list';
$plans = DB::all("SELECT p.*, (SELECT COUNT(*) FROM memberships m WHERE m.plan_id=p.id AND m.status='active' AND m.end_date>=CURDATE()) AS active_count FROM membership_plans p ORDER BY p.duration_days");
$q = req('q'); $st = req('status'); $pl = (int) req('plan'); $br = (int) req('branch'); $ps = req('pstatus');
$eff = "CASE WHEN m.status='active' AND m.end_date<CURDATE() THEN 'expired' ELSE m.status END";
[$sc, $sp] = scope_branch('m.branch_id', $br);
$w = " WHERE 1=1 $sc"; $p = $sp;
if ($q !== '') { $w .= ' AND (c.full_name LIKE ? OR c.customer_code LIKE ? OR c.phone LIKE ?)'; array_push($p, "%$q%", "%$q%", "%$q%"); }
if (in_array($st, ['pending', 'active', 'expired', 'suspended', 'cancelled'], true)) { $w .= " AND ($eff)=?"; $p[] = $st; }
if (in_array($ps, ['paid', 'partial', 'unpaid'], true)) { $w .= ' AND m.payment_status=?'; $p[] = $ps; }
if ($pl) { $w .= ' AND m.plan_id=?'; $p[] = $pl; }
$from = ' FROM memberships m JOIN customers c ON c.id=m.customer_id JOIN membership_plans pl ON pl.id=m.plan_id JOIN branches b ON b.id=m.branch_id';
$pg = paginate((int) DB::val('SELECT COUNT(*)' . $from . $w, $p), 15);
$rows = DB::all("SELECT m.*, c.full_name, c.customer_code, c.phone, c.profile_picture, pl.name AS plan_name, b.name AS branch_name, ($eff) AS eff" . $from . $w . ' ORDER BY m.id DESC LIMIT 15 OFFSET ' . $pg['offset'], $p);
render('memberships', compact('plans', 'rows', 'pg', 'tab', 'q', 'st', 'pl', 'br', 'ps') + ['pageTitle' => 'Memberships & Plans']);
