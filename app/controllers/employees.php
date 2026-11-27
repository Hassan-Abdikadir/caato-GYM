<?php
Auth::need('employees.manage');
$roles = Auth::isSuper() ? DB::all('SELECT id, name, label FROM roles ORDER BY id')
                         : DB::all("SELECT id, name, label FROM roles WHERE name IN ('receptionist','accountant','trainer') ORDER BY id");
if (is_post()) {
    try {
        $a = req('action'); $id = (int) req('id');
        $cur = $id ? DB::one('SELECT * FROM employees WHERE id=?', [$id]) : null;
        if ($cur && !Auth::isSuper() && (int) $cur['branch_id'] !== (int) Auth::branchId()) throw new RuntimeException('You can only manage employees of your own branch.');
        if ($a === 'toggle' && $cur) {
            $ns = $cur['status'] === 'active' ? 'inactive' : 'active';
            DB::update('employees', ['status' => $ns], 'id=?', [$id]);
            if ($cur['user_id']) DB::update('users', ['status' => $ns], 'id=?', [$cur['user_id']]);
            audit('employee_toggle', 'employee', $id, $ns); flash('success', 'Employee ' . $ns . '.');
        } elseif ($a === 'delete' && $cur) {
            if (!Auth::isSuper() && (int)$cur['branch_id'] !== (int)Auth::branchId()) throw new RuntimeException('You can only delete employees from your own branch.');
            DB::tx(function() use ($id, $cur) {
                if ($cur['user_id']) DB::exec('DELETE FROM users WHERE id=?', [$cur['user_id']]);
                DB::exec('DELETE FROM employees WHERE id=?', [$id]);
            });
            audit('employee_delete', 'employee', $id, $cur['full_name']); flash('success', 'Employee permanently deleted.');
        } elseif ($a === 'save') {
            $roleId = (int) req('role_id');
            if (!in_array($roleId, array_column($roles, 'id'))) throw new RuntimeException('You cannot assign that role.');
            $branch = Auth::resolveBranch(req('branch_id'));
            if (mb_strlen(req('full_name')) < 3 || req('phone') === '' || req('position') === '' || !strtotime(req('joining_date'))) throw new RuntimeException('Name, phone, position and joining date are required.');
            if (req('email') !== '' && !filter_var(req('email'), FILTER_VALIDATE_EMAIL)) throw new RuntimeException('Email address is not valid.');
            $salary = Auth::isSuper() || !$cur ? max(0, (float) req('salary')) : (float) $cur['salary'];
            $d = ['full_name' => req('full_name'), 'phone' => req('phone'), 'email' => req('email') ?: null, 'position' => req('position'), 'role_id' => $roleId,
                  'branch_id' => $branch, 'salary' => $salary, 'joining_date' => req('joining_date'), 'status' => req('status') === 'inactive' ? 'inactive' : 'active'];
            DB::tx(function () use (&$id, $cur, $d, $roleId, $branch) {
                if ($id) DB::update('employees', $d, 'id=?', [$id]); else $id = DB::insert('employees', $d);
                $uid = $cur['user_id'] ?? null;
                if ($uid) {
                    DB::update('users', ['full_name' => $d['full_name'], 'role_id' => $roleId, 'branch_id' => $branch, 'status' => $d['status']], 'id=?', [$uid]);
                } elseif (req('create_login')) {
                    $un = strtolower(req('username')); $pw = (string) ($_POST['password'] ?? '');
                    if (!preg_match('/^[a-z0-9._-]{3,50}$/', $un)) throw new RuntimeException('Username must be 3–50 letters/numbers.');
                    if (strlen($pw) < 8) throw new RuntimeException('Password must be at least 8 characters.');
                    if (DB::val('SELECT id FROM users WHERE username=?', [$un])) throw new RuntimeException('That username is taken.');
                    $uid = DB::insert('users', ['full_name' => $d['full_name'], 'username' => $un, 'email' => $d['email'], 'password_hash' => password_hash($pw, PASSWORD_DEFAULT), 'role_id' => $roleId, 'branch_id' => $branch]);
                    DB::update('employees', ['user_id' => $uid], 'id=?', [$id]);
                }
            });
            audit('employee_save', 'employee', $id, $d['full_name']); flash('success', 'Employee saved.');
        }
    } catch (PDOException $e) { flash('error', 'Could not save: the email or username is already used by another account.'); }
    catch (RuntimeException $e) { flash('error', $e->getMessage()); }
    redirect('employees');
}
$br = (int) req('branch'); $rl = (int) req('role'); $st = req('status'); $q = req('q');
[$sc, $sp] = scope_branch('e.branch_id', $br); $w = " WHERE 1=1 $sc"; $p = $sp;
if ($rl) { $w .= ' AND e.role_id=?'; $p[] = $rl; }
if (in_array($st, ['active', 'inactive'], true)) { $w .= ' AND e.status=?'; $p[] = $st; }
if ($q !== '') { $w .= ' AND (e.full_name LIKE ? OR e.phone LIKE ?)'; array_push($p, "%$q%", "%$q%"); }
$from = ' FROM employees e JOIN roles r ON r.id=e.role_id JOIN branches b ON b.id=e.branch_id LEFT JOIN users u ON u.id=e.user_id';
$pg = paginate((int) DB::val('SELECT COUNT(*)' . $from . $w, $p), 15);
$rows = DB::all('SELECT e.*, r.label AS role_label, b.name AS branch_name, u.username' . $from . $w . ' ORDER BY b.id, e.full_name LIMIT 15 OFFSET ' . $pg['offset'], $p);
render('employees', compact('rows', 'pg', 'roles', 'br', 'rl', 'st', 'q') + ['pageTitle' => 'Employees', 'branches' => branch_list()]);
