<?php
Auth::need('trainers.view');
if (is_post()) {
    Auth::need('trainers.manage');
    try {
        $a = req('action'); $id = (int) req('id');
        $cur = $id ? DB::one('SELECT * FROM trainers WHERE id=?', [$id]) : null;
        if ($cur && !Auth::isSuper() && (int) $cur['branch_id'] !== (int) Auth::branchId()) throw new RuntimeException('You can only manage trainers of your own branch.');
        if ($a === 'toggle' && $cur) { DB::exec("UPDATE trainers SET status=IF(status='active','inactive','active') WHERE id=?", [$id]); audit('trainer_toggle', 'trainer', $id); flash('success', 'Trainer status changed.'); }
        elseif ($a === 'delete' && $cur) {
            if (!Auth::isSuper() && (int)$cur['branch_id'] !== (int)Auth::branchId()) throw new RuntimeException('You can only delete trainers of your own branch.');
            DB::exec('DELETE FROM trainers WHERE id=?', [$id]);
            audit('trainer_delete', 'trainer', $id, $cur['full_name']); flash('success', 'Trainer permanently deleted.');
        }
        elseif ($a === 'save') {
            if (mb_strlen(req('full_name')) < 3 || req('phone') === '') throw new RuntimeException('Trainer name and phone are required.');
            $d = ['full_name' => req('full_name'), 'phone' => req('phone'), 'specialization' => req('specialization') ?: null, 'branch_id' => Auth::resolveBranch(req('branch_id'))];
            if ($id) DB::update('trainers', $d, 'id=?', [$id]); else $id = DB::insert('trainers', $d);
            audit('trainer_save', 'trainer', $id, $d['full_name']); flash('success', 'Trainer saved.');
        }
    } catch (RuntimeException $e) { flash('error', $e->getMessage()); }
    redirect('trainers');
}
[$sc, $sp] = scope_branch('t.branch_id', (int) req('branch'));
$rows = DB::all("SELECT t.*, b.name AS branch_name FROM trainers t JOIN branches b ON b.id=t.branch_id WHERE 1=1 $sc ORDER BY b.id, t.full_name", $sp);
render('trainers', ['pageTitle' => 'Trainers', 'rows' => $rows, 'branches' => branch_list(), 'canManage' => Auth::can('trainers.manage')]);
