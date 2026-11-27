<?php
Auth::need('attendance.view');
if (is_post()) {
    try {
        if (req('action') === 'delete_log') {
            Auth::need('attendance.checkin');
            $id = (int) req('id');
            DB::exec('DELETE FROM attendance WHERE id=?', [$id]);
            audit('attendance_delete', 'attendance', $id); flash('success', 'Attendance log deleted.');
            redirect('attendance');
        }
    } catch (RuntimeException $e) { flash('error', $e->getMessage()); redirect('attendance'); }
}
$br = (int) req('branch'); $q = req('hq');
$df = req('date_from', date('Y-m-d')); $dt = req('date_to', date('Y-m-d'));
[$sc, $sp] = scope_branch('a.branch_id', $br);
$w = " WHERE a.attendance_date BETWEEN ? AND ? $sc"; $p = array_merge([$df, $dt], $sp);
if ($q !== '') { $w .= ' AND (c.full_name LIKE ? OR c.customer_code LIKE ? OR c.fingerprint_id LIKE ?)'; array_push($p, "%$q%", "%$q%", "%$q%"); }
$from = ' FROM attendance a JOIN customers c ON c.id=a.customer_id JOIN branches b ON b.id=a.branch_id JOIN branches rb ON rb.id=c.registered_branch_id';
$pg = paginate((int) DB::val('SELECT COUNT(*)' . $from . $w, $p), 15);
$rows = DB::all('SELECT a.id, a.check_in_at, a.checkin_method, c.id AS cid, c.full_name, c.customer_code, c.profile_picture, c.fingerprint_id, b.name AS visit_branch, rb.name AS reg_branch, a.branch_id, c.registered_branch_id' . $from . $w . ' ORDER BY a.id DESC LIMIT 15 OFFSET ' . $pg['offset'], $p);
render('attendance', ['pageTitle' => 'Biometric & QR Check-in Console', 'rows' => $rows, 'pg' => $pg, 'stats' => Report::branchStats(), 'df' => $df, 'dt' => $dt, 'br' => $br, 'hq' => $q,
    'canCheckin' => Auth::can('attendance.checkin'), 'branches' => branch_list(), 'initialQ' => req('q')]);
