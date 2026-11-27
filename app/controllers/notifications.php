<?php
[$sc, $sp] = Auth::isSuper() ? ['', []] : [' AND (branch_id IS NULL OR branch_id=?)', [Auth::branchId() ?? 0]];
if (is_post()) {
    if (req('action') === 'clear_read') {
        DB::exec('DELETE FROM notifications WHERE is_read=1' . $sc, $sp);
        flash('success', 'All read notifications cleared.');
    } else {
        DB::exec('UPDATE notifications SET is_read=1 WHERE is_read=0' . $sc, $sp);
        flash('success', 'All notifications marked as read.');
    }
    redirect('notifications');
}
$type = req('type');
$w = ' WHERE 1=1' . $sc; $p = $sp;
if ($type !== '') { $w .= ' AND type=?'; $p[] = $type; }
$pg = paginate((int) DB::val('SELECT COUNT(*) FROM notifications' . $w, $p), 20);
$rows = DB::all('SELECT * FROM notifications' . $w . ' ORDER BY id DESC LIMIT 20 OFFSET ' . $pg['offset'], $p);
render('notifications', ['pageTitle' => 'Notification Hub', 'rows' => $rows, 'pg' => $pg, 'type' => $type]);
