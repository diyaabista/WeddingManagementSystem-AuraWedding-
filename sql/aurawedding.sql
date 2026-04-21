-- ============================================================
-- AuraWedding Database Schema - Complete
-- ============================================================

DROP DATABASE IF EXISTS aurawedding;
CREATE DATABASE IF NOT EXISTS aurawedding CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE aurawedding;

-- ---- USERS (Wedding Hosts & Admins) ----
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    wedding_date DATE,
    is_admin TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_is_admin (is_admin)
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

-- ---- GALLERY IMAGES ----
CREATE TABLE IF NOT EXISTS gallery_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category VARCHAR(50) NOT NULL,
    image_url VARCHAR(255),
    title VARCHAR(150),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_category (category)
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
    category ENUM('starters','main_course','desserts','drinks','live_counter') NOT NULL,
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
    wedding_id INT,
    client_name VARCHAR(100) NOT NULL,
    client_email VARCHAR(150) NOT NULL,
    client_phone VARCHAR(20),
    bride_name VARCHAR(100),
    groom_name VARCHAR(100),
    selected_items LONGTEXT,
    total_amount DECIMAL(12,2) DEFAULT 0,
    total_guests INT DEFAULT 0,
    total_rooms INT DEFAULT 0,
    terms_accepted TINYINT(1) DEFAULT 0,
    status ENUM('pending', 'accepted', 'rejected', 'cancelled') DEFAULT 'pending',
    rejection_reason TEXT,
    admin_notes TEXT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (client_email),
    INDEX idx_status (status),
    INDEX idx_user_id (user_id),
    INDEX idx_wedding_id (wedding_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
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
    INDEX idx_event_date (event_date),
    INDEX idx_venue (venue)
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

-- ---- ROOM BOOKINGS (Hotel rooms in a booking) ----
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
    INDEX idx_event_id (booking_event_id),
    INDEX idx_hotel (hotel_name)
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

-- Demo users (password: "wedding123")
INSERT INTO users (name, email, password, phone, is_admin) VALUES
('Priya & Arjun', 'user@aurawedding.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '9876543210', 0),
('Admin Manager', 'admin@aurawedding.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '9000000000', 1);

INSERT INTO weddings (user_id, bride_name, groom_name, venue, wedding_date, total_budget) VALUES
(1, 'Priya Sharma', 'Arjun Mehta', 'The Grand Palace, Mumbai', '2025-02-14', 2500000);

-- Sub Events
INSERT INTO sub_events (wedding_id, event_type, event_date, venue, time_start, time_end, notes) VALUES
(1, 'haldi', '2025-02-11', 'Family Home Garden', '09:00:00', '12:00:00', 'Traditional haldi ceremony'),
(1, 'mehendi', '2025-02-12', 'Rooftop Terrace, The Grand Palace', '16:00:00', '21:00:00', 'Mehendi celebration'),
(1, 'sangeet', '2025-02-13', 'Ballroom A, The Grand Palace', '19:00:00', '23:00:00', 'Musical evening'),
(1, 'vivaha', '2025-02-14', 'Main Hall, The Grand Palace', '10:00:00', '15:00:00', 'Wedding ceremony');

-- Sample Guests
INSERT INTO guests (wedding_id, name, phone, email, rsvp_status, side, relation) VALUES
(1, 'Rajesh Sharma', '9876543211', 'rajesh@example.com', 'confirmed', 'bride', 'Father'),
(1, 'Kavya Sharma', '9876543212', 'kavya@example.com', 'confirmed', 'bride', 'Mother'),
(1, 'Vikram Mehta', '9876543213', 'vikram@example.com', 'confirmed', 'groom', 'Father'),
(1, 'Neha Mehta', '9876543214', 'neha@example.com', 'pending', 'groom', 'Mother');

-- Catering Menu
INSERT INTO catering_menu (category, item_name, description, price_per_plate, is_veg) VALUES
('starters', 'Samosa', 'Crispy triangular pastry', 150, 1),
('starters', 'Paneer Tikka', 'Marinated cottage cheese', 250, 1),
('starters', 'Chicken Kebab', 'Spiced chicken skewers', 300, 0),
('main_course', 'Butter Chicken', 'Creamy tomato-based curry', 400, 0),
('main_course', 'Paneer Butter Masala', 'Rich cottage cheese curry', 350, 1),
('main_course', 'Biryani', 'Fragrant rice preparation', 250, 1),
('main_course', 'Tandoori Chicken', 'Clay oven baked chicken', 350, 0),
('desserts', 'Gulab Jamun', 'Sweet milk solids dumplings', 100, 1),
('desserts', 'Kheer', 'Rice pudding with cardamom', 120, 1),
('desserts', 'Jalebi', 'Spiral sugar-soaked pastry', 80, 1),
('drinks', 'Lassi', 'Yogurt-based drink', 60, 1),
('drinks', 'Fruit Juice', 'Fresh seasonal juices', 80, 1),
('drinks', 'Masala Chai', 'Spiced tea', 40, 1);

INSERT INTO guests (wedding_id, name, phone, email, rsvp_status, side, relation) VALUES
(1, 'Amit Sharma', '9876543210', 'amit@email.com', 'confirmed', 'bride', 'Brother'),
(1, 'Sunita Mehta', '9765432109', 'sunita@email.com', 'confirmed', 'groom', 'Mother'),
(1, 'Rahul Gupta', '9654321098', 'rahul@email.com', 'pending', 'both', 'Friend'),
(1, 'Kavita Singh', '9543210987', 'kavita@email.com', 'confirmed', 'bride', 'Cousin'),
(1, 'Vikram Joshi', '9432109876', 'vikram@email.com', 'declined', 'groom', 'Friend');

INSERT INTO hotel_bookings (guest_id, hotel_name, room_number, check_in, check_out) VALUES
(1, 'The Grand Palace Hotel', '101', '2025-02-11', '2025-02-15'),
(2, 'The Grand Palace Hotel', '205', '2025-02-12', '2025-02-15'),
(4, 'Nearby Comfort Inn', 'A12', '2025-02-12', '2025-02-14');

INSERT INTO catering_menu (category, item_name, price_per_plate, is_veg) VALUES
-- STARTERS (Vegetarian)
('starters', 'Paneer Tikka', 120, 1),
('starters', 'Hara Bhara Kabab', 90, 1),
('starters', 'Dahi Puri', 60, 1),
('starters', 'Vegetable Samosa', 50, 1),
('starters', 'Spinach & Corn Kabab', 100, 1),
('starters', 'Mushroom Tikka', 130, 1),
('starters', 'Corn & Cheese Pakora', 75, 1),
('starters', 'Mixed Vegetable Fritters', 65, 1),
('starters', 'Smoked Paneer Tikka', 150, 1),
('starters', 'Tandoori Broccoli', 95, 1),
-- STARTERS (Non-Vegetarian)
('starters', 'Chicken Malai Tikka', 180, 0),
('starters', 'Seekh Kabab (Chicken)', 170, 0),
('starters', 'Fish Tikka', 200, 0),
('starters', 'Tandoori Prawns', 250, 0),
('starters', 'Mutton Galauti Kabab', 190, 0),
('starters', 'Chicken Lollipop', 160, 0),
('starters', 'Smoked Chicken Tikka', 185, 0),
-- MAIN COURSE (Vegetarian)
('main_course', 'Dal Makhani', 150, 1),
('main_course', 'Palak Paneer', 140, 1),
('main_course', 'Dum Biryani (Veg)', 180, 1),
('main_course', 'Paneer Butter Masala', 160, 1),
('main_course', 'Chana Masala', 120, 1),
('main_course', 'Vegetable Korma', 130, 1),
('main_course', 'Baingan Bharta', 110, 1),
('main_course', 'Mixed Vegetable Curry', 100, 1),
('main_course', 'Mushroom Matar', 140, 1),
('main_course', 'Rajma & Rice', 105, 1),
('main_course', 'Paneer Lababdar', 155, 1),
-- MAIN COURSE (Non-Vegetarian)
('main_course', 'Butter Chicken', 200, 0),
('main_course', 'Dum Biryani (Chicken)', 250, 0),
('main_course', 'Butter Mutton', 220, 0),
('main_course', 'Rogan Josh (Mutton)', 240, 0),
('main_course', 'Nihari', 230, 0),
('main_course', 'Fish Curry (Bengali Style)', 280, 0),
('main_course', 'Tandoori Chicken', 210, 0),
('main_course', 'Chicken Tikka Masala', 195, 0),
('main_course', 'Prawn Curry', 320, 0),
('main_course', 'Keema Kaleji', 200, 0),
-- BREADS
('main_course', 'Naan (Butter)', 35, 1),
('main_course', 'Kulcha (Paneer)', 45, 1),
('main_course', 'Roti (Whole Wheat)', 25, 1),
('main_course', 'Paratha (Aloo)', 40, 1),
('main_course', 'Tandoori Naan', 40, 1),
('main_course', 'Puri & Halwa', 50, 1),
-- RICE PREPARATIONS
('main_course', 'Jeera Rice', 60, 1),
('main_course', 'Saffron Rice', 80, 1),
('main_course', 'Vegetable Pulao', 90, 1),
-- DESSERTS
('desserts', 'Gulab Jamun', 80, 1),
('desserts', 'Rasmalai', 100, 1),
('desserts', 'Gajar Ka Halwa', 90, 1),
('desserts', 'Kheer (Rice Pudding)', 85, 1),
('desserts', 'Jalebi & Imarti', 75, 1),
('desserts', 'Barfi Mix', 95, 1),
('desserts', 'Motichur Ke Ladoo', 100, 1),
('desserts', 'Ras Malai', 120, 1),
('desserts', 'Payesh (Bengali)', 110, 1),
('desserts', 'Malpua with Rabri', 105, 1),
('desserts', 'Ice Cream (3 Scoops)', 120, 1),
('desserts', 'Chocolate Mousse', 130, 1),
('desserts', 'Mango Cheesecake', 140, 1),
('desserts', 'Tiramisu', 135, 1),
('desserts', 'Khubani Ka Meetha', 95, 1),
-- DRINKS
('drinks', 'Masala Chaas', 40, 1),
('drinks', 'Fresh Lime Soda', 50, 1),
('drinks', 'Mango Lassi', 60, 1),
('drinks', 'Sweet Lassi', 55, 1),
('drinks', 'Jaljeera', 45, 1),
('drinks', 'Rose Milk', 50, 1),
('drinks', 'Badam Milk', 70, 1),
('drinks', 'Hot Chai', 30, 1),
('drinks', 'Fresh Orange Juice', 65, 1),
('drinks', 'Sugarcane Juice', 55, 1),
('drinks', 'Mixed Fruit Juice', 75, 1),
('drinks', 'Watermelon Juice', 60, 1),
-- LIVE COUNTERS
('live_counter', 'Pani Puri Station', 100, 1),
('live_counter', 'Chaat Counter (Bhel, Sev, Samosa)', 120, 1),
('live_counter', 'Dosa Station', 150, 1),
('live_counter', 'Taco & Wrap Station', 140, 1),
('live_counter', 'Momos & Dim Sum', 110, 0),
('live_counter', 'Tandoori Chicken Wrap Station', 160, 0),
('live_counter', 'Kulfi & Ice Cream Cart', 130, 1),
('live_counter', 'Kebab Station (Veg & Non-Veg)', 180, 0),
('live_counter', 'Biryani Station', 200, 0),
('live_counter', 'Street Food Grill', 170, 0);

INSERT INTO mandap_selections (wedding_id, mandap_style, flower_theme, color_scheme) VALUES
(1, 'Royal Rajasthani Arch', 'Marigold & Rose', 'Gold & Pink');

INSERT INTO gifts (wedding_id, guest_id, gift_type, description, amount) VALUES
(1, 1, 'shagun', 'Envelope from Brother', 51000),
(1, 2, 'cash', 'Cash gift from Groom Family', 101000),
(1, 4, 'item', 'Gold Necklace Set', 25000);

-- ---- PLAYLISTS (Sangeet Music) ----
INSERT INTO playlists (wedding_id, playlist_name, description, event_type, mood) VALUES
(1, 'Classical Sangeet Collection', 'Traditional classical songs perfect for sangeet ceremony', 'sangeet', 'Classical'),
(1, 'Bollywood Sangeet Hits', 'Modern Bollywood romantic songs for sangeet celebration', 'sangeet', 'Romantic');

-- ---- PLAYLIST SONGS (50+ songs total) ----
-- Classical Sangeet Collection (25 songs)
INSERT INTO playlist_songs (playlist_id, song_name, artist_name, genre, duration_seconds, song_order) VALUES
(1, 'Bole Chudiyan', 'Shreya Ghoshal', 'Classical Fusion', 244, 1),
(1, 'Dola Re Dola', 'Anushka Manchanda', 'Classical Fusion', 220, 2),
(1, 'Morni Banke', 'Shreya Ghoshal', 'Classical Fusion', 210, 3),
(1, 'Jiya Jale', 'Lata Mangeshkar', 'Classical Romance', 264, 4),
(1, 'Chandni O Chandni', 'Lata Mangeshkar', 'Classical Romance', 238, 5),
(1, 'Saathiya', 'Mera Naam Chin Chin Chu', 'Classical Fusion', 198, 6),
(1, 'Aati Ho Ati Ho Ati Ho', 'Shreya Ghoshal', 'Devotional Classical', 216, 7),
(1, 'Raina Beeti Jaye', 'Shreya Ghoshal', 'Classical Romance', 252, 8),
(1, 'Teri Aankhon Ke Samne', 'Mohammed Rafi & Lata Mangeshkar', 'Classical Duet', 226, 9),
(1, 'Hazaron Khwahishen Aisi', 'Jagjit Singh', 'Classical Ghazal', 244, 10),
(1, 'Shringaar Karo Dulhan Meri', 'Asha Parekh', 'Classical Sangeet', 278, 11),
(1, 'Suhaag Raat Mein Jao Re', 'Suman Kalyanpur', 'Classical Wedding', 254, 12),
(1, 'Mhara Maher Aayo Bapu', 'Traditional', 'Rajasthani Folk', 268, 13),
(1, 'Raat Bhar Jaagey Hain Saath', 'Alka Yagnik & Udit Narayan', 'Classical Duet', 242, 14),
(1, 'Dheere Dheere Se Bheegy', 'Shreya Ghoshal', 'Classical Romantic', 238, 15),
(1, 'Choli Ke Peeche', 'Asha Bhosle & Mohammed Rafi', 'Retro Sangeet', 248, 16),
(1, 'Mehndi Hai Rachai', 'Shreya Ghoshal', 'Classical Mehndi', 222, 17),
(1, 'Badhai Ho Badhai', 'Lata Mangeshkar', 'Traditional Sangeet', 256, 18),
(1, 'Sukhmani Sahib', 'Jagjit Singh', 'Devotional', 294, 19),
(1, 'Ae Mere Pyare Watan', 'Mohammed Rafi', 'Patriotic Sangeet', 212, 20),
(1, 'Radhey Radhey Naam', 'Shreya Ghoshal', 'Devotional Classical', 245, 21),
(1, 'Priya Priya Teri', 'K.S. Chithra', 'South Indian Classical', 238, 22),
(1, 'Lavni Tani Baaje', 'Shreya Ghoshal', 'Marathi Traditional', 234, 23),
(1, 'Bhangde Di Reet', 'Nooran Sisters', 'Punjabi Folk', 256, 24),
(1, 'Dulari Teri Suhani Aankh', 'Sonu Nigam', 'Classical Sangeet', 250, 25);

-- Bollywood Sangeet Hits (25 songs)
INSERT INTO playlist_songs (playlist_id, song_name, artist_name, genre, duration_seconds, song_order) VALUES
(2, 'Gale Lagaa Le', 'Udit Narayan & Alka Yagnik', 'Bollywood Romance', 246, 1),
(2, 'Tum Tak', 'Rahat Fateh Ali Khan', 'Bollywood Sufi', 296, 2),
(2, 'Humdumm', 'Shreya Ghoshal & Rahat Fateh Ali Khan', 'Bollywood Romantic', 218, 3),
(2, 'Tenu Leke Main Jaaunga', 'Rahat Fateh Ali Khan', 'Bollywood Romance', 268, 4),
(2, 'Pyaar Ki Ek Moti Khushbu', 'Sonu Nigam & Shreya Ghoshal', 'Bollywood Romantic', 242, 5),
(2, 'Kabhii Mayne Kaha Tha', 'Arijit Singh', 'Bollywood Romantic', 258, 6),
(2, 'Pal Pal Dil Ke Paas', 'Arijit Singh', 'Bollywood Romance', 274, 7),
(2, 'Raabta', 'Arijit Singh & Nidhhi Agerwal', 'Bollywood Modern', 224, 8),
(2, 'Teri Khair Mangdi', 'Akhil Sachdeva', 'Bollywood Romantic', 252, 9),
(2, 'Main Tera Ban Jaunga', 'Arjun Kanungo & Carla Dennis', 'Bollywood Modern', 240, 10),
(2, 'Ae Dil Hai Mushkil', 'Anushka Manchanda', 'Bollywood Ballad', 278, 11),
(2, 'Kabhi Kabhi Aditi Zindagi', 'Arijit Singh', 'Bollywood Melancholy', 286, 12),
(2, 'Thaane Ke Liye', 'Shreya Ghoshal', 'Bollywood Dance', 232, 13),
(2, 'Saiyaan', 'Kailash Kher', 'Bollywood Sufi', 242, 14),
(2, 'Bheegi Bheegi Raaton Mein', 'Anupam Roy', 'Bollywood Rain Song', 254, 15),
(2, 'Kahin Door', 'Mohammed Rafi', 'Evergreen Romance', 268, 16),
(2, 'Kitni Haseen Hogi', 'Sonu Nigam', 'Modern Romantic', 246, 17),
(2, 'Toh Phir Aao', 'Arjun Kanungo', 'Contemporary Sangeet', 238, 18),
(2, 'Ishq Mein Marjawan', 'Neha Kakkar', 'Modern Romantic', 224, 19),
(2, 'O Sanam', 'Arjun Kanungo & Carla Dennis', 'Duet Love Song', 252, 20),
(2, 'Khuda Aur Mohabbat', 'Amjad Sabri', 'Qawwali Sufi', 296, 21),
(2, 'Mere Haath Mein', 'Prem Joshua', 'Sufi Instrumental', 286, 22),
(2, 'Tere Bina', 'Shreya Ghoshal', 'Wedding Song', 242, 23),
(2, 'Jab Se Tum Ho Paas Mere', 'Sonu Nigam', 'Romantic Duet', 268, 24),
(2, 'Main Tenu Samjheya Kare', 'Rahat Fateh Ali Khan', 'Punjabi Love', 260, 25);

-- ---- MIGRATION: Add wedding_id to bookings if it doesn't exist ----
ALTER TABLE bookings ADD COLUMN IF NOT EXISTS wedding_id INT AFTER user_id;
ALTER TABLE bookings ADD INDEX IF NOT EXISTS idx_wedding_id (wedding_id);

SELECT 'Database setup complete!' AS status;
