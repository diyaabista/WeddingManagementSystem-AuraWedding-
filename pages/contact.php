<?php
$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($conn, $_POST['name'] ?? '');
    $email = sanitize($conn, $_POST['email'] ?? '');
    $phone = sanitize($conn, $_POST['phone'] ?? '');
    $subject = sanitize($conn, $_POST['subject'] ?? '');
    $message = sanitize($conn, $_POST['message'] ?? '');

    if ($name && $email && $message) {
        $stmt = $conn->prepare("INSERT INTO messages (name, email, phone, subject, message) VALUES (?,?,?,?,?)");
        $stmt->bind_param("sssss", $name, $email, $phone, $subject, $message);
        if ($stmt->execute()) {
            $success = true;
        } else {
            $error = "Failed to send message. Please try again.";
        }
    } else {
        $error = "Please fill in all required fields.";
    }
}
?>

<div class="page-header">
    <h1>📞 Contact Us</h1>
    <p>We'd love to hear from you. Reach out and we'll respond quickly.</p>
</div>

<section class="section">
    <div style="display:grid; grid-template-columns:1fr 1.5fr; gap:50px; max-width:1100px; margin:0 auto; align-items:start">

        <!-- Info -->
        <div>
            <h2 style="font-family:'Playfair Display',serif; font-size:1.8rem; color:var(--dark); margin-bottom:20px">Get In Touch 💌</h2>
            <p style="color:#666; line-height:1.8; margin-bottom:30px">Our wedding planning experts are available to answer all your questions and help you plan the perfect wedding.</p>

            <?php
            $contacts = [
                ['📞','Call Us','+91 98765 43210','Mon – Sat: 9AM to 7PM'],
                ['✉️','Email Us','hello@aurawedding.com','We reply within 24 hours'],
                ['📍','Visit Us','Mumbai, Maharashtra, India','By appointment only'],
                ['💬','WhatsApp','+91 98765 43210','Chat with our planners'],
            ];
            foreach ($contacts as [$icon, $title, $value, $note]):
            ?>
            <div style="display:flex; gap:16px; align-items:flex-start; margin-bottom:24px; background:#fff; border-radius:14px; padding:18px 20px; box-shadow:0 4px 16px rgba(0,0,0,0.06)">
                <div style="font-size:1.8rem; flex-shrink:0"><?= $icon ?></div>
                <div>
                    <div style="font-size:0.8rem; color:#888; font-weight:600; text-transform:uppercase"><?= $title ?></div>
                    <div style="font-weight:700; color:var(--dark); margin:4px 0"><?= $value ?></div>
                    <div style="font-size:0.85rem; color:#999"><?= $note ?></div>
                </div>
            </div>
            <?php endforeach; ?>

            <div style="margin-top:20px; background:var(--pink-light); border-radius:14px; padding:20px">
                <h4 style="color:var(--pink-main); margin-bottom:10px">🌸 Our Promise</h4>
                <p style="font-size:0.9rem; color:#666; line-height:1.6">Every wedding we plan is unique. We bring creativity, dedication, and love to each ceremony we manage.</p>
            </div>
        </div>

        <!-- Form -->
        <div style="background:#fff; border-radius:20px; padding:40px; box-shadow:0 4px 20px rgba(0,0,0,0.08)">
            <?php if ($success): ?>
            <div style="text-align:center; padding:40px">
                <div style="font-size:3.5rem; margin-bottom:16px">🎉</div>
                <h3 style="color:var(--dark); margin-bottom:10px">Message Sent!</h3>
                <p style="color:#888;">We'll get back to you within 24 hours.</p>
                <a href="index.php?page=contact" class="btn btn-outline" style="margin-top:20px">Send Another</a>
            </div>
            <?php else: ?>

            <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <h3 style="font-size:1.3rem; font-weight:700; color:var(--dark); margin-bottom:24px">Send Us a Message</h3>
            <form method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Your Name *</label>
                        <input type="text" name="name" placeholder="Full name" required>
                    </div>
                    <div class="form-group">
                        <label>Email Address *</label>
                        <input type="email" name="email" placeholder="your@email.com" required>
                    </div>
                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="tel" name="phone" placeholder="+91 98765 43210">
                    </div>
                    <div class="form-group">
                        <label>Subject</label>
                        <select name="subject">
                            <option value="">Select a topic</option>
                            <option>General Enquiry</option>
                            <option>Booking Help</option>
                            <option>Pricing & Packages</option>
                            <option>Feedback</option>
                            <option>Other</option>
                        </select>
                    </div>
                    <div class="form-group full">
                        <label>Message *</label>
                        <textarea name="message" rows="5" placeholder="Tell us about your dream wedding..." required></textarea>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%; margin-top:16px; padding:14px; font-size:1rem">
                    Send Message ✈️
                </button>
            </form>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Map placeholder -->
<div style="background:#f0f0f0; height:300px; display:flex; align-items:center; justify-content:center; font-size:1.1rem; color:#888">
    📍 Map: Mumbai, Maharashtra, India — <a href="https://maps.google.com/?q=Mumbai" target="_blank" style="color:var(--pink-main); margin-left:8px">Open in Google Maps</a>
</div>
