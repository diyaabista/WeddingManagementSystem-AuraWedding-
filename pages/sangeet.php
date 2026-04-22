<?php
$gallery = $conn->query("SELECT * FROM gallery WHERE ceremony_type='sangeet' AND is_active=1 ORDER BY id");
?>

<div class="page-header" style="background:linear-gradient(135deg,#fce4ec,#fff8e1)">
    <h1>🎵 Sangeet Ceremony</h1>
    <p>Music, dance, and joyful celebrations — the night before your wedding</p>
</div>

<section class="section">
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:50px; max-width:1100px; margin:0 auto; align-items:center">
        <div>
            <h2 style="font-family:'Playfair Display',serif; font-size:2rem; color:var(--dark); margin-bottom:16px">A Night to Remember 🎶</h2>
            <p style="color:#666; line-height:1.8; margin-bottom:20px">The Sangeet night is one of the most joyous pre-wedding celebrations. Filled with music, dance performances, laughter, and love — it's the perfect way to bring both families together.</p>
            <ul style="list-style:none; display:flex; flex-direction:column; gap:10px">
                <?php foreach(['Professional DJ & Live Band','LED Dance Floor & Stage','Photo & Video Booth','Coordinated Dance Performances','Customized Lighting Setup','Catering & Refreshments'] as $f): ?>
                <li style="display:flex; align-items:center; gap:10px; font-size:0.95rem; color:var(--text)">
                    <span style="color:var(--pink-main); font-weight:700">✓</span> <?= $f ?>
                </li>
                <?php endforeach; ?>
            </ul>
            <div style="margin-top:28px">
                <a href="index.php?page=booking&ceremony=sangeet" class="btn btn-primary">Book Sangeet Night</a>
            </div>
        </div>
        <div>
            <img src="https://images.unsplash.com/photo-1522673607200-164d1b6ce486?w=600" alt="Sangeet" style="width:100%; border-radius:20px; box-shadow:0 12px 40px rgba(0,0,0,0.12)">
        </div>
    </div>
</section>

<!-- Gallery -->
<section class="section section-light">
    <h2 class="section-title">Sangeet Moments 📸</h2>
    <p class="section-subtitle">Beautiful memories from our Sangeet celebrations</p>
    <div class="gallery-grid" style="max-width:1100px; margin:0 auto">
        <?php if ($gallery && $gallery->num_rows > 0): while ($img = $gallery->fetch_assoc()): ?>
        <div class="gallery-item">
            <img src="<?= htmlspecialchars($img['image_url']) ?>" alt="<?= htmlspecialchars($img['title']) ?>" loading="lazy" style="width:100%; height:250px; object-fit:cover; border-radius:12px">
        </div>
        <?php endwhile; endif; ?>
    </div>
</section>

<!-- Packages -->
<section class="section">
    <h2 class="section-title">Sangeet Packages</h2>
    <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(260px,1fr)); gap:24px; max-width:900px; margin:0 auto">
        <?php
        $pkgs = [
            ['Silver Night','₹25,000','DJ Setup, Basic Lighting, Decoration, Catering for 50'],
            ['Gold Night','₹45,000','DJ + Live Dhol, LED Dance Floor, Premium Decor, Catering for 100'],
            ['Royal Night','₹85,000','Live Band, LED Stage, Custom Performances, Photography, Catering for 200+'],
        ];
        foreach ($pkgs as [$name, $price, $desc]):
        ?>
        <div style="background:#fff; border-radius:16px; padding:28px; text-align:center; box-shadow:0 4px 20px rgba(0,0,0,0.08)">
            <div style="font-size:2rem; margin-bottom:12px">🎵</div>
            <h3 style="font-weight:700; color:var(--dark); margin-bottom:8px"><?= $name ?></h3>
            <div style="font-size:1.6rem; font-weight:800; color:var(--pink-main); margin-bottom:12px"><?= $price ?></div>
            <p style="font-size:0.9rem; color:#888; line-height:1.6"><?= $desc ?></p>
            <a href="index.php?page=booking&ceremony=sangeet" class="btn btn-primary btn-sm" style="margin-top:16px">Book Now</a>
        </div>
        <?php endforeach; ?>
    </div>
</section>
