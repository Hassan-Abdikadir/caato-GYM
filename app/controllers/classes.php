<?php
Auth::need('trainers.view');
$canManage = Auth::can('trainers.manage');

if (is_post()) {
    Auth::need('trainers.manage');
    try {
        $a = req('action');
        $id = (int) req('id');
        if ($a === 'save') {
            $title = req('title');
            $branch = Auth::resolveBranch(req('branch_id'));
            if ($title === '' || !req('start_time') || !req('end_time')) throw new RuntimeException('Class title, start time and end time are required.');
            $d = [
                'title' => $title,
                'category' => req('category', 'HIIT & Cardio'),
                'trainer_id' => (int) req('trainer_id') ?: null,
                'branch_id' => $branch,
                'day_of_week' => req('day_of_week', 'Mon, Wed, Fri'),
                'start_time' => req('start_time'),
                'end_time' => req('end_time'),
                'capacity' => max(1, (int) req('capacity', 25)),
                'room' => req('room', 'Main Studio'),
                'status' => req('status') === 'cancelled' ? 'cancelled' : 'active',
            ];
            if ($id) DB::update('gym_classes', $d, 'id=?', [$id]);
            else $id = DB::insert('gym_classes', $d);
            audit('class_save', 'gym_class', $id, $title);
            flash('success', 'Workout class schedule saved.');
        } elseif ($a === 'delete' && $id) {
            [$sc, $sp] = scope_branch('branch_id');
            DB::exec('DELETE FROM gym_classes WHERE id=?' . $sc, array_merge([$id], $sp));
            audit('class_delete', 'gym_class', $id);
            flash('success', 'Workout class removed.');
        }
    } catch (RuntimeException $e) {
        flash('error', $e->getMessage());
    }
    redirect('classes');
}

$br = (int) req('branch');
[$sc, $sp] = scope_branch('c.branch_id', $br);
$rows = DB::all("SELECT c.*, b.name AS branch_name, t.full_name AS trainer_name
                 FROM gym_classes c JOIN branches b ON b.id=c.branch_id
                 LEFT JOIN trainers t ON t.id=c.trainer_id
                 WHERE 1=1 $sc ORDER BY c.status, c.start_time", $sp);
$trainers = DB::all("SELECT id, full_name FROM trainers WHERE status='active' ORDER BY full_name");

render('classes', [
    'pageTitle' => 'Group Classes & Schedules',
    'rows' => $rows,
    'trainers' => $trainers,
    'branches' => branch_list(),
    'br' => $br,
    'canManage' => $canManage,
]);
