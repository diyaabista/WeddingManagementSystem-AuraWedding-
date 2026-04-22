<?php
$gallery = $conn->query("SELECT * FROM gallery WHERE ceremony_type='haldi' AND is_active=1 ORDER BY id");
?>

<div class="page-header" style="background:linear-gradient(135deg,#fff8e1,#fce4ec)">
    <h1>🌻 Haldi Ceremony</h1>
    <p>Sacred turmeric blessings for a glowing bride and groom</p>
</div>

<section class="section">
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:50px; max-width:1100px; margin:0 auto; align-items:center">
        <div>
            <img src="https://images.unsplash.com/photo-1519741497674-611481863552?w=600" alt="Haldi" style="width:100%; border-radius:20px; box-shadow:0 12px 40px rgba(0,0,0,0.12)">
        </div>
        <div>
            <h2 style="font-family:'Playfair Display',serif; font-size:2rem; color:var(--dark); margin-bottom:16px">Yellow Blessings 🌼</h2>
            <p style="color:#666; line-height:1.8; margin-bottom:20px">The Haldi ceremony is a sacred and joyful pre-wedding ritual where turmeric paste is applied to the bride and groom by family members, bringing blessings, beauty, and good luck.</p>
            <div style="background:#fff8e1; border-radius:16px; padding:20px; margin-bottom:20px">
                <h4 style="color:#c9a227; margin-bottom:10px">✨ Why Haldi?</h4>
                <p style="font-size:0.9rem; color:#666; line-height:1.7">Turmeric has natural antibacterial and skin-brightening properties. It's believed to ward off evil and purify the soul before marriage.</p>
            </div>
            <ul style="list-style:none; display:flex; flex-direction:column; gap:10px">
                <?php foreach(['Marigold & Sunflower Decoration','Traditional Brass Thalis','Professional Photography','Fun Haldi Games','Live Dhol & Music','Fresh Floral Backdrop'] as $f): ?>
                <li style="display:flex; gap:10px; font-size:0.95rem; color:var(--text)">
                    <span style="color:#c9a227; font-weight:700">✓</span> <?= $f ?>
                </li>
                <?php endforeach; ?>
            </ul>
            <a href="index.php?page=booking&ceremony=haldi" class="btn btn-gold" style="margin-top:24px">Book Haldi Ceremony</a>
        </div>
    </div>
</section>

<section class="section section-light">
    <h2 class="section-title">Haldi Gallery 🌻</h2>
    <div class="gallery-grid" style="max-width:1100px; margin:0 auto">
        <?php if ($gallery && $gallery->num_rows > 0): while ($img = $gallery->fetch_assoc()): ?>
        <div class="gallery-item">
            <img src="<?= htmlspecialchars($img['image_url']) ?>" alt="<?= htmlspecialchars($img['title']) ?>" loading="lazy" style="width:100%; height:250px; object-fit:cover; border-radius:12px">
        </div>
        <?php endwhile; endif; ?>
    </div>
</section>

<div style="text-align:center; padding:40px">
    <a href="index.php?page=booking&ceremony=haldi" class="btn btn-gold" style="font-size:1.1rem; padding:14px 36px">Book Haldi Ceremony — Starting ₹25,000</a>
</div>
