<?php
Auth::need('reports.view');
$f = ['type' => req('type', 'customers'), 'branch' => (int) req('branch'), 'date_from' => req('date_from'), 'date_to' => req('date_to'), 'plan' => (int) req('plan'), 'pstatus' => req('pstatus')];
$run = isset($_GET['run']) || isset($_GET['type']);
$result = null;
if ($run) { $result = Report::run($f['type'], $f); }
render('reports', ['pageTitle' => 'Reports & Analytics', 'f' => $f, 'result' => $result, 'plans' => DB::all('SELECT id, name FROM membership_plans ORDER BY duration_days'), 'qs' => http_build_query(['type' => $f['type'], 'branch' => $f['branch'], 'date_from' => $f['date_from'], 'date_to' => $f['date_to'], 'plan' => $f['plan'], 'pstatus' => $f['pstatus']])]);
