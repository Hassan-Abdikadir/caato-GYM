-- ============================================================
-- CAATO GYM — Clear All Transactional Data
-- Keeps: roles, permissions, role_permissions, branches,
--        users (admin), membership_plans, payment_methods, settings
-- Removes: customers, memberships, payments, attendance,
--          expenses, notifications, audit_logs
-- Run this in phpMyAdmin → caato_gym database → SQL tab
-- ============================================================

USE caato_gym;
SET FOREIGN_KEY_CHECKS = 0;

TRUNCATE TABLE audit_logs;
TRUNCATE TABLE notifications;
TRUNCATE TABLE attendance;
TRUNCATE TABLE payments;
TRUNCATE TABLE memberships;
TRUNCATE TABLE expenses;
TRUNCATE TABLE body_metrics;
TRUNCATE TABLE pos_sales;
TRUNCATE TABLE customers;

SET FOREIGN_KEY_CHECKS = 1;

-- Confirm what remains:
SELECT 'customers'   AS tbl, COUNT(*) AS rows FROM customers   UNION ALL
SELECT 'memberships' AS tbl, COUNT(*) AS rows FROM memberships  UNION ALL
SELECT 'payments'    AS tbl, COUNT(*) AS rows FROM payments     UNION ALL
SELECT 'attendance'  AS tbl, COUNT(*) AS rows FROM attendance   UNION ALL
SELECT 'expenses'    AS tbl, COUNT(*) AS rows FROM expenses     UNION ALL
SELECT 'notifications' AS tbl, COUNT(*) AS rows FROM notifications UNION ALL
SELECT 'audit_logs'  AS tbl, COUNT(*) AS rows FROM audit_logs;
