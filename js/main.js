// AuraWedding - Main JS

// Gallery Filter
function initGalleryFilter() {
    const filterBtns = document.querySelectorAll('.filter-btn');
    const subFilterBtns = document.querySelectorAll('.sub-filter-btn');
    const galleryItems = document.querySelectorAll('.gallery-item');

    filterBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            filterBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            const category = btn.dataset.category;

            galleryItems.forEach(item => {
                if (category === 'all' || item.dataset.category === category) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });

            // Reset sub filter
            document.querySelectorAll('.sub-filter').forEach(sf => {
                sf.style.display = (category !== 'all') ? 'flex' : 'none';
            });
        });
    });

    subFilterBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            subFilterBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            const sub = btn.dataset.sub;

            galleryItems.forEach(item => {
                const visible = sub === 'all' || item.dataset.sub === sub;
                item.style.display = visible ? 'block' : 'none';
            });
        });
    });
}

// Lightbox
function initLightbox() {
    const lightbox = document.getElementById('lightbox');
    if (!lightbox) return;
    const lbImg = lightbox.querySelector('img');
    const lbClose = lightbox.querySelector('.lightbox-close');

    document.querySelectorAll('.gallery-item img').forEach(img => {
        img.addEventListener('click', () => {
            lbImg.src = img.src;
            lightbox.classList.add('open');
        });
    });

    lbClose.addEventListener('click', () => lightbox.classList.remove('open'));
    lightbox.addEventListener('click', (e) => {
        if (e.target === lightbox) lightbox.classList.remove('open');
    });
}

// Auth tabs
function initAuthTabs() {
    const tabs = document.querySelectorAll('.auth-tab');
    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            tabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            const target = tab.dataset.tab;
            document.querySelectorAll('.auth-form').forEach(f => {
                f.style.display = f.id === target ? 'block' : 'none';
            });
        });
    });
}

// Animate on scroll
function initScrollAnimation() {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, { threshold: 0.1 });

    document.querySelectorAll('.ceremony-card, .package-card, .gallery-item').forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(20px)';
        el.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
        observer.observe(el);
    });
}

// Smooth scroll
document.querySelectorAll('a[href^="#"]').forEach(a => {
    a.addEventListener('click', e => {
        const target = document.querySelector(a.getAttribute('href'));
        if (target) {
            e.preventDefault();
            target.scrollIntoView({ behavior: 'smooth' });
        }
    });
});

// Auto-dismiss alerts
document.querySelectorAll('.alert').forEach(alert => {
    setTimeout(() => {
        alert.style.transition = 'opacity 0.5s';
        alert.style.opacity = '0';
        setTimeout(() => alert.remove(), 500);
    }, 4000);
});

// Counter animation for stats
function animateCounters() {
    document.querySelectorAll('.stat-count').forEach(counter => {
        const target = parseInt(counter.dataset.target);
        let count = 0;
        const step = Math.ceil(target / 60);
        const timer = setInterval(() => {
            count += step;
            if (count >= target) { count = target; clearInterval(timer); }
            counter.textContent = count;
        }, 30);
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initGalleryFilter();
    initLightbox();
    initAuthTabs();
    initScrollAnimation();
    animateCounters();

    // Booking ceremony checkboxes visual
    document.querySelectorAll('.ceremony-checkbox').forEach(cb => {
        const label = cb.nextElementSibling;
        cb.addEventListener('change', () => {
            if (label) label.style.background = cb.checked ? '#fce4ec' : '';
        });
    });
});
