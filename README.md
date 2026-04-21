# AuraWedding - Complete Wedding Event Management System

## 🎊 Overview
AuraWedding is a full-stack wedding event management system designed for managing all aspects of Indian wedding ceremonies. It includes separate login systems for users and admins, complete CRUD operations for weddings, guests, events, and bookings.

---

## 🏗️ System Architecture

**Frontend:**
- HTML5, CSS3 (with custom properties and animations)
- Vanilla JavaScript (DHTML - Dynamic HTML)
- Responsive design with mobile-first approach

**Backend:**
- PHP 7.4+ with MySQLi
- RESTful API design
- Secure session handling
- Input sanitization & validation

**Database:**
- MySQL 5.7+
- Normalized schema with foreign keys
- Indexes for performance

---

## 📋 Quick Setup

### 1. Database Setup
```bash
# Option A: Import via phpMyAdmin
# - Visit http://localhost/phpmyadmin
# - Go to Import tab
# - Select sql/aurawedding.sql
# - Click Go

# Option B: Run setup script
# - Visit http://localhost/AuraWedding/php/setup_db.php
```

### 2. Configure Database
Edit `php/config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'aurawedding');
```

### 3. Start Application
```
http://localhost/AuraWedding/index.html
```

---

## 🔐 Demo Credentials

**User Account:**
- Email: `user@aurawedding.com`
- Password: `wedding123`

**Admin Account:**
- Email: `admin@aurawedding.com`
- Password: `wedding123`

---

## ✨ Features

### User Features
✅ User & Admin separate login systems
✅ Wedding profile management (Bride, Groom, Venue, Budget)
✅ Sub-events CRUD (Haldi, Mehendi, Sangeet, Vivaha)
✅ Guest management with RSVP tracking
✅ Hotel booking for guests
✅ Browse 1000+ design gallery with filtering
✅ Mandap style selection with customization
✅ Catering menu selection
✅ Seat types planning (Normal Chair, Sofa Seating, Round Table)
✅ Gift/Shagun registry
✅ Live budget calculator
✅ Dashboard with timeline & statistics
✅ Add items to "My List" and submit booking
✅ Session management with role-based access

### Admin Features
✅ Admin login with admin verification
✅ View all client bookings
✅ Update booking status
✅ Track booking timeline
✅ System overview

---

## 📁 Project Structure

```
AuraWedding/
├── index.html                    # Main application (SPA)
├── admin.php                     # Admin dashboard
├── README.md                     # Documentation
│
├── php/
│   ├── config.php               # DB config & helpers
│   ├── auth.php                 # Login/Register/Logout
│   ├── events.php               # Event CRUD
│   ├── guests.php               # Guest CRUD
│   ├── admin_actions.php        # Admin operations
│   ├── book.php                 # Booking handler
│   ├── user_dashboard.php       # User dashboard page
│   └── setup_db.php             # DB initialization
│
├── sql/
│   └── aurawedding.sql          # Complete schema + seed data
│
├── css/
│   └── style.css                # Stylesheet (1000+ lines)
│
├── js/
│   └── app.js                   # JavaScript logic (1000+ lines)
│
└── images/
    ├── mehendi/                 # Mehendi design images
    └── sangeet/                 # Sangeet event images
```

---

## 🌐 API Endpoints

### Authentication
- `POST php/auth.php` with `action=login`
- `POST php/auth.php` with `action=register`
- `POST php/auth.php` with `action=logout`
- `POST php/auth.php` with `action=check`

### Events Management
- `GET php/events.php?action=list`
- `GET php/events.php?action=get&id=1`
- `POST php/events.php` with `action=create`
- `POST php/events.php` with `action=update`
- `POST php/events.php` with `action=delete`

### Guest Management
- `GET php/guests.php?action=list`
- `POST php/guests.php` with `action=create`
- `POST php/guests.php` with `action=update`
- `POST php/guests.php` with `action=delete`
- `GET php/guests.php?action=stats`

### Booking Management
- `POST php/book.php`
- `POST php/admin_actions.php` with `action=update_booking_status`

---

## 💾 Database Schema

### Tables
- **users** - User accounts (Host/Admin)
- **weddings** - Wedding details
- **sub_events** - Haldi, Mehendi, Sangeet, Vivaha
- **guests** - Guest list with RSVP
- **hotel_bookings** - Guest accommodation
- **catering_menu** - Menu items
- **selected_menu** - Wedding's menu choices
- **mandap_selections** - Mandap styling
- **gifts** - Gift registry
- **bookings** - Client booking submissions
- **gallery_images** - Design gallery

---

## 🎯 Key Features Explained

### 1. Authentication System
- Separate login for Users and Admins
- Password hashing with BCrypt
- Session-based authentication
- Role-based access control
- Secure logout

### 2. Event Management (CRUD)
- Create wedding sub-events
- Set dates, times, venues
- Add custom notes
- Edit event details
- Delete events
- View timeline in dashboard

### 3. Guest Management (CRUD)
- Add guests with relations
- Track RSVP status
- Add hotel bookings
- Update guest info
- Delete guests
- View statistics

### 4. Interactive Gallery
- Filter 1000+ designs by category
- Lightbox preview
- Add designs to personal list
- Categories: Mehendi, Sangeet, Haldi, Vivaha

### 5. Budget Calculator
- Real-time calculation (DHTML)
- Catering costs
- Venue, decoration, DJ, photography
- Per-guest cost calculation
- No page reload

### 6. Booking System
- Add items to "My List"
- Submit booking form
- Store booking with customer details
- Admin reviews bookings
- Update booking status

---

## 🎨 Design Features

### Color Scheme
- Primary: Pink (#ff6b9d)
- Secondary: Rose Gold (#c9956c)
- Accent: Gold (#d4a853)
- Text: Dark Brown (#3d1a2e)

### Animations
- Shake animation on main button
- Smooth transitions (0.3s)
- Hover effects on cards
- Loading spinner

### Responsive Design
- Mobile-first approach
- Flexbox & CSS Grid
- Breakpoints for tablets
- Touch-friendly buttons

---

## 🔒 Security Features

✅ **Password Security**
- BCrypt hashing
- Constant-time comparison

✅ **Input Protection**
- HTML entity encoding
- SQL prepared statements
- Input sanitization

✅ **Session Security**
- Server-side sessions
- Session timeout
- CSRF prevention

✅ **Validation**
- Client-side (JavaScript)
- Server-side (PHP)
- Email & phone format checks

---

## 📱 Browser Support

- Chrome (Latest) ✓
- Firefox (Latest) ✓
- Safari (Latest) ✓
- Edge (Latest) ✓
- Mobile browsers ✓

---

## 🚀 How to Use

### For Users

1. **Register Account**
   - Click "Begin Your Wedding Journey"
   - Fill name, email, password, wedding date

2. **Create Wedding**
   - Go to Dashboard
   - Edit Wedding Profile
   - Enter bride, groom, venue, budget

3. **Add Events**
   - Dashboard → Add Sub Event
   - Create Haldi, Mehendi, Sangeet, Vivaha

4. **Manage Guests**
   - Dashboard → Add Guest
   - Fill guest details
   - Track RSVP status

5. **Plan Details**
   - Browse galleries
   - Select mandap style
   - Choose catering items
   - Plan seating

6. **Submit Booking**
   - Click "Book Now"
   - Select items
   - Submit form
   - Admin reviews

### For Admins

1. **Login**
   - Use admin credentials
   - Redirected to admin panel

2. **Manage Bookings**
   - View all bookings
   - Update status
   - Track timeline

---

## 🧪 Testing

### Test Cases
1. User Registration & Login
2. Event CRUD operations
3. Guest management
4. Gallery filtering
5. Budget calculation
6. Booking submission
7. Admin approval workflow
8. Session persistence
9. Mobile responsiveness
10. Form validation

---

## 🐛 Troubleshooting

| Issue | Solution |
|-------|----------|
| Database connection error | Verify MySQL is running, check config.php |
| Login not working | Verify database has user table with seed data |
| Images not showing | Check images/ folder exists |
| Session lost | Ensure session_start() is called |
| CORS errors | Check php/ files are accessible |

---

## 📝 Technical Highlights

- **1000+ lines of CSS** - Custom styling, animations, responsive design
- **1500+ lines of JavaScript** - Gallery filter, calculator, validation, CRUD
- **500+ lines of PHP** - Authentication, CRUD operations, sessions
- **Complete SQL schema** - 11 tables with relationships
- **Single Page Application** - Fast navigation without reload
- **Progressive Enhancement** - Works with and without JavaScript
- **Accessible** - Semantic HTML, ARIA labels
- **Mobile Optimized** - Touch-friendly, responsive

---

## 🔄 Project Flow

```
User
  ↓
Register / Login (auth.php)
  ↓
Dashboard (index.html + events.php)
  ├─ Add Events (sub_events table)
  ├─ Add Guests (guests table)
  ├─ Plan Details (mandap, catering)
  └─ Submit Booking (bookings table)
  ↓
Admin
  ↓
Review Booking (admin.php + admin_actions.php)
  ↓
Approve / Update Status
```

---

## 💡 Key Achievements

✓ Complete CRUD operations for all entities
✓ Session-based authentication with roles
✓ Interactive gallery with 1000+ items
✓ Real-time budget calculator (no reload)
✓ Responsive mobile design
✓ Secure password handling
✓ SQL injection prevention
✓ User-friendly dashboard
✓ Admin booking management
✓ Professional UI/UX

---

## 📚 Technologies Used

| Category | Technology |
|----------|-----------|
| Frontend | HTML5, CSS3, JavaScript (ES6+) |
| Backend | PHP 7.4+, MySQLi |
| Database | MySQL 5.7+ |
| Security | BCrypt, Prepared Statements |
| Architecture | MVC-style, RESTful API |

---

## 🎓 Learning Outcomes

This project demonstrates:
- Full-stack web development
- Database design & normalization
- Authentication & authorization
- Input validation & sanitization
- DHTML techniques
- Responsive design
- Security best practices
- API design

---

## 📄 License
Educational & Demonstration Purpose

---

**Built with 💕 for Indian Weddings** | Last Updated: April 2026


### Step 4: Place Project
Copy the entire `AuraWedding/` folder into your server's web root:
- **XAMPP:** `C:/xampp/htdocs/AuraWedding/`
- **WAMP:** `C:/wamp64/www/AuraWedding/`
- **Linux:** `/var/www/html/AuraWedding/`

### Step 5: Run
Open your browser:
```
http://localhost/AuraWedding/index.html
```

### Demo Login
- **Email:** host@aurawedding.com
- **Password:** wedding123

---

## 📚 Features Checklist (Mark Criteria)

### HTML/CSS — 10 Marks ✅
| Feature | Implementation |
|---------|---------------|
| CSS Box Model | Every element uses `padding`, `border`, `margin` explicitly documented |
| `border-radius` | 15px–50px "Bubbly" on all cards, buttons, inputs |
| `box-shadow` | 3 levels: `shadow-sm`, `shadow-md`, `shadow-lg` |
| `@keyframes shake` | Main marriage button shakes continuously |
| `@keyframes float` | Haldi/Mehendi/Sangeet/Vivaha buttons float |
| `@keyframes pulseGlow` | Glow effect on budget total |
| `@keyframes bounceIn` | Modal open animation |
| `@keyframes shimmer` | Gold shimmer text effect |
| Flexbox Layout | Navigation, gallery grid, cards, forms |
| Responsive Design | Media queries for mobile/tablet |

### JavaScript/DHTML — 10 Marks ✅
| Feature | Implementation |
|---------|---------------|
| Gallery Filter | Click filter → shows category designs (Bridal/Arabic/Indo-Western) |
| 1000+ Gallery | Programmatically generated items per category |
| Live Budget Calculator | Instant total update on input change (no refresh) |
| Form Validation | RSVP form, Hotel form, Guest form, Login — all validated |
| DHTML | DOM manipulation for gallery, tabs, modals, toasts |
| AJAX/Fetch API | All PHP calls via JavaScript fetch() |

### PHP/MySQL — 20 Marks ✅
| Feature | Implementation |
|---------|---------------|
| PHP Sessions | `session_start()`, `$_SESSION` for login persistence |
| User Authentication | Login, Register, Logout via `php/auth.php` |
| Relational Schema | `weddings` ← `sub_events`, `guests`, `hotel_bookings` all linked by foreign keys |
| CRUD - Create | Add guests, sub-events, hotel bookings |
| CRUD - Read | List guests, events, menu, gallery |
| CRUD - Update | Edit guest info, hotel room, event details |
| CRUD - Delete | Remove guests (cancellations) |
| Hospitality Module | `hotel_bookings` table: guest ↔ hotel ↔ room |
| Catering Selection | `catering_menu` + `selected_menu` per wedding |
| Mandap Tracker | `mandap_selections` table per wedding |
| Gift Registry | `gifts` table with amounts |

---

## 🎓 Viva Talking Points

**On PHP Sessions:**
> "I used PHP's built-in `session_start()` and `$_SESSION` to persist the host's login state across pages. `requireLogin()` in config.php redirects unauthenticated users automatically."

**On MySQL Relational Schema:**
> "My database uses a relational design where one `weddings.id` is the parent key for `sub_events`, `guests`, `hotel_bookings`, `mandap_selections`, and `gifts` — all linked via FOREIGN KEY constraints with CASCADE deletes."

**On CSS Box Model:**
> "Every container, button, and card in the project explicitly uses all four box model properties: content width, padding for inner spacing, border for visual boundaries, and margin for outer spacing. I documented these in comments throughout style.css."

**On JavaScript DHTML:**
> "The gallery filter dynamically shows/hides DOM elements without any page reload. The budget calculator listens to input events and recalculates in real-time. Form validation runs client-side before any server call is made."

**On Keyframe Animations:**
> "I implemented `@keyframes shake` for the main marriage button to create a continuous attention-grabbing effect. Secondary event buttons use `@keyframes float` via `transform: translateY(-8px)` for a bubbly hover interaction."

---

## 🌸 Theme Details

- **Primary:** `#ff6b9d` (Pink Main)
- **Accent:** `#c9956c` (Rose Gold) + `#d4a853` (Gold)
- **Background:** `#fff0f5` (Pink Light Cream)
- **Font:** Playfair Display (headings) + Quicksand (body)
- **Border Radius:** 15px / 25px / 35px / 50px (Bubbly scale)

---

*AuraWedding — Built for the Complete Multistage Marriage Management project*
