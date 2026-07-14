# CCTV AMC Management System

PHP + MySQL + HTML/CSS/JS me bana hua complete AMC (Annual Maintenance Contract)
management system — CCTV/Camera installation business ke liye.

## Features

**Admin Panel**
- Dashboard (stats: total customers, active/expiring/expired AMC, technicians, unpaid invoices)
- Customers: Add / Edit / Delete
- AMC Contracts: Add / Edit / Delete, Technician assign
- Expiring AMCs ka alag page (30 din pehle warning)
- Technician management (add/remove login)
- Invoices: create, mark paid/unpaid, printable invoice view
- Reports: revenue, technician performance, sabhi visit reports
- WhatsApp click-to-call (customer ke mobile number par click karke seedha WhatsApp khulta hai)

**Technician Panel**
- Login (alag role)
- Assigned Sites list
- Visit Report add karna (date, notes)
- Photo upload (multiple)
- Issue Resolved mark karna
- Remaining AMC visits dikhna

## Folder Structure

```
amc_system/
├── admin/              -> Admin panel pages
├── technician/          -> Technician panel pages
├── assets/css, assets/js
├── config/db.php        -> Database connection settings (EDIT THIS)
├── config/config.php    -> Base URL settings (EDIT THIS)
├── database/schema.sql  -> Import this in phpMyAdmin first
├── includes/            -> Shared header/footer/auth code
├── install/seed.php     -> Run once to create default logins, then DELETE
├── uploads/visit_photos -> Technician uploaded photos save hoti hain
├── index.php            -> Login page (admin + technician dono)
└── logout.php
```

## Setup Steps (Shared Hosting - cPanel)

1. **Upload files**
   - Poora `amc_system` folder cPanel File Manager se apne `public_html` (ya subfolder) me upload karein.
   - Ya `.zip` upload karke "Extract" karein.

2. **Database banayein**
   - cPanel > MySQL Databases > naya database banayein (e.g. `yourid_amc`)
   - Naya MySQL user banayein aur database se attach karein (All Privileges)
   - phpMyAdmin kholein, apna database select karein, aur **Import** tab me
     `database/schema.sql` file import kar dein.

3. **Database connect karein**
   - `config/db.php` file kholein aur apne hosting ke actual values daalein:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_NAME', 'yourid_amc');
     define('DB_USER', 'yourid_dbuser');
     define('DB_PASS', 'your_db_password');
     ```

4. **Base URL set karein**
   - `config/config.php` me apni site ka actual URL daalein:
     ```php
     define('BASE_URL', 'https://yourdomain.com/amc_system');
     ```
     (Agar root par upload kiya hai to `/amc_system` hata dein)

5. **Default login banayein**
   - Browser me kholein: `https://yourdomain.com/amc_system/install/seed.php`
   - "Create Default Logins" button dabayein.
   - Ye 2 logins bana dega:
     - **Admin:** admin@amc.com / admin123
     - **Technician:** tech@amc.com / tech123
   - **Zaroori:** Isके baad `install/` folder File Manager se DELETE kar dein (security ke liye).

6. **Login karein aur password badal lein**
   - `index.php` par jaakar login karein.
   - Admin panel > Technicians page se naye technician bhi add kar sakte hain.
   - (Password change feature abhi nahi hai — chahen to database me user ka
     naya `password_hash()` generate karke update kar sakte hain, ya request
     karein to yeh feature add kar diya jayega.)

## Important Security Notes

- `install/seed.php` use karne ke turant baad **delete** kar dein.
- Apna `config/db.php` kabhi public GitHub repo me na daalein.
- Uploads folder me sirf image files allow hain (`.htaccess` se protected hai).
- Production me hamesha HTTPS use karein.

## Notes on Tech Stack

- **Frontend:** Plain HTML + CSS + minimal JS (no framework, fast loading)
- **Backend:** Core PHP with PDO (no framework — easy to host anywhere, even
  cheapest shared hosting jaise Hostinger, GoDaddy, BigRock etc.)
- **Database:** MySQL
- Koi Composer/NPM build step nahi hai — bas files upload karo aur chal jayega.

## Possible Future Additions (agar chahiye to bata dein, add kar denge)

- Admin/Technician password change + forgot password
- SMS/WhatsApp API se auto reminder jab AMC expire hone wala ho
- PDF invoice download (currently print-to-PDF via browser)
- Multi-admin roles / permissions
- Customer self-login portal
