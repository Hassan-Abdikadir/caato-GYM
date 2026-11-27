<?php
Auth::need('reports.view');
$f = ['type' => req('type', 'customers'), 'branch' => (int) req('branch'), 'date_from' => req('date_from'), 'date_to' => req('date_to'), 'plan' => (int) req('plan'), 'pstatus' => req('pstatus')];
[$title, $cols, $rows] = Report::run($f['type'], $f);
$base = preg_replace('/[^a-z0-9_]/i', '', $f['type']) . '_' . date('Ymd_His');
audit('report_export', 'report', null, $title . ' (' . req('format') . ')');
if (req('format') === 'xlsx') Xlsx::download($base . '.xlsx', $title, $cols, $rows);
$bn = $f['branch'] ? (string) DB::val('SELECT name FROM branches WHERE id=?', [$f['branch']]) : (Auth::isSuper() ? 'All branches' : Auth::user()['branch_name']);
$sub = $bn . ($f['date_from'] || $f['date_to'] ? '  |  ' . ($f['date_from'] ?: '...') . ' to ' . ($f['date_to'] ?: '...') : '');
$pdf = new Pdf(count($cols) > 5);
$pdf->header($title, $sub);
$pdf->table($cols, $rows, count($cols) > 8 ? 7 : 8);
$pdf->output($base . '.pdf');
