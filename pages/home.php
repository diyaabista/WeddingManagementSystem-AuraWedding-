<?php
// Fetch packages from DB
$packages_result = $conn->query("SELECT * FROM packages WHERE is_active=1 LIMIT 5");
?>

<!-- HERO -->
<section class="hero">
    <div class="hero-badge">✨ COMPLETE WEDDING MANAGEMENT SYSTEM</div>
    <h1>Your Dream Wedding <span>Managed Perfectly</span></h1>
    <p>From intimate Haldi ceremonies to grand Vivaha celebrations — plan every moment with love and elegance.</p>
    <div class="hero-cta">
        <a href="index.php?page=booking" class="btn btn-primary" style="font-size:1.1rem; padding:14px 36px">💍 Book Your Wedding</a>
        <a href="#ceremonies" class="btn btn-outline" style="font-size:1.1rem; padding:14px 36px">🌸 Explore Ceremonies</a>
    </div>
    <?php if (!isLoggedIn()): ?>
    <p class="hero-sign">Already registered? <a href="index.php?page=login">Sign In →</a></p>
    <?php endif; ?>
</section>

<!-- STATS BAR -->
<div style="background:#fff; padding:30px 40px; box-shadow:0 2px 10px rgba(0,0,0,0.06)">
    <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(150px,1fr)); gap:20px; max-width:900px; margin:0 auto; text-align:center">
        <div><div style="font-size:2rem; font-weight:800; color:var(--pink-main)" class="stat-count" data-target="500">0</div><div style="color:#888; font-size:0.9rem">Weddings Done</div></div>
        <div><div style="font-size:2rem; font-weight:800; color:var(--gold)" class="stat-count" data-target="12">0</div><div style="color:#888; font-size:0.9rem">Years Experience</div></div>
        <div><div style="font-size:2rem; font-weight:800; color:var(--pink-main)" class="stat-count" data-target="4">0</div><div style="color:#888; font-size:0.9rem">Ceremony Types</div></div>
        <div><div style="font-size:2rem; font-weight:800; color:var(--gold)" class="stat-count" data-target="98">0</div><div style="color:#888; font-size:0.9rem">% Happy Couples</div></div>
    </div>
</div>

<!-- CEREMONIES -->
<section class="section" id="ceremonies">
    <h2 class="section-title">Our Complete Wedding Package</h2>
    <p class="section-subtitle">Each ceremony crafted with tradition and modern elegance</p>
    <div class="ceremonies-grid">
        <a href="index.php?page=sangeet" class="ceremony-card">
            <div class="ceremony-icon">🎵</div>
            <h3>Sangeet</h3>
            <p>Music, dance, and joyful celebrations the night before the wedding</p>
        </a>
        <a href="index.php?page=mehendi" class="ceremony-card">
            <div class="ceremony-icon">🌿</div>
            <h3>Mehendi</h3>
            <p>Intricate henna designs on front, back, and traditional patterns</p>
        </a>
        <a href="index.php?page=haldi" class="ceremony-card">
            <div class="ceremony-icon">🌻</div>
            <h3>Haldi</h3>
            <p>Sacred turmeric ceremony bringing blessings and glowing skin</p>
        </a>
        <a href="index.php?page=vivaha" class="ceremony-card">
            <div class="ceremony-icon">👰</div>
            <h3>Vivaha</h3>
            <p>The grand wedding ceremony with sacred vows and timeless rituals</p>
        </a>
    </div>
</section>

<!-- PACKAGES -->
<section class="section section-light">
    <h2 class="section-title">Our Wedding Packages</h2>
    <p class="section-subtitle">Choose the perfect package for your special day</p>
    <div class="packages-grid">
        <?php if ($packages_result && $packages_result->num_rows > 0): ?>
            <?php while ($pkg = $packages_result->fetch_assoc()):
                $icons = ['sangeet'=>'🎵','mehendi'=>'🌿','haldi'=>'🌻','vivaha'=>'👰','full'=>'💍'];
                $icon = $icons[$pkg['ceremony_type']] ?? '💍';
                $features = explode(',', $pkg['features']);
                $isFeatured = ($pkg['ceremony_type'] === 'full');
            ?>
            <div class="package-card <?= $isFeatured ? 'featured' : '' ?>">
                <?php if ($isFeatured): ?><div style="text-align:center"><span class="package-badge">⭐ Most Popular</span></div><?php endif; ?>
                <div class="package-header">
                    <div class="package-icon"><?= $icon ?></div>
                    <h3><?= htmlspecialchars($pkg['name']) ?></h3>
                    <div class="package-price">₹<?= number_format($pkg['price']) ?> <span>/ package</span></div>
                    <div style="font-size:0.85rem; color:#888; margin-top:4px"><?= htmlspecialchars($pkg['duration']) ?></div>
                </div>
                <div class="package-body">
                    <p style="font-size:0.9rem; color:#777; margin-bottom:16px"><?= htmlspecialchars($pkg['description']) ?></p>
                    <ul class="package-features">
                        <?php foreach(array_slice($features, 0, 5) as $f): ?>
                        <li><?= htmlspecialchars(trim($f)) ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <a href="index.php?page=booking&package=<?= $pkg['id'] ?>" class="btn btn-primary" style="width:100%; text-align:center">Book This Package</a>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="text-align:center; color:#888; grid-column:1/-1">Packages loading... Please set up the database.</p>
        <?php endif; ?>
    </div>
</section>

<!-- WHY US -->
<section class="section">
    <h2 class="section-title">Why Choose AuraWedding?</h2>
    <p class="section-subtitle">We make your dream wedding a beautiful reality</p>
    <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(240px,1fr)); gap:28px; max-width:1100px; margin:0 auto">
        <?php
        $features = [
            ['🏆','Expert Planners','15+ experienced wedding planners to handle every detail'],
            ['📸','Photography','Professional photography and videography teams'],
            ['🍽️','Catering','Exquisite vegetarian and non-vegetarian catering options'],
            ['🌺','Floral Decor','Stunning floral arrangements for all ceremonies'],
            ['🎪','Venue Setup','Complete venue decoration and management'],
            ['💌','24/7 Support','Round-the-clock support from booking to ceremony'],
        ];
        foreach ($features as [$icon, $title, $desc]):
        ?>
        <div style="background:#fff; border-radius:16px; padding:28px 22px; box-shadow:0 4px 20px rgba(0,0,0,0.06); text-align:center">
            <div style="font-size:2.5rem; margin-bottom:14px"><?= $icon ?></div>
            <h3 style="font-size:1.1rem; font-weight:700; color:var(--dark); margin-bottom:8px"><?= $title ?></h3>
            <p style="font-size:0.9rem; color:#888; line-height:1.5"><?= $desc ?></p>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- TESTIMONIALS -->
<section class="section section-light">
    <h2 class="section-title">Happy Couples 💑</h2>
    <p class="section-subtitle">What our clients say about us</p>
    <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(280px,1fr)); gap:24px; max-width:1100px; margin:0 auto">
        <?php
        $testimonials = [
            ['Priya & Rahul', 'Mumbai', '⭐⭐⭐⭐⭐', 'AuraWedding made our Vivaha ceremony absolutely magical! Every detail was perfect.'],
            ['Sneha & Karan', 'Pune', '⭐⭐⭐⭐⭐', 'The Mehendi and Haldi arrangements were stunning. Our guests were amazed!'],
            ['Anita & Vikram', 'Delhi', '⭐⭐⭐⭐⭐', 'From Sangeet to Vivaha, every ceremony was beautifully organized. Highly recommended!'],
        ];
        foreach ($testimonials as [$name, $city, $stars, $review]):
        ?>
        <div style="background:#fff; border-radius:16px; padding:28px; box-shadow:0 4px 20px rgba(0,0,0,0.06)">
            <div style="font-size:1.1rem; margin-bottom:12px"><?= $stars ?></div>
            <p style="font-size:0.95rem; color:#555; line-height:1.7; margin-bottom:16px; font-style:italic">"<?= $review ?>"</p>
            <div style="font-weight:700; color:var(--dark)"><?= $name ?></div>
            <div style="font-size:0.85rem; color:#888">📍 <?= $city ?></div>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- CTA -->
<section style="background:linear-gradient(135deg,var(--dark),#6d1a3c); padding:70px 40px; text-align:center; color:#fff">
    <h2 style="font-family:'Playfair Display',serif; font-size:2.4rem; margin-bottom:16px">Ready to Plan Your Dream Wedding? 💍</h2>
    <p style="font-size:1.1rem; opacity:0.85; margin-bottom:36px">Let us make every moment unforgettable</p>
    <div style="display:flex; gap:16px; justify-content:center; flex-wrap:wrap">
        <a href="index.php?page=booking" class="btn btn-primary" style="font-size:1.1rem; padding:14px 36px">Book Now</a>
        <a href="index.php?page=contact" class="btn btn-outline" style="font-size:1.1rem; padding:14px 36px; color:#fff; border-color:#fff">Contact Us</a>
    </div>
</section>
