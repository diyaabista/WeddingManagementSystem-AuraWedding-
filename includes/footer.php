<?php if (!in_array($page, ['login','register'])): ?>
<footer>
    <div class="footer-grid">
        <div class="footer-col">
            <h4>💍 AuraWedding</h4>
            <p>Your dream wedding, managed perfectly. From intimate Haldi ceremonies to grand Vivaha celebrations.</p>
            <div style="margin-top:16px; display:flex; gap:12px;">
                <a href="#">📘</a>
                <a href="#">📸</a>
                <a href="#">🐦</a>
                <a href="#">▶️</a>
            </div>
        </div>
        <div class="footer-col">
            <h4>Ceremonies</h4>
            <a href="index.php?page=sangeet">🎵 Sangeet</a>
            <a href="index.php?page=mehendi">🌿 Mehendi</a>
            <a href="index.php?page=haldi">🌻 Haldi</a>
            <a href="index.php?page=vivaha">👰 Vivaha</a>
        </div>
        <div class="footer-col">
            <h4>Quick Links</h4>
            <a href="index.php?page=wedding-planner">Wedding Planner</a>
            <a href="index.php?page=booking">Book Now</a>
            <a href="index.php?page=contact">Contact Us</a>
            <a href="index.php?page=login">Login / Register</a>
        </div>
        <div class="footer-col">
            <h4>Contact</h4>
            <p>📞 +91 98765 43210</p>
            <p>✉️ hello@aurawedding.com</p>
            <p>📍 Mumbai, Maharashtra, India</p>
            <p style="margin-top:8px">⏰ Mon–Sat: 9AM – 7PM</p>
        </div>
    </div>
    <div class="footer-bottom">
        <p>© <?= date('Y') ?> AuraWedding. All rights reserved. | Crafted with ❤️ for beautiful weddings</p>
    </div>
</footer>
<?php endif; ?>

<!-- Lightbox -->
<div class="lightbox" id="lightbox">
    <span class="lightbox-close" onclick="document.getElementById('lightbox').classList.remove('open')">&times;</span>
    <img src="" alt="Gallery Image">
</div>

<script src="js/main.js"></script>
</body>
</html>
