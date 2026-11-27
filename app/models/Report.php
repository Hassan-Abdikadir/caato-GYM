<?php
final class Report
{
    const TYPES = [
        'customers' => 'All Customers', 'active_members' => 'Active Members', 'expired' => 'Expired Memberships',
        'attendance' => 'Customer Attendance', 'daily_payments' => 'Daily Payments', 'monthly_revenue' => 'Monthly Revenue',
        'annual_revenue' => 'Annual Revenue', 'branch_performance' => 'Branch Performance', 'employees' => 'Employee Records',
        'renewals' => 'Membership Renewals', 'pos_sales' => 'POS & Supplement Bar Sales', 'equipment' => 'Gym Equipment Inventory',
    ];

    public static function branchStats(): array
    {
        [$sc, $sp] = scope_branch('b.id');
        return DB::all("SELECT b.id, b.code, b.name, b.status,
            (SELECT u.full_name FROM users u JOIN roles r ON r.id=u.role_id WHERE u.branch_id=b.id AND r.name='branch_manager' AND u.status='active' LIMIT 1) AS manager,
            (SELECT COUNT(*) FROM employees e WHERE e.branch_id=b.id AND e.status='active') AS employees,
            (SELECT COUNT(*) FROM customers c WHERE c.registered_branch_id=b.id) AS customers,
            (SELECT COUNT(DISTINCT m.customer_id) FROM memberships m JOIN customers c ON c.id=m.customer_id WHERE c.registered_branch_id=b.id AND m.status='active' AND m.end_date>=CURDATE()) AS active_members,
            ((SELECT COALESCE(SUM(amount),0) FROM payments WHERE branch_id=b.id AND payment_date>=DATE_FORMAT(CURDATE(),'%Y-%m-01')) +
             (SELECT COALESCE(SUM(total_amount),0) FROM pos_sales WHERE branch_id=b.id AND sold_at>=DATE_FORMAT(CURDATE(),'%Y-%m-01'))) AS revenue_month,
            ((SELECT COALESCE(SUM(amount),0) FROM payments WHERE branch_id=b.id) +
             (SELECT COALESCE(SUM(total_amount),0) FROM pos_sales WHERE branch_id=b.id)) AS revenue_total,
            (SELECT COUNT(*) FROM attendance WHERE branch_id=b.id AND attendance_date>=DATE_FORMAT(CURDATE(),'%Y-%m-01')) AS attendance_month,
            (SELECT COUNT(*) FROM attendance WHERE branch_id=b.id AND attendance_date=CURDATE()) AS attendance_today
            FROM branches b WHERE 1=1 $sc ORDER BY b.id", $sp);
    }

    private static function dr(string $col, array $f, array &$w, array &$p): void
    {
        if (!empty($f['date_from'])) { $w[] = "DATE($col) >= ?"; $p[] = $f['date_from']; }
        if (!empty($f['date_to'])) { $w[] = "DATE($col) <= ?"; $p[] = $f['date_to']; }
    }

    private static function d($v): string { return number_format((float) $v, 2, '.', ''); }

    /** @return array [title, columns, rows] */
    public static function run(string $type, array $f): array
    {
        if (!isset(self::TYPES[$type])) $type = 'customers';
        $title = self::TYPES[$type];
        $w = []; $p = []; $br = (int) ($f['branch'] ?? 0);
        $plan = (int) ($f['plan'] ?? 0); $ps = $f['pstatus'] ?? '';
        $ps = in_array($ps, ['paid', 'partial', 'unpaid'], true) ? $ps : '';

        if (in_array($type, ['customers', 'active_members', 'expired'], true)) {
            [$sc, $sp] = scope_branch('c.registered_branch_id', $br);
            $w[] = '1=1'; $p = [];
            self::dr('c.registration_date', $f, $w, $p);
            if ($plan) { $w[] = 'm.plan_id=?'; $p[] = $plan; }
            if ($ps) { $w[] = 'm.payment_status=?'; $p[] = $ps; }
            if ($type === 'active_members') { $w[] = "(" . Customer::EFF . ")='active'"; }
            if ($type === 'expired') { $w[] = "(" . Customer::EFF . ")='expired'"; }
            $rows = DB::all(Customer::base() . ' WHERE ' . implode(' AND ', $w) . $sc . ' ORDER BY c.full_name', array_merge($p, $sp));
            $out = [];
            foreach ($rows as $r) $out[] = [$r['customer_code'], $r['full_name'], $r['phone'], ucfirst($r['gender']), $r['reg_branch'], $r['registration_date'],
                $r['plan_name'] ?: '-', $r['eff_status'], $r['payment_status'] ?: '-', $r['end_date'] ?: '-'];
            return [$title, ['Customer ID', 'Name', 'Phone', 'Gender', 'Registered Branch', 'Reg. Date', 'Plan', 'Membership', 'Payment', 'Expiry'], $out];
        }

        if ($type === 'attendance') {
            [$sc, $sp] = scope_branch('a.branch_id', $br); $p = $sp;
            self::dr('a.attendance_date', $f, $w, $p);
            if ($plan) { $w[] = 'm.plan_id=?'; $p[] = $plan; }
            if ($ps) { $w[] = 'm.payment_status=?'; $p[] = $ps; }
            $rows = DB::all("SELECT a.check_in_at, c.customer_code, c.full_name, rb.name AS reg_branch, b.name AS visit_branch, pl.name AS plan
                FROM attendance a JOIN customers c ON c.id=a.customer_id JOIN branches b ON b.id=a.branch_id JOIN branches rb ON rb.id=c.registered_branch_id
                JOIN memberships m ON m.id=a.membership_id JOIN membership_plans pl ON pl.id=m.plan_id
                WHERE 1=1 $sc" . ($w ? ' AND ' . implode(' AND ', $w) : '') . ' ORDER BY a.check_in_at DESC', $p);
            return [$title, ['Check-in', 'Customer ID', 'Name', 'Registered Branch', 'Checked-in Branch', 'Plan'], array_map('array_values', $rows)];
        }

        if (in_array($type, ['daily_payments', 'monthly_revenue', 'annual_revenue'], true)) {
            [$sc, $sp] = scope_branch('p.branch_id', $br); $p = $sp;
            if ($type === 'daily_payments' && empty($f['date_from']) && empty($f['date_to'])) $f['date_from'] = $f['date_to'] = date('Y-m-d');
            self::dr('p.payment_date', $f, $w, $p);
            if ($plan) { $w[] = 'm.plan_id=?'; $p[] = $plan; }
            if ($ps) { $w[] = 'm.payment_status=?'; $p[] = $ps; }
            $where = "WHERE 1=1 $sc" . ($w ? ' AND ' . implode(' AND ', $w) : '');
            $join = 'FROM payments p JOIN memberships m ON m.id=p.membership_id JOIN membership_plans pl ON pl.id=m.plan_id';
            if ($type === 'daily_payments') {
                $rows = DB::all("SELECT p.receipt_no, p.payment_date, c.full_name, b.name AS branch, pm.name AS method, pl.name AS plan, p.amount
                    $join JOIN customers c ON c.id=p.customer_id JOIN branches b ON b.id=p.branch_id JOIN payment_methods pm ON pm.id=p.method_id $where ORDER BY p.payment_date DESC", $p);
                $tot = 0; $out = [];
                foreach ($rows as $r) { $tot += $r['amount']; $r['amount'] = self::d($r['amount']); $out[] = array_values($r); }
                if ($out) $out[] = ['', '', '', '', '', 'TOTAL', self::d($tot)];
                return [$title, ['Receipt', 'Date', 'Customer', 'Branch', 'Method', 'Plan', 'Amount'], $out];
            }
            $fmt = $type === 'monthly_revenue' ? '%Y-%m' : '%Y';
            $rows = DB::all("SELECT DATE_FORMAT(p.payment_date,'$fmt') AS period, COUNT(*) AS n, SUM(p.amount) AS total $join $where GROUP BY period ORDER BY period DESC", $p);
            $out = []; $tot = 0; $cnt = 0;
            foreach ($rows as $r) { $tot += $r['total']; $cnt += $r['n']; $out[] = [$r['period'], $r['n'], self::d($r['total'])]; }
            if ($out) $out[] = ['TOTAL', $cnt, self::d($tot)];
            return [$title, [$type === 'monthly_revenue' ? 'Month' : 'Year', 'Payments', 'Revenue'], $out];
        }

        if ($type === 'branch_performance') {
            [$sc, $sp] = scope_branch('b.id', $br);
            $from = ($f['date_from'] ?? '') ?: '2000-01-01'; $to = ($f['date_to'] ?? '') ?: '2999-12-31';
            $rows = DB::all("SELECT b.name,
                (SELECT COUNT(*) FROM customers c WHERE c.registered_branch_id=b.id AND c.registration_date BETWEEN ? AND ?) AS new_customers,
                (SELECT COUNT(DISTINCT m.customer_id) FROM memberships m JOIN customers c ON c.id=m.customer_id WHERE c.registered_branch_id=b.id AND m.status='active' AND m.end_date>=CURDATE()) AS active_members,
                (SELECT COUNT(*) FROM attendance a WHERE a.branch_id=b.id AND a.attendance_date BETWEEN ? AND ?) AS visits,
                (SELECT COUNT(*) FROM payments p WHERE p.branch_id=b.id AND DATE(p.payment_date) BETWEEN ? AND ?) AS payments,
                ((SELECT COALESCE(SUM(p.amount),0) FROM payments p WHERE p.branch_id=b.id AND DATE(p.payment_date) BETWEEN ? AND ?) +
                 (SELECT COALESCE(SUM(s.total_amount),0) FROM pos_sales s WHERE s.branch_id=b.id AND DATE(s.sold_at) BETWEEN ? AND ?)) AS revenue,
                (SELECT COALESCE(SUM(x.amount),0) FROM expenses x WHERE x.branch_id=b.id AND x.expense_date BETWEEN ? AND ?) AS expenses
                FROM branches b WHERE 1=1 $sc ORDER BY b.id", array_merge([$from, $to, $from, $to, $from, $to, $from, $to, $from, $to, $from, $to], $sp));
            $out = [];
            foreach ($rows as $r) $out[] = [$r['name'], $r['new_customers'], $r['active_members'], $r['visits'], $r['payments'], self::d($r['revenue']), self::d($r['expenses']), self::d($r['revenue'] - $r['expenses'])];
            return [$title, ['Branch', 'New Customers', 'Active Members', 'Visits', 'Payments', 'Revenue', 'Expenses', 'Net'], $out];
        }

        if ($type === 'employees') {
            [$sc, $sp] = scope_branch('e.branch_id', $br);
            $rows = DB::all("SELECT e.full_name, e.phone, e.email, e.position, r.label, b.name AS branch, e.salary, e.joining_date, e.status
                FROM employees e JOIN roles r ON r.id=e.role_id JOIN branches b ON b.id=e.branch_id WHERE 1=1 $sc ORDER BY b.id, e.full_name", $sp);
            $out = [];
            foreach ($rows as $r) { $r['salary'] = self::d($r['salary']); $out[] = array_values($r); }
            return [$title, ['Name', 'Phone', 'Email', 'Position', 'Role', 'Branch', 'Salary', 'Joined', 'Status'], $out];
        }

        if ($type === 'pos_sales') {
            [$sc, $sp] = scope_branch('s.branch_id', $br); $p = $sp;
            self::dr('s.sold_at', $f, $w, $p);
            $rows = DB::all("SELECT s.sale_no, s.sold_at, pr.name AS product, pr.category, s.qty, s.unit_price, s.total_amount, pm.name AS method, b.name AS branch
                FROM pos_sales s JOIN pos_products pr ON pr.id=s.product_id JOIN payment_methods pm ON pm.id=s.method_id JOIN branches b ON b.id=s.branch_id
                WHERE 1=1 $sc" . ($w ? ' AND ' . implode(' AND ', $w) : '') . ' ORDER BY s.sold_at DESC', $p);
            $tot = 0; $out = [];
            foreach ($rows as $r) {
                $tot += $r['total_amount'];
                $out[] = [$r['sale_no'], $r['sold_at'], $r['product'], $r['category'], $r['qty'], self::d($r['unit_price']), self::d($r['total_amount']), $r['method'], $r['branch']];
            }
            if ($out) $out[] = ['', '', '', '', '', 'TOTAL', self::d($tot), '', ''];
            return [$title, ['Sale #', 'Date', 'Product', 'Category', 'Qty', 'Unit Price', 'Total', 'Method', 'Branch'], $out];
        }

        if ($type === 'equipment') {
            [$sc, $sp] = scope_branch('e.branch_id', $br);
            $rows = DB::all("SELECT e.code, e.name, e.category, b.name AS branch, e.quantity, e.condition_status, e.last_service_date, e.next_service_date
                FROM equipment e JOIN branches b ON b.id=e.branch_id WHERE 1=1 $sc ORDER BY b.id, e.name", $sp);
            $out = [];
            foreach ($rows as $r) $out[] = [$r['code'], $r['name'], $r['category'], $r['branch'], $r['quantity'], ucfirst(str_replace('_', ' ', $r['condition_status'])), $r['last_service_date'] ?: '-', $r['next_service_date'] ?: '-'];
            return [$title, ['Code', 'Equipment Name', 'Category', 'Branch', 'Qty', 'Condition', 'Last Service', 'Next Service'], $out];
        }

        // renewals
        [$sc, $sp] = scope_branch('m.branch_id', $br); $p = $sp;
        self::dr('m.created_at', $f, $w, $p);
        if ($plan) { $w[] = 'm.plan_id=?'; $p[] = $plan; }
        if ($ps) { $w[] = 'm.payment_status=?'; $p[] = $ps; }
        $rows = DB::all("SELECT m.created_at, c.customer_code, c.full_name, b.name AS branch, pl.name AS plan, m.start_date, m.end_date, m.price, m.amount_paid, m.status
            FROM memberships m JOIN customers c ON c.id=m.customer_id JOIN branches b ON b.id=m.branch_id JOIN membership_plans pl ON pl.id=m.plan_id
            WHERE EXISTS (SELECT 1 FROM memberships o WHERE o.customer_id=m.customer_id AND o.id<m.id) $sc" . ($w ? ' AND ' . implode(' AND ', $w) : '') . ' ORDER BY m.created_at DESC', $p);
        $out = [];
        foreach ($rows as $r) { $r['price'] = self::d($r['price']); $r['amount_paid'] = self::d($r['amount_paid']); $out[] = array_values($r); }
        return [$title, ['Date', 'Customer ID', 'Name', 'Branch', 'Plan', 'Start', 'Expiry', 'Price', 'Paid', 'Status'], $out];
    }
}
