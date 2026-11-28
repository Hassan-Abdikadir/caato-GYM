<?php
final class DB
{
    private static ?PDO $pdo = null;
    private static string $driver = 'mysql';

    public static function isSqlite(): bool
    {
        return self::$driver === 'sqlite';
    }

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

            $forceSqlite = (($c['driver'] ?? '') === 'sqlite')
                || (!empty($_ENV['VERCEL']) || !empty(getenv('VERCEL'))) && in_array($c['host'] ?? '127.0.0.1', ['127.0.0.1', 'localhost'], true);

            if (!$forceSqlite) {
                try {
                    $dsn = "mysql:host={$c['host']};port={$c['port']};dbname={$c['name']};charset={$c['charset']}";
                    self::$pdo = new PDO($dsn, $user, $pass, $opts);
                    self::$driver = 'mysql';
                } catch (PDOException $e) {
                    // Local XAMPP automatic fallback if remote host is unreachable on localhost
                    if (($c['host'] ?? '') !== '127.0.0.1' && ($c['host'] ?? '') !== 'localhost') {
                        try {
                            $localDsn = "mysql:host=127.0.0.1;port=3306;dbname=caato_gym;charset=utf8mb4";
                            self::$pdo = new PDO($localDsn, 'root', '', $opts);
                            self::$driver = 'mysql';
                        } catch (Throwable $ignore) {
                            self::initSqliteFallback();
                        }
                    } else {
                        self::initSqliteFallback();
                    }
                }
            } else {
                self::initSqliteFallback();
            }

            if (self::$driver === 'mysql') {
                // keep MySQL CURDATE()/NOW() on the same clock as PHP
                self::$pdo->exec("SET time_zone = '" . date('P') . "'");
            }
            self::autoMigrate();
        }
        return self::$pdo;
    }

    private static function initSqliteFallback(): void
    {
        self::$driver = 'sqlite';
        $storageDir = is_writable(ROOT . '/storage') ? (ROOT . '/storage') : sys_get_temp_dir();
        $dbFile = rtrim($storageDir, '/\\') . '/caato_gym_cloud.sqlite';
        self::$pdo = new PDO('sqlite:' . $dbFile, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        self::$pdo->exec('PRAGMA foreign_keys = ON');
        self::$pdo->exec('PRAGMA journal_mode = WAL');

        // Register MySQL-compatible user functions inside SQLite
        self::$pdo->sqliteCreateFunction('CURDATE', fn() => date('Y-m-d'), 0);
        self::$pdo->sqliteCreateFunction('NOW', fn() => date('Y-m-d H:i:s'), 0);
        self::$pdo->sqliteCreateFunction('GREATEST', fn($a, $b) => max($a, $b), 2);
        self::$pdo->sqliteCreateFunction('IF', fn($cond, $t, $f) => $cond ? $t : $f, 3);
        self::$pdo->sqliteCreateFunction('DATE_FORMAT', function ($dt, $fmt) {
            if (!$dt) return null;
            $ts = is_numeric($dt) ? (int) $dt : strtotime((string) $dt);
            if ($ts === false) return null;
            $map = ['%Y' => 'Y', '%y' => 'y', '%m' => 'm', '%d' => 'd', '%H' => 'H', '%i' => 'i', '%s' => 's'];
            return date(strtr((string) $fmt, $map), $ts);
        }, 2);

        self::bootSqliteSchema();
    }

    private static function bootSqliteSchema(): void
    {
        $hasRoles = (bool) self::$pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='roles'")->fetchColumn();
        if ($hasRoles) return;

        $sql = <<<'SQL'
CREATE TABLE IF NOT EXISTS roles (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  name TEXT NOT NULL UNIQUE,
  label TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS permissions (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  perm_key TEXT NOT NULL UNIQUE,
  label TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS role_permissions (
  role_id INTEGER NOT NULL,
  permission_id INTEGER NOT NULL,
  PRIMARY KEY (role_id, permission_id)
);

CREATE TABLE IF NOT EXISTS branches (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  code TEXT NOT NULL UNIQUE,
  name TEXT NOT NULL,
  address TEXT NULL,
  phone TEXT NULL,
  email TEXT NULL,
  status TEXT NOT NULL DEFAULT 'active',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS users (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  full_name TEXT NOT NULL,
  username TEXT NOT NULL UNIQUE,
  email TEXT NULL UNIQUE,
  password_hash TEXT NOT NULL,
  role_id INTEGER NOT NULL,
  branch_id INTEGER NULL,
  status TEXT NOT NULL DEFAULT 'active',
  failed_attempts INTEGER NOT NULL DEFAULT 0,
  locked_until DATETIME NULL,
  last_login DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS employees (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id INTEGER NULL UNIQUE,
  full_name TEXT NOT NULL,
  phone TEXT NOT NULL,
  email TEXT NULL,
  position TEXT NOT NULL,
  role_id INTEGER NOT NULL,
  branch_id INTEGER NOT NULL,
  salary REAL NOT NULL DEFAULT 0,
  joining_date TEXT NOT NULL,
  status TEXT NOT NULL DEFAULT 'active',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS trainers (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  employee_id INTEGER NULL,
  full_name TEXT NOT NULL,
  phone TEXT NOT NULL,
  specialization TEXT NULL,
  branch_id INTEGER NOT NULL,
  status TEXT NOT NULL DEFAULT 'active',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS membership_plans (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  name TEXT NOT NULL UNIQUE,
  type TEXT NOT NULL,
  duration_days INTEGER NOT NULL,
  price REAL NOT NULL,
  status TEXT NOT NULL DEFAULT 'active',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS customers (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  customer_code TEXT NOT NULL UNIQUE,
  full_name TEXT NOT NULL,
  phone TEXT NOT NULL,
  phone_norm TEXT NOT NULL UNIQUE,
  email TEXT NULL,
  gender TEXT NOT NULL,
  date_of_birth TEXT NULL,
  address TEXT NULL,
  profile_picture TEXT NULL,
  fingerprint_id TEXT NULL UNIQUE,
  fingerprint_enrolled INTEGER NOT NULL DEFAULT 0,
  emergency_name TEXT NULL,
  emergency_phone TEXT NULL,
  fitness_goal TEXT NULL DEFAULT 'General Fitness',
  medical_notes TEXT NULL,
  assigned_trainer_id INTEGER NULL,
  registered_branch_id INTEGER NOT NULL,
  registration_date TEXT NOT NULL,
  status TEXT NOT NULL DEFAULT 'active',
  created_by INTEGER NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS memberships (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  customer_id INTEGER NOT NULL,
  plan_id INTEGER NOT NULL,
  branch_id INTEGER NOT NULL,
  start_date TEXT NOT NULL,
  end_date TEXT NOT NULL,
  price REAL NOT NULL,
  amount_paid REAL NOT NULL DEFAULT 0,
  payment_status TEXT NOT NULL DEFAULT 'unpaid',
  status TEXT NOT NULL DEFAULT 'pending',
  suspended_on TEXT NULL,
  notes TEXT NULL,
  created_by INTEGER NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS payment_methods (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  name TEXT NOT NULL UNIQUE,
  requires_reference INTEGER NOT NULL DEFAULT 0,
  status TEXT NOT NULL DEFAULT 'active'
);

CREATE TABLE IF NOT EXISTS payments (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  receipt_no TEXT NOT NULL UNIQUE,
  customer_id INTEGER NOT NULL,
  membership_id INTEGER NOT NULL,
  branch_id INTEGER NOT NULL,
  method_id INTEGER NOT NULL,
  amount REAL NOT NULL,
  payment_date DATETIME NOT NULL,
  reference TEXT NULL,
  notes TEXT NULL,
  received_by INTEGER NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS attendance (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  customer_id INTEGER NOT NULL,
  membership_id INTEGER NOT NULL,
  branch_id INTEGER NOT NULL,
  attendance_date TEXT NOT NULL,
  check_in_at DATETIME NOT NULL,
  checkin_method TEXT NOT NULL DEFAULT 'fingerprint_door',
  recorded_by INTEGER NULL,
  UNIQUE (customer_id, attendance_date)
);

CREATE TABLE IF NOT EXISTS expenses (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  branch_id INTEGER NOT NULL,
  category TEXT NOT NULL,
  description TEXT NULL,
  amount REAL NOT NULL,
  expense_date TEXT NOT NULL,
  created_by INTEGER NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS notifications (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  branch_id INTEGER NULL,
  customer_id INTEGER NULL,
  type TEXT NOT NULL,
  title TEXT NOT NULL,
  message TEXT NOT NULL,
  is_read INTEGER NOT NULL DEFAULT 0,
  dedupe_key TEXT NULL UNIQUE,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS audit_logs (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id INTEGER NULL,
  branch_id INTEGER NULL,
  action TEXT NOT NULL,
  entity TEXT NULL,
  entity_id INTEGER NULL,
  details TEXT NULL,
  ip_address TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS settings (
  skey TEXT PRIMARY KEY,
  svalue TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS body_metrics (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  customer_id INTEGER NOT NULL,
  recorded_date TEXT NOT NULL,
  weight_kg REAL NOT NULL,
  height_cm REAL NULL,
  body_fat_pct REAL NULL,
  muscle_mass_kg REAL NULL,
  waist_cm REAL NULL,
  chest_cm REAL NULL,
  bmi REAL NULL,
  notes TEXT NULL,
  recorded_by INTEGER NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS equipment (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  code TEXT NOT NULL UNIQUE,
  name TEXT NOT NULL,
  category TEXT NOT NULL DEFAULT 'Strength',
  branch_id INTEGER NOT NULL,
  quantity INTEGER NOT NULL DEFAULT 1,
  condition_status TEXT NOT NULL DEFAULT 'operational',
  last_service_date TEXT NULL,
  next_service_date TEXT NULL,
  notes TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS gym_classes (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  title TEXT NOT NULL,
  category TEXT NOT NULL DEFAULT 'HIIT & Cardio',
  trainer_id INTEGER NULL,
  branch_id INTEGER NOT NULL,
  day_of_week TEXT NOT NULL DEFAULT 'Mon, Wed, Fri',
  start_time TEXT NOT NULL,
  end_time TEXT NOT NULL,
  capacity INTEGER NOT NULL DEFAULT 25,
  room TEXT NULL DEFAULT 'Main Studio',
  status TEXT NOT NULL DEFAULT 'active',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS pos_products (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  sku TEXT NOT NULL UNIQUE,
  name TEXT NOT NULL,
  category TEXT NOT NULL DEFAULT 'Supplements',
  price REAL NOT NULL,
  stock_qty INTEGER NOT NULL DEFAULT 0,
  branch_id INTEGER NULL,
  status TEXT NOT NULL DEFAULT 'active',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS pos_sales (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  sale_no TEXT NOT NULL UNIQUE,
  product_id INTEGER NOT NULL,
  customer_id INTEGER NULL,
  branch_id INTEGER NOT NULL,
  method_id INTEGER NOT NULL,
  qty INTEGER NOT NULL DEFAULT 1,
  unit_price REAL NOT NULL,
  total_amount REAL NOT NULL,
  reference TEXT NULL,
  sold_at DATETIME NOT NULL,
  sold_by INTEGER NULL
);

INSERT OR IGNORE INTO roles (name,label) VALUES
 ('super_admin','Super Admin'),('branch_manager','Branch Manager'),
 ('receptionist','Receptionist'),('accountant','Accountant'),('trainer','Trainer');

INSERT OR IGNORE INTO permissions (perm_key,label) VALUES
 ('dashboard.view','View dashboard'),
 ('branches.manage','Manage branches'),
 ('customers.view','View customers'),('customers.create','Register customers'),('customers.edit','Edit customers'),
 ('memberships.view','View memberships'),('memberships.renew','Sell / renew memberships'),
 ('memberships.manage','Extend / suspend / cancel memberships'),('plans.manage','Manage membership plans & prices'),
 ('payments.view','View payments'),('payments.create','Record payments'),('expenses.manage','Manage expenses'),
 ('attendance.view','View attendance'),('attendance.checkin','Check customers in'),
 ('employees.manage','Manage employees'),('trainers.view','View trainers'),('trainers.manage','Manage trainers'),
 ('classes.view','View workout classes'),('classes.manage','Manage workout classes'),
 ('equipment.view','View gym equipment'),('equipment.manage','Manage gym equipment'),
 ('pos.view','View POS & Supplement Bar'),('pos.create','Sell POS Bar items'),('pos.manage','Manage POS inventory & stock'),
 ('reports.view','View & export reports'),('settings.manage','System settings'),('users.manage','Manage users');

INSERT OR IGNORE INTO role_permissions SELECT r.id,p.id FROM roles r, permissions p WHERE r.name='super_admin';
INSERT OR IGNORE INTO role_permissions SELECT r.id,p.id FROM roles r JOIN permissions p ON p.perm_key IN
 ('dashboard.view','customers.view','customers.create','customers.edit','memberships.view','memberships.renew','memberships.manage',
  'payments.view','payments.create','expenses.manage','attendance.view','attendance.checkin','employees.manage','trainers.view','trainers.manage',
  'classes.view','classes.manage','equipment.view','equipment.manage','pos.view','pos.create','pos.manage','reports.view')
 WHERE r.name='branch_manager';
INSERT OR IGNORE INTO role_permissions SELECT r.id,p.id FROM roles r JOIN permissions p ON p.perm_key IN
 ('dashboard.view','customers.view','customers.create','customers.edit','memberships.view','memberships.renew',
  'payments.view','payments.create','attendance.view','attendance.checkin','trainers.view','classes.view','pos.view','pos.create')
 WHERE r.name='receptionist';
INSERT OR IGNORE INTO role_permissions SELECT r.id,p.id FROM roles r JOIN permissions p ON p.perm_key IN
 ('dashboard.view','customers.view','memberships.view','payments.view','payments.create','expenses.manage','pos.view','reports.view')
 WHERE r.name='accountant';
INSERT OR IGNORE INTO role_permissions SELECT r.id,p.id FROM roles r JOIN permissions p ON p.perm_key IN
 ('dashboard.view','customers.view','memberships.view','attendance.view','trainers.view','classes.view','equipment.view')
 WHERE r.name='trainer';

INSERT OR IGNORE INTO branches (code,name) VALUES
 ('B1','CAATO FITNESS GYM - Branch 1'),('B2','CAATO FITNESS GYM - Branch 2'),('B3','CAATO FITNESS GYM - Branch 3'),
 ('B4','CAATO FITNESS GYM - Branch 4'),('B5','CAATO FITNESS GYM - Branch 5'),('B6','CAATO FITNESS GYM - Branch 6');

INSERT OR IGNORE INTO membership_plans (name,type,duration_days,price) VALUES
 ('Daily','daily',1,2.00),('Weekly','weekly',7,8.00),('Monthly','monthly',30,15.00),
 ('Three-Month','quarterly',90,40.00),('Six-Month','semiannual',180,75.00),('Annual','annual',365,140.00);

INSERT OR IGNORE INTO payment_methods (name,requires_reference) VALUES
 ('EVC Plus',1),('Salaam Bank',1),('E-Dahab',1);

INSERT OR IGNORE INTO settings (skey,svalue) VALUES
 ('currency','$'),('expiry_warning_days','7'),('gym_phone',''),('gym_address',''),('last_notify_run','');

INSERT OR IGNORE INTO users (full_name,username,email,password_hash,role_id,branch_id)
SELECT 'System Super Admin','admin','admin@caatofitness.local','$2b$12$U.56e28/UVPhCqUG4ega3uDknRlvLHtWTCmvG8zRQuyLGcCuOT/vm',id,NULL FROM roles WHERE name='super_admin';
SQL;
        self::$pdo->exec($sql);
    }

    /** Translates MySQL-specific SQL constructs into SQLite-compatible equivalents when running on SQLite. */
    private static function normalizeSql(string $sql): string
    {
        if (self::$driver !== 'sqlite') {
            return $sql;
        }
        $sql = preg_replace('/\bINSERT\s+IGNORE\s+INTO\b/i', 'INSERT OR IGNORE INTO', $sql);
        $sql = preg_replace('/\s+FOR\s+UPDATE\b/i', '', $sql);
        $sql = preg_replace('/CURDATE\(\)\s*-\s*INTERVAL\s+(\d+)\s+DAY/i', "date('now','localtime','-$1 days')", $sql);
        $sql = preg_replace('/CURDATE\(\)\s*\+\s*INTERVAL\s+(\d+)\s+DAY/i', "date('now','localtime','+$1 days')", $sql);
        return $sql;
    }

    private static function autoMigrate(): void
    {
        static $migrated = false;
        if ($migrated) return;
        $migrated = true;
        try {
            // 1. Payment Methods: replace Cash & Zaad with EVC Plus & Salaam Bank
            $hasCash = (bool) self::val("SELECT id FROM payment_methods WHERE name='Cash' LIMIT 1");
            $hasZaad = (bool) self::val("SELECT id FROM payment_methods WHERE name='Zaad' LIMIT 1");
            $hasEvc = (bool) self::val("SELECT id FROM payment_methods WHERE name='EVC Plus' LIMIT 1");
            $hasSalaam = (bool) self::val("SELECT id FROM payment_methods WHERE name='Salaam Bank' OR name='Salam Bank' LIMIT 1");

            if (!$hasEvc) {
                if ($hasZaad) {
                    self::exec("UPDATE payment_methods SET name='EVC Plus', requires_reference=1, status='active' WHERE name='Zaad'");
                } else {
                    self::exec("INSERT INTO payment_methods (name, requires_reference, status) VALUES ('EVC Plus', 1, 'active')");
                }
            }
            if (!$hasSalaam) {
                if ($hasCash) {
                    self::exec("UPDATE payment_methods SET name='Salaam Bank', requires_reference=1, status='active' WHERE name='Cash'");
                } else {
                    self::exec("INSERT INTO payment_methods (name, requires_reference, status) VALUES ('Salaam Bank', 1, 'active')");
                }
            }
            self::exec("UPDATE payment_methods SET status='inactive' WHERE name IN ('Cash', 'Zaad', 'Bank Transfer')");

            // 1c. Plan prices sync
            self::exec("UPDATE membership_plans SET price=15.00 WHERE name='Monthly' AND type='monthly' AND price=25.00");
            self::exec("UPDATE membership_plans SET price=40.00  WHERE name='Three-Month'  AND type='quarterly'   AND price=65.00");
            self::exec("UPDATE membership_plans SET price=75.00  WHERE name='Six-Month'    AND type='semiannual'  AND price=120.00");
            self::exec("UPDATE membership_plans SET price=140.00 WHERE name='Annual'       AND type='annual'      AND price=220.00");

            if (self::$driver === 'mysql') {
                // MySQL schema migrations for older installations
                try { self::$pdo->exec("ALTER TABLE customers ADD COLUMN fingerprint_id VARCHAR(100) NULL UNIQUE"); } catch (Throwable $e) {}
                try { self::$pdo->exec("ALTER TABLE customers ADD COLUMN fingerprint_enrolled TINYINT(1) NOT NULL DEFAULT 0"); } catch (Throwable $e) {}
                try { self::$pdo->exec("ALTER TABLE attendance ADD COLUMN checkin_method VARCHAR(40) NOT NULL DEFAULT 'fingerprint_door'"); } catch (Throwable $e) {}
                try { self::$pdo->exec("ALTER TABLE customers ADD COLUMN fitness_goal VARCHAR(80) NULL DEFAULT 'General Fitness'"); } catch (Throwable $e) {}
                try { self::$pdo->exec("ALTER TABLE customers ADD COLUMN medical_notes VARCHAR(255) NULL"); } catch (Throwable $e) {}
                try { self::$pdo->exec("ALTER TABLE customers ADD COLUMN assigned_trainer_id INT UNSIGNED NULL"); } catch (Throwable $e) {}

                self::$pdo->exec("CREATE TABLE IF NOT EXISTS body_metrics (
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

                self::$pdo->exec("CREATE TABLE IF NOT EXISTS equipment (
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

                self::$pdo->exec("CREATE TABLE IF NOT EXISTS gym_classes (
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

                self::$pdo->exec("CREATE TABLE IF NOT EXISTS pos_products (
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

                self::$pdo->exec("CREATE TABLE IF NOT EXISTS pos_sales (
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
            }

            // Seed starter POS products if empty
            if ((int) self::val("SELECT COUNT(*) FROM pos_products") === 0) {
                self::exec("INSERT IGNORE INTO pos_products (sku, name, category, price, stock_qty, status) VALUES
                    ('POS-001', 'Whey Protein Isolate (1 Scoop Shake)', 'Supplements', 2.50, 120, 'active'),
                    ('POS-002', 'Pre-Workout Energy Shot', 'Supplements', 2.00, 85, 'active'),
                    ('POS-003', 'Mineral Water (1L Cold)', 'Beverages', 0.50, 300, 'active'),
                    ('POS-004', 'Isotonic Electrolyte Drink', 'Beverages', 1.50, 150, 'active'),
                    ('POS-005', 'CAATO Pro Lifting Gloves', 'Gear & Apparel', 12.00, 40, 'active'),
                    ('POS-006', 'Creatine Monohydrate (300g Tub)', 'Supplements', 22.00, 25, 'active')");
            }

            // 6. Seed official default staff accounts for all 4 branch roles if not present
            $b1 = (int) self::val("SELECT id FROM branches ORDER BY id LIMIT 1");
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
                self::exec("INSERT IGNORE INTO role_permissions SELECT $saId, id FROM permissions");
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
        $pdo = self::pdo();
        $st = $pdo->prepare(self::normalizeSql($sql));
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
