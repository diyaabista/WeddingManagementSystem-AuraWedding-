💍 AuraWedding — Wedding Event Management System

A full-stack Wedding Event Management System built using PHP, MySQL, HTML, CSS, and JavaScript.
It provides a complete solution for managing wedding events, bookings, guest coordination, and admin control.

🚀 Features
👤 User Features
Secure user registration & login
Wedding event booking system
Multi-ceremony management (Haldi, Mehendi, Sangeet, Vivaha, Reception)
Guest list & RSVP tracking
Catering & decoration selection
Wedding planner checklist & budget calculator
Personal dashboard with booking status
“My Bookings” tracking system
🔐 Admin Features 
Admin dashboard with analytics
Manage bookings (confirm / reject / complete)
User management system
Wedding package management
Gallery management (add/edit/delete images)
Contact message handling
🛠 Tech Stack
Frontend: HTML5, CSS3, JavaScript
Backend: PHP
Database: MySQL
Server: Apache / Nginx (XAMPP, WAMP, LAMP supported)
📁 Project Structure
AuraWedding/
├── index.php
├── database.sql
├── includes/
│   ├── db.php
│   ├── auth.php
│   ├── header.php
│   └── footer.php
├── pages/
├── admin/
├── css/
├── js/
└── uploads/
⚙️ Installation Guide
1️⃣ Clone Repository
git clone https://github.com/your-username/AuraWedding.git
2️⃣ Move to Server Directory
XAMPP → htdocs/
WAMP → www/
Linux → /var/www/html/
3️⃣ Setup Database
Open: http://localhost/phpmyadmin
Create database: aurawedding
Import: database.sql
4️⃣ Configure Database

Edit includes/db.php:

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'aurawedding');
5️⃣ Run Project

Open in browser:

http://localhost/AuraWedding/
🔑 Demo Credentials
Role	Email	Password
Admin	admin@aurawedding.com
	password

⚠️ Change default credentials before production use.

🎨 UI Theme
Primary: #e91e63 (Pink)
Accent: #c9a227 (Gold)
Background: #fce4ec
📌 Notes
Uses bcrypt password hashing for security
Replace default images (Unsplash) with your own assets
Ensure proper folder permissions for uploads
No external frameworks required (pure PHP project)
📈 Future Improvements
Payment gateway integration
Email/SMS notifications
Multi-language support
Vendor marketplace integration
📄 License
