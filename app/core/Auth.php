<?php
final class Auth
{
    public static function boot(): void
    {
        if (self::check()) {
            $max = (int) cfg('session_minutes') * 60;
            if (isset($_SESSION['last']) && time() - $_SESSION['last'] > $max) {
                self::logout();
                flash('warning', 'You were signed out after a period of inactivity.');
                return;
            }
            $_SESSION['last'] = time();
        }
    }
    public static function check(): bool { return !empty($_SESSION['user']); }
    public static function user(): array { return $_SESSION['user'] ?? []; }
    public static function id(): ?int { return isset($_SESSION['user']['id']) ? (int) $_SESSION['user']['id'] : null; }
    public static function role(): string { return $_SESSION['user']['role'] ?? ''; }
    public static function branchId(): ?int { $b = $_SESSION['user']['branch_id'] ?? null; return $b ? (int) $b : null; }
    public static function isSuper(): bool { return self::role() === 'super_admin'; }
    public static function can(string $p): bool { return self::isSuper() || in_array($p, $_SESSION['perms'] ?? [], true); }

    public static function need(string $perm = ''): void
    {
        if (!self::check()) redirect('login');
        if ($perm !== '' && !self::can($perm)) {
            http_response_code(403);
            render('errors/403');
            exit;
        }
    }

    /** Branch used for new records: the user's own branch, or (Super Admin) the posted branch. */
    public static function resolveBranch($posted): int
    {
        if (!self::isSuper()) {
            $b = self::branchId();
            if (!$b) throw new RuntimeException('Your account is not assigned to a branch.');
            return $b;
        }
        $b = (int) $posted;
        if (!$b || !DB::val("SELECT id FROM branches WHERE id=? AND status='active'", [$b]))
            throw new RuntimeException('Please select an active branch.');
        return $b;
    }

    public static function login(string $username, string $password): array
    {
        $u = DB::one("SELECT u.*, r.name AS role_name, b.name AS branch_name, b.status AS branch_status
                      FROM users u JOIN roles r ON r.id=u.role_id LEFT JOIN branches b ON b.id=u.branch_id
                      WHERE u.username=? LIMIT 1", [$username]);
        if (!$u) { password_verify($password, '$2y$10$abcdefghijklmnopqrstuuG3yZ8mW9q4a7nH5Yl3m0b9O1kqj8y6a'); return [false, 'Invalid username or password.']; }
        if ($u['locked_until'] && strtotime($u['locked_until']) > time())
            return [false, 'Too many failed attempts. Try again in a few minutes.'];
        if (!password_verify($password, $u['password_hash'])) {
            $n = (int) $u['failed_attempts'] + 1;
            DB::update('users', ['failed_attempts' => $n >= 5 ? 0 : $n, 'locked_until' => $n >= 5 ? date('Y-m-d H:i:s', time() + 900) : null], 'id=?', [$u['id']]);
            return [false, 'Invalid username or password.'];
        }
        if ($u['status'] !== 'active') return [false, 'This account has been deactivated.'];
        if ($u['role_name'] !== 'super_admin' && (!$u['branch_id'] || $u['branch_status'] !== 'active'))
            return [false, 'Your branch is inactive or not assigned. Contact the Super Admin.'];

        DB::update('users', ['failed_attempts' => 0, 'locked_until' => null, 'last_login' => date('Y-m-d H:i:s')], 'id=?', [$u['id']]);
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id' => (int) $u['id'], 'name' => $u['full_name'], 'username' => $u['username'],
            'role' => $u['role_name'], 'branch_id' => $u['branch_id'], 'branch_name' => $u['branch_name'],
        ];
        $_SESSION['perms'] = array_column(DB::all(
            "SELECT p.perm_key FROM role_permissions rp JOIN permissions p ON p.id=rp.permission_id WHERE rp.role_id=?", [$u['role_id']]), 'perm_key');
        $_SESSION['last'] = time();
        audit('login', 'user', (int) $u['id'], 'Signed in');
        return [true, 'ok'];
    }

    public static function logout(): void
    {
        if (self::check()) audit('logout', 'user', self::id(), 'Signed out');
        $_SESSION = [];
        session_regenerate_id(true);
    }
}
