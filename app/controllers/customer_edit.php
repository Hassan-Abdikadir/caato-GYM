<?php
Auth::need('customers.edit');
$id = (int) req('id'); $c = Customer::find($id);
if (!$c) redirect('customers');
if (is_post()) {
    $photo = null;
    try {
        $phone = req('phone'); $norm = normalize_phone($phone);
        if (mb_strlen(req('full_name')) < 3) throw new RuntimeException('Enter the full name.');
        if (strlen($norm) < 7 || strlen($norm) > 15) throw new RuntimeException('Enter a valid phone number.');
        if (DB::val('SELECT id FROM customers WHERE phone_norm=? AND id<>?', [$norm, $id])) throw new RuntimeException('Another customer already uses this phone number.');
        if (!in_array(req('gender'), ['male', 'female'], true)) throw new RuntimeException('Select the gender.');
        if (req('email') !== '' && !filter_var(req('email'), FILTER_VALIDATE_EMAIL)) throw new RuntimeException('Email address is not valid.');
        $d = [
            'full_name' => req('full_name'), 'phone' => $phone, 'phone_norm' => $norm,
            'email' => req('email') ?: null, 'gender' => req('gender'),
            'date_of_birth' => req('dob') ?: null, 'address' => req('address') ?: null,
            'emergency_name' => req('emergency_name') ?: null, 'emergency_phone' => req('emergency_phone') ?: null,
            'fitness_goal' => req('fitness_goal', 'General Fitness'),
            'medical_notes' => req('medical_notes') ?: null,
            'assigned_trainer_id' => (int) req('assigned_trainer_id') ?: null,
        ];
        $fp = trim(req('fingerprint_id', ''));
        if ($fp !== '') {
            $dup = DB::one('SELECT id, full_name, customer_code FROM customers WHERE fingerprint_id=? AND id<>?', [$fp, $id]);
            if ($dup) throw new RuntimeException("This fingerprint ID is already assigned to {$dup['full_name']} ({$dup['customer_code']}).");
            $d['fingerprint_id'] = $fp;
            $d['fingerprint_enrolled'] = 1;
        } else {
            $d['fingerprint_id'] = null;
            $d['fingerprint_enrolled'] = 0;
        }
        if (Auth::can('memberships.manage') && in_array(req('status'), ['active', 'blocked'], true)) $d['status'] = req('status');
        $photo = save_photo($_FILES['photo'] ?? []);
        if (!$photo) $photo = save_photo_base64(req('photo_base64'));
        if ($photo) $d['profile_picture'] = $photo;
        DB::update('customers', $d, 'id=?', [$id]);
        if ($photo && $c['profile_picture']) @unlink(ROOT . '/uploads/profiles/' . basename($c['profile_picture']));
        audit('customer_edit', 'customer', $id, $c['customer_code']);
        flash('success', 'Customer profile updated.');
        redirect('customer_view', ['id' => $id]);
    } catch (RuntimeException $e) { if ($photo) @unlink(ROOT . '/uploads/profiles/' . $photo); flash('error', $e->getMessage()); }
}
render('customer_edit', ['pageTitle' => 'Edit Customer', 'c' => $c, 'v' => $_POST ?: $c]);
