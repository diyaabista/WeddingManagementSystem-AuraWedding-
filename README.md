# 💍 AuraWedding — Complete Wedding Management System

A full-featured PHP + MySQL wedding event management website with user portal, admin dashboard, booking system, and multi-ceremony support.

---

## 🚀 Installation Guide

### Requirements
- PHP 7.4+ (or 8.x)
- MySQL 5.7+ / MariaDB
- Apache or Nginx with mod_rewrite
- XAMPP / WAMP / LAMP recommended for local

---

### Step 1 — Copy Files
Copy the `AuraWedding` folder to your web server root:
- **XAMPP**: `C:/xampp/htdocs/AuraWedding`
- **WAMP**: `C:/wamp/www/AuraWedding`
- **Linux**: `/var/www/html/AuraWedding`

---

### Step 2 — Create Database
1. Open **phpMyAdmin** → `http://localhost/phpmyadmin`
2. Click **"New"** to create a database named `aurawedding`
3. Select the database and go to **"Import"**
4. Upload and run `database.sql`

---

### Step 3 — Configure Database
Edit `includes/db.php` and update credentials if needed:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');      // Your MySQL username
define('DB_PASS', '');          // Your MySQL password
define('DB_NAME', 'aurawedding');
```

---

### Step 4 — Run the Website
Open your browser and visit:
```
http://localhost/AuraWedding/
```

---

## 🔐 Login Credentials

| Role    | Email                        | Password   |
|---------|------------------------------|------------|
| Admin   | admin@aurawedding.com        | password   |
| Planner | planner@aurawedding.com      | password   |
| User    | Register via the website     | (your own) |

> **Note:** The default passwords use `password` as the string. These are hashed with bcrypt in the database.

---

## 📁 Project Structure

```
AuraWedding/
├── index.php               # Main router
├── database.sql            # Database setup (run this first!)
├── includes/
│   ├── db.php              # Database connection
│   ├── auth.php            # Auth helpers
│   ├── header.php          # Site header/navbar
│   └── footer.php          # Site footer
├── pages/
│   ├── home.php            # Homepage
│   ├── login.php           # Login & Register
│   ├── sangeet.php         # Sangeet ceremony
│   ├── mehendi.php         # Mehendi ceremony + gallery
│   ├── haldi.php           # Haldi ceremony
│   ├── vivaha.php          # Vivaha ceremony
│   ├── booking.php         # Booking form (login required)
│   ├── my-bookings.php     # User's bookings
│   ├── wedding-planner.php # Planning checklist & budget tool
│   └── contact.php         # Contact form
├── admin/
│   ├── index.php           # Admin dashboard
│   ├── bookings.php        # Manage all bookings
│   ├── users.php           # Manage users
│   ├── packages.php        # Manage wedding packages
│   ├── gallery.php         # Manage gallery images
│   ├── messages.php        # View contact messages
│   ├── header.php          # Admin header/sidebar
│   └── footer.php          # Admin footer
├── css/
│   └── style.css           # All styles
├── js/
│   └── main.js             # Frontend JavaScript
└── uploads/
    └── bookings/           # Booking attachments (future use)
```

---

## ✨ Features

### Public Website
- 🏠 Beautiful home page with hero section, packages, testimonials
- 🎵 **Sangeet** ceremony page with gallery
- 🌿 **Mehendi** ceremony with filterable gallery (Venue / Front-side / Back-side)
- 🌻 **Haldi** ceremony page
- 👰 **Vivaha** ceremony with timeline
- 📋 **Wedding Planner** checklist + budget estimator
- 📞 Contact form

### User System
- 👤 User registration and login
- 💍 **Booking form** with ceremony selection, package choice, date, venue, budget
- 📋 **My Bookings** page — track status, see planner notes
- Unique booking code per submission

### Admin Dashboard
- 📊 Dashboard with live stats (total bookings, pending, confirmed, users, messages)
- 📋 **Bookings Management** — filter by status, view details, confirm/reject/complete
- 👥 **Users Management** — add, delete, change roles
- 📦 **Packages Management** — add/edit/delete/toggle wedding packages
- 🖼️ **Gallery Management** — add/remove/hide images per ceremony
- ✉️ **Messages** — read contact form submissions, reply via email

---

## 🎨 Colour Theme
- Primary Pink: `#e91e63`
- Gold Accent: `#c9a227`
- Dark: `#3d1c2e`
- Background: `#fce4ec`

---

## 🛠 Built With
- **PHP** — server-side logic
- **MySQL** — database
- **HTML5 / CSS3** — frontend
- **Vanilla JavaScript** — interactivity
- **Google Fonts** — Poppins + Playfair Display
- **Unsplash** — placeholder gallery images

---

## 📝 Notes
- Gallery images use Unsplash URLs by default. You can replace with your own uploads.
- For production, change all default passwords immediately.
- To enable file uploads, configure `uploads/` directory permissions.
- Session is used for auth; no external dependencies required.

---

*Made with ❤️ for beautiful Indian weddings*
