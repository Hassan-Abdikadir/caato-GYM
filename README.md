# CAATO FITNESS GYM – Multi-Branch Management System
Core PHP 8 (MVC-style) · MySQL · Bootstrap 5 · Chart.js · one centralized database for all six branches.

## Install on XAMPP (Windows)
1. Install XAMPP (PHP 8.0+). In the XAMPP Control Panel start **Apache** and **MySQL**.
2. Copy the `caato-gym` folder to `C:\xampp\htdocs\caato-gym`.
3. Open **http://localhost/phpmyadmin** → *Import* → choose `database/caato_gym.sql` → *Go*.
   (It creates the `caato_gym` database, all 14+ tables, the 6 branches, roles, permissions, plans and payment methods.)
4. If your MySQL root has a password, edit `config/config.php`.
5. Open **http://localhost/caato-gym/check.php** – every line should be green. Press
   **"Run cross-branch self-test"**: it registers customers at one branch, pays at another, checks in at all six
   branches, tests unpaid / expired / duplicate rules – then rolls everything back (no test data is kept).
6. Open **http://localhost/caato-gym/** and sign in: `admin` / `Admin@12345` → **change the password immediately**
   (Users → Edit). Delete `check.php` before putting the system online.

Internet is needed on the browser for Bootstrap, Chart.js, icons and fonts (CDN) and for QR images on membership cards.

## First-day setup (as Super Admin)
1. **Branches** – rename Branch 1–6 and add address/phone.
2. **Memberships → Plans & prices** – set your real prices (starter prices are only placeholders).
3. **Users** – create a *Branch Manager* for each branch; managers then add receptionists/accountants/trainers under **Employees**.

## How cross-branch access works
* One `customers` table serves every branch. A customer is registered once (phone number is unique).
* Any receptionist searches by name / phone / customer ID (**Check-in & Attendance**) and sees the same profile,
  plan, payment status, expiry and last visit – whichever branch registered or sold the plan.
* Check-in is allowed only when: account not blocked · membership **active** · **fully paid** · not expired/suspended · started.
  Otherwise a payment / expiry warning is shown. Each check-in stores the **actual branch**; a unique key
  `(customer_id, attendance_date)` blocks duplicate check-ins even under simultaneous clicks.
* Payments store the branch that received the money; memberships store the branch that sold them.
* A membership is created **pending** and becomes **active only when payments reach the full price** (all inside a DB transaction).
  If a pending plan is paid after its start date, the clock starts on the payment date.

## Roles
Super Admin (everything, all branches) · Branch Manager · Receptionist · Accountant · Trainer – permissions are in the
`permissions` / `role_permissions` tables; non-super users are always locked to their own branch in every query.

## Folder map
`index.php` front controller · `app/core` (DB, Auth, helpers, Pdf, Xlsx) · `app/models` (Customer, Membership, Payment, Attendance, Report)
· `app/controllers` · `app/views` · `assets` · `uploads/profiles` · `database/caato_gym.sql` · `check.php`

## Notes
* PDF and Excel exports use small built-in writers (no Composer needed). Excel export needs PHP's `zip` extension (enabled in XAMPP).
* QR scanners that "type" the customer ID + Enter work in the check-in box (auto check-in on exact match).
* Security: password hashing, sessions with idle timeout + login lockout, CSRF tokens on every POST, prepared statements everywhere,
  role checks on every page, upload validation, audit log (Settings → Audit log).
