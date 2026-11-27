-- CAATO FITNESS GYM - Centralized multi-branch database (MySQL 5.7+ / MariaDB 10.3+)
CREATE DATABASE IF NOT EXISTS caato_gym CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE caato_gym;
SET FOREIGN_KEY_CHECKS=0;

CREATE TABLE roles (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(40) NOT NULL UNIQUE,
  label VARCHAR(80) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE permissions (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  perm_key VARCHAR(60) NOT NULL UNIQUE,
  label VARCHAR(120) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE role_permissions (
  role_id INT UNSIGNED NOT NULL,
  permission_id INT UNSIGNED NOT NULL,
  PRIMARY KEY (role_id, permission_id),
  FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
  FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE branches (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(20) NOT NULL UNIQUE,
  name VARCHAR(120) NOT NULL,
  address VARCHAR(255) NULL,
  phone VARCHAR(30) NULL,
  email VARCHAR(120) NULL,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(120) NOT NULL,
  username VARCHAR(50) NOT NULL UNIQUE,
  email VARCHAR(120) NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role_id INT UNSIGNED NOT NULL,
  branch_id INT UNSIGNED NULL,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  failed_attempts TINYINT UNSIGNED NOT NULL DEFAULT 0,
  locked_until DATETIME NULL,
  last_login DATETIME NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (role_id) REFERENCES roles(id),
  FOREIGN KEY (branch_id) REFERENCES branches(id)
) ENGINE=InnoDB;

CREATE TABLE employees (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NULL UNIQUE,
  full_name VARCHAR(120) NOT NULL,
  phone VARCHAR(30) NOT NULL,
  email VARCHAR(120) NULL,
  position VARCHAR(80) NOT NULL,
  role_id INT UNSIGNED NOT NULL,
  branch_id INT UNSIGNED NOT NULL,
  salary DECIMAL(12,2) NOT NULL DEFAULT 0,
  joining_date DATE NOT NULL,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  FOREIGN KEY (role_id) REFERENCES roles(id),
  FOREIGN KEY (branch_id) REFERENCES branches(id)
) ENGINE=InnoDB;

CREATE TABLE trainers (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  employee_id INT UNSIGNED NULL,
  full_name VARCHAR(120) NOT NULL,
  phone VARCHAR(30) NOT NULL,
  specialization VARCHAR(120) NULL,
  branch_id INT UNSIGNED NOT NULL,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE SET NULL,
  FOREIGN KEY (branch_id) REFERENCES branches(id)
) ENGINE=InnoDB;

CREATE TABLE membership_plans (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(80) NOT NULL UNIQUE,
  type ENUM('daily','weekly','monthly','quarterly','semiannual','annual','custom') NOT NULL,
  duration_days SMALLINT UNSIGNED NOT NULL,
  price DECIMAL(10,2) NOT NULL,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE customers (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  customer_code VARCHAR(20) NOT NULL UNIQUE,
  full_name VARCHAR(120) NOT NULL,
  phone VARCHAR(30) NOT NULL,
  phone_norm VARCHAR(20) NOT NULL UNIQUE,
  email VARCHAR(120) NULL,
  gender ENUM('male','female') NOT NULL,
  date_of_birth DATE NULL,
  address VARCHAR(255) NULL,
  profile_picture VARCHAR(255) NULL,
  fingerprint_id VARCHAR(100) NULL UNIQUE,
  fingerprint_enrolled TINYINT(1) NOT NULL DEFAULT 0,
  emergency_name VARCHAR(120) NULL,
  emergency_phone VARCHAR(30) NULL,
  fitness_goal VARCHAR(80) NULL DEFAULT 'General Fitness',
  medical_notes VARCHAR(255) NULL,
  assigned_trainer_id INT UNSIGNED NULL,
  registered_branch_id INT UNSIGNED NOT NULL,
  registration_date DATE NOT NULL,
  status ENUM('active','blocked') NOT NULL DEFAULT 'active',
  created_by INT UNSIGNED NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_name (full_name),
  KEY idx_regdate (registration_date),
  FOREIGN KEY (registered_branch_id) REFERENCES branches(id),
  FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE memberships (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  customer_id INT UNSIGNED NOT NULL,
  plan_id INT UNSIGNED NOT NULL,
  branch_id INT UNSIGNED NOT NULL COMMENT 'branch that sold/renewed it',
  start_date DATE NOT NULL,
  end_date DATE NOT NULL,
  price DECIMAL(10,2) NOT NULL,
  amount_paid DECIMAL(10,2) NOT NULL DEFAULT 0,
  payment_status ENUM('unpaid','partial','paid') NOT NULL DEFAULT 'unpaid',
  status ENUM('pending','active','expired','suspended','cancelled') NOT NULL DEFAULT 'pending',
  suspended_on DATE NULL,
  notes VARCHAR(255) NULL,
  created_by INT UNSIGNED NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_cust (customer_id, status),
  KEY idx_end (status, end_date),
  FOREIGN KEY (customer_id) REFERENCES customers(id),
  FOREIGN KEY (plan_id) REFERENCES membership_plans(id),
  FOREIGN KEY (branch_id) REFERENCES branches(id),
  FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE payment_methods (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(60) NOT NULL UNIQUE,
  requires_reference TINYINT(1) NOT NULL DEFAULT 0,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB;

CREATE TABLE payments (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  receipt_no VARCHAR(30) NOT NULL UNIQUE,
  customer_id INT UNSIGNED NOT NULL,
  membership_id INT UNSIGNED NOT NULL,
  branch_id INT UNSIGNED NOT NULL COMMENT 'branch that received the money',
  method_id INT UNSIGNED NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  payment_date DATETIME NOT NULL,
  reference VARCHAR(80) NULL,
  notes VARCHAR(255) NULL,
  received_by INT UNSIGNED NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_pdate (payment_date),
  KEY idx_pbranch (branch_id, payment_date),
  FOREIGN KEY (customer_id) REFERENCES customers(id),
  FOREIGN KEY (membership_id) REFERENCES memberships(id),
  FOREIGN KEY (branch_id) REFERENCES branches(id),
  FOREIGN KEY (method_id) REFERENCES payment_methods(id),
  FOREIGN KEY (received_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE attendance (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  customer_id INT UNSIGNED NOT NULL,
  membership_id INT UNSIGNED NOT NULL,
  branch_id INT UNSIGNED NOT NULL COMMENT 'branch where the customer actually checked in',
  attendance_date DATE NOT NULL,
  check_in_at DATETIME NOT NULL,
  checkin_method VARCHAR(40) NOT NULL DEFAULT 'fingerprint_door',
  recorded_by INT UNSIGNED NULL,
  UNIQUE KEY uq_session (customer_id, attendance_date),
  KEY idx_att_branch (branch_id, attendance_date),
  FOREIGN KEY (customer_id) REFERENCES customers(id),
  FOREIGN KEY (membership_id) REFERENCES memberships(id),
  FOREIGN KEY (branch_id) REFERENCES branches(id),
  FOREIGN KEY (recorded_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE expenses (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  branch_id INT UNSIGNED NOT NULL,
  category VARCHAR(60) NOT NULL,
  description VARCHAR(255) NULL,
  amount DECIMAL(10,2) NOT NULL,
  expense_date DATE NOT NULL,
  created_by INT UNSIGNED NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (branch_id) REFERENCES branches(id),
  FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE notifications (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  branch_id INT UNSIGNED NULL,
  customer_id INT UNSIGNED NULL,
  type VARCHAR(40) NOT NULL,
  title VARCHAR(160) NOT NULL,
  message VARCHAR(255) NOT NULL,
  is_read TINYINT(1) NOT NULL DEFAULT 0,
  dedupe_key VARCHAR(120) NULL UNIQUE,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_notif (is_read, created_at)
) ENGINE=InnoDB;

CREATE TABLE audit_logs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NULL,
  branch_id INT UNSIGNED NULL,
  action VARCHAR(60) NOT NULL,
  entity VARCHAR(40) NULL,
  entity_id INT UNSIGNED NULL,
  details VARCHAR(500) NULL,
  ip_address VARCHAR(45) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_audit (created_at)
) ENGINE=InnoDB;

CREATE TABLE settings (
  skey VARCHAR(60) PRIMARY KEY,
  svalue VARCHAR(500) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE body_metrics (
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
  KEY idx_bm_cust (customer_id, recorded_date),
  FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE equipment (
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
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE gym_classes (
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
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (trainer_id) REFERENCES trainers(id) ON DELETE SET NULL,
  FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE pos_products (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  sku VARCHAR(30) NOT NULL UNIQUE,
  name VARCHAR(120) NOT NULL,
  category VARCHAR(60) NOT NULL DEFAULT 'Supplements',
  price DECIMAL(10,2) NOT NULL,
  stock_qty INT NOT NULL DEFAULT 0,
  branch_id INT UNSIGNED NULL COMMENT 'NULL = shared or specific branch',
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE pos_sales (
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
  KEY idx_pos_branch (branch_id, sold_at),
  FOREIGN KEY (product_id) REFERENCES pos_products(id),
  FOREIGN KEY (branch_id) REFERENCES branches(id)
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS=1;

-- ===================== SEED DATA (configuration only, no fake customers) =====================
INSERT INTO roles (name,label) VALUES
 ('super_admin','Super Admin'),('branch_manager','Branch Manager'),
 ('receptionist','Receptionist'),('accountant','Accountant'),('trainer','Trainer');

INSERT INTO permissions (perm_key,label) VALUES
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

-- Super admin: everything (also hard-coded in code)
INSERT INTO role_permissions SELECT r.id,p.id FROM roles r, permissions p WHERE r.name='super_admin';
INSERT INTO role_permissions SELECT r.id,p.id FROM roles r JOIN permissions p ON p.perm_key IN
 ('dashboard.view','customers.view','customers.create','customers.edit','memberships.view','memberships.renew','memberships.manage',
  'payments.view','payments.create','expenses.manage','attendance.view','attendance.checkin','employees.manage','trainers.view','trainers.manage',
  'classes.view','classes.manage','equipment.view','equipment.manage','pos.view','pos.create','pos.manage','reports.view')
 WHERE r.name='branch_manager';
INSERT INTO role_permissions SELECT r.id,p.id FROM roles r JOIN permissions p ON p.perm_key IN
 ('dashboard.view','customers.view','customers.create','customers.edit','memberships.view','memberships.renew',
  'payments.view','payments.create','attendance.view','attendance.checkin','trainers.view','classes.view','pos.view','pos.create')
 WHERE r.name='receptionist';
INSERT INTO role_permissions SELECT r.id,p.id FROM roles r JOIN permissions p ON p.perm_key IN
 ('dashboard.view','customers.view','memberships.view','payments.view','payments.create','expenses.manage','pos.view','reports.view')
 WHERE r.name='accountant';
INSERT INTO role_permissions SELECT r.id,p.id FROM roles r JOIN permissions p ON p.perm_key IN
 ('dashboard.view','customers.view','memberships.view','attendance.view','trainers.view','classes.view','equipment.view')
 WHERE r.name='trainer';

INSERT INTO branches (code,name) VALUES
 ('B1','CAATO FITNESS GYM - Branch 1'),('B2','CAATO FITNESS GYM - Branch 2'),('B3','CAATO FITNESS GYM - Branch 3'),
 ('B4','CAATO FITNESS GYM - Branch 4'),('B5','CAATO FITNESS GYM - Branch 5'),('B6','CAATO FITNESS GYM - Branch 6');

-- Starter prices: the Super Admin can change these any time (Memberships > Plans)
INSERT INTO membership_plans (name,type,duration_days,price) VALUES
 ('Daily','daily',1,2.00),('Weekly','weekly',7,8.00),('Monthly','monthly',30,15.00),
 ('Three-Month','quarterly',90,40.00),('Six-Month','semiannual',180,75.00),('Annual','annual',365,140.00);

INSERT INTO payment_methods (name,requires_reference) VALUES
 ('EVC Plus',1),('Salaam Bank',1),('E-Dahab',1);

INSERT INTO settings (skey,svalue) VALUES
 ('currency','$'),('expiry_warning_days','7'),('gym_phone',''),('gym_address',''),('last_notify_run','');

-- Default Super Admin  ->  username: admin   password: Admin@12345   (CHANGE IT AFTER FIRST LOGIN)
INSERT INTO users (full_name,username,email,password_hash,role_id,branch_id)
SELECT 'System Super Admin','admin','admin@caatofitness.local','$2b$12$U.56e28/UVPhCqUG4ega3uDknRlvLHtWTCmvG8zRQuyLGcCuOT/vm',id,NULL FROM roles WHERE name='super_admin';

-- Official Default Staff Role Accounts (Assigned to Branch 1 by default)
INSERT IGNORE INTO users (full_name,username,email,password_hash,role_id,branch_id)
SELECT 'Branch 1 Manager','manager','manager@caatofitness.local','$2y$10$w8S7U/eG.8N.6A6aW6aW6u7v3mK9.P5bQ2sR1tY8uI0oP3aS4dF5g',r.id,b.id FROM roles r, branches b WHERE r.name='branch_manager' AND b.code='B1';
INSERT IGNORE INTO users (full_name,username,email,password_hash,role_id,branch_id)
SELECT 'Front Desk Receptionist','reception','reception@caatofitness.local','$2y$10$w8S7U/eG.8N.6A6aW6aW6u7v3mK9.P5bQ2sR1tY8uI0oP3aS4dF5g',r.id,b.id FROM roles r, branches b WHERE r.name='receptionist' AND b.code='B1';
INSERT IGNORE INTO users (full_name,username,email,password_hash,role_id,branch_id)
SELECT 'Chief Accountant','accountant','accountant@caatofitness.local','$2y$10$w8S7U/eG.8N.6A6aW6aW6u7v3mK9.P5bQ2sR1tY8uI0oP3aS4dF5g',r.id,b.id FROM roles r, branches b WHERE r.name='accountant' AND b.code='B1';
INSERT IGNORE INTO users (full_name,username,email,password_hash,role_id,branch_id)
SELECT 'Head Fitness Coach','trainer','trainer@caatofitness.local','$2y$10$w8S7U/eG.8N.6A6aW6aW6u7v3mK9.P5bQ2sR1tY8uI0oP3aS4dF5g',r.id,b.id FROM roles r, branches b WHERE r.name='trainer' AND b.code='B1';
