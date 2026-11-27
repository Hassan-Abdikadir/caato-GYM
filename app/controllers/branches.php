<?php
Auth::need('branches.manage');
if (is_post()) {
    try {
        $a = req('action'); $id = (int) req('id');
        if ($a === 'save') {
            $code = strtoupper(req('code')); $name = req('name');
            if ($code === '' || $name === '') throw new RuntimeException('Branch code and name are required.');
            if (req('email') !== '' && !filter_var(req('email'), FILTER_VALIDATE_EMAIL)) throw new RuntimeException('Branch email is not valid.');
            $d = ['code' => $code, 'name' => $name, 'address' => req('address') ?: null, 'phone' => req('phone') ?: null, 'email' => req('email') ?: null];
            if ($id) DB::update('branches', $d, 'id=?', [$id]); else $id = DB::insert('branches', $d);
            audit('branch_save', 'branch', $id, $name); flash('success', 'Branch saved.');
        } elseif ($a === 'toggle') {
            DB::exec("UPDATE branches SET status=IF(status='active','inactive','active') WHERE id=?", [$id]);
            audit('branch_toggle', 'branch', $id); flash('success', 'Branch status changed. Staff of an inactive branch cannot sign in.');
        } elseif ($a === 'delete') {
            $custCount = (int) DB::val('SELECT COUNT(*) FROM customers WHERE registered_branch_id=?', [$id]);
            $empCount  = (int) DB::val('SELECT COUNT(*) FROM employees WHERE branch_id=?', [$id]);
            $payCount  = (int) DB::val('SELECT COUNT(*) FROM payments WHERE branch_id=?', [$id]);
            $msCount   = (int) DB::val('SELECT COUNT(*) FROM memberships WHERE branch_id=?', [$id]);
            $attCount  = (int) DB::val('SELECT COUNT(*) FROM attendance WHERE branch_id=?', [$id]);
            if ($custCount > 0 || $empCount > 0 || $payCount > 0 || $msCount > 0 || $attCount > 0) {
                throw new RuntimeException("Cannot delete this branch: it has historical records ($custCount customers, $empCount staff, $msCount memberships, $payCount payments). Deactivate it instead.");
            }
            DB::exec('DELETE FROM branches WHERE id=?', [$id]);
            audit('branch_delete', 'branch', $id); flash('success', 'Branch permanently deleted.');
        }
    } catch (PDOException $e) { flash('error', 'That branch code or name is already in use.'); }
    catch (RuntimeException $e) { flash('error', $e->getMessage()); }
    redirect('branches');
}
$stats = Report::branchStats();
$extra = DB::all("SELECT id, address, phone, email FROM branches"); $ex = [];
foreach ($extra as $r) $ex[$r['id']] = $r;
render('branches', ['pageTitle' => 'Branches', 'stats' => $stats, 'ex' => $ex]);
