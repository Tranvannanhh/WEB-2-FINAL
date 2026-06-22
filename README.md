# VNUIS Campus Booking System

A production-ready campus facility booking platform built with PHP 8, MySQL, Bootstrap 5, and modern web technologies.

---

## 📋 Requirements

| Component | Version |
|-----------|---------|
| XAMPP     | 8.x (PHP 8.0+, MySQL 8.0+, Apache 2.4+) |
| Browser   | Chrome 90+, Firefox 88+, Edge 90+, Safari 14+ |

---

## 🚀 Installation Guide (XAMPP)

### Step 1 – Copy Files
Place the project folder at:
```
C:\xampp\htdocs\final\campus-booking-system\
```

### Step 2 – Start Services
Open **XAMPP Control Panel** and start:
- ✅ **Apache**
- ✅ **MySQL**

### Step 3 – Import Database
1. Open [http://localhost/phpmyadmin](http://localhost/phpmyadmin)
2. Click **New** → Database name: `campus_booking` → Collation: `utf8mb4_unicode_ci` → **Create**
3. Select the `campus_booking` database
4. Click **Import** tab
5. Choose file: `database/campus_booking.sql`
6. Click **Go**

### Step 4 – Configure (if needed)
Edit `config/config.php`:
```php
define('APP_URL', 'http://localhost/final/campus-booking-system');
define('DB_HOST', 'localhost');
define('DB_NAME', 'campus_booking');
define('DB_USER', 'root');
define('DB_PASS', '');  // Change if your MySQL has a password
```

### Step 5 – Access the App
Open: [http://localhost/final/campus-booking-system](http://localhost/final/campus-booking-system)

---

## 🔐 Demo Credentials

| Role     | Email                      | Password   |
|----------|----------------------------|------------|
| Admin    | admin@vnuis.edu.vn         | admin123   |
| Student  | student@vnuis.edu.vn       | admin123   |
| Lecturer | lecturer@vnuis.edu.vn      | admin123   |
| Student  | duc.le@vnuis.edu.vn        | admin123   |
| Student  | mai.pham@vnuis.edu.vn      | admin123   |

---

## 📁 Folder Structure

```
campus-booking-system/
├── api/
│   └── notifications.php        # AJAX API endpoint
├── assets/
│   ├── css/style.css            # Main stylesheet (theme, layout, components)
│   └── js/main.js               # Main JavaScript (sidebar, AJAX, charts)
├── config/
│   ├── config.php               # App constants (URL, session, timezone)
│   └── db.php                   # PDO database singleton
├── controllers/
│   ├── AuthController.php       # Login, logout, register
│   ├── BookingController.php    # Create, cancel, conflict check
│   └── AdminController.php      # Admin approval, report update
├── database/
│   └── campus_booking.sql       # Full schema + sample data
├── includes/
│   ├── auth.php                 # requireLogin, requireAdmin, requireRole
│   ├── functions.php            # Utilities (sanitize, flash, badges, etc.)
│   ├── header.php               # HTML head, CDN links
│   ├── footer.php               # Scripts, Bootstrap JS, Chart.js
│   ├── navbar.php               # Top navigation bar with notifications
│   └── sidebar.php              # Role-aware side navigation
├── models/
│   ├── User.php                 # User CRUD, auth helpers
│   ├── Booking.php              # Booking CRUD, conflict check, stats
│   ├── Facility.php             # Facility CRUD, search, availability
│   ├── Equipment.php            # Equipment CRUD per facility
│   ├── Notification.php         # Notification CRUD, unread count
│   ├── Review.php               # Review CRUD, rating aggregation
│   └── Report.php               # Issue report CRUD, status tracking
├── uploads/
│   ├── avatars/                 # User profile pictures
│   └── facilities/              # Facility images
├── views/
│   ├── auth/
│   │   ├── login.php            # Sign in page
│   │   └── register.php         # Registration page
│   ├── dashboard/
│   │   ├── admin.php            # Admin dashboard with KPIs & charts
│   │   └── index.php            # Student/Lecturer dashboard
│   ├── facilities/
│   │   ├── index.php            # Browse facilities (search/filter/grid)
│   │   ├── view.php             # Facility detail + reviews
│   │   ├── manage.php           # Admin: manage all facilities
│   │   ├── form.php             # Admin: add/edit facility form
│   │   ├── equipment.php        # Admin: manage facility equipment
│   │   └── delete.php           # Admin: delete facility action
│   ├── bookings/
│   │   ├── create.php           # New booking form (with conflict check)
│   │   ├── index.php            # My bookings list
│   │   ├── view.php             # Booking detail + approval log
│   │   ├── manage.php           # Admin: all bookings management
│   │   ├── approve.php          # Admin: approve/reject interface
│   │   └── review.php           # Write a review (completed bookings)
│   ├── notifications/
│   │   └── index.php            # All notifications
│   ├── profile/
│   │   └── index.php            # Edit profile, change password, avatar
│   ├── reports/
│   │   ├── facility.php         # Report/manage facility issues
│   │   └── index.php            # Admin: analytics & charts
│   └── users/
│       └── manage.php           # Admin: user management
└── index.php                    # Entry point (redirects by role)
```

---

## 🗄️ Database ERD (Entity Relationship Diagram)

```
users ──────────────────────────────────────────────────────┐
  │ id, full_name, email, password, role,                   │
  │ student_code, phone, avatar, is_active                  │
  │                                                         │
  ├──< bookings >──< facilities                             │
  │     id, user_id, facility_id                            │
  │     booking_date, start_time, end_time                  │
  │     purpose, status                                     │
  │        │                  │                             │
  │        ├──< approval_logs │── equipment                 │
  │        │    booking_id    │   facility_id, name         │
  │        │    admin_id      │   quantity, status          │
  │        │    action, note  │                             │
  │        ├──< reviews            facility_id, image_path  │
  │        │    booking_id                                   │
  │        │    user_id, rating                             │
  │        │                                                │
  ├──< notifications                                         │
  │     user_id, title, message, is_read                    │
  │                                                         │
  └──< reports ────────────────────────────────────────────┘
        user_id, facility_id
        issue_description, report_status, admin_note
```

---

## ✨ Features Overview

### Student / Lecturer
- Register & login with secure bcrypt password hashing
- Browse and search facilities with filters
- Book facilities with real-time conflict detection
- View booking history with status tracking
- Cancel pending bookings
- Write reviews for completed bookings
- Report facility issues
- Receive real-time notifications
- Manage profile & change password

### Admin
- Full dashboard with KPI cards and 5 chart types
- Complete user management (CRUD, role change, toggle active)
- Full facility management with image upload
- Equipment management per facility
- Approve / Reject bookings with notes
- Analytics & reports page
- Issue report management with status updates
- System-wide notifications

---

## 🛡️ Security Features

- PDO prepared statements (no SQL injection)
- `password_hash()` + `password_verify()` (bcrypt)
- Session-based authentication with timeout
- Role-based access control (RBAC)
- HTML output escaping with `htmlspecialchars`
- CSRF token generation utility
- Server-side + client-side validation
- File upload type/size validation

---

## 🎨 Tech Stack

| Layer      | Technology |
|------------|-----------|
| Backend    | PHP 8.x, PDO, MVC pattern |
| Frontend   | HTML5, CSS3, Bootstrap 5.3 |
| Database   | MySQL 8 / MariaDB 10.3+ |
| Charts     | Chart.js 4.4 |
| Icons      | Font Awesome 6.4 |
| Animations | AOS (Animate on Scroll) |
| Dialogs    | SweetAlert2 |
| Tables     | DataTables 1.13 |
| Ajax       | jQuery 3.7 |
| Fonts      | Google Fonts – Poppins |

---

*Built for VNUIS · Version 1.0.0 · PHP MVC Architecture*
=======
CAMPUS BOOKING SYSTEM
>>>>>>> 061c5a8098ba5a196aaf2c32d2c1369c7b7c9385
