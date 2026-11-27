<?php
Auth::need('expenses.manage');
if (is_post()) {
    try {
        if (req('action') === 'add') {
            $amt = (float) req('amount');
            if ($amt <= 0 || req('category') === '' || !strtotime(req('expense_date'))) throw new RuntimeException('Category, a positive amount and a date are required.');
            $id = DB::insert('expenses', ['branch_id' => Auth::resolveBranch(req('branch_id')), 'category' => req('category'), 'description' => req('description') ?: null, 'amount' => $amt, 'expense_date' => req('expense_date'), 'created_by' => Auth::id()]);
            audit('expense_add', 'expense', $id, money($amt) . ' ' . req('category')); flash('success', 'Expense recorded.');
        } elseif (req('action') === 'delete') {
            [$sc, $sp] = scope_branch('branch_id');
            DB::exec('DELETE FROM expenses WHERE id=?' . $sc, array_merge([(int) req('id')], $sp));
            audit('expense_delete', 'expense', (int) req('id')); flash('success', 'Expense deleted.');
        }
    } catch (RuntimeException $e) { flash('error', $e->getMessage()); }
    redirect('expenses');
}
$br = (int) req('branch'); $df = req('date_from', date('Y-m-01')); $dt = req('date_to', date('Y-m-d'));
[$sc, $sp] = scope_branch('x.branch_id', $br); $w = " WHERE x.expense_date BETWEEN ? AND ? $sc"; $p = array_merge([$df, $dt], $sp);
$total = (float) DB::val('SELECT COALESCE(SUM(x.amount),0) FROM expenses x' . $w, $p);
$catBreakdown = DB::all('SELECT x.category, SUM(x.amount) AS total, COUNT(*) AS cnt FROM expenses x' . $w . ' GROUP BY x.category ORDER BY total DESC LIMIT 6', $p);
$pg = paginate((int) DB::val('SELECT COUNT(*) FROM expenses x' . $w, $p), 15);
$rows = DB::all('SELECT x.*, b.name AS branch_name, u.full_name AS by_name FROM expenses x JOIN branches b ON b.id=x.branch_id LEFT JOIN users u ON u.id=x.created_by' . $w . ' ORDER BY x.expense_date DESC, x.id DESC LIMIT 15 OFFSET ' . $pg['offset'], $p);
render('expenses', ['pageTitle' => 'Operating Expenses', 'rows' => $rows, 'pg' => $pg, 'total' => $total, 'catBreakdown' => $catBreakdown, 'df' => $df, 'dt' => $dt, 'br' => $br, 'branches' => branch_list()]);
