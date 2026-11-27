<?php
Auth::need('settings.manage');
if (is_post()) {
    try {
        $a = req('action');
        if ($a === 'general') {
            $days = (int) req('expiry_warning_days'); $cur = req('currency');
            if ($days < 1 || $days > 60 || $cur === '' || mb_strlen($cur) > 5) throw new RuntimeException('Currency symbol and a warning period of 1–60 days are required.');
            set_setting('currency', $cur); set_setting('expiry_warning_days', (string) $days);
            set_setting('gym_name', req('gym_name') ?: 'Caato Gym');
            set_setting('gym_phone', req('gym_phone')); set_setting('gym_address', req('gym_address'));
            set_setting('gym_whatsapp', req('gym_whatsapp'));
            audit('settings_update', 'settings', null, 'General settings'); flash('success', 'Settings saved.');
        } elseif ($a === 'smtp') {
            set_setting('smtp_host', req('smtp_host')); set_setting('smtp_port', req('smtp_port') ?: '587');
            set_setting('smtp_from', req('smtp_from')); set_setting('smtp_user', req('smtp_user'));
            if (req('smtp_pass') !== '') set_setting('smtp_pass', req('smtp_pass'));
            audit('settings_update', 'settings', null, 'SMTP settings'); flash('success', 'SMTP settings saved.');
        } elseif ($a === 'smtp_test') {
            $to = req('test_email');
            if (!filter_var($to, FILTER_VALIDATE_EMAIL)) throw new RuntimeException('Enter a valid destination email for the SMTP test.');
            $ok = Mailer::send($to, 'CAATO Admin', 'CAATO OS v4.0 — SMTP Gateway Test', '<h3>CAATO FITNESS GYM</h3><p>Your outbound SMTP gateway is configured and working properly.</p>');
            if ($ok) flash('success', "Test email dispatched to $to.");
            else throw new RuntimeException('SMTP connection failed. Check host, port and credentials in storage/error.log.');
        } elseif ($a === 'method_add') {
            if (req('name') === '') throw new RuntimeException('Enter a payment method name.');
            DB::insert('payment_methods', ['name' => req('name'), 'requires_reference' => req('requires_reference') ? 1 : 0]);
            audit('method_add', 'payment_method', null, req('name')); flash('success', 'Payment method added.');
        } elseif ($a === 'method_toggle') {
            DB::exec("UPDATE payment_methods SET status=IF(status='active','inactive','active') WHERE id=?", [(int) req('id')]); flash('success', 'Payment method updated.');
        }
    } catch (PDOException $e) { flash('error', 'That payment method already exists.'); }
    catch (RuntimeException $e) { flash('error', $e->getMessage()); }
    redirect('settings', ['tab' => req('tab', 'general')]);
}
$tab = in_array(req('tab'), ['methods', 'smtp', 'audit'], true) ? req('tab') : 'general';
$pg = null; $logs = [];
if ($tab === 'audit') {
    $pg = paginate((int) DB::val('SELECT COUNT(*) FROM audit_logs'), 25);
    $logs = DB::all('SELECT l.*, u.username, b.code AS branch_code FROM audit_logs l LEFT JOIN users u ON u.id=l.user_id LEFT JOIN branches b ON b.id=l.branch_id ORDER BY l.id DESC LIMIT 25 OFFSET ' . $pg['offset']);
}
render('settings', ['pageTitle' => 'System Settings', 'tab' => $tab, 'logs' => $logs, 'pg' => $pg, 'methods' => DB::all('SELECT * FROM payment_methods ORDER BY id')]);
