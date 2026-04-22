<div class="page-header">
    <h1>📋 Wedding Planner</h1>
    <p>Your complete guide to planning the perfect wedding</p>
</div>

<section class="section">
    <h2 class="section-title">Wedding Planning Checklist</h2>
    <p class="section-subtitle">Stay organized with our comprehensive timeline</p>

    <div style="max-width:900px; margin:0 auto">
        <?php
        $months = [
            ['12 Months Before', '📅', [
                'Fix your wedding date and budget',
                'Book a wedding planner',
                'Decide on ceremony types (Sangeet, Mehendi, Haldi, Vivaha)',
                'Create initial guest list',
                'Start venue research',
            ]],
            ['8 Months Before', '🏛️', [
                'Finalize and book the venue',
                'Book photographers and videographers',
                'Select your bridal outfit',
                'Book catering service',
                'Send save-the-date cards',
            ]],
            ['6 Months Before', '💐', [
                'Book Mehendi artists',
                'Finalize decoration theme',
                'Book DJ and/or live band for Sangeet',
                'Plan honeymoon (book flights & hotel)',
                'Finalize wedding invitations design',
            ]],
            ['3 Months Before', '💌', [
                'Send wedding invitations',
                'Confirm all vendor bookings',
                'Arrange wedding jewelry',
                'Plan gifts for family members',
                'Do a venue walkthrough',
            ]],
            ['1 Month Before', '✨', [
                'Confirm final guest count',
                'Do bridal trials (makeup, hair)',
                'Final fitting for wedding outfit',
                'Confirm catering menu',
                'Prepare wedding day timeline',
            ]],
            ['1 Week Before', '🎊', [
                'Final venue decoration check',
                'Confirm all vendors with arrival times',
                'Pack everything needed',
                'Rest well and relax!',
                'Enjoy Haldi & Mehendi ceremonies',
            ]],
        ];
        foreach ($months as [$timeline, $icon, $tasks]):
        ?>
        <div style="background:#fff; border-radius:16px; padding:28px; box-shadow:0 4px 20px rgba(0,0,0,0.07); margin-bottom:20px">
            <div style="display:flex; align-items:center; gap:14px; margin-bottom:18px">
                <div style="font-size:2rem"><?= $icon ?></div>
                <h3 style="font-size:1.1rem; font-weight:700; color:var(--dark)"><?= $timeline ?></h3>
            </div>
            <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(280px,1fr)); gap:10px">
                <?php foreach ($tasks as $task): ?>
                <label style="display:flex; align-items:center; gap:10px; cursor:pointer; font-size:0.9rem; color:var(--text); padding:8px 12px; border:1.5px solid #eee; border-radius:8px; transition:all 0.2s" onmouseenter="this.style.borderColor='var(--pink-main)'" onmouseleave="this.style.borderColor='#eee'">
                    <input type="checkbox" style="accent-color:var(--pink-main)"> <?= $task ?>
                </label>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- Budget Calculator -->
<section class="section section-light">
    <h2 class="section-title">Wedding Budget Estimator 💰</h2>
    <p class="section-subtitle">Get an approximate cost for your dream wedding</p>
    <div style="max-width:700px; margin:0 auto; background:#fff; border-radius:20px; padding:36px; box-shadow:0 4px 20px rgba(0,0,0,0.08)">
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:24px">
            <?php
            $items = [
                ['venue','Venue & Decor',80000],
                ['catering','Catering (per person ₹800)',''],
                ['photography','Photography & Video',35000],
                ['mehendi','Mehendi Artists',15000],
                ['music','Music & DJ',25000],
                ['flowers','Floral Arrangements',30000],
            ];
            ?>
            <?php foreach ($items as [$id, $label, $default]): ?>
            <div class="form-group">
                <label><?= $label ?></label>
                <input type="number" id="<?= $id ?>" placeholder="₹ Amount" value="<?= $default ?>" oninput="calculateTotal()">
            </div>
            <?php endforeach; ?>
        </div>
        <div style="text-align:center; padding:20px; background:var(--pink-light); border-radius:12px">
            <div style="font-size:0.9rem; color:#888; margin-bottom:8px">Estimated Total Budget</div>
            <div id="total" style="font-size:2.2rem; font-weight:800; color:var(--pink-main)">₹0</div>
        </div>
        <div style="text-align:center; margin-top:20px">
            <a href="index.php?page=booking" class="btn btn-primary">Book With This Budget</a>
        </div>
    </div>
</section>

<section class="section">
    <h2 class="section-title">Need Help Planning? 💬</h2>
    <p class="section-subtitle">Our expert planners are here to help</p>
    <div style="text-align:center">
        <div style="display:inline-flex; gap:16px; flex-wrap:wrap; justify-content:center">
            <a href="tel:+919876543210" class="btn btn-primary" style="font-size:1rem; padding:14px 32px">📞 Call Us</a>
            <a href="index.php?page=booking" class="btn btn-gold" style="font-size:1rem; padding:14px 32px">💍 Book Now</a>
            <a href="index.php?page=contact" class="btn btn-outline" style="font-size:1rem; padding:14px 32px">✉️ Contact Us</a>
        </div>
    </div>
</section>

<script>
function calculateTotal() {
    const ids = ['venue','catering','photography','mehendi','music','flowers'];
    let total = 0;
    ids.forEach(id => {
        const val = parseFloat(document.getElementById(id)?.value || 0);
        if (!isNaN(val)) total += val;
    });
    document.getElementById('total').textContent = '₹' + total.toLocaleString('en-IN');
}
calculateTotal();
</script>
