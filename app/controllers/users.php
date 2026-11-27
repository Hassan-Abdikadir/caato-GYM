<?php
Auth::need('users.manage');
$roles = DB::all('SELECT id, name, label FROM roles ORDER BY id');
if (is_post()) {
    try {
        if (req('action') === 'delete') {
            $id = (int) req('id');
            if ($id === Auth::id()) throw new RuntimeException('You cannot delete your own account.');
            $u = DB::one('SELECT full_name, username FROM users WHERE id=?', [$id]);
            if (!$u) throw new RuntimeException('User not found.');
            DB::exec('UPDATE employees SET user_id=NULL WHERE user_id=?', [$id]);
            DB::exec('DELETE FROM users WHERE id=?', [$id]);
            audit('user_delete', 'user', $id, $u['username']); flash('success', "User {$u['username']} deleted."); redirect('users');
        }
        $id = (int) req('id'); $un = strtolower(req('username')); $pw = (string) ($_POST['password'] ?? ''); $roleId = (int) req('role_id');
        $role = DB::val('SELECT name FROM roles WHERE id=?', [$roleId]);
        if (!$role) throw new RuntimeException('Select a role.');
        if (mb_strlen(req('full_name')) < 3) throw new RuntimeException('Enter the full name.');
        if (!preg_match('/^[a-z0-9._-]{3,50}$/', $un)) throw new RuntimeException('Username must be 3–50 letters, numbers, dot, dash or underscore.');
        if (req('email') !== '' && !filter_var(req('email'), FILTER_VALIDATE_EMAIL)) throw new RuntimeException('Email address is not valid.');
        $branch = $role === 'super_admin' ? null : (int) req('branch_id');
        if ($role !== 'super_admin' && !DB::val("SELECT id FROM branches WHERE id=?", [$branch])) throw new RuntimeException('Staff accounts must be assigned to a branch.');
        $status = req('status') === 'inactive' ? 'inactive' : 'active';
        if ($id === Auth::id() && ($status === 'inactive' || $role !== 'super_admin')) throw new RuntimeException('You cannot deactivate or demote your own account.');
        $d = ['full_name' => req('full_name'), 'username' => $un, 'email' => req('email') ?: null, 'role_id' => $roleId, 'branch_id' => $branch, 'status' => $status];
        if ($pw !== '') { if (strlen($pw) < 8) throw new RuntimeException('Password must be at least 8 characters.'); $d['password_hash'] = password_hash($pw, PASSWORD_DEFAULT); $d['failed_attempts'] = 0; $d['locked_until'] = null; }
        if ($id) DB::update('users', $d, 'id=?', [$id]);
        else { if ($pw === '') throw new RuntimeException('Set a password for the new user.'); $id = DB::insert('users', $d); }
        audit('user_save', 'user', $id, "$un ($role)"); flash('success', 'User saved.');
    } catch (PDOException $e) { flash('error', 'That username or email is already in use.'); }
    catch (RuntimeException $e) { flash('error', $e->getMessage()); }
    redirect('users');
}
$rows = DB::all('SELECT u.*, r.label AS role_label, r.name AS role_name, b.name AS branch_name FROM users u JOIN roles r ON r.id=u.role_id LEFT JOIN branches b ON b.id=u.branch_id ORDER BY u.role_id, u.full_name');
render('users', ['pageTitle' => 'User Management', 'rows' => $rows, 'roles' => $roles, 'branches' => DB::all('SELECT id, name FROM branches ORDER BY id')]);
