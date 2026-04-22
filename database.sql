-- ============================================================
-- AuraWedding Database Schema - Complete Wedding Management
-- ============================================================

DROP DATABASE IF EXISTS aurawedding;
CREATE DATABASE IF NOT EXISTS aurawedding CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE aurawedding;

-- ---- USERS (Wedding Hosts, Planners & Admins) ----
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    role ENUM('user','admin','planner') DEFAULT 'user',
    wedding_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role)
);

-- ---- WEDDINGS ----
CREATE TABLE IF NOT EXISTS weddings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    bride_name VARCHAR(100),
    groom_name VARCHAR(100),
    venue VARCHAR(255),
    wedding_date DATE,
    total_budget DECIMAL(12,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_wedding_date (wedding_date)
);

-- ---- SUB EVENTS (Sangeet, Mehendi, Haldi, Vivaha) ----
CREATE TABLE IF NOT EXISTS sub_events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    wedding_id INT NOT NULL,
    event_type ENUM('sangeet','mehendi','haldi','vivaha') NOT NULL,
    event_date DATE,
    venue VARCHAR(255),
    time_start TIME,
    time_end TIME,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (wedding_id) REFERENCES weddings(id) ON DELETE CASCADE,
    INDEX idx_wedding_id (wedding_id),
    INDEX idx_event_type (event_type)
);

-- ---- GUESTS ----
CREATE TABLE IF NOT EXISTS guests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    wedding_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    email VARCHAR(150),
    rsvp_status ENUM('pending','confirmed','declined') DEFAULT 'pending',
    side ENUM('bride','groom','both') DEFAULT 'both',
    relation VARCHAR(100),
    dietary_preference VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (wedding_id) REFERENCES weddings(id) ON DELETE CASCADE,
    INDEX idx_wedding_id (wedding_id),
    INDEX idx_rsvp_status (rsvp_status)
);

-- ---- HOTEL BOOKINGS ----
CREATE TABLE IF NOT EXISTS hotel_bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    guest_id INT NOT NULL,
    hotel_name VARCHAR(150),
    room_number VARCHAR(20),
    check_in DATE,
    check_out DATE,
    room_type VARCHAR(50),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (guest_id) REFERENCES guests(id) ON DELETE CASCADE,
    INDEX idx_guest_id (guest_id)
);

-- ---- PACKAGES TABLE ----
CREATE TABLE IF NOT EXISTS packages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    ceremony_type ENUM('sangeet','mehendi','haldi','vivaha','full') NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    duration VARCHAR(50),
    features TEXT,
    image VARCHAR(255),
    is_active TINYINT(1) DEFAULT 1
);

-- ---- GALLERY (Ceremony-wise images) ----
CREATE TABLE IF NOT EXISTS gallery (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150),
    ceremony_type ENUM('sangeet','mehendi','haldi','vivaha') NOT NULL,
    sub_category VARCHAR(100),
    image_url VARCHAR(255) NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ceremony_type (ceremony_type),
    INDEX idx_sub_category (sub_category)
);

-- ---- MANDAP SELECTION ----
CREATE TABLE IF NOT EXISTS mandap_selections (
    id INT AUTO_INCREMENT PRIMARY KEY,
    wedding_id INT NOT NULL,
    mandap_style VARCHAR(100),
    flower_theme VARCHAR(100),
    color_scheme VARCHAR(100),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (wedding_id) REFERENCES weddings(id) ON DELETE CASCADE,
    INDEX idx_wedding_id (wedding_id)
);

-- ---- CATERING MENU ----
CREATE TABLE IF NOT EXISTS catering_menu (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category ENUM('starters','main_course','desserts','drinks','live_counter','breads') NOT NULL,
    item_name VARCHAR(150) NOT NULL,
    description TEXT,
    price_per_plate DECIMAL(8,2),
    is_veg TINYINT(1) DEFAULT 1,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_category (category)
);

-- ---- SELECTED MENU (per wedding) ----
CREATE TABLE IF NOT EXISTS selected_menu (
    id INT AUTO_INCREMENT PRIMARY KEY,
    wedding_id INT NOT NULL,
    menu_id INT NOT NULL,
    quantity INT DEFAULT 1,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (wedding_id) REFERENCES weddings(id) ON DELETE CASCADE,
    FOREIGN KEY (menu_id) REFERENCES catering_menu(id) ON DELETE CASCADE,
    INDEX idx_wedding_id (wedding_id)
);

-- ---- GIFTS & REGISTRY ----
CREATE TABLE IF NOT EXISTS gifts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    wedding_id INT NOT NULL,
    guest_id INT,
    gift_type ENUM('shagun','item','cash') DEFAULT 'shagun',
    description VARCHAR(255),
    amount DECIMAL(10,2) DEFAULT 0,
    received_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (wedding_id) REFERENCES weddings(id) ON DELETE CASCADE,
    FOREIGN KEY (guest_id) REFERENCES guests(id) ON DELETE SET NULL,
    INDEX idx_wedding_id (wedding_id)
);

-- ---- BOOKINGS (Complete Wedding Packages) ----
CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    booking_code VARCHAR(20) UNIQUE,
    bride_name VARCHAR(100) NOT NULL,
    groom_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    wedding_date DATE NOT NULL,
    ceremony_types VARCHAR(255) NOT NULL,
    venue VARCHAR(255),
    guest_count INT,
    package_id INT,
    special_requests TEXT,
    budget DECIMAL(12,2),
    status ENUM('pending','confirmed','rejected','completed') DEFAULT 'pending',
    admin_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (package_id) REFERENCES packages(id) ON DELETE SET NULL,
    INDEX idx_email (email),
    INDEX idx_status (status),
    INDEX idx_user_id (user_id)
);

-- ---- BOOKING EVENTS (Events in a booking) ----
CREATE TABLE IF NOT EXISTS booking_events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    event_type VARCHAR(50) NOT NULL,
    event_date DATE NOT NULL,
    event_time TIME,
    venue VARCHAR(255),
    guest_count INT DEFAULT 0,
    room_count INT DEFAULT 0,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    INDEX idx_booking_id (booking_id),
    INDEX idx_event_date (event_date)
);

-- ---- BOOKING GUESTS (Guest list in a booking) ----
CREATE TABLE IF NOT EXISTS booking_guests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    event_id INT NOT NULL,
    guest_name VARCHAR(100) NOT NULL,
    guest_phone VARCHAR(20),
    guest_email VARCHAR(150),
    dietary_preference VARCHAR(50),
    rsvp_status ENUM('pending','confirmed','declined') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    FOREIGN KEY (event_id) REFERENCES booking_events(id) ON DELETE CASCADE,
    INDEX idx_booking_id (booking_id),
    INDEX idx_event_id (event_id)
);

-- ---- BOOKING ROOMS (Hotel rooms in a booking) ----
CREATE TABLE IF NOT EXISTS booking_rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_event_id INT NOT NULL,
    hotel_name VARCHAR(150) NOT NULL,
    room_count INT DEFAULT 1,
    room_type VARCHAR(50),
    check_in DATE NOT NULL,
    check_out DATE NOT NULL,
    price_per_room DECIMAL(10,2) DEFAULT 0,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_event_id) REFERENCES booking_events(id) ON DELETE CASCADE,
    INDEX idx_event_id (booking_event_id)
);

-- ---- MESSAGES / CONTACT TABLE ----
CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    email VARCHAR(150),
    phone VARCHAR(20),
    subject VARCHAR(200),
    message TEXT,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ---- USER CUSTOM DESIGNS (User uploads during booking) ----
CREATE TABLE IF NOT EXISTS user_designs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    booking_id INT,
    ceremony_type ENUM('sangeet','mehendi','haldi','vivaha') NOT NULL,
    design_type ENUM('mehendi','decor','venue','other') DEFAULT 'mehendi',
    image_url VARCHAR(255) NOT NULL,
    description TEXT,
    is_approved TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
);

-- ---- PLAYLISTS (Music playlists for events) ----
CREATE TABLE IF NOT EXISTS playlists (
    id INT AUTO_INCREMENT PRIMARY KEY,
    wedding_id INT NOT NULL,
    event_id INT,
    playlist_name VARCHAR(150) NOT NULL,
    description TEXT,
    event_type ENUM('sangeet','mehendi','haldi','reception') DEFAULT 'sangeet',
    mood VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (wedding_id) REFERENCES weddings(id) ON DELETE CASCADE,
    FOREIGN KEY (event_id) REFERENCES sub_events(id) ON DELETE SET NULL,
    INDEX idx_wedding_id (wedding_id),
    INDEX idx_event_type (event_type)
);

-- ---- PLAYLIST SONGS (Songs in a playlist) ----
CREATE TABLE IF NOT EXISTS playlist_songs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    playlist_id INT NOT NULL,
    song_name VARCHAR(200) NOT NULL,
    artist_name VARCHAR(150),
    genre VARCHAR(50),
    duration_seconds INT,
    song_order INT DEFAULT 0,
    is_selected TINYINT(1) DEFAULT 1,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (playlist_id) REFERENCES playlists(id) ON DELETE CASCADE,
    INDEX idx_playlist_id (playlist_id),
    INDEX idx_song_order (song_order)
);

-- ============================================================
-- SEED DATA
-- ============================================================

-- Insert Admin & Planner users (password: "password")
INSERT INTO users (name, email, password, phone, role) VALUES
('Admin', 'admin@aurawedding.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '9999999999', 'admin'),
('Wedding Planner', 'planner@aurawedding.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '8888888888', 'planner');

-- Insert sample packages
INSERT INTO packages (name, ceremony_type, description, price, duration, features, image) VALUES
('Sangeet Celebration', 'sangeet', 'Complete Sangeet night management with music, dance floor, LED lights and DJ.', 45000, '1 Day', 'DJ Setup,Dance Floor,LED Lighting,Photo Booth,Floral Decor,Catering Setup', 'sangeet1.jpg'),
('Mehendi Magic', 'mehendi', 'Beautiful Mehendi ceremony with professional artists and floral decor.', 35000, '1 Day', 'Professional Mehendi Artists,Floral Backdrop,Photo Zone,Refreshments,Traditional Decor', 'mehendi1.jpg'),
('Golden Haldi', 'haldi', 'Traditional Haldi ceremony with marigold decorations and complete setup.', 30000, '1 Day', 'Marigold Decor,Haldi Setup,Photography,Fun Games,Traditional Ambiance,Catering', 'haldi1.jpg'),
('Royal Vivaha', 'vivaha', 'Grand wedding ceremony with mandap, floral arrangements and complete management.', 150000, '2 Days', 'Grand Mandap,Floral Arrangements,Bridal Entry,Varmala Setup,Photography,Videography,Catering', 'vivaha1.jpg'),
('Complete Wedding Package', 'full', 'Full wedding package covering all ceremonies from Sangeet to Vivaha.', 250000, '5 Days', 'All 4 Ceremonies,Complete Decor,Professional Photography,Videography,Catering,Coordination', 'full1.jpg');

-- Insert sample gallery images (using ONLY local images)
INSERT INTO gallery (title, ceremony_type, sub_category, image_url) VALUES
-- Mehendi Gallery - All real images
('Mehendi Design 1', 'mehendi', 'Mehendi Design', 'images/mehendi/real_1.jpg'),
('Mehendi Design 2', 'mehendi', 'Mehendi Design', 'images/mehendi/real_2.jpg'),
('Mehendi Design 3', 'mehendi', 'Mehendi Design', 'images/mehendi/real_3.jpg'),
('Mehendi Design 4', 'mehendi', 'Mehendi Design', 'images/mehendi/real_4.jpg'),
('Mehendi Design 5', 'mehendi', 'Mehendi Design', 'images/mehendi/real_5.jpg'),
('Mehendi Design 6', 'mehendi', 'Mehendi Design', 'images/mehendi/real_6.jpg'),
('Mehendi Design 7', 'mehendi', 'Mehendi Design', 'images/mehendi/real_7.jpg'),
('Mehendi Design 8', 'mehendi', 'Mehendi Design', 'images/mehendi/real_8.jpg'),
('Mehendi Design 9', 'mehendi', 'Mehendi Design', 'images/mehendi/real_9.jpg'),
('Mehendi Special 1', 'mehendi', 'Mehendi Design', 'images/mehendi/arabic_1.png'),
('Mehendi Bridal 1', 'mehendi', 'Mehendi Design', 'images/mehendi/bridal_0.png'),
('Mehendi Bridal 2', 'mehendi', 'Mehendi Design', 'images/mehendi/bridal_1.png'),
('Mehendi Indo-Western', 'mehendi', 'Mehendi Design', 'images/mehendi/indowestern_1.png'),
('Mehendi Venue 1', 'mehendi', 'Mehendi Venue', 'images/mehendi/real_10.jpg'),
('Mehendi Venue 2', 'mehendi', 'Mehendi Venue', 'images/mehendi/real_11.jpg'),
('Mehendi Venue 3', 'mehendi', 'Mehendi Venue', 'images/mehendi/real_12.jpg'),
('Mehendi Venue 4', 'mehendi', 'Mehendi Venue', 'images/mehendi/real_13.jpg'),
('Mehendi Venue 5', 'mehendi', 'Mehendi Venue', 'images/mehendi/real_14.jpg'),
('Mehendi Venue 6', 'mehendi', 'Mehendi Venue', 'images/mehendi/real_15.jpg'),
('Mehendi Venue 7', 'mehendi', 'Mehendi Venue', 'images/mehendi/real_16.jpg'),
('Mehendi Venue 8', 'mehendi', 'Mehendi Venue', 'images/mehendi/real_17.jpg'),
('Mehendi Venue 9', 'mehendi', 'Mehendi Venue', 'images/mehendi/real_18.jpg'),
('Mehendi Venue 10', 'mehendi', 'Mehendi Venue', 'images/mehendi/real_19.jpg'),
('Mehendi Venue 11', 'mehendi', 'Mehendi Venue', 'images/mehendi/real_20.jpg'),
-- Front-side Mehendi Gallery
('Front Mehendi 1', 'mehendi', 'Front-side Mehendi', 'images/frontsidemehendidesign/Screenshot 2026-04-19 193829.png'),
('Front Mehendi 2', 'mehendi', 'Front-side Mehendi', 'images/frontsidemehendidesign/Screenshot 2026-04-19 193845 - Copy.png'),
('Front Mehendi 3', 'mehendi', 'Front-side Mehendi', 'images/frontsidemehendidesign/Screenshot 2026-04-19 193859 - Copy.png'),
('Front Mehendi 4', 'mehendi', 'Front-side Mehendi', 'images/frontsidemehendidesign/Screenshot 2026-04-19 193911 - Copy.png'),
('Front Mehendi 5', 'mehendi', 'Front-side Mehendi', 'images/frontsidemehendidesign/Screenshot 2026-04-19 193927 - Copy.png'),
('Front Mehendi 6', 'mehendi', 'Front-side Mehendi', 'images/frontsidemehendidesign/Screenshot 2026-04-19 193951 - Copy.png'),
-- Back-side Mehendi Gallery
('Back Mehendi 1', 'mehendi', 'Back-side Mehendi', 'images/backsidemehendidesign/Screenshot 2026-04-19 193206.png'),
('Back Mehendi 2', 'mehendi', 'Back-side Mehendi', 'images/backsidemehendidesign/Screenshot 2026-04-19 193437.png'),
('Back Mehendi 3', 'mehendi', 'Back-side Mehendi', 'images/backsidemehendidesign/Screenshot 2026-04-19 193445.png'),
('Back Mehendi 4', 'mehendi', 'Back-side Mehendi', 'images/backsidemehendidesign/Screenshot 2026-04-19 193501.png'),
('Back Mehendi 5', 'mehendi', 'Back-side Mehendi', 'images/backsidemehendidesign/Screenshot 2026-04-19 193516.png'),
('Back Mehendi 6', 'mehendi', 'Back-side Mehendi', 'images/backsidemehendidesign/Screenshot 2026-04-19 193531.png'),
('Back Mehendi 7', 'mehendi', 'Back-side Mehendi', 'images/backsidemehendidesign/Screenshot 2026-04-19 193542.png'),
('Back Mehendi 8', 'mehendi', 'Back-side Mehendi', 'images/backsidemehendidesign/Screenshot 2026-04-19 193606.png'),
('Back Mehendi 9', 'mehendi', 'Back-side Mehendi', 'images/backsidemehendidesign/real_4.jpg'),
('Back Mehendi 10', 'mehendi', 'Back-side Mehendi', 'images/backsidemehendidesign/real_5.jpg'),
('Back Mehendi 11', 'mehendi', 'Back-side Mehendi', 'images/backsidemehendidesign/real_6.jpg'),
('Back Mehendi 12', 'mehendi', 'Back-side Mehendi', 'images/backsidemehendidesign/arabic_1.png'),
('Back Mehendi 13', 'mehendi', 'Back-side Mehendi', 'images/backsidemehendidesign/bridal_0.png'),
-- Sangeet Gallery - All images
('Sangeet 1', 'sangeet', 'Sangeet Setup', 'images/sangeet/Screenshot 2026-04-19 172223.png'),
('Sangeet 2', 'sangeet', 'Sangeet Setup', 'images/sangeet/Screenshot 2026-04-19 172246.png'),
('Sangeet 3', 'sangeet', 'Sangeet Setup', 'images/sangeet/Screenshot 2026-04-19 172305.png'),
('Sangeet 4', 'sangeet', 'Sangeet Setup', 'images/sangeet/Screenshot 2026-04-19 172401.png'),
('Sangeet 5', 'sangeet', 'Sangeet Setup', 'images/sangeet/Screenshot 2026-04-19 172449.png'),
('Sangeet 6', 'sangeet', 'Sangeet Setup', 'images/sangeet/Screenshot 2026-04-19 172507.png'),
('Sangeet 7', 'sangeet', 'Sangeet Setup', 'images/sangeet/Screenshot 2026-04-19 172520.png'),
('Sangeet 8', 'sangeet', 'Sangeet Setup', 'images/sangeet/Screenshot 2026-04-19 172534.png'),
('Sangeet 9', 'sangeet', 'Sangeet Setup', 'images/sangeet/Screenshot 2026-04-19 172549.png'),
-- Haldi Gallery - All images
('Haldi 1', 'haldi', 'Haldi Setup', 'images/haldi/Screenshot 2026-04-19 115356.png'),
('Haldi 2', 'haldi', 'Haldi Setup', 'images/haldi/Screenshot 2026-04-19 115413.png'),
('Haldi 3', 'haldi', 'Haldi Setup', 'images/haldi/Screenshot 2026-04-19 115430.png'),
('Haldi 4', 'haldi', 'Haldi Setup', 'images/haldi/Screenshot 2026-04-19 115449.png'),
('Haldi 5', 'haldi', 'Haldi Setup', 'images/haldi/Screenshot 2026-04-19 115504.png'),
('Haldi 6', 'haldi', 'Haldi Setup', 'images/haldi/Screenshot 2026-04-19 115526.png'),
('Haldi 7', 'haldi', 'Haldi Setup', 'images/haldi/Screenshot 2026-04-19 115538.png'),
('Haldi 8', 'haldi', 'Haldi Setup', 'images/haldi/Screenshot 2026-04-19 115548.png'),
('Haldi 9', 'haldi', 'Haldi Setup', 'images/haldi/Screenshot 2026-04-19 115603.png'),
('Haldi 10', 'haldi', 'Haldi Setup', 'images/haldi/Screenshot 2026-04-19 122915.png'),
('Haldi 11', 'haldi', 'Haldi Setup', 'images/haldi/Screenshot 2026-04-19 122926.png'),
('Haldi 12', 'haldi', 'Haldi Setup', 'images/haldi/Screenshot 2026-04-19 122938.png'),
('Haldi 13', 'haldi', 'Haldi Setup', 'images/haldi/Screenshot 2026-04-19 122947.png'),
('Haldi 14', 'haldi', 'Haldi Setup', 'images/haldi/Screenshot 2026-04-19 122958.png'),
('Haldi 15', 'haldi', 'Haldi Setup', 'images/haldi/Screenshot 2026-04-19 123020.png'),
('Haldi 16', 'haldi', 'Haldi Setup', 'images/haldi/Screenshot 2026-04-19 123049.png'),
-- Vivaha Gallery - All images
('Vivaha 1', 'vivaha', 'Wedding Setup', 'images/mandapvenue/Screenshot 2026-04-19 214833.png'),
('Vivaha 2', 'vivaha', 'Wedding Setup', 'images/mandapvenue/Screenshot 2026-04-19 215058.png'),
('Vivaha 3', 'vivaha', 'Wedding Setup', 'images/mandapvenue/Screenshot 2026-04-19 215117.png'),
('Vivaha 4', 'vivaha', 'Wedding Setup', 'images/mandapvenue/Screenshot 2026-04-19 215136.png'),
('Vivaha 5', 'vivaha', 'Wedding Setup', 'images/mandapvenue/Screenshot 2026-04-19 215156.png'),
('Vivaha 6', 'vivaha', 'Wedding Setup', 'images/mandapvenue/Screenshot 2026-04-19 215215.png'),
('Vivaha 7', 'vivaha', 'Wedding Setup', 'images/mandapvenue/Screenshot 2026-04-19 215336.png'),
('Vivaha 8', 'vivaha', 'Wedding Setup', 'images/mandapvenue/Screenshot 2026-04-19 215603.png'),
('Vivaha 9', 'vivaha', 'Wedding Setup', 'images/mandapvenue/Screenshot 2026-04-19 215622.png'),
('Vivaha 10', 'vivaha', 'Wedding Setup', 'images/mandapvenue/Screenshot 2026-04-19 215636.png'),
('Vivaha 11', 'vivaha', 'Wedding Setup', 'images/mandapvenue/Screenshot 2026-04-19 215652.png');
