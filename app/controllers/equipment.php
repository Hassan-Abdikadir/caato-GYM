<?php
Auth::need('equipment.view');
$canManage = Auth::can('equipment.manage');

if (is_post()) {
    Auth::need('equipment.manage');
    try {
        $a = req('action');
        $id = (int) req('id');
        if ($a === 'save') {
            $name = req('name');
            $branch = Auth::resolveBranch(req('branch_id'));
            if ($name === '') throw new RuntimeException('Equipment name is required.');
            $d = [
                'name' => $name,
                'category' => req('category', 'Strength Machines'),
                'branch_id' => $branch,
                'quantity' => max(1, (int) req('quantity', 1)),
                'condition_status' => in_array(req('condition_status'), ['operational', 'maintenance', 'out_of_order'], true) ? req('condition_status') : 'operational',
                'last_service_date' => req('last_service_date') ?: null,
                'next_service_date' => req('next_service_date') ?: null,
                'notes' => req('notes') ?: null,
            ];
            if ($id) {
                DB::update('equipment', $d, 'id=?', [$id]);
            } else {
                $d['code'] = 'EQ-' . strtoupper(bin2hex(random_bytes(3)));
                $id = DB::insert('equipment', $d);
                DB::update('equipment', ['code' => 'EQ-' . str_pad((string) $id, 4, '0', STR_PAD_LEFT)], 'id=?', [$id]);
            }
            audit('equipment_save', 'equipment', $id, $name);
            flash('success', 'Equipment asset saved.');
        } elseif ($a === 'delete' && $id) {
            [$sc, $sp] = scope_branch('branch_id');
            DB::exec('DELETE FROM equipment WHERE id=?' . $sc, array_merge([$id], $sp));
            audit('equipment_delete', 'equipment', $id);
            flash('success', 'Equipment record deleted.');
        }
    } catch (RuntimeException $e) {
        flash('error', $e->getMessage());
    }
    redirect('equipment');
}

$br = (int) req('branch');
$st = req('status');
[$sc, $sp] = scope_branch('e.branch_id', $br);
$w = " WHERE 1=1 $sc";
$p = $sp;
if (in_array($st, ['operational', 'maintenance', 'out_of_order'], true)) {
    $w .= ' AND e.condition_status=?';
    $p[] = $st;
}
$rows = DB::all("SELECT e.*, b.name AS branch_name FROM equipment e JOIN branches b ON b.id=e.branch_id $w ORDER BY e.condition_status DESC, e.id DESC", $p);

render('equipment', [
    'pageTitle' => 'Gym Equipment & Maintenance',
    'rows' => $rows,
    'branches' => branch_list(),
    'br' => $br,
    'st' => $st,
    'canManage' => $canManage,
]);
