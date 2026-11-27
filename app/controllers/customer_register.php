<?php
Auth::need('customers.create');
$plans = DB::all("SELECT * FROM membership_plans WHERE status='active' ORDER BY duration_days");
$methods = DB::all("SELECT * FROM payment_methods WHERE status='active' ORDER BY id");
$v = [];
if (is_post()) {
    $v = $_POST; $photo = null;
    try {
        $name = req('full_name'); $phone = req('phone'); $email = req('email'); $dob = req('dob');
        if (mb_strlen($name) < 3) throw new RuntimeException('Enter the customer\'s full name.');
        $digits = normalize_phone($phone);
        if (strlen($digits) < 7 || strlen($digits) > 15) throw new RuntimeException('Enter a valid phone number (7–15 digits).');
        if (!in_array(req('gender'), ['male', 'female'], true)) throw new RuntimeException('Select the gender.');
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) throw new RuntimeException('Email address is not valid.');
        if ($dob !== '' && (!strtotime($dob) || $dob > date('Y-m-d'))) throw new RuntimeException('Date of birth is not valid.');
        $branch = Auth::resolveBranch(req('branch_id'));
        $startDate = req('start_date');
        if ($startDate !== '') {
            if (!strtotime($startDate)) throw new RuntimeException('Enter a valid membership start date.');
            if ($startDate < date('Y-m-d')) throw new RuntimeException('Membership start date cannot be in the past. Taariikhdii hore ee la soo dhaafey lama ogola (Please select today or a future date).');
        }
        $planId = (int) req('plan_id'); $amount = (float) req('amount'); $methodId = (int) req('method_id');
        $fp = trim(req('fingerprint_id', ''));
        if ($fp !== '') {
            $dupFp = DB::one('SELECT id, full_name, customer_code FROM customers WHERE fingerprint_id=?', [$fp]);
            if ($dupFp) throw new RuntimeException("Fingerprint token $fp is already enrolled for {$dupFp['full_name']} ({$dupFp['customer_code']}).");
        }
        if ($amount > 0 && !$planId) throw new RuntimeException('Choose a membership plan before entering a payment.');
        if ($amount > 0 && !$methodId) throw new RuntimeException('Choose the payment method.');
        if ($amount > 0 && !req('verified')) throw new RuntimeException('Tick the box to confirm the payment has been received and verified.');
        $photo = save_photo($_FILES['photo'] ?? []);
        if (!$photo) $photo = save_photo_base64(req('photo_base64'));
        $id = DB::tx(function () use ($name, $phone, $email, $dob, $branch, $planId, $amount, $methodId, $photo, $startDate, $fp) {
            $cid = Customer::create([
                'full_name' => $name, 'phone' => $phone, 'email' => $email, 'gender' => req('gender'), 'dob' => $dob,
                'address' => req('address'), 'emergency_name' => req('emergency_name'), 'emergency_phone' => req('emergency_phone'),
                'fitness_goal' => req('fitness_goal', 'General Fitness'),
                'medical_notes' => req('medical_notes'),
                'assigned_trainer_id' => (int) req('assigned_trainer_id') ?: null,
                'branch_id' => $branch, 'photo' => $photo
            ]);
            if ($fp !== '') {
                DB::update('customers', ['fingerprint_id' => $fp, 'fingerprint_enrolled' => 1], 'id=?', [$cid]);
            }
            // Optional initial weight & height at registration
            $wKg = (float) req('init_weight_kg');
            $hCm = (float) req('init_height_cm');
            if ($wKg > 20 && $wKg < 350) {
                $bmi = ($hCm > 50) ? round($wKg / (($hCm / 100) * ($hCm / 100)), 1) : null;
                DB::insert('body_metrics', [
                    'customer_id' => $cid, 'recorded_date' => date('Y-m-d'), 'weight_kg' => $wKg,
                    'height_cm' => $hCm > 50 ? $hCm : null, 'bmi' => $bmi,
                    'notes' => 'Baseline measurement at registration', 'recorded_by' => Auth::id()
                ]);
            }
            if ($planId) {
                $mid = Membership::create($cid, $planId, $branch, $startDate ?: null);
                if ($amount > 0) Payment::record($mid, $amount, $methodId, $branch, req('reference'), 'Payment at registration');
            }
            return $cid;
        });
        audit('customer_register', 'customer', $id, "$name registered at branch #$branch");
        if ($email) {
            $ms = $planId ? DB::one('SELECT p.name AS plan_name, m.start_date, m.end_date FROM memberships m JOIN membership_plans p ON p.id=m.plan_id WHERE m.customer_id=? ORDER BY m.id DESC LIMIT 1', [$id]) : null;
            send_welcome_message($email, $name, $ms['plan_name'] ?? null, $ms['start_date'] ?? null, $ms['end_date'] ?? null);
        }
        flash('success', 'Customer registered. The record is now synchronized across all 6 branches.');
        redirect('customer_view', ['id' => $id]);
    } catch (RuntimeException $e) {
        if ($photo) @unlink(ROOT . '/uploads/profiles/' . $photo);
        flash('error', $e->getMessage());
    }
}
render('customer_register', ['pageTitle' => 'Register Customer', 'plans' => $plans, 'methods' => $methods, 'v' => $v]);
