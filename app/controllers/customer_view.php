<?php
Auth::need('customers.view');
$id = (int) req('id');
$c = Customer::find($id);
if (!$c) { flash('error', 'Customer not found.'); redirect('customers'); }

if (is_post()) {
    try {
        $act = req('action');
        if ($act === 'add_metric') {
            $w = (float) req('weight_kg');
            $h = (float) req('height_cm');
            if ($w < 20 || $w > 350) throw new RuntimeException('Enter a valid weight in kg (20–350).');
            if (!$h) {
                $h = (float) DB::val('SELECT height_cm FROM body_metrics WHERE customer_id=? AND height_cm IS NOT NULL ORDER BY id DESC LIMIT 1', [$id]);
            }
            $bmi = ($h > 50) ? round($w / (($h / 100) * ($h / 100)), 1) : null;
            DB::insert('body_metrics', [
                'customer_id' => $id,
                'recorded_date' => req('recorded_date') ?: date('Y-m-d'),
                'weight_kg' => $w,
                'height_cm' => $h > 50 ? $h : null,
                'body_fat_pct' => req('body_fat_pct') !== '' ? (float) req('body_fat_pct') : null,
                'muscle_mass_kg' => req('muscle_mass_kg') !== '' ? (float) req('muscle_mass_kg') : null,
                'waist_cm' => req('waist_cm') !== '' ? (float) req('waist_cm') : null,
                'chest_cm' => req('chest_cm') !== '' ? (float) req('chest_cm') : null,
                'bmi' => $bmi,
                'notes' => req('notes') ?: null,
                'recorded_by' => Auth::id(),
            ]);
            audit('body_metric_add', 'customer', $id, "{$w}kg" . ($bmi ? " (BMI $bmi)" : ''));
            flash('success', 'Body measurement & BMI logged.');
        } elseif ($act === 'del_metric') {
            $mid = (int) req('metric_id');
            DB::exec('DELETE FROM body_metrics WHERE id=? AND customer_id=?', [$mid, $id]);
            flash('success', 'Measurement entry removed.');
        } elseif ($act === 'update_coaching') {
            $goal = trim(req('fitness_goal')) ?: 'General Fitness';
            $trId = (int) req('assigned_trainer_id') ?: null;
            $med  = trim(req('medical_notes')) ?: null;
            DB::update('customers', [
                'fitness_goal' => $goal,
                'assigned_trainer_id' => $trId,
                'medical_notes' => $med,
            ], 'id=?', [$id]);
            audit('coaching_update', 'customer', $id, "Goal: $goal");
            flash('success', 'Athlete fitness goal, personal coach & training notes updated.');
        }
    } catch (RuntimeException $e) {
        flash('error', $e->getMessage());
    }
    redirect('customer_view', ['id' => $id]);
}

$metrics = [];
try {
    $metrics = DB::all('SELECT * FROM body_metrics WHERE customer_id=? ORDER BY recorded_date DESC, id DESC LIMIT 24', [$id]);
} catch (Throwable $e) {}

$brScope = !Auth::isSuper() && Auth::branchId() ? ' AND branch_id=' . (int) Auth::branchId() : '';
render('customer_view', [
    'pageTitle' => $c['full_name'],
    'c' => $c,
    'access' => Membership::access($c),
    'metrics' => $metrics,
    'trainers' => DB::all("SELECT id, full_name, specialization FROM trainers WHERE status='active'$brScope ORDER BY full_name"),
    'history' => DB::all('SELECT m.*, pl.name AS plan_name, b.name AS branch_name FROM memberships m JOIN membership_plans pl ON pl.id=m.plan_id JOIN branches b ON b.id=m.branch_id WHERE m.customer_id=? ORDER BY m.id DESC', [$id]),
    'pays' => DB::all('SELECT p.*, pm.name AS method, b.name AS branch FROM payments p JOIN payment_methods pm ON pm.id=p.method_id JOIN branches b ON b.id=p.branch_id WHERE p.customer_id=? ORDER BY p.id DESC LIMIT 50', [$id]),
    'att' => DB::all('SELECT a.check_in_at, a.checkin_method, b.name AS branch FROM attendance a JOIN branches b ON b.id=a.branch_id WHERE a.customer_id=? ORDER BY a.id DESC LIMIT 30', [$id]),
    'plans' => DB::all("SELECT * FROM membership_plans WHERE status='active' ORDER BY duration_days"),
    'methods' => DB::all("SELECT * FROM payment_methods WHERE status='active' ORDER BY id"),
]);
