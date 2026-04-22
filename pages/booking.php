<?php
requireLogin();

// Only regular users can create bookings, not admin or planner
if ($_SESSION['role'] !== 'user') {
    header("Location: index.php?error=Only users can create bookings. Admins/Planners can confirm bookings only.");
    exit;
}

$success = false;
$bookingCode = '';
$error = '';

// Pre-select ceremony from URL
$preCeremony = $_GET['ceremony'] ?? '';
$prePackage = $_GET['package'] ?? '';

// Create uploads directory if not exists
$uploadDir = 'uploads/designs/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Fetch packages for dropdown
$pkgs = $conn->query("SELECT * FROM packages WHERE is_active=1 ORDER BY price");

// Handle booking submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bride = sanitize($conn, $_POST['bride_name'] ?? '');
    $groom = sanitize($conn, $_POST['groom_name'] ?? '');
    $email = sanitize($conn, $_POST['email'] ?? '');
    $phone = sanitize($conn, $_POST['phone'] ?? '');
    $wedding_date = sanitize($conn, $_POST['wedding_date'] ?? '');
    $venue = sanitize($conn, $_POST['venue'] ?? '');
    $guest_count = intval($_POST['guest_count'] ?? 0);
    $package_id = intval($_POST['package_id'] ?? 0) ?: null;
    $special_requests = sanitize($conn, $_POST['special_requests'] ?? '');
    $budget = floatval(str_replace(',', '', $_POST['budget'] ?? 0)) ?: null;
    $ceremonies = $_POST['ceremonies'] ?? [];
    $ceremony_str = implode(',', array_map(fn($c) => sanitize($conn, $c), $ceremonies));

    if ($bride && $groom && $email && $phone && $wedding_date && $ceremony_str) {
        // Validate date
        $bookDate = new DateTime($wedding_date);
        $today = new DateTime();
        if ($bookDate <= $today) {
            $error = "Wedding date must be in the future.";
        } else {
            $code = generateBookingCode();
            $uid = $_SESSION['user_id'];

            $stmt = $conn->prepare("INSERT INTO bookings (user_id, booking_code, bride_name, groom_name, email, phone, wedding_date, ceremony_types, venue, guest_count, package_id, special_requests, budget, status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,'pending')");
            $stmt->bind_param("issssssssiids", $uid, $code, $bride, $groom, $email, $phone, $wedding_date, $ceremony_str, $venue, $guest_count, $package_id, $special_requests, $budget);

            if ($stmt->execute()) {
                $bookingId = $conn->insert_id;
                $success = true;
                $bookingCode = $code;
                
                // Handle design uploads
                if (isset($_FILES['design_image']) && is_array($_FILES['design_image']['name'])) {
                    $design_types = $_POST['design_type'] ?? [];
                    $design_ceremonies = $_POST['design_ceremony'] ?? [];
                    $design_descriptions = $_POST['design_description'] ?? [];
                    $design_sources = $_POST['design_source_' . ($_ ?? '')] ?? []; // Will be populated by JS
                    
                    foreach ($_FILES['design_image']['name'] as $idx => $filename) {
                        if ($filename && $_FILES['design_image']['error'][$idx] === 0) {
                            $filesize = $_FILES['design_image']['size'][$idx];
                            // Validate file size (max 5MB)
                            if ($filesize > 5242880) continue;
                            
                            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                            if (!in_array($ext, $allowed)) continue;
                            
                            $newFilename = 'design_' . $bookingId . '_' . time() . '_' . rand(100, 999) . '.' . $ext;
                            $filepath = $uploadDir . $newFilename;
                            
                            if (move_uploaded_file($_FILES['design_image']['tmp_name'][$idx], $filepath)) {
                                $design_type = sanitize($conn, $design_types[$idx] ?? 'other');
                                $design_ceremony = sanitize($conn, $design_ceremonies[$idx] ?? 'vivaha');
                                $design_desc = sanitize($conn, $design_descriptions[$idx] ?? '');
                                
                                try {
                                    $designStmt = $conn->prepare("INSERT INTO user_designs (user_id, booking_id, ceremony_type, design_type, image_url, description) VALUES (?, ?, ?, ?, ?, ?)");
                                    if ($designStmt) {
                                        $designStmt->bind_param("iissss", $uid, $bookingId, $design_ceremony, $design_type, $filepath, $design_desc);
                                        $designStmt->execute();
                                    }
                                } catch (Exception $e) {
                                    // Design table may not exist yet - continue with booking
                                }
                            }
                        }
                    }
                }
                
                // Handle gallery image selections
                if (isset($_POST['design_image_gallery']) && is_array($_POST['design_image_gallery'])) {
                    $design_types = $_POST['design_type'] ?? [];
                    $design_ceremonies = $_POST['design_ceremony'] ?? [];
                    $design_descriptions = $_POST['design_description'] ?? [];
                    
                    foreach ($_POST['design_image_gallery'] as $idx => $gallery_image) {
                        if ($gallery_image) {
                            $gallery_image = sanitize($conn, $gallery_image);
                            $design_type = sanitize($conn, $design_types[$idx] ?? 'other');
                            $design_ceremony = sanitize($conn, $design_ceremonies[$idx] ?? 'vivaha');
                            $design_desc = sanitize($conn, $design_descriptions[$idx] ?? '');
                            
                            try {
                                $designStmt = $conn->prepare("INSERT INTO user_designs (user_id, booking_id, ceremony_type, design_type, image_url, description) VALUES (?, ?, ?, ?, ?, ?)");
                                if ($designStmt) {
                                    $designStmt->bind_param("iissss", $uid, $bookingId, $design_ceremony, $design_type, $gallery_image, $design_desc);
                                    $designStmt->execute();
                                }
                            } catch (Exception $e) {
                                // Design table may not exist yet - continue with booking
                            }
                        }
                    }
                }
            } else {
                $error = "Booking failed. Please try again.";
            }
        }
    } else {
        $error = "Please fill in all required fields.";
    }
}
?>

<div class="page-header">
    <h1>💍 Book Your Wedding</h1>
    <p>Fill in the details and our team will confirm your booking</p>
</div>

<section class="section">
    <?php if ($success): ?>
    <div style="max-width:600px; margin:0 auto; text-align:center; background:#fff; border-radius:24px; padding:48px; box-shadow:0 8px 40px rgba(0,0,0,0.1)">
        <div style="font-size:4rem; margin-bottom:20px">🎊</div>
        <h2 style="font-family:'Playfair Display',serif; color:var(--dark); margin-bottom:12px">Booking Submitted!</h2>
        <p style="color:#666; margin-bottom:24px">Your wedding booking has been received. Our planner will contact you within 24 hours to confirm.</p>
        <div style="background:var(--pink-light); border-radius:14px; padding:20px; margin-bottom:28px">
            <div style="font-size:0.9rem; color:#888; margin-bottom:6px">Your Booking Code</div>
            <div style="font-size:1.8rem; font-weight:800; color:var(--pink-main); letter-spacing:2px"><?= $bookingCode ?></div>
        </div>
        <div style="display:flex; gap:12px; justify-content:center; flex-wrap:wrap">
            <a href="index.php?page=my-bookings" class="btn btn-primary">View My Bookings</a>
            <a href="index.php" class="btn btn-outline">Back to Home</a>
        </div>
    </div>

    <?php else: ?>

    <?php if ($error): ?>
    <div class="alert alert-error" style="max-width:820px; margin:0 auto 20px"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="booking-form-wrap">
        <h2 style="font-family:'Playfair Display',serif; font-size:1.6rem; color:var(--dark); margin-bottom:8px; text-align:center">Wedding Booking Form</h2>
        <p style="text-align:center; color:#888; margin-bottom:32px">All fields marked * are required</p>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-grid">
                <div class="form-group">
                    <label>Bride's Name *</label>
                    <input type="text" name="bride_name" placeholder="Bride's full name" required>
                </div>
                <div class="form-group">
                    <label>Groom's Name *</label>
                    <input type="text" name="groom_name" placeholder="Groom's full name" required>
                </div>
                <div class="form-group">
                    <label>Email Address *</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($_SESSION['email'] ?? '') ?>" placeholder="contact@email.com" required>
                </div>
                <div class="form-group">
                    <label>Phone Number *</label>
                    <input type="tel" name="phone" placeholder="+91 98765 43210" required>
                </div>
                <div class="form-group">
                    <label>Wedding Date *</label>
                    <input type="date" name="wedding_date" min="<?= date('Y-m-d', strtotime('+1 day')) ?>" required>
                </div>
                <div class="form-group">
                    <label>Expected Guest Count</label>
                    <input type="number" name="guest_count" placeholder="e.g. 200" min="1">
                </div>
                <div class="form-group full">
                    <label>Venue / Location</label>
                    <input type="text" name="venue" placeholder="Banquet hall name or city">
                </div>

                <!-- Ceremonies -->
                <div class="form-group full">
                    <label>Select Ceremonies * (choose all that apply)</label>
                    <div class="checkbox-group">
                        <?php
                        $ceremonies_list = [
                            'sangeet'=>'🎵 Sangeet',
                            'mehendi'=>'🌿 Mehendi',
                            'haldi'=>'🌻 Haldi',
                            'vivaha'=>'👰 Vivaha'
                        ];
                        foreach ($ceremonies_list as $val => $label):
                        ?>
                        <label>
                            <input type="checkbox" class="ceremony-checkbox" name="ceremonies[]" value="<?= $val ?>" <?= ($preCeremony === $val) ? 'checked' : '' ?>>
                            <?= $label ?>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Package -->
                <div class="form-group full">
                    <label>Select Package (Optional)</label>
                    <select name="package_id">
                        <option value="">-- No specific package, I'll discuss --</option>
                        <?php if ($pkgs): while ($p = $pkgs->fetch_assoc()): ?>
                        <option value="<?= $p['id'] ?>" <?= ($prePackage == $p['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($p['name']) ?> — ₹<?= number_format($p['price']) ?>
                        </option>
                        <?php endwhile; endif; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Budget Range (₹)</label>
                    <input type="text" name="budget" placeholder="e.g. 150000">
                </div>
                <div class="form-group"></div>

                <div class="form-group full">
                    <label>Special Requests / Notes</label>
                    <textarea name="special_requests" rows="4" placeholder="Any special requirements, dietary restrictions, theme preferences..."></textarea>
                </div>

                <!-- Custom Designs Upload -->
                <div class="form-group full" style="border-top:2px solid #eee; padding-top:24px; margin-top:24px">
                    <h3 style="font-size:1.1rem; color:var(--dark); margin-bottom:12px">🎨 Add Your Custom Designs (Optional)</h3>
                    <p style="color:#888; font-size:0.9rem; margin-bottom:16px">Want to showcase your mehendi design, decor ideas, or venue preferences? Upload your own or select from our gallery!</p>
                    
                    <div id="designsContainer" style="display:grid; gap:16px">
                        <!-- Design upload template will be added by JavaScript -->
                    </div>
                    
                    <button type="button" onclick="addDesignUpload()" class="btn btn-outline" style="margin-top:16px">+ Add Design</button>
                </div>
            </div>

            <div style="text-align:center; margin-top:30px">
                <button type="submit" class="btn btn-primary" style="font-size:1.1rem; padding:15px 48px">
                    💍 Submit Booking Request
                </button>
                <p style="margin-top:12px; font-size:0.85rem; color:#888">Our team will confirm within 24 hours</p>
            </div>
        </form>
    </div>
    <?php endif; ?>
</section>

<script>
let designCount = 0;

function addDesignUpload() {
    designCount++;
    const container = document.getElementById('designsContainer');
    const designDiv = document.createElement('div');
    designDiv.id = 'design-' + designCount;
    designDiv.style.cssText = 'padding:16px; background:#f9f9f9; border-radius:12px; border:2px dashed #ddd';
    
    designDiv.innerHTML = `
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px">
            <label style="font-weight:600; color:var(--dark)">Design ${designCount}</label>
            <button type="button" onclick="document.getElementById('design-${designCount}').remove()" style="background:none; border:none; font-size:1.2rem; cursor:pointer; color:#999">×</button>
        </div>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:12px">
            <div class="form-group">
                <label>Design Type</label>
                <select name="design_type[]" required>
                    <option value="mehendi">🌿 Mehendi Design</option>
                    <option value="decor">✨ Decor Idea</option>
                    <option value="venue">📍 Venue Photo</option>
                    <option value="other">💡 Other</option>
                </select>
            </div>
            <div class="form-group">
                <label>Ceremony Type</label>
                <select name="design_ceremony[]" required>
                    <option value="mehendi">Mehendi</option>
                    <option value="sangeet">Sangeet</option>
                    <option value="haldi">Haldi</option>
                    <option value="vivaha">Vivaha</option>
                </select>
            </div>
        </div>
        <div style="background:#fff; border-radius:8px; padding:12px; margin-bottom:12px">
            <p style="font-size:0.85rem; color:#666; margin-bottom:10px; font-weight:600">Choose Option:</p>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px">
                <div>
                    <input type="radio" name="design_source_${designCount}" value="upload" checked onchange="toggleDesignSource(${designCount}, 'upload')">
                    <label style="margin-left:6px">📤 Upload Your Own</label>
                </div>
                <div>
                    <input type="radio" name="design_source_${designCount}" value="gallery" onchange="toggleDesignSource(${designCount}, 'gallery')">
                    <label style="margin-left:6px">🎨 Select from Gallery</label>
                </div>
            </div>
        </div>
        <div id="design_upload_${designCount}" class="form-group" style="display:block">
            <label>Upload Image *</label>
            <input type="file" name="design_image[]" accept="image/*" style="padding:10px; border:2px solid #e8e8e8; border-radius:8px; width:100%">
        </div>
        <div id="design_gallery_${designCount}" class="form-group" style="display:none">
            <label>Select from Gallery *</label>
            <select name="design_image_gallery[]" style="padding:10px; border:2px solid #e8e8e8; border-radius:8px; width:100%">
                <option value="">-- Choose an image --</option>
                <?php 
                $allGallery = $conn->query("SELECT * FROM gallery ORDER BY ceremony_type, sub_category");
                if ($allGallery) {
                    while ($img = $allGallery->fetch_assoc()) {
                        echo '<option value="' . htmlspecialchars($img['image_url']) . '">' . htmlspecialchars($img['title']) . ' (' . ucfirst($img['ceremony_type']) . ')</option>';
                    }
                }
                ?>
            </select>
        </div>
        <div class="form-group">
            <label>Description (Optional)</label>
            <textarea name="design_description[]" rows="2" placeholder="Tell us about this design..."></textarea>
        </div>
    `;
    container.appendChild(designDiv);
}

function toggleDesignSource(count, type) {
    const uploadDiv = document.getElementById('design_upload_' + count);
    const galleryDiv = document.getElementById('design_gallery_' + count);
    
    if (type === 'upload') {
        uploadDiv.style.display = 'block';
        galleryDiv.style.display = 'none';
    } else {
        uploadDiv.style.display = 'none';
        galleryDiv.style.display = 'block';
    }
}
</script>
