<?php
$gallery = $conn->query("SELECT * FROM gallery WHERE ceremony_type='mehendi' AND is_active=1 ORDER BY id");
$subCats = $conn->query("SELECT DISTINCT sub_category FROM gallery WHERE ceremony_type='mehendi' AND is_active=1");
?>

<div class="page-header" style="background:linear-gradient(135deg,#e8f5e9,#fce4ec)">
    <h1>🌿 Mehendi Ceremony</h1>
    <p>Intricate henna art for your beautiful hands and feet</p>
</div>

<section class="section">

    <!-- Main Filter Tabs -->
    <div class="gallery-filter">
        <button class="filter-btn active" data-category="all" onclick="filterCategory('all')">
            🌿 All Designs
        </button>

        <?php if ($subCats): ?>
            <?php while ($sub = $subCats->fetch_assoc()): ?>

                <button class="filter-btn"
                        data-category="<?= htmlspecialchars($sub['sub_category']) ?>"
                        onclick="filterCategory('<?= htmlspecialchars($sub['sub_category']) ?>')">

                    <?php
                    $icons = [
                        'Mehendi Venue' => '🏛️',
                        'Front-side Mehendi' => '🤚',
                        'Back-side Mehendi' => '👋'
                    ];

                    echo ($icons[$sub['sub_category']] ?? '🌿') . ' ' . htmlspecialchars($sub['sub_category']);
                    ?>
                </button>

            <?php endwhile; ?>
        <?php endif; ?>
    </div>

    <!-- Sub Filter -->
    <div class="sub-filter" id="sub-filter" style="display:none">
        <button class="sub-filter-btn active" data-sub="all">All</button>
        <button class="sub-filter-btn" data-sub="Mehendi Venue">🏛️ Mehendi Venue</button>
        <button class="sub-filter-btn" data-sub="Front-side Mehendi">🤚 Front-side Mehendi</button>
        <button class="sub-filter-btn" data-sub="Back-side Mehendi">👋 Back-side Mehendi</button>
    </div>

    <!-- Gallery Grid -->
    <div class="gallery-grid" id="gallery-grid">

        <!-- Database Images -->
        <?php if ($gallery && $gallery->num_rows > 0): ?>

            <?php while ($img = $gallery->fetch_assoc()): ?>

                <div class="gallery-item"
                     data-category="<?= htmlspecialchars($img['sub_category']) ?>"
                     data-sub="<?= htmlspecialchars($img['sub_category']) ?>">

                    <img src="<?= htmlspecialchars($img['image_url']) ?>"
                         alt="<?= htmlspecialchars($img['title']) ?>"
                         loading="lazy"
                         style="width:100%; height:250px; object-fit:cover; border-radius:12px">
                </div>

            <?php endwhile; ?>

        <?php else: ?>

            <!-- Placeholder Images -->
            <?php
            $mehendiImages = [
                ['https://images.unsplash.com/photo-1583939003579-730e3918a45a?w=400', 'Mehendi Venue'],
                ['https://images.unsplash.com/photo-1606800052052-a08af7148866?w=400', 'Mehendi Venue'],
                ['https://images.unsplash.com/photo-1519741497674-611481863552?w=400', 'Mehendi Venue'],
                ['https://images.unsplash.com/photo-1583697733328-9b7d81eedc18?w=400', 'Front-side Mehendi'],
                ['https://images.unsplash.com/photo-1591824438708-ce405f36ba3d?w=400', 'Front-side Mehendi'],
                ['https://images.unsplash.com/photo-1469371670807-013ccf25f16a?w=400', 'Front-side Mehendi'],
                ['https://images.unsplash.com/photo-1542051841857-5f90071e7989?w=400', 'Back-side Mehendi'],
                ['https://images.unsplash.com/photo-1519225421980-715cb0215aed?w=400', 'Back-side Mehendi'],
                ['https://images.unsplash.com/photo-1465495976277-4387d4b0b4c6?w=400', 'Mehendi Venue'],
                ['https://images.unsplash.com/photo-1531058020387-3be344556be6?w=400', 'Front-side Mehendi'],
            ];

            foreach ($mehendiImages as [$url, $sub]):
            ?>

                <div class="gallery-item"
                     data-category="<?= htmlspecialchars($sub) ?>"
                     data-sub="<?= htmlspecialchars($sub) ?>">

                    <img src="<?= htmlspecialchars($url) ?>"
                         alt="Mehendi Design"
                         loading="lazy"
                         style="width:100%; height:250px; object-fit:cover; border-radius:12px">

                </div>

            <?php endforeach; ?>

        <?php endif; ?>

    </div>

</section>

<!-- Artists Section -->
<section class="section section-light">

    <h2 class="section-title">Our Mehendi Artists</h2>
    <p class="section-subtitle">
        Professional artists with years of experience
    </p>

    <div style="
        display:grid;
        grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
        gap:24px;
        max-width:900px;
        margin:0 auto;
    ">

        <?php
        $artists = [
            ['🎨', 'Priya Sharma', '10+ Years Experience', 'Rajasthani & Arabic Expert'],
            ['🎨', 'Kavya Mehta', '8 Years Experience', 'Bridal & Contemporary'],
            ['🎨', 'Rekha Patel', '12 Years Experience', 'Traditional & Indo-Arabic'],
        ];

        foreach ($artists as [$icon, $name, $exp, $spec]):
        ?>

            <div style="
                background:#fff;
                border-radius:16px;
                padding:28px;
                text-align:center;
                box-shadow:0 4px 20px rgba(0,0,0,0.06);
            ">

                <div style="font-size:3rem; margin-bottom:14px">
                    <?= $icon ?>
                </div>

                <h3 style="font-weight:700; color:var(--dark)">
                    <?= $name ?>
                </h3>

                <p style="
                    font-size:0.85rem;
                    color:var(--pink-main);
                    font-weight:600;
                ">
                    <?= $exp ?>
                </p>

                <p style="
                    font-size:0.85rem;
                    color:#888;
                    margin-top:6px;
                ">
                    <?= $spec ?>
                </p>

            </div>

        <?php endforeach; ?>

    </div>

</section>

<div style="text-align:center; padding:40px">
    <a href="index.php?page=booking&ceremony=mehendi"
       class="btn btn-primary"
       style="font-size:1.1rem; padding:14px 36px">

        Book Mehendi Ceremony

    </a>
</div>

<script>
function filterCategory(category) {

    const items = document.querySelectorAll('.gallery-item');
    const buttons = document.querySelectorAll('.filter-btn');

    buttons.forEach(btn => {
        btn.classList.remove('active');

        if (btn.dataset.category === category) {
            btn.classList.add('active');
        }
    });

    items.forEach(item => {

        if (category === 'all' || item.dataset.category === category) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }

    });
}
</script>