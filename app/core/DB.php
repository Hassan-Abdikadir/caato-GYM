<?php
final class DB
{
    private static ?PDO $pdo = null;

    public static function pdo(): PDO
    {
        if (!self::$pdo) {
            $c = cfg('db');
            $user = trim((string) ($c['user'] ?? 'root'));
            $pass = (string) ($c['pass'] ?? '');
            $opts = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            try {
                $dsn = "mysql:host={$c['host']};port={$c['port']};dbname={$c['name']};charset={$c['charset']}";
                self::$pdo = new PDO($dsn, $user, $pass, $opts);
            } catch (PDOException $e) {
                // Local XAMPP automatic fallback if remote host is unreachable on localhost
                if (($c['host'] ?? '') !== '127.0.0.1' && ($c['host'] ?? '') !== 'localhost') {
                    try {
                        $localDsn = "mysql:host=127.0.0.1;port=3306;dbname=caato_gym;charset=utf8mb4";
                        self::$pdo = new PDO($localDsn, 'root', '', $opts);
                    } catch (Throwable $ignore) {
                        throw $e;
                    }
                } else {
                    throw $e;
                }
            }
            // keep MySQL CURDATE()/NOW() on the same clock as PHP
            self::$pdo->exec("SET time_zone = '" . date('P') . "'");
            self::autoMigrate();
        }
        return self::$pdo;
    }

    private static function autoMigrate(): void
    {
        static $migrated = false;
        if ($migrated) return;
        $migrated = true;
        try {
            // 1. Payment Methods: replace Cash & Zaad with EVC Plus & Salaam Bank
            $hasCash = (bool) self::pdo()->query("SELECT id FROM payment_methods WHERE name='Cash' LIMIT 1")->fetchColumn();
            $hasZaad = (bool) self::pdo()->query("SELECT id FROM payment_methods WHERE name='Zaad' LIMIT 1")->fetchColumn();
            $hasEvc = (bool) self::pdo()->query("SELECT id FROM payment_methods WHERE name='EVC Plus' LIMIT 1")->fetchColumn();
            $hasSalaam = (bool) self::pdo()->query("SELECT id FROM payment_methods WHERE name='Salaam Bank' OR name='Salam Bank' LIMIT 1")->fetchColumn();

            if (!$hasEvc) {
                if ($hasZaad) {
                    self::pdo()->exec("UPDATE payment_methods SET name='EVC Plus', requires_reference=1, status='active' WHERE name='Zaad'");
                } else {
                    self::pdo()->exec("INSERT INTO payment_methods (name, requires_reference, status) VALUES ('EVC Plus', 1, 'active')");
                }
            }
            if (!$hasSalaam) {
                if ($hasCash) {
                    self::pdo()->exec("UPDATE payment_methods SET name='Salaam Bank', requires_reference=1, status='active' WHERE name='Cash'");
                } else {
                    self::pdo()->exec("INSERT INTO payment_methods (name, requires_reference, status) VALUES ('Salaam Bank', 1, 'active')");
                }
            }
            // Deactivate remaining Cash or Zaad if they still exist
            self::pdo()->exec("UPDATE payment_methods SET status='inactive' WHERE name IN ('Cash', 'Zaad')");

            // 1b. Remove / deactivate Bank Transfer
            self::pdo()->exec("UPDATE payment_methods SET status='inactive' WHERE name='Bank Transfer'");

            // 1c. Monthly plan price → $15.00 (if it is still at the old $25 seed value)
            self::pdo()->exec("UPDATE membership_plans SET price=15.00 WHERE name='Monthly' AND type='monthly' AND price=25.00");
            // Also fix the other plan prices to match the new seed
            self::pdo()->exec("UPDATE membership_plans SET price=40.00  WHERE name='Three-Month'  AND type='quarterly'   AND price=65.00");
            self::pdo()->exec("UPDATE membership_plans SET price=75.00  WHERE name='Six-Month'    AND type='semiannual'  AND price=120.00");
            self::pdo()->exec("UPDATE membership_plans SET price=140.00 WHERE name='Annual'       AND type='annual'      AND price=220.00");

            // 2. Add biometric fingerprint fields to customers table
            try {
                self::pdo()->exec("ALTER TABLE customers ADD COLUMN fingerprint_id VARCHAR(100) NULL UNIQUE");
            } catch (Throwable $e) {}
            try {
                self::pdo()->exec("ALTER TABLE customers ADD COLUMN fingerprint_enrolled TINYINT(1) NOT NULL DEFAULT 0");
            } catch (Throwable $e) {}

            // 3. Add checkin_method to attendance table
            try {
                self::pdo()->exec("ALTER TABLE attendance ADD COLUMN checkin_method VARCHAR(40) NOT NULL DEFAULT 'fingerprint_door'");
            } catch (Throwable $e) {}

            // 4. Add fitness_goal, medical_notes, assigned_trainer_id to customers
            try {
                self::pdo()->exec("ALTER TABLE customers ADD COLUMN fitness_goal VARCHAR(80) NULL DEFAULT 'General Fitness'");
            } catch (Throwable $e) {}
            try {
                self::pdo()->exec("ALTER TABLE customers ADD COLUMN medical_notes VARCHAR(255) NULL");
            } catch (Throwable $e) {}
            try {
                self::pdo()->exec("ALTER TABLE customers ADD COLUMN assigned_trainer_id INT UNSIGNED NULL");
            } catch (Throwable $e) {}

            // 5. Create 2026 Enterprise Gym Tables (Body Metrics, Equipment, Group Classes, POS Store)
            self::pdo()->exec("CREATE TABLE IF NOT EXISTS body_metrics (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                customer_id INT UNSIGNED NOT NULL,
                recorded_date DATE NOT NULL,
                weight_kg DECIMAL(5,2) NOT NULL,
                height_cm DECIMAL(5,2) NULL,
                body_fat_pct DECIMAL(4,1) NULL,
                muscle_mass_kg DECIMAL(5,2) NULL,
                waist_cm DECIMAL(5,1) NULL,
                chest_cm DECIMAL(5,1) NULL,
                bmi DECIMAL(4,1) NULL,
                notes VARCHAR(255) NULL,
                recorded_by INT UNSIGNED NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                KEY idx_bm_cust (customer_id, recorded_date)
            ) ENGINE=InnoDB");

            self::pdo()->exec("CREATE TABLE IF NOT EXISTS equipment (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                code VARCHAR(30) NOT NULL UNIQUE,
                name VARCHAR(120) NOT NULL,
                category VARCHAR(60) NOT NULL DEFAULT 'Strength',
                branch_id INT UNSIGNED NOT NULL,
                quantity SMALLINT UNSIGNED NOT NULL DEFAULT 1,
                condition_status ENUM('operational','maintenance','out_of_order') NOT NULL DEFAULT 'operational',
                last_service_date DATE NULL,
                next_service_date DATE NULL,
                notes VARCHAR(255) NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB");

            self::pdo()->exec("CREATE TABLE IF NOT EXISTS gym_classes (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(120) NOT NULL,
                category VARCHAR(60) NOT NULL DEFAULT 'HIIT & Cardio',
                trainer_id INT UNSIGNED NULL,
                branch_id INT UNSIGNED NOT NULL,
                day_of_week VARCHAR(40) NOT NULL DEFAULT 'Mon, Wed, Fri',
                start_time TIME NOT NULL,
                end_time TIME NOT NULL,
                capacity SMALLINT UNSIGNED NOT NULL DEFAULT 25,
                room VARCHAR(60) NULL DEFAULT 'Main Studio',
                status ENUM('active','cancelled') NOT NULL DEFAULT 'active',
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB");

            self::pdo()->exec("CREATE TABLE IF NOT EXISTS pos_products (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                sku VARCHAR(30) NOT NULL UNIQUE,
                name VARCHAR(120) NOT NULL,
                category VARCHAR(60) NOT NULL DEFAULT 'Supplements',
                price DECIMAL(10,2) NOT NULL,
                stock_qty INT NOT NULL DEFAULT 0,
                branch_id INT UNSIGNED NULL,
                status ENUM('active','inactive') NOT NULL DEFAULT 'active',
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB");

            self::pdo()->exec("CREATE TABLE IF NOT EXISTS pos_sales (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                sale_no VARCHAR(30) NOT NULL UNIQUE,
                product_id INT UNSIGNED NOT NULL,
                customer_id INT UNSIGNED NULL,
                branch_id INT UNSIGNED NOT NULL,
                method_id INT UNSIGNED NOT NULL,
                qty SMALLINT UNSIGNED NOT NULL DEFAULT 1,
                unit_price DECIMAL(10,2) NOT NULL,
                total_amount DECIMAL(10,2) NOT NULL,
                reference VARCHAR(80) NULL,
                sold_at DATETIME NOT NULL,
                sold_by INT UNSIGNED NULL,
                KEY idx_pos_branch (branch_id, sold_at)
            ) ENGINE=InnoDB");

            // Seed starter POS products if empty
            if ((int) self::pdo()->query("SELECT COUNT(*) FROM pos_products")->fetchColumn() === 0) {
                self::pdo()->exec("INSERT IGNORE INTO pos_products (sku, name, category, price, stock_qty, status) VALUES
                    ('POS-001', 'Whey Protein Isolate (1 Scoop Shake)', 'Supplements', 2.50, 120, 'active'),
                    ('POS-002', 'Pre-Workout Energy Shot', 'Supplements', 2.00, 85, 'active'),
                    ('POS-003', 'Mineral Water (1L Cold)', 'Beverages', 0.50, 300, 'active'),
                    ('POS-004', 'Isotonic Electrolyte Drink', 'Beverages', 1.50, 150, 'active'),
                    ('POS-005', 'CAATO Pro Lifting Gloves', 'Gear & Apparel', 12.00, 40, 'active'),
                    ('POS-006', 'Creatine Monohydrate (300g Tub)', 'Supplements', 22.00, 25, 'active')");
            }

            // 6. Seed official default staff accounts for all 4 branch roles if not present
            $b1 = (int) self::pdo()->query("SELECT id FROM branches ORDER BY id LIMIT 1")->fetchColumn();
            if ($b1 > 0) {
                $staffSeeds = [
                    ['Branch 1 Manager', 'manager', 'manager@caatofitness.local', 'Manager@12345', 'branch_manager', 'Branch Manager'],
                    ['Front Desk Receptionist', 'reception', 'reception@caatofitness.local', 'Reception@12345', 'receptionist', 'Front Desk Receptionist'],
                    ['Chief Accountant', 'accountant', 'accountant@caatofitness.local', 'Accountant@12345', 'accountant', 'Senior Accountant'],
                    ['Head Fitness Coach', 'trainer', 'trainer@caatofitness.local', 'Trainer@12345', 'trainer', 'Head Fitness Coach'],
                ];
                foreach ($staffSeeds as [$fn, $un, $em, $pw, $rn, $pos]) {
                    $exists = (int) self::val("SELECT id FROM users WHERE username=?", [$un]);
                    if (!$exists) {
                        $rid = (int) self::val("SELECT id FROM roles WHERE name=?", [$rn]);
                        if ($rid) {
                            $uid = self::insert('users', [
                                'full_name' => $fn,
                                'username' => $un,
                                'email' => $em,
                                'password_hash' => password_hash($pw, PASSWORD_DEFAULT),
                                'role_id' => $rid,
                                'branch_id' => $b1,
                                'status' => 'active',
                            ]);
                            self::insert('employees', [
                                'user_id' => $uid,
                                'full_name' => $fn,
                                'phone' => '0615000' . $uid,
                                'email' => $em,
                                'position' => $pos,
                                'role_id' => $rid,
                                'branch_id' => $b1,
                                'salary' => 450.00,
                                'joining_date' => date('Y-m-d'),
                                'status' => 'active',
                            ]);
                            if ($rn === 'trainer') {
                                self::insert('trainers', [
                                    'full_name' => $fn,
                                    'phone' => '0615000' . $uid,
                                    'specialization' => 'Strength, HIIT & Body Transformation',
                                    'branch_id' => $b1,
                                    'status' => 'active',
                                ]);
                            }
                        }
                    }
                }
            }
            // 7. Auto-sync granular RBAC permissions for all 5 roles
            $newPerms = [
                ['classes.view', 'View workout classes'],
                ['classes.manage', 'Manage workout classes'],
                ['equipment.view', 'View gym equipment'],
                ['equipment.manage', 'Manage gym equipment'],
                ['pos.view', 'View POS & Supplement Bar'],
                ['pos.create', 'Sell POS Bar items'],
                ['pos.manage', 'Manage POS inventory & stock'],
            ];
            foreach ($newPerms as [$pk, $pl]) {
                self::q("INSERT IGNORE INTO permissions (perm_key, label) VALUES (?, ?)", [$pk, $pl]);
            }
            $roleMap = [
                'branch_manager' => ['classes.view', 'classes.manage', 'equipment.view', 'equipment.manage', 'pos.view', 'pos.create', 'pos.manage'],
                'receptionist'   => ['classes.view', 'pos.view', 'pos.create'],
                'accountant'     => ['pos.view'],
                'trainer'        => ['classes.view', 'equipment.view'],
            ];
            foreach ($roleMap as $rName => $pKeys) {
                $rid = (int) self::val("SELECT id FROM roles WHERE name=?", [$rName]);
                if ($rid) {
                    foreach ($pKeys as $pk) {
                        $pid = (int) self::val("SELECT id FROM permissions WHERE perm_key=?", [$pk]);
                        if ($pid) self::q("INSERT IGNORE INTO role_permissions (role_id, permission_id) VALUES (?, ?)", [$rid, $pid]);
                    }
                }
            }
            $saId = (int) self::val("SELECT id FROM roles WHERE name='super_admin'");
            if ($saId) {
                self::pdo()->exec("INSERT IGNORE INTO role_permissions SELECT $saId, id FROM permissions");
            }

            // 8. Sanitize any corrupted UTF-8 / CP850 dash characters in branch names
            foreach (self::all("SELECT id, code, name FROM branches") as $brRow) {
                if (preg_match('/Branch\s*(\d+)/i', $brRow['name'], $m)) {
                    $cleanName = 'CAATO FITNESS GYM - Branch ' . $m[1];
                    if ($brRow['name'] !== $cleanName && strpos($brRow['name'], 'CAATO FITNESS GYM') === 0) {
                        self::update('branches', ['name' => $cleanName], 'id=?', [$brRow['id']]);
                    }
                }
            }
        } catch (Throwable $e) {
            // Gracefully ignore migration errors if DB is uninitialized
        }
    }

    public static function q(string $sql, array $p = []): PDOStatement
    {
        $st = self::pdo()->prepare($sql);
        $st->execute(array_values($p));
        return $st;
    }
    public static function all(string $sql, array $p = []): array { return self::q($sql, $p)->fetchAll(); }
    public static function one(string $sql, array $p = []): ?array { $r = self::q($sql, $p)->fetch(); return $r ?: null; }
    public static function val(string $sql, array $p = []) { $r = self::q($sql, $p)->fetchColumn(); return $r === false ? null : $r; }
    public static function exec(string $sql, array $p = []): int { return self::q($sql, $p)->rowCount(); }
    public static function id(): int { return (int) self::pdo()->lastInsertId(); }

    public static function insert(string $table, array $data): int
    {
        $cols = implode(',', array_map(fn($c) => "`$c`", array_keys($data)));
        $ph = implode(',', array_fill(0, count($data), '?'));
        self::q("INSERT INTO `$table` ($cols) VALUES ($ph)", array_values($data));
        return self::id();
    }

    public static function update(string $table, array $data, string $where, array $wp = []): int
    {
        $set = implode(',', array_map(fn($c) => "`$c`=?", array_keys($data)));
        return self::exec("UPDATE `$table` SET $set WHERE $where", array_merge(array_values($data), $wp));
    }

    /** Run $fn inside a transaction (commit on success, rollback on any exception). */
    public static function tx(callable $fn)
    {
        $pdo = self::pdo();
        if ($pdo->inTransaction()) return $fn(); // already inside a transaction
        $pdo->beginTransaction();
        try {
            $r = $fn();
            $pdo->commit();
            return $r;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            throw $e;
        }
    }
}
