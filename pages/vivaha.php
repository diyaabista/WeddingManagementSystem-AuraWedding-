<?php
$gallery = $conn->query("
    SELECT * 
    FROM gallery 
    WHERE ceremony_type='vivaha' 
    AND is_active=1 
    ORDER BY id
");
?>

<div class="page-header" style="background:linear-gradient(135deg,#fce4ec,#f3e5f5)">
    <h1>👰 Vivaha — The Grand Wedding</h1>
    <p>Sacred vows, timeless rituals, and eternal love</p>
</div>

<section class="section">

    <h2 class="section-title">The Sacred Union 💍</h2>
    <p class="section-subtitle">
        Your Vivaha ceremony, crafted with perfection
    </p>

    <!-- Gallery -->
    <div class="gallery-grid" style="max-width:1100px; margin:0 auto 50px">

        <?php if ($gallery && $gallery->num_rows > 0): ?>

            <?php while ($img = $gallery->fetch_assoc()): ?>

                <?php
                $image = trim($img['image_url']);

                // Skip empty images
                if (empty($image)) {
                    continue;
                }
                ?>

                <div class="gallery-item">

                    <img
                        src="<?= htmlspecialchars($image) ?>"
                        alt="<?= htmlspecialchars($img['title'] ?? 'Vivaha Image') ?>"
                        loading="lazy"
                        onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1511285560929-80b456fea0bc?w=600';"
                        style="
                            width:100%;
                            height:260px;
                            object-fit:cover;
                            border-radius:16px;
                            display:block;
                        "
                    >

                </div>

            <?php endwhile; ?>

        <?php else: ?>

            <?php
            // Fallback Images
            $vivahaImages = [
                'https://images.unsplash.com/photo-1511285560929-80b456fea0bc?w=600',
                'https://images.unsplash.com/photo-1522673607200-164d1b6ce486?w=600',
                'https://images.unsplash.com/photo-1519225421980-715cb0215aed?w=600',
                'https://images.unsplash.com/photo-1469371670807-013ccf25f16a?w=600',
                'https://images.unsplash.com/photo-1519741497674-611481863552?w=600',
                'https://images.unsplash.com/photo-1507504031003-b417219a0fde?w=600',
                'https://images.unsplash.com/photo-1520854221256-17451cc331bf?w=600',
                'https://images.unsplash.com/photo-1545239351-1141bd82e8a6?w=600'
            ];

            foreach ($vivahaImages as $image):
            ?>

                <div class="gallery-item">

                    <img
                        src="<?= htmlspecialchars($image) ?>"
                        alt="Vivaha Ceremony"
                        loading="lazy"
                        style="
                            width:100%;
                            height:260px;
                            object-fit:cover;
                            border-radius:16px;
                            display:block;
                        "
                    >

                </div>

            <?php endforeach; ?>

        <?php endif; ?>

    </div>

    <!-- Services -->
    <div style="
        display:grid;
        grid-template-columns:repeat(auto-fit,minmax(250px,1fr));
        gap:24px;
        max-width:1100px;
        margin:0 auto;
    ">

        <?php
        $services = [
            ['🏛️', 'Grand Mandap', 'Custom mandap designs in floral, gold, or traditional themes'],
            ['🌺', 'Floral Arrangements', 'Fresh flowers sourced daily for a vibrant ceremony'],
            ['📸', 'Photography & Video', 'Professional teams to capture every sacred moment'],
            ['🍽️', 'Grand Catering', 'Luxury vegetarian and non-veg wedding menus'],
            ['💃', 'Bridal Entry', 'Spectacular bride entry with flowers, lights, and dhol'],
            ['🎵', 'Live Music', 'Traditional and modern music throughout the ceremony'],
        ];

        foreach ($services as [$icon, $title, $desc]):
        ?>

            <div style="
                background:#fff;
                border-radius:18px;
                padding:24px;
                box-shadow:0 4px 20px rgba(0,0,0,0.06);
                transition:0.3s;
            ">

                <div style="font-size:2rem; margin-bottom:12px">
                    <?= $icon ?>
                </div>

                <h3 style="
                    font-weight:700;
                    color:var(--dark);
                    margin-bottom:8px;
                ">
                    <?= $title ?>
                </h3>

                <p style="
                    font-size:0.92rem;
                    color:#777;
                    line-height:1.6;
                ">
                    <?= $desc ?>
                </p>

            </div>

        <?php endforeach; ?>

    </div>

</section>

<!-- Timeline -->
<section class="section section-light">

    <h2 class="section-title">Ceremony Timeline</h2>

    <p class="section-subtitle">
        A traditional Vivaha ceremony experience
    </p>

    <div style="
        max-width:750px;
        margin:0 auto;
        display:flex;
        flex-direction:column;
        gap:20px;
    ">

        <?php
        $timeline = [
            ['🌅', '7:00 AM', 'Baraat Arrival', 'Groom procession with music and celebration'],
            ['💐', '9:00 AM', 'Jaimala Ceremony', 'Exchange of garlands between bride and groom'],
            ['🔥', '10:00 AM', 'Sacred Rituals', 'Kanyadaan and seven sacred vows'],
            ['💍', '12:00 PM', 'Sindoor Ritual', 'Final sacred marriage rituals'],
            ['🍽️', '1:00 PM', 'Wedding Feast', 'Lunch celebration with guests'],
            ['🌙', '7:00 PM', 'Reception Party', 'Dinner, dance, and celebration'],
        ];

        foreach ($timeline as [$icon, $time, $title, $desc]):
        ?>

            <div style="
                display:flex;
                gap:20px;
                align-items:center;
                background:#fff;
                border-radius:16px;
                padding:22px;
                box-shadow:0 4px 16px rgba(0,0,0,0.06);
            ">

                <div style="
                    font-size:2rem;
                    flex-shrink:0;
                ">
                    <?= $icon ?>
                </div>

                <div style="
                    min-width:90px;
                    color:var(--pink-main);
                    font-weight:700;
                    font-size:0.9rem;
                ">
                    <?= $time ?>
                </div>

                <div>

                    <div style="
                        font-weight:700;
                        color:var(--dark);
                        margin-bottom:4px;
                    ">
                        <?= $title ?>
                    </div>

                    <div style="
                        color:#777;
                        font-size:0.9rem;
                    ">
                        <?= $desc ?>
                    </div>

                </div>

            </div>

        <?php endforeach; ?>

    </div>

</section>

<!-- CTA -->
<div style="
    text-align:center;
    padding:60px 20px;
    background:linear-gradient(135deg,var(--pink-light),#f3e5f5);
">

    <h2 style="
        font-family:'Playfair Display',serif;
        font-size:2.2rem;
        color:var(--dark);
        margin-bottom:12px;
    ">
        Begin Your Forever Today 💍
    </h2>

    <p style="
        color:#777;
        margin-bottom:30px;
        font-size:1rem;
    ">
        Starting from ₹1,50,000 for a complete Vivaha ceremony
    </p>

    <a href="index.php?page=booking&ceremony=vivaha"
       class="btn btn-primary"
       style="
            font-size:1.1rem;
            padding:14px 36px;
       ">

        Book Vivaha Ceremony

    </a>

</div>