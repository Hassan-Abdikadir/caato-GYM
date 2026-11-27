<?php
header('Cache-Control: no-store');
$a = req('action');
try {
    if ($a === 'search') {
        if (!Auth::can('attendance.view') && !Auth::can('customers.view')) {
            json_out(['ok' => false, 'message' => 'Not allowed.'], 403);
        }
        $q = req('q');
        if (mb_strlen($q) < 2) json_out(['ok' => true, 'results' => []]);
        [$rows] = Customer::search($q, 0, '', 8, 0);   // searches shared database of all 6 branches
        $out = [];
        foreach ($rows as $r) {
            [$ok, $code, $msg, $lvl] = Membership::access($r);
            $out[] = [
                'id' => (int) $r['id'],
                'code' => $r['customer_code'],
                'name' => $r['full_name'],
                'phone' => $r['phone'],
                'photo' => photo_url($r['profile_picture']),
                'fingerprint_id' => $r['fingerprint_id'] ?? null,
                'reg_branch' => short_branch($r['reg_branch']),
                'plan' => $r['plan_name'] ?: '—',
                'payment' => $r['payment_status'] ?: '—',
                'start' => fmt_date($r['start_date']),
                'end' => fmt_date($r['end_date']),
                'status' => $r['eff_status'],
                'last' => fmt_dt($r['last_attendance']),
                'allowed' => $ok,
                'level' => $lvl,
                'message' => $msg,
                'profile' => url('customer_view', ['id' => $r['id']])
            ];
        }
        json_out(['ok' => true, 'results' => $out]);
    }

    if ($a === 'checkin' && is_post()) {
        if (!Auth::can('attendance.checkin')) {
            json_out(['ok' => false, 'message' => 'You are not allowed to check customers in.', 'level' => 'danger'], 403);
        }
        $branch = Auth::resolveBranch(req('branch_id'));
        $method = req('method', 'qr_scan');
        $res = Attendance::checkIn((int) req('customer_id'), $branch, $method);
        json_out($res);
    }

    // Door Fingerprint Reader Endpoint
    if ($a === 'door_fingerprint' && is_post()) {
        if (!Auth::can('attendance.checkin')) {
            json_out(['ok' => false, 'door_status' => 'locked', 'message' => 'Biometric door terminal access denied.', 'level' => 'danger'], 403);
        }
        $token = trim((string) req('fingerprint_token', req('code', req('token', ''))));
        $customerId = (int) req('customer_id');
        $branch = Auth::resolveBranch(req('branch_id'));

        $c = null;
        if ($customerId > 0) {
            $c = Customer::find($customerId);
        } elseif ($token !== '') {
            $c = Customer::findByFingerprint($token);
        }

        if (!$c) {
            json_out([
                'ok' => false,
                'door_status' => 'locked',
                'code' => 'not_found',
                'message' => 'FARTA LAMA AQOONSAN (Fingerprint Not Recognized). Please check enrollment or use member card.',
                'level' => 'danger',
                'timestamp' => date('h:i:s A, d M Y')
            ], 200);
        }

        $res = Attendance::checkIn((int) $c['id'], $branch, 'fingerprint_door');
        json_out($res);
    }

    // Enroll Customer Fingerprint Token
    if ($a === 'enroll_fingerprint' && is_post()) {
        if (!Auth::can('customers.edit')) {
            json_out(['ok' => false, 'message' => 'Permission denied.'], 403);
        }
        $cid = (int) req('customer_id');
        $fp = trim(req('fingerprint_id'));
        if (!$cid || $fp === '') {
            json_out(['ok' => false, 'message' => 'Customer ID and Fingerprint identifier are required.'], 422);
        }
        // Check uniqueness
        $dup = DB::one('SELECT id, full_name, customer_code FROM customers WHERE fingerprint_id=? AND id!=?', [$fp, $cid]);
        if ($dup) {
            json_out(['ok' => false, 'message' => "Fingerprint token already assigned to {$dup['full_name']} ({$dup['customer_code']})."], 422);
        }
        DB::update('customers', ['fingerprint_id' => $fp, 'fingerprint_enrolled' => 1], 'id=?', [$cid]);
        audit('fingerprint_enroll', 'customer', $cid, "Enrolled door fingerprint $fp");
        json_out(['ok' => true, 'message' => "Fingerprint successfully enrolled for door access ($fp)."]);
    }

    // Live Door Log
    if ($a === 'latest_door_log') {
        if (!Auth::can('attendance.view')) {
            json_out(['ok' => false, 'message' => 'Not allowed.'], 403);
        }
        $branch = req('branch_id') ? (int) req('branch_id') : (Auth::isSuper() ? null : Auth::branchId());
        $sql = "SELECT a.id, a.check_in_at, a.checkin_method, c.id AS cid, c.customer_code, c.full_name, c.profile_picture, b.name AS visit_branch, rb.name AS reg_branch
                FROM attendance a
                JOIN customers c ON c.id=a.customer_id
                JOIN branches b ON b.id=a.branch_id
                JOIN branches rb ON rb.id=c.registered_branch_id
                WHERE a.attendance_date=CURDATE()" . ($branch ? " AND a.branch_id=$branch" : "") . "
                ORDER BY a.check_in_at DESC LIMIT 15";
        $rows = DB::all($sql);
        $logs = [];
        foreach ($rows as $r) {
            $logs[] = [
                'id' => $r['id'],
                'cid' => $r['cid'],
                'name' => $r['full_name'],
                'code' => $r['customer_code'],
                'photo' => photo_url($r['profile_picture']),
                'time' => date('h:i:s A', strtotime($r['check_in_at'])),
                'datetime' => fmt_dt($r['check_in_at']),
                'method' => $r['checkin_method'] ?: 'fingerprint_door',
                'branch' => short_branch($r['visit_branch']),
                'is_visiting' => $r['visit_branch'] !== $r['reg_branch']
            ];
        }
        json_out(['ok' => true, 'logs' => $logs]);
    }

    // Self-Service Password Change for Any Logged-in Staff
    if ($a === 'change_password' && is_post()) {
        if (!Auth::check()) json_out(['ok' => false, 'message' => 'Not signed in.'], 401);
        $old = (string) ($_POST['old_password'] ?? '');
        $new = (string) ($_POST['new_password'] ?? '');
        if (strlen($new) < 8) json_out(['ok' => false, 'message' => 'New password must be at least 8 characters.'], 422);
        $u = DB::one('SELECT id, password_hash FROM users WHERE id=?', [Auth::id()]);
        if (!$u || !password_verify($old, $u['password_hash'])) {
            json_out(['ok' => false, 'message' => 'Current password is incorrect.'], 422);
        }
        DB::update('users', ['password_hash' => password_hash($new, PASSWORD_DEFAULT)], 'id=?', [Auth::id()]);
        audit('password_change', 'user', Auth::id(), 'Self password update');
        json_out(['ok' => true, 'message' => 'Password updated successfully!']);
    }

    json_out(['ok' => false, 'message' => 'Unknown API action.'], 400);
} catch (RuntimeException $e) {
    json_out(['ok' => false, 'message' => $e->getMessage(), 'level' => 'danger'], 422);
}
