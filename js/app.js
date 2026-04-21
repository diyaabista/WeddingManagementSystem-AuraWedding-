// ============================================================
// AuraWedding - Main JavaScript (js/app.js)
// DHTML: Gallery Filter, Budget Calculator, Form Validation
// ============================================================

const AuraWedding = {

  // ---- STATE ----
  currentPage: 'home',
  currentUser: null,
  selectedMenuIds: [],
  selectedMandap: null,
  myList: [],
  currentLightboxItem: null,
  allMenuItems: [],

  // ---- INIT ----
  async init() {
    console.log('🌸 AuraWedding.init() starting...');
    
    // EXPOSE AuraWedding to window for direct html access
    window.AuraWedding = this;
    
    try {
      await this.checkSession();
      console.log('✅ checkSession complete');
      
      this.bindNav();
      console.log('✅ bindNav complete');
      
      this.initGallery();
      console.log('✅ initGallery complete');
      
      this.initBudgetCalculator();
      console.log('✅ initBudgetCalculator complete');
      
      this.initFormValidation();
      console.log('✅ initFormValidation complete - Login form listener attached');
      
      this.bindModalEvents();
      console.log('✅ bindModalEvents complete');
      
      this.loadMyList();
      console.log('✅ loadMyList complete');
      
      console.log('🌸 AuraWedding initialized successfully!');
    } catch(error) {
      console.error('❌ AuraWedding.init() error:', error);
    }
  },

  // ---- SESSION CHECK ----
  async checkSession() {
    try {
      const res = await fetch('php/auth_simple.php', {
        method: 'POST',
        headers: { 
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        },
        body: new URLSearchParams({ action: 'check' }),
        credentials: 'include'
      });
      const data = await res.json();
      if (data.logged_in) {
        this.currentUser = { 
          name: data.user_name, 
          wedding_id: data.wedding_id, 
          is_admin: data.is_admin, 
          login_type: data.login_type,
          is_planner: data.is_planner
        };
        this.updateAuthUI(true);
      } else {
        this.updateAuthUI(false);
      }
    } catch (e) {
      console.log('Session check failed or skipped:', e);
      this.updateAuthUI(false);
    }
  },

  updateAuthUI(loggedIn) {
    console.log('🎨 updateAuthUI called:', { loggedIn, currentUser: this.currentUser });
    
    const loginBtn  = document.getElementById('loginBtn');
    const logoutBtn = document.getElementById('logoutBtn');
    const userName  = document.getElementById('userName');
    const dashLink  = document.getElementById('dashLink');
    const bookNowBtn = document.getElementById('bookNowBtn');
    const plannerLink = document.getElementById('plannerLink');
    const beginJourneyBtn = document.getElementById('beginJourneyBtn');
    const createPlaylistBtn = document.getElementById('createPlaylistBtn');
    const createPlaylistBtnLogin = document.getElementById('createPlaylistBtnLogin');
    const hotelSection = document.getElementById('hotelBookingSection');
    const rsvpSection = document.getElementById('rsvpSection');
    const budgetSection = document.getElementById('budgetCalculatorSection');
    const customizationSection = document.getElementById('customizationOptionsSection');
    const mandapSelectionSection = document.getElementById('mandapSelectionSection');
    const cateringMenuSection = document.getElementById('cateringMenuSection');
    const weddingPlannerPage = document.getElementById('page-wedding-planner');
    const isAdmin = this.currentUser && this.currentUser.is_admin;
    const isUser = this.currentUser && this.currentUser.login_type === 'user';
    const isPlanner = this.currentUser && this.currentUser.login_type === 'planner';

    console.log('🎨 Auth state:', { isAdmin, isUser, isPlanner });

    if (loginBtn) {
      loginBtn.style.display = loggedIn ? 'none' : 'inline-flex';
      console.log('🎨 Login btn:', loggedIn ? 'HIDDEN' : 'VISIBLE');
    }
    if (logoutBtn) {
      logoutBtn.style.display = loggedIn ? 'inline-flex' : 'none';
      console.log('🎨 Logout btn:', loggedIn ? 'VISIBLE' : 'HIDDEN');
    }
    if (dashLink)  dashLink.style.display  = (loggedIn && !isAdmin && isUser) ? 'inline-flex' : 'none';
    if (bookNowBtn) bookNowBtn.style.display = (loggedIn && isUser) ? 'inline-flex' : 'none';
    if (plannerLink) plannerLink.style.display = (loggedIn && isPlanner) ? 'inline-flex' : 'none';
    if (createPlaylistBtn) createPlaylistBtn.style.display = loggedIn ? 'inline-block' : 'none';
    if (createPlaylistBtnLogin) createPlaylistBtnLogin.style.display = loggedIn ? 'none' : 'block';
    if (beginJourneyBtn) beginJourneyBtn.style.display = loggedIn ? 'none' : 'inline-block';
    if (hotelSection) hotelSection.style.display = (loggedIn && isUser && !isPlanner) ? 'block' : 'none'; // Show for user panel, hide for wedding planner
    if (rsvpSection) rsvpSection.style.display = 'none'; // RSVP section hidden from all views
    if (budgetSection) budgetSection.style.display = (loggedIn && isUser && !isPlanner) ? 'block' : 'none'; // Show for user panel, hide for wedding planner
    if (customizationSection) customizationSection.style.display = (loggedIn && isUser && !isPlanner) ? 'block' : 'none'; // Show for user panel, hide for wedding planner
    if (mandapSelectionSection) mandapSelectionSection.style.display = (loggedIn && !isAdmin) ? 'block' : 'none'; // Show for both user and planner, but disable selection for planner
    if (cateringMenuSection) cateringMenuSection.style.display = (loggedIn && !isAdmin) ? 'block' : 'none'; // Show for both user and planner, but disable selection for planner
    // Wedding planner page visibility handled by CSS active class, NOT inline styles
    if (userName && this.currentUser) {
      userName.textContent = this.currentUser.name;
      console.log('🎨 User name displayed:', this.currentUser.name);
    }

    // Hide gallery pages for non-logged-in users and admins
    // Only show: loggedIn AND NOT admin
    const userPages = ['sangeet', 'mehendi', 'haldi', 'vivaha'];
    const shouldDisplay = loggedIn && !isAdmin;
    
    console.log(`🔍 Gallery display check: loggedIn=${loggedIn}, isAdmin=${isAdmin}, shouldDisplay=${shouldDisplay}`);
    
    // Show/hide gallery nav items
    const galleryNavItems = document.querySelectorAll('.gallery-nav-item');
    galleryNavItems.forEach(item => {
      if (shouldDisplay) {
        item.style.display = '';
        item.classList.remove('hidden-from-guests');
      } else {
        item.style.display = 'none';
        item.classList.add('hidden-from-guests');
      }
    });
    
    userPages.forEach(page => {
      console.log(`🔍 Checking ${page}: shouldDisplay=${shouldDisplay}`);
      
      // Hide/show the page content
      const pageEl = document.getElementById(`page-${page}`);
      if (pageEl) {
        if (shouldDisplay) {
          // Show the page - remove the hidden class so it can be shown
          pageEl.classList.remove('hidden-from-guests');
          console.log(`✅ Page ${page}: ALLOWING DISPLAY`);
        } else {
          // Hide the page - add the hidden class
          pageEl.classList.add('hidden-from-guests');
          pageEl.classList.remove('active'); // Also remove active class
          console.log(`❌ Page ${page}: FORCING HIDDEN`);
        }
      }
    });
  },

  // ---- NAVIGATION ----
  toggleMenu() {
    const navLinks = document.getElementById('navLinks');
    if (navLinks) {
      navLinks.classList.toggle('active');
    }
  },

  bindNav() {
    document.querySelectorAll('[data-page]').forEach(el => {
      el.addEventListener('click', (e) => {
        // Prevent default only if it's an anchor tag to a page
        if (el.tagName === 'A') e.preventDefault();
        const page = el.dataset.page;
        this.navigateTo(page);
        
        // Close mobile nav if open
        const navLinks = document.getElementById('navLinks');
        if (navLinks) {
          navLinks.classList.remove('active');
        }
      });
    });
  },

  navigateTo(page) {
    console.log(`\n🌍 navigateTo('${page}') called`);
    
    // Prevent navigation to gallery pages if not logged in or if admin
    const galleryPages = ['sangeet', 'mehendi', 'haldi', 'vivaha'];
    const isGalleryPage = galleryPages.includes(page);
    const isLoggedIn = this.currentUser && this.currentUser.login_type;
    const isAdmin = this.currentUser && this.currentUser.is_admin;
    
    console.log(`🔍 Page: ${page}, IsGallery: ${isGalleryPage}, LoggedIn: ${!!isLoggedIn}, IsAdmin: ${isAdmin}`);
    
    if (isGalleryPage && (!isLoggedIn || isAdmin)) {
      console.error(`❌ Access denied to gallery page: ${page}`);
      return;
    }
    
    try {
      // Hide all pages
      const allPages = document.querySelectorAll('.section-page');
      console.log(`Found ${allPages.length} total pages`);
      
      allPages.forEach((s, index) => {
        if (s.classList.contains('active')) {
          console.log(`  ${index}. ${s.id} - currently ACTIVE, hiding`);
          s.classList.remove('active');
        }
      });
      
      // Show target page
      const target = document.getElementById('page-' + page);
      if (!target) {
        console.error(`❌ Target NOT found: #page-${page}`);
        return;
      }
      
      console.log(`✅ Found: ${target.id}`);
      target.classList.add('active');
      window.scrollTo({ top: 0, behavior: 'smooth' });
    } catch (error) {
      console.error(`ERROR:`, error);
    }
    
    // Update nav active states
    document.querySelectorAll('[data-page]').forEach(a => {
      a.classList.toggle('active', a.dataset.page === page);
    });
    this.currentPage = page;

    // Load page-specific data
    if (page === 'dashboard') this.loadDashboard();
    if (page === 'guests')    this.loadGuests();
    if (page === 'catering')  this.loadCateringMenu();
    if (page === 'vivaha')    this.loadCateringMenu(); // Load catering menu for vivaha page
    if (page === 'gifts')     this.loadGifts();
    if (page === 'wedding-planner' && this.currentUser && this.currentUser.login_type === 'planner') this.loadPlannerReview(); // Load planner review for wedding planner logins
  },

  // ============================================================
  // GALLERY - Interactive Filter (DHTML)
  // ============================================================
  initGallery() {
    this.generateGalleryItems();
    document.querySelectorAll('.filter-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        this.filterGallery(btn.dataset.filter);
      });
    });
  },

  // Generate 1000+ sample gallery items programmatically
  async generateGalleryItems() {
    const galleries = {
      mehendi: {
        venue: { images: ['images/mehendivenue/Screenshot 2026-04-19 195420.png', 'images/mehendivenue/Screenshot 2026-04-19 195432.png', 'images/mehendivenue/Screenshot 2026-04-19 195502.png', 'images/mehendivenue/Screenshot 2026-04-19 195556.png', 'images/mehendivenue/Screenshot 2026-04-19 195624.png', 'images/mehendivenue/Screenshot 2026-04-19 195640.png', 'images/mehendivenue/Screenshot 2026-04-19 195649.png', 'images/mehendivenue/Screenshot 2026-04-19 195705.png'], label: 'Mehendi Venue', count: 80 },
        frontside: { images: ['images/frontsidemehendidesign/Screenshot 2026-04-19 193829.png', 'images/frontsidemehendidesign/Screenshot 2026-04-19 193845 - Copy.png', 'images/frontsidemehendidesign/Screenshot 2026-04-19 193859 - Copy.png', 'images/frontsidemehendidesign/Screenshot 2026-04-19 193911 - Copy.png', 'images/frontsidemehendidesign/Screenshot 2026-04-19 193927 - Copy.png', 'images/frontsidemehendidesign/Screenshot 2026-04-19 193951 - Copy.png'], label: 'Front-side Mehendi', count: 150 },
        backside: { images: ['images/backsidemehendidesign/arabic_1.png', 'images/backsidemehendidesign/bridal_0.png', 'images/backsidemehendidesign/real_4.jpg', 'images/backsidemehendidesign/real_5.jpg', 'images/backsidemehendidesign/real_6.jpg', 'images/backsidemehendidesign/Screenshot 2026-04-19 193206.png', 'images/backsidemehendidesign/Screenshot 2026-04-19 193437.png', 'images/backsidemehendidesign/Screenshot 2026-04-19 193445.png', 'images/backsidemehendidesign/Screenshot 2026-04-19 193501.png', 'images/backsidemehendidesign/Screenshot 2026-04-19 193516.png', 'images/backsidemehendidesign/Screenshot 2026-04-19 193531.png', 'images/backsidemehendidesign/Screenshot 2026-04-19 193542.png', 'images/backsidemehendidesign/Screenshot 2026-04-19 193606.png'], label: 'Back-side Mehendi', count: 150 },
      },
      haldi: {
        venue: { images: ['images/haldi/Screenshot 2026-04-19 115356.png', 'images/haldi/Screenshot 2026-04-19 115413.png', 'images/haldi/Screenshot 2026-04-19 115430.png', 'images/haldi/Screenshot 2026-04-19 115449.png', 'images/haldi/Screenshot 2026-04-19 115504.png', 'images/haldi/Screenshot 2026-04-19 115526.png', 'images/haldi/Screenshot 2026-04-19 115538.png', 'images/haldi/Screenshot 2026-04-19 115548.png', 'images/haldi/Screenshot 2026-04-19 115603.png', 'images/haldi/Screenshot 2026-04-19 122915.png', 'images/haldi/Screenshot 2026-04-19 122926.png', 'images/haldi/Screenshot 2026-04-19 122938.png', 'images/haldi/Screenshot 2026-04-19 122947.png', 'images/haldi/Screenshot 2026-04-19 122958.png', 'images/haldi/Screenshot 2026-04-19 123020.png', 'images/haldi/Screenshot 2026-04-19 123049.png'], label: 'Haldi Venue', count: 16 },
      },
      sangeet: {
        setup:   { images: ['images/sangeet/Screenshot 2026-04-19 172223.png', 'images/sangeet/Screenshot 2026-04-19 172246.png', 'images/sangeet/Screenshot 2026-04-19 172305.png', 'images/sangeet/Screenshot 2026-04-19 172401.png', 'images/sangeet/Screenshot 2026-04-19 172449.png', 'images/sangeet/Screenshot 2026-04-19 172507.png', 'images/sangeet/Screenshot 2026-04-19 172520.png', 'images/sangeet/Screenshot 2026-04-19 172534.png', 'images/sangeet/Screenshot 2026-04-19 172549.png'], label: 'Sangeet Setup', count: 9 },
      },
      vivaha: {
        catering:  { emoji: ['🍛','🍽️','🥘','🍲','🥗','🍜'], label: 'Catering Setup', count: 80 },
      }
    };

    // Merge database images with fallback images
    try {
      const dbCategories = {
        'mehendi-venue': 'mehendi.venue',
        'mehendi-frontside': 'mehendi.frontside',
        'mehendi-backside': 'mehendi.backside',
        'haldi-venue': 'haldi.venue',
        'sangeet-setup': 'sangeet.setup'
      };

      for (const [dbCat, path] of Object.entries(dbCategories)) {
        const response = await fetch(`php/gallery.php?action=list&category=${dbCat}`);
        const data = await response.json();
        if (data.success && data.images) {
          const [section, sub] = path.split('.');
          if (galleries[section] && galleries[section][sub]) {
             // Store the full objects including DB IDs
             galleries[section][sub].dbImages = data.images;
          }
        }
      }
    } catch (e) {
      console.log('Database gallery fetch failed, using fallbacks only');
    }

    Object.entries(galleries).forEach(([section, categories]) => {
      Object.entries(categories).forEach(([catKey, cat]) => {
        const galleryEl = document.getElementById(`gallery-${section}-${catKey}`);
        if (!galleryEl) return;
        galleryEl.innerHTML = ''; // Clear existing

        const categoryKey = `${section}_${catKey}`;
        
        // Render DB images first
        if (cat.dbImages) {
          cat.dbImages.forEach((imgObj, idx) => {
            const item = document.createElement('div');
            item.className = 'gallery-item';
            item.dataset.category = categoryKey;
            item.innerHTML = `
              <img src="${imgObj.image_url}" alt="${cat.label}" style="width:100%;height:100%;object-fit:cover;border-radius:10px;">
              <div class="gallery-overlay">${imgObj.title || cat.label}</div>
            `;
            item.addEventListener('click', () => this.openLightbox(imgObj.image_url, imgObj.title || `${cat.label} #${idx+1}`, true, imgObj.id, categoryKey));
            galleryEl.appendChild(item);
          });
        }

        // Add fallback items if count > dbImages count
        const dbCount = cat.dbImages ? cat.dbImages.length : 0;
        for (let i = dbCount; i < cat.count; i++) {
          const item = document.createElement('div');
          item.className = 'gallery-item';
          item.dataset.category = categoryKey;
          
          let content = '';
          let thumbSrc = '';
          const hasImages = Array.isArray(cat.images);
          
          if (hasImages && cat.images.length > 0) {
              thumbSrc = cat.images[i % cat.images.length];
              content = `<img src="${thumbSrc}" alt="${cat.label}" style="width:100%;height:100%;object-fit:cover;border-radius:10px;">`;
          } else {
              const emojiList = cat.emoji || ['🌸'];
              const emoji = emojiList[i % emojiList.length];
              content = `<div class="gallery-img">${emoji}</div>`;
              thumbSrc = emoji;
          }
          
          item.innerHTML = `
            ${content}
            <div class="gallery-overlay">${cat.label} #${i + 1}</div>
          `;
          item.addEventListener('click', () => this.openLightbox(thumbSrc, `${cat.label} Design #${i + 1}`, hasImages, null, categoryKey));
          galleryEl.appendChild(item);
        }
      });
    });

    this.updateGalleryCount();
  },

  // Load images from database
  async loadDatabaseImages() {
    try {
      const categories = {
        'mehendi-venue': 'mehendi.venue',
        'mehendi-frontside': 'mehendi.frontside',
        'mehendi-backside': 'mehendi.backside',
        'haldi-venue': 'haldi.venue',
        'sangeet-setup': 'sangeet.setup'
      };
      
      const dbImages = {};
      
      for (const [dbCategory, jsPath] of Object.entries(categories)) {
        const response = await fetch('php/gallery.php?action=list&category=' + encodeURIComponent(dbCategory), {
          credentials: 'include'
        });
        const data = await response.json();
        
        if (data.success && data.images && data.images.length > 0) {
          const [section, key] = jsPath.split('.');
          if (!dbImages[section]) dbImages[section] = {};
          dbImages[section][key] = data.images.map(img => img.image_url);
        }
      }
      
      return dbImages;
    } catch (err) {
      console.error('Error loading database images:', err);
      return null;
    }
  },

  filterGallery(filter) {
    const items = document.querySelectorAll('.gallery-item');
    let visibleCount = 0;
    items.forEach(item => {
      const cat = item.dataset.category || '';
      const show = filter === 'all' || cat.includes(filter);
      item.style.display = show ? '' : 'none';
      if (show) visibleCount++;
    });
    const counter = document.getElementById('galleryCount');
    if (counter) counter.textContent = visibleCount + ' Designs';
  },

  updateGalleryCount() {
    const total = document.querySelectorAll('.gallery-item').length;
    const counter = document.getElementById('galleryCount');
    if (counter) counter.textContent = total + '+ Designs';
  },

  openLightbox(contentSrc, title, isImage = false, imageId = null, category = null) {
    const lb = document.getElementById('lightbox');
    if (!lb) return;
    this.currentLightboxItem = title;
    this.currentLightboxImageId = imageId;
    this.currentLightboxCategory = category;

    const lbContent = document.getElementById('lbEmoji');
    if (isImage) {
        lbContent.innerHTML = `<img src="${contentSrc}" id="lbImageElement" style="width:100%; height:auto; max-height:60vh; object-fit:contain; border-radius:10px;">`;
        lbContent.style.fontSize = "1rem"; // reset large font for images
    } else {
        lbContent.innerHTML = contentSrc;
        lbContent.style.fontSize = "8rem";
    }
    document.getElementById('lbTitle').textContent = title;

    const userView = document.getElementById('lightboxUserView');
    const plannerView = document.getElementById('lightboxPlannerView');
    const isPlanner = this.currentUser && (this.currentUser.login_type === 'planner' || this.currentUser.is_planner);

    console.log('🖼️ Lightbox toggle state:', { isPlanner, loginType: this.currentUser?.login_type, is_planner: this.currentUser?.is_planner });

    if (userView) userView.style.display = isPlanner ? 'none' : 'block';
    if (plannerView) {
      plannerView.style.display = isPlanner ? 'block' : 'none';
      
      // If planner is logged in but the image has no ID (system default image)
      // we still show the replace option but maybe disable it?
      // Or just allow them to "upload fresh" if it's a default.
    }

    lb.classList.add('active');
  },

  async deleteGalleryImage() {
    // Allow deleting default images (no imageId)
    if (!this.currentLightboxImageId) {
      if (!confirm('Are you sure you want to delete this default image? This will be instantly reflected for users.')) return;
      // Send a delete request with a special flag or handle as needed
      try {
        const formData = new FormData();
        formData.append('action', 'delete_default');
        formData.append('category', this.currentLightboxCategory || '');
        const res = await fetch('php/gallery.php', { method: 'POST', body: formData, credentials: 'include' });
        const data = await res.json();
        if (data.success) {
          this.showToast('Default image deleted successfully', 'success');
          this.closeModal('lightbox');
          this.refreshGallery(this.currentLightboxCategory);
        } else {
          this.showToast(data.message || 'Failed to delete default image', 'error');
        }
      } catch (err) {
        console.error('Delete error:', err);
        this.showToast('Error connecting to server', 'error');
      }
      return;
    }

    if (!confirm('Are you sure you want to delete this image? This will be instantly reflected for users.')) return;

    try {
      const formData = new FormData();
      formData.append('action', 'delete');
      formData.append('id', this.currentLightboxImageId);

      const res = await fetch('php/gallery.php', { method: 'POST', body: formData, credentials: 'include' });
      const data = await res.json();

      if (data.success) {
        this.showToast('Image deleted successfully', 'success');
        this.closeModal('lightbox');
        // Instantly Refresh Gallery
        this.refreshGallery(this.currentLightboxCategory);
      } else {
        this.showToast(data.message || 'Failed to delete', 'error');
      }
    } catch (err) {
      console.error('Delete error:', err);
      this.showToast('Error connecting to server', 'error');
    }
  },

  async replaceGalleryImage(input) {
    if (!input.files || !input.files[0]) return;
    
    // Allow replacing even if it's a "system default" (no currentLightboxImageId)
    // In that case, we treat it as a NEW upload for that category.
    const isNewUpload = !this.currentLightboxImageId;

    const file = input.files[0];
    const formData = new FormData();
    formData.append('action', isNewUpload ? 'upload' : 'replace');
    if (!isNewUpload) {
      formData.append('id', this.currentLightboxImageId);
    }
    formData.append('category', this.currentLightboxCategory.replace('_', '-')); // convert mehendi_venue to mehendi-venue
    formData.append('image', file);
    formData.append('title', this.currentLightboxItem);

    try {
      this.showToast(isNewUpload ? 'Uploading custom design...' : 'Uploading replacement...', 'info');
      const res = await fetch('php/gallery.php', { method: 'POST', body: formData, credentials: 'include' });
      const data = await res.json();

      if (data.success) {
        this.showToast(isNewUpload ? 'New design added successfully!' : 'Image replaced successfully', 'success');
        // Close and refresh
        this.closeModal('lightbox');
        
        // Refresh the gallery in background
        this.refreshGallery(this.currentLightboxCategory);
      } else {
        this.showToast(data.message || 'Failed to replace', 'error');
      }
    } catch (err) {
      console.error('Replace/Upload error:', err);
      this.showToast('Error connecting to server', 'error');
    }
    input.value = ''; // clear input
  },

  async refreshGallery(categoryKey) {
    console.log('🔄 Refreshing gallery for:', categoryKey);
    // Logic to reload specific gallery section from DB
    // For simplicity, we can just regenerate the items
    // In a production app, we would only update the changed item
    const gallerySections = {
      'mehendi_venue': 'gallery-mehendi-venue',
      'mehendi_frontside': 'gallery-mehendi-frontside',
      'mehendi_backside': 'gallery-mehendi-backside',
      'haldi_venue': 'gallery-haldi-venue',
      'sangeet_setup': 'gallery-sangeet-setup'
    };

    const elementId = gallerySections[categoryKey];
    if (elementId) {
      const el = document.getElementById(elementId);
      if (el) {
        el.innerHTML = '<div class="loader"></div>';
        // We re-run generateGalleryItems but we could make it more targeted
        await this.generateGalleryItems();
      }
    }
  },

  // ============================================================
  // BUDGET CALCULATOR (Live JS - no page refresh)
  // ============================================================
  initBudgetCalculator() {
    const inputs = document.querySelectorAll('.calc-input');
    inputs.forEach(inp => {
      inp.addEventListener('input', () => this.calculateBudget());
      inp.addEventListener('change', () => this.calculateBudget());
    });
    this.calculateBudget(); // Initial calculation
  },

  calculateBudget() {
    // Catering - Use default plate costs
    const guestCount   = parseInt(document.getElementById('calcGuests')?.value)   || 0;
    const plateVeg     = 800; // Default veg plate cost
    const plateNonVeg  = 1200; // Default non-veg plate cost
    const vegGuests    = parseInt(document.getElementById('calcTotalVegGuests')?.value) || 0;
    const nonVegGuests = parseInt(document.getElementById('calcTotalNonVegGuests')?.value) || 0;
    const cateringCost = (vegGuests * plateVeg) + (nonVegGuests * plateNonVeg);

    // Only calculate catering cost
    const totalCost = cateringCost;
    const perHeadCost = guestCount > 0 ? totalCost / guestCount : 0;

    // Update DOM instantly (DHTML - no refresh)
    this.setCalcDisplay('calcCateringResult', cateringCost);
    this.setCalcDisplay('calcTotal', totalCost);
    this.setCalcDisplay('calcPerHead', perHeadCost);

    // Animate the total
    const totalEl = document.getElementById('calcTotal');
    if (totalEl) {
      totalEl.style.transform = 'scale(1.1)';
      setTimeout(() => { totalEl.style.transform = 'scale(1)'; }, 200);
    }
  },

  setCalcDisplay(id, value) {
    const el = document.getElementById(id);
    if (el) el.textContent = '₹' + value.toLocaleString('en-IN', { maximumFractionDigits: 0 });
  },

  // ============================================================
  // FORM VALIDATION (JS - before server submission)
  // ============================================================
  initFormValidation() {
    console.log('🔧 initFormValidation started with event delegation');
    
    // Use event delegation on the document body for the login form
    document.body.addEventListener('submit', (e) => {
      if (e.target && e.target.id === 'loginForm') {
        console.log('✅ Form delegation caught #loginForm submission');
        this.handleLogin(e);
      }
    });

    // Also delegate click for the submit button specifically if needed
    document.body.addEventListener('click', (e) => {
      const btn = e.target.closest('#loginForm button[type="submit"]');
      if (btn) {
        console.log('✅ Click delegation caught #loginForm submit button');
        // The submit event will handle it, but this ensures we catch it
      }
    });
  },

  // Validate a field and show/hide error
  validate(fieldId, errorId, rules) {
    const field = document.getElementById(fieldId);
    const errEl = document.getElementById(errorId);
    if (!field || !errEl) return true;

    const val = field.value.trim();
    let error = '';

    if (rules.required && !val)          error = rules.required;
    else if (rules.email && val) {
      if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) error = rules.email;
    }
    else if (rules.minLength && val.length < rules.minLength)
      error = `Minimum ${rules.minLength} characters required.`;
    else if (rules.phone && val && !/^[\d\s\+\-]{8,15}$/.test(val))
      error = rules.phone;
    else if (rules.date && val && isNaN(Date.parse(val)))
      error = 'Please enter a valid date.';

    field.classList.toggle('error', !!error);
    errEl.textContent = error;
    errEl.style.display = error ? 'block' : 'none';
    return !error;
  },

  validateAll(validations) {
    return validations.map(v => this.validate(v[0], v[1], v[2])).every(Boolean);
  },

  // ---- LOGIN MODAL SELECTION ----
  openLoginModal(type) {
    const loginTitle = document.getElementById('loginTitle');
    const loginType = document.getElementById('loginType');
    const loginEmail = document.getElementById('loginEmail');
    const loginPassword = document.getElementById('loginPassword');
    
    if (type === 'planner') {
      loginTitle.textContent = '💍 Wedding Planner Login';
      loginType.value = 'planner';
    } else {
      loginTitle.textContent = '👰 User Login';
      loginType.value = 'user';
    }
    
    // Clear form
    if (loginEmail) loginEmail.value = '';
    if (loginPassword) loginPassword.value = '';
    
    this.closeModal('modal-login-select');
    this.openModal('modal-login');
  },

  // Simple public login function - can be called directly from onclick
  simpleLogin() {
    console.log('🔐 simpleLogin() called');
    this.handleLogin(null);
    return false;
  },

  async handleLogin(e) {
    if (e) e.preventDefault();
    const statusDiv = document.getElementById('loginStatus');
    if (statusDiv) {
      statusDiv.style.display = 'block';
      statusDiv.style.color = 'var(--pink-main)';
      statusDiv.textContent = '⌛ Signing in...';
    }

    try {
      console.log('🔐 handleLogin() called');
      
      // Get form fields
      const emailField = document.getElementById('loginEmail');
      const passwordField = document.getElementById('loginPassword');
      const loginTypeField = document.getElementById('loginType');
      
      if (!emailField || !passwordField || !loginTypeField) {
        throw new Error('Form fields not found in DOM');
      }
      
      const email = emailField.value.trim();
      const password = passwordField.value.trim();
      const loginType = loginTypeField.value;
      
      if (!email || !password) {
        throw new Error('Email and password are required');
      }
      
      console.log('🔐 Login attempt:', { email, loginType, passwordLength: password.length });
      console.log('📤 Sending to php/auth_simple.php...');
      
      // Make the fetch request
      const response = await fetch('php/auth_simple.php', {
        method: 'POST',
        headers: { 
          'Content-Type': 'application/x-www-form-urlencoded',
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        },
        body: new URLSearchParams({
          action: 'login',
          email: email,
          password: password,
          login_type: loginType
        }),
        credentials: 'include'
      });
      
      console.log('📡 Response status:', response.status);
      
      const text = await response.text();
      console.log('📝 Raw response:', text);
      
      // Try to parse as JSON
      let data;
      try {
        // Clean text if it contains unexpected output before/after JSON
        const jsonMatch = text.match(/\{[\s\S]*\}/);
        const cleanText = jsonMatch ? jsonMatch[0] : text;
        data = JSON.parse(cleanText);
      } catch (e) {
        throw new Error('Server response is not valid JSON: ' + text);
      }
      
      console.log('✅ Parsed response:', data);
      
      // Check if login was successful
      if (!data.success) {
        throw new Error(data.message || 'Login failed');
      }
      
      // Login successful!
      console.log('✅ Login successful!');
      
      // Set current user
      this.currentUser = {
        name: data.name || email,
        email: email,
        is_admin: data.is_admin || false,
        wedding_id: data.wedding_id,
        login_type: loginType
      };
      
      console.log('✅ User object set:', this.currentUser);
      
      if (statusDiv) {
        statusDiv.style.color = 'green';
        statusDiv.textContent = '✅ Success! Redirecting...';
      }

      // Close modals
      const modalLogin = document.getElementById('modal-login');
      const modalSelect = document.getElementById('modal-login-select');
      if (modalLogin) modalLogin.classList.remove('active');
      if (modalSelect) modalSelect.classList.remove('active');
      console.log('✅ Modals closed');
      
      // Show success message
      alert('✅ Welcome ' + (data.name || 'User') + '!');
      
      // Redirect after short delay
      console.log('🚀 Redirecting...');
      if (loginType === 'planner') {
        console.log('➡️ Redirecting to index.html#wedding-planner');
        window.location.href = 'index.html?page=wedding-planner';
      } else {
        console.log('➡️ Redirecting to user panel');
        window.location.href = 'php/user_dashboard.php';
      }
      
    } catch (error) {
      console.error('❌ Login error:', error);
      if (statusDiv) {
        statusDiv.style.color = 'red';
        statusDiv.textContent = '❌ ' + error.message;
      }
      alert('❌ Login Error:\n' + error.message);
    }
  },

  // ---- REGISTER ----
  async handleRegister(e) {
    e.preventDefault();
    const valid = this.validateAll([
      ['regName',     'regNameErr',     { required: 'Your name is required.' }],
      ['regEmail',    'regEmailErr',    { required: 'Email is required.', email: 'Invalid email.' }],
      ['regPassword', 'regPasswordErr', { required: 'Password required.', minLength: 6 }],
    ]);
    if (!valid) return;

    const formData = new FormData(e.target);
    formData.append('action', 'register');

    try {
      const res  = await fetch('php/auth_simple.php', { method: 'POST', body: formData, credentials: 'include' });
      const data = await res.json();
      if (data.success) {
        this.showToast('Account created! Welcome! 💍', 'success');
        document.getElementById('modal-register').classList.remove('active');
        this.updateAuthUI(true);
      } else {
        this.showToast(data.message, 'error');
      }
    } catch {
      this.showToast('Registration ready — connect to PHP server!', 'info');
    }
  },

  // ---- RSVP FORM VALIDATION ----
  handleRsvp(e) {
    e.preventDefault();
    const valid = this.validateAll([
      ['rsvpName',  'rsvpNameErr',  { required: 'Please enter your full name.' }],
      ['rsvpPhone', 'rsvpPhoneErr', { required: 'Phone number is required.', phone: 'Invalid phone number.' }],
      ['rsvpEmail', 'rsvpEmailErr', { email: 'Invalid email address.' }],
      ['rsvpCount', 'rsvpCountErr', { required: 'Please enter number of guests.' }],
    ]);
    if (!valid) return;
    this.showToast('RSVP submitted successfully! 🎉', 'success');
    e.target.reset();
  },

  // ---- HOTEL BOOKING FORM VALIDATION ----
  handleHotel(e) {
    e.preventDefault();
    const valid = this.validateAll([
      ['hotelGuest',   'hotelGuestErr',   { required: 'Guest name is required.' }],
      ['hotelName',    'hotelNameErr',    { required: 'Hotel name is required.' }],
      ['hotelRoom',    'hotelRoomErr',    { required: 'Room number is required.' }],
      ['hotelCheckIn', 'hotelCheckInErr', { required: 'Check-in date required.', date: true }],
      ['hotelCheckOut','hotelCheckOutErr',{ required: 'Check-out date required.', date: true }],
    ]);
    if (!valid) return;
    this.showToast('Hotel booking saved! 🏨', 'success');
  },

  // ---- GUEST CRUD ----
  async handleGuestSubmit(e) {
    e.preventDefault();
    const valid = this.validateAll([
      ['guestName',  'guestNameErr',  { required: 'Guest name is required.' }],
      ['guestPhone', 'guestPhoneErr', { phone: 'Invalid phone number.' }],
      ['guestEmail', 'guestEmailErr', { email: 'Invalid email.' }],
    ]);
    if (!valid) return;

    const formData = new FormData(e.target);
    const guestId = document.getElementById('guestId')?.value;
    formData.append('action', guestId ? 'update' : 'create');

    try {
      const res  = await fetch('php/guests.php', { method: 'POST', body: formData, credentials: 'include' });
      const data = await res.json();
      if (data.success) {
        this.showToast(guestId ? 'Guest updated! ✅' : 'Guest added! 🎊', 'success');
        this.closeModal('modal-guest');
        this.loadGuests();
      } else {
        this.showToast(data.message, 'error');
      }
    } catch {
      this.showToast('Guest saved (demo mode)! 🌸', 'success');
      this.closeModal('modal-guest');
    }
  },

  // ---- LOAD DASHBOARD ----
  async loadDashboard() {
    console.log('📊 loadDashboard() triggered');
    try {
      // Load bookings first
      await this.loadBookings();
      
      console.log('🌐 Fetching events and stats...');
      const [evRes, gstRes] = await Promise.all([
        fetch('php/events.php?action=list', { credentials: 'include' }),
        fetch('php/guests.php?action=stats', { credentials: 'include' })
      ]);
      
      if (!evRes.ok) throw new Error('Events fetch failed');
      
      const eventsData = await evRes.json();
      const gStats = await gstRes.json();
      
      console.log('✅ Received events:', eventsData);
      
      this.events = eventsData.events || [];  // Store events for editing
      this.renderTimeline(this.events);
      this.renderGuestStats(gStats.stats || {});
    } catch (err) {
      console.warn('⚠️ Dashboard fetch failed, using demo mode:', err);
      this.loadBookings(); // Still load bookings even if other requests fail
      this.renderDemoTimeline();
    }
  },

  renderTimeline(events) {
    const container = document.getElementById('eventTimeline');
    if (!container) return;
    
    // Check if the user is logged in
    const isLoggedIn = this.currentUser && this.currentUser.login_type;
    
    // Sort events by date
    const sortedEvents = [...events].sort((a, b) => {
      const dateA = new Date(a.event_date || '9999-12-31');
      const dateB = new Date(b.event_date || '9999-12-31');
      return dateA - dateB;
    });

    if (!sortedEvents.length) { 
      container.innerHTML = '<div style="text-align:center;padding:40px;background:var(--bg-light);border-radius:15px;border:2px dashed var(--pink-light)"><p class="text-light" style="font-size:1.1rem">No events added yet. Start by adding your first sub-event! ✨</p></div>'; 
      return; 
    }

    const icons = { haldi: '🌼', mehendi: '🌿', sangeet: '🎵', vivaha: '💒' };
    container.innerHTML = sortedEvents.map(ev => `
      <div class="timeline-item">
        <div class="timeline-dot"></div>
        <div class="card" style="margin-left:10px; width: 100%;">
          <div style="display:flex;align-items:center;gap:15px;flex-wrap:wrap">
            <div style="background:var(--gradient-pink); color:white; width:60px; height:60px; border-radius:15px; display:flex; align-items:center; justify-content:center; font-size:2rem; box-shadow:0 4px 15px rgba(246,70,107,0.2)">
              ${icons[ev.event_type]||'💍'}
            </div>
            <div style="flex:1; min-width:200px">
              <h4 style="text-transform:capitalize; margin-bottom:5px; font-size:1.2rem; color:var(--text-dark)">${ev.event_type}</h4>
              <p style="color:var(--text-light);font-size:0.95rem; display:flex; align-items:center; gap:10px">
                <span>📅 ${ev.event_date || 'TBD'}</span>
                ${ev.time_start ? `<span>⏰ ${ev.time_start.substring(0,5)} - ${ev.time_end ? ev.time_end.substring(0,5) : '...'}</span>` : ''}
                <span>📍 ${ev.venue || 'TBD'}</span>
              </p>
              ${ev.notes ? `<p style="margin-top:8px;font-size:0.9rem; color:var(--text-dark); background:rgba(246,70,107,0.05); padding:8px 12px; border-radius:8px; border-left:3px solid var(--pink-mid)">${ev.notes}</p>` : ''}
            </div>
            <div style="display:flex;gap:10px">
              <button class="btn btn-outline btn-sm" style="border-radius:8px" onclick="AuraWedding.editEvent(${ev.id})">✏️ Edit</button>
              <button class="btn btn-danger btn-sm" style="border-radius:8px; background:#ff4d4f; border-color:#ff4d4f" onclick="AuraWedding.deleteEvent(${ev.id})">🗑️ Delete</button>
            </div>
          </div>
        </div>
      </div>
    `).join('');
  },

  renderDemoTimeline() {
    const demoEvents = [
      { id: 1, event_type: 'haldi', event_date: '2025-02-11', venue: 'Family Home Garden', notes: 'Morning ceremony', time_start: '10:00', time_end: '14:00' },
      { id: 2, event_type: 'mehendi', event_date: '2025-02-12', venue: 'Rooftop Terrace', notes: 'Evening event', time_start: '18:00', time_end: '23:00' },
      { id: 3, event_type: 'sangeet', event_date: '2025-02-13', venue: 'Ballroom A', notes: 'Night celebration', time_start: '20:00', time_end: '23:59' },
      { id: 4, event_type: 'vivaha', event_date: '2025-02-14', venue: 'Main Hall', notes: 'The big day! 💍', time_start: '09:00', time_end: '12:00' },
    ];
    this.events = demoEvents;  // Store demo events for editing
    this.renderTimeline(demoEvents);
  },

  renderGuestStats(stats) {
    const el = document.getElementById('guestStats');
    if (!el) return;
    el.innerHTML = `
      <div class="stat-card"><div class="stat-number">${stats.total||0}</div><div class="stat-label">Total Guests</div></div>
      <div class="stat-card"><div class="stat-number" style="color:#56ab2f">${stats.confirmed||0}</div><div class="stat-label">Confirmed</div></div>
      <div class="stat-card"><div class="stat-number" style="color:#f39c12">${stats.pending||0}</div><div class="stat-label">Pending</div></div>
      <div class="stat-card"><div class="stat-number" style="color:#ee0979">${stats.declined||0}</div><div class="stat-label">Declined</div></div>
    `;
  },

  // ---- LOAD GUESTS ----
  async loadGuests() {
    const tbody = document.getElementById('guestTableBody');
    if (!tbody) return;
    tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:30px"><div class="loader"></div></td></tr>';

    try {
      const res  = await fetch('php/guests.php?action=list', { credentials: 'include' });
      const data = await res.json();
      this.renderGuestTable(data.guests || []);
    } catch {
      this.renderDemoGuests();
    }
  },

  renderGuestTable(guests) {
    const tbody = document.getElementById('guestTableBody');
    if (!tbody) return;
    if (!guests.length) {
      tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;color:var(--text-light);padding:40px">No guests yet. Add your first guest! 🌸</td></tr>';
      return;
    }
    tbody.innerHTML = guests.map(g => `
      <tr>
        <td><strong>${g.name}</strong></td>
        <td>${g.phone||'—'}</td>
        <td>${g.relation||'—'}</td>
        <td><span class="badge badge-${g.rsvp_status}">${g.rsvp_status}</span></td>
        <td>${g.hotel_name ? `${g.hotel_name} (Rm ${g.room_number})` : '—'}</td>
        <td style="text-transform:capitalize">${g.side}</td>
        <td>
          <button class="btn btn-outline btn-sm" onclick="AuraWedding.openEditGuest(${JSON.stringify(g).replace(/"/g,"'")})">✏️</button>
          <button class="btn btn-danger btn-sm" onclick="AuraWedding.deleteGuest(${g.id})">🗑️</button>
        </td>
      </tr>
    `).join('');
  },

  renderDemoGuests() {
    const demoGuests = [
      { id:1, name:'Amit Sharma',   phone:'9876543210', relation:'Brother', rsvp_status:'confirmed', hotel_name:'Grand Palace', room_number:'101', side:'bride' },
      { id:2, name:'Sunita Mehta',  phone:'9765432109', relation:'Mother',  rsvp_status:'confirmed', hotel_name:'Grand Palace', room_number:'205', side:'groom' },
      { id:3, name:'Rahul Gupta',   phone:'9654321098', relation:'Friend',  rsvp_status:'pending',   hotel_name:null, room_number:null, side:'both' },
      { id:4, name:'Kavita Singh',  phone:'9543210987', relation:'Cousin',  rsvp_status:'confirmed', hotel_name:'Comfort Inn', room_number:'A12', side:'bride' },
      { id:5, name:'Vikram Joshi',  phone:'9432109876', relation:'Friend',  rsvp_status:'declined',  hotel_name:null, room_number:null, side:'groom' },
    ];
    this.renderGuestTable(demoGuests);
    document.getElementById('guestStats') && this.renderGuestStats({ total:5, confirmed:3, pending:1, declined:1 });
  },

  openAddGuest() {
    document.getElementById('guestId').value = '';
    document.getElementById('guestForm').reset();
    document.getElementById('modalGuestTitle').textContent = 'Add New Guest 🌸';
    this.openModal('modal-guest');
  },

  openEditGuest(guest) {
    document.getElementById('guestId').value      = guest.id;
    document.getElementById('guestName').value    = guest.name;
    document.getElementById('guestPhone').value   = guest.phone || '';
    document.getElementById('guestEmail').value   = guest.email || '';
    document.getElementById('guestSide').value    = guest.side;
    document.getElementById('guestRelation').value= guest.relation || '';
    document.getElementById('guestRsvp').value    = guest.rsvp_status;
    document.getElementById('guestHotel').value   = guest.hotel_name || '';
    document.getElementById('guestRoom').value    = guest.room_number || '';
    document.getElementById('modalGuestTitle').textContent = 'Edit Guest ✏️';
    
    // Set hotel booking dates if they exist
    const checkInInputs = document.getElementById('guestForm').querySelectorAll('input[name="check_in"]');
    const checkOutInputs = document.getElementById('guestForm').querySelectorAll('input[name="check_out"]');
    if (checkInInputs.length > 0) checkInInputs[0].value = guest.check_in || '';
    if (checkOutInputs.length > 0) checkOutInputs[0].value = guest.check_out || '';
    
    this.openModal('modal-guest');
  },

  async deleteGuest(id) {
    if (!confirm('Remove this guest? This cannot be undone.')) return;
    try {
      const fd = new FormData();
      fd.append('action', 'delete'); 
      fd.append('id', id);
      const res  = await fetch('php/guests.php', { method: 'POST', body: fd, credentials: 'include' });
      const data = await res.json();
      if (data.success) { 
        this.showToast('Guest removed.', 'success'); 
        this.loadGuests(); 
      } else {
        this.showToast(data.message || 'Failed to delete guest.', 'error');
        console.error('Delete error:', data);
      }
    } catch (error) {
      this.showToast('Error deleting guest: ' + error.message, 'error');
      console.error('Delete exception:', error);
    }
  },

  async deleteEvent(id) {
    if (!confirm('Delete this event?')) return;
    try {
      const fd = new FormData(); fd.append('action', 'delete'); fd.append('id', id);
      await fetch('php/events.php', { method: 'POST', body: fd, credentials: 'include' });
      this.showToast('Event deleted.', 'success');
      this.loadDashboard();
    } catch {
      this.showToast('Event deleted (demo mode).', 'success');
    }
  },

  // ---- EDIT EVENT ----
  async editEvent(eventId) {
    try {
      const res = await fetch(`php/events.php?action=get&id=${eventId}`, {
        credentials: 'include'
      });
      const data = await res.json();
      if (data.success && data.event) {
        this.populateEventForm(data.event);
        this.openModal('modal-event');
      } else {
        // Fallback to demo mode events
        const event = this.events && this.events.find(e => e.id == eventId);
        if (event) {
          this.populateEventForm(event);
          this.openModal('modal-event');
        } else {
          this.showToast('Could not load event details.', 'error');
        }
      }
    } catch (e) {
      // Demo mode fallback: try to find event in stored events array
      const event = this.events && this.events.find(e => e.id == eventId);
      if (event) {
        this.populateEventForm(event);
        this.openModal('modal-event');
      } else {
        this.showToast('Error loading event details.', 'error');
      }
    }
  },

  populateEventForm(event) {
    document.getElementById('eventId').value = event.id;
    document.getElementById('eventForm').elements['event_type'].value = event.event_type;
    document.getElementById('eventForm').elements['event_date'].value = event.event_date || '';
    document.getElementById('eventForm').elements['venue'].value = event.venue || '';
    document.getElementById('eventForm').elements['time_start'].value = event.time_start || '';
    document.getElementById('eventForm').elements['time_end'].value = event.time_end || '';
    document.getElementById('eventForm').elements['notes'].value = event.notes || '';
    document.querySelector('#modal-event .modal-header h3').textContent = '✏️ Edit Event';
  },

  // ---- OPEN EVENT MODAL FOR NEW EVENT ----
  openAddEvent(type = '') {
    document.getElementById('eventId').value = '';
    document.getElementById('eventForm').reset();
    if (type) {
      document.getElementById('eventForm').elements['event_type'].value = type;
    }
    document.querySelector('#modal-event .modal-header h3').textContent = '📅 Add Sub Event';
    this.openModal('modal-event');
  },

  // ---- LOAD CATERING ----
  async loadCateringMenu() {
    console.log('🍽️ loadCateringMenu called - loading demo catering menu...');
    // Always load demo catering for now to ensure items display
    this.renderDemoCatering();
    
    // Also try to load from database in background
    try {
      const res  = await fetch('php/events.php?action=menu', { credentials: 'include' });
      const data = await res.json();
      
      if (data.menu && data.menu.length > 0) {
        console.log('✅ Loaded', data.menu.length, 'items from database');
        this.renderCateringMenu(data.menu, data.selected || []);
      }
    } catch (error) {
      console.log('Could not load from database, using demo items:', error);
    }
  },

  renderCateringMenu(items, selected) {
    this.selectedMenuIds = [...selected];
    console.log('🍛 renderCateringMenu called with', items.length, 'items');
    
    // Helper function to render items in a container
    const renderItemsToContainer = (containerId, itemList) => {
      const container = document.getElementById(containerId);
      
      if (!container) {
        console.error(`❌ Container NOT FOUND: ${containerId}`);
        return;
      }
      
      if (itemList.length === 0) {
        console.log(`⚠️ ${containerId}: No items to display`);
        container.innerHTML = '';
        return;
      }
      
      console.log(`✅ ${containerId}: Rendering ${itemList.length} items`);
      const html = itemList.map(item => `
        <div style="display:flex;align-items:center;gap:10px;padding:10px;border-radius:8px;transition:all 0.3s;background:${selected.includes(item.id) ? '#ffe0f0' : 'transparent'};cursor:pointer" 
             onclick="AuraWedding.toggleMenuItem(${item.id}, this)">
          <input type="checkbox" ${selected.includes(item.id) ? 'checked' : ''} style="cursor:pointer;width:18px;height:18px" onclick="event.stopPropagation()">
          <span style="flex:1">
            <span style="font-weight:600;color:#333;font-size:0.95rem">${item.item_name}</span>
            <span style="font-size:0.8rem;color:#999;margin-left:8px">₹${item.price_per_plate}</span>
          </span>
        </div>
      `).join('');
      
      container.innerHTML = html;
    };
    
    // Organize items by category
    const mainVeg = items.filter(i => i.category === 'main_veg');
    const mainNonveg = items.filter(i => i.category === 'main_nonveg');
    const riceVeg = items.filter(i => i.category === 'rice_veg');
    const riceNonveg = items.filter(i => i.category === 'rice_nonveg');
    const vegStarters = items.filter(i => i.category === 'starters' && i.is_veg === 1);
    const nonVegStarters = items.filter(i => i.category === 'starters' && i.is_veg === 0);
    const bread = items.filter(i => i.category === 'bread');
    const misc = items.filter(i => i.category === 'misc');
    const desserts = items.filter(i => i.category === 'desserts');
    const iceCream = items.filter(i => i.category === 'ice_cream');
    const drinks = items.filter(i => i.category === 'drinks');
    
    console.log('📊 Categories:', {mainVeg: mainVeg.length, mainNonveg: mainNonveg.length, riceVeg: riceVeg.length, riceNonveg: riceNonveg.length, vegStarters: vegStarters.length, nonVegStarters: nonVegStarters.length, bread: bread.length, misc: misc.length, desserts: desserts.length, iceCream: iceCream.length, drinks: drinks.length});
    
    // Render all sections
    renderItemsToContainer('menu-main_veg', mainVeg);
    renderItemsToContainer('menu-main_nonveg', mainNonveg);
    renderItemsToContainer('menu-rice_veg', riceVeg);
    renderItemsToContainer('menu-rice_nonveg', riceNonveg);
    renderItemsToContainer('menu-veg_starters', vegStarters);
    renderItemsToContainer('menu-non_veg_starters', nonVegStarters);
    renderItemsToContainer('menu-bread', bread);
    renderItemsToContainer('menu-misc', misc);
    renderItemsToContainer('menu-desserts', desserts);
    renderItemsToContainer('menu-ice_cream', iceCream);
    renderItemsToContainer('menu-drinks', drinks);
    
    this.updateMenuCount();
    console.log('✅ renderCateringMenu completed');
  },

  renderDemoCatering() {
    console.log('🍽️ Loading demo catering menu...');
    const demoItems = [
      // MAIN COURSE - Vegetarian
      { id:1, category:'main_veg', item_name:'Aloo Baigan', price_per_plate:120, is_veg:1 },
      { id:2, category:'main_veg', item_name:'Aloo Gobi', price_per_plate:110, is_veg:1 },
      { id:3, category:'main_veg', item_name:'Aloo Methi', price_per_plate:100, is_veg:1 },
      { id:4, category:'main_veg', item_name:'Baingan Masala', price_per_plate:130, is_veg:1 },
      { id:5, category:'main_veg', item_name:'Bombay Aloo', price_per_plate:125, is_veg:1 },
      { id:6, category:'main_veg', item_name:'Chaana Masala', price_per_plate:115, is_veg:1 },
      { id:7, category:'main_veg', item_name:'Chilli Paneer', price_per_plate:140, is_veg:1 },
      { id:8, category:'main_veg', item_name:'Chole Bhature', price_per_plate:150, is_veg:1 },
      { id:9, category:'main_veg', item_name:'Matar Paneer', price_per_plate:135, is_veg:1 },
      { id:10, category:'main_veg', item_name:'Mixed Vegetable Kofta', price_per_plate:145, is_veg:1 },
      { id:11, category:'main_veg', item_name:'Paneer Butter Masala', price_per_plate:160, is_veg:1 },
      { id:12, category:'main_veg', item_name:'Mushroom Masala', price_per_plate:135, is_veg:1 },
      { id:13, category:'main_veg', item_name:'Palak Paneer', price_per_plate:140, is_veg:1 },
      { id:14, category:'main_veg', item_name:'Mixed Vegetables Dry', price_per_plate:130, is_veg:1 },
      
      // MAIN COURSE - Non-Vegetarian
      { id:15, category:'main_nonveg', item_name:'Chicken Biryani', price_per_plate:200, is_veg:0 },
      { id:16, category:'main_nonveg', item_name:'Chicken Pilau', price_per_plate:190, is_veg:0 },
      { id:17, category:'main_nonveg', item_name:'Lamb Biryani', price_per_plate:220, is_veg:0 },
      { id:18, category:'main_nonveg', item_name:'Lamb Kebab Biryani', price_per_plate:210, is_veg:0 },
      { id:19, category:'main_nonveg', item_name:'Lamb Jahi', price_per_plate:215, is_veg:0 },
      { id:20, category:'main_nonveg', item_name:'Butter Chicken', price_per_plate:200, is_veg:0 },
      
      // RICE DISHES - Vegetarian
      { id:21, category:'rice_veg', item_name:'Yellow Rice', price_per_plate:120, is_veg:1 },
      { id:22, category:'rice_veg', item_name:'Chaana Pilau', price_per_plate:130, is_veg:1 },
      { id:23, category:'rice_veg', item_name:'Vegetable Biryani', price_per_plate:135, is_veg:1 },
      { id:24, category:'rice_veg', item_name:'Matar Pilau (Peas)', price_per_plate:125, is_veg:1 },
      
      // RICE DISHES - Non-Vegetarian
      { id:25, category:'rice_nonveg', item_name:'Chicken Biryani', price_per_plate:180, is_veg:0 },
      { id:26, category:'rice_nonveg', item_name:'Chicken Pilau', price_per_plate:170, is_veg:0 },
      { id:27, category:'rice_nonveg', item_name:'Lamb Biryani', price_per_plate:200, is_veg:0 },
      { id:28, category:'rice_nonveg', item_name:'Lamb Kebab Biryani', price_per_plate:195, is_veg:0 },
      { id:29, category:'rice_nonveg', item_name:'Lamb Jahi', price_per_plate:205, is_veg:0 },
      
      // STARTERS - Vegetarian
      { id:30, category:'starters', item_name:'Vegetable Samosa', price_per_plate:60, is_veg:1 },
      { id:31, category:'starters', item_name:'Paneer Tikka', price_per_plate:80, is_veg:1 },
      { id:32, category:'starters', item_name:'Crispy Spring Roll', price_per_plate:70, is_veg:1 },
      { id:33, category:'starters', item_name:'Vegetable Pakora', price_per_plate:65, is_veg:1 },
      
      // STARTERS - Non-Vegetarian
      { id:34, category:'starters', item_name:'Chicken Tikka', price_per_plate:90, is_veg:0 },
      { id:35, category:'starters', item_name:'Tandoori Chicken', price_per_plate:100, is_veg:0 },
      { id:36, category:'starters', item_name:'Lamb Seekh Kebab', price_per_plate:110, is_veg:0 },
      { id:37, category:'starters', item_name:'Shrimp Pakora', price_per_plate:95, is_veg:0 },
      
      // BREAD
      { id:38, category:'bread', item_name:'Plain Naan', price_per_plate:40, is_veg:1 },
      { id:39, category:'bread', item_name:'Zeera Naan', price_per_plate:50, is_veg:1 },
      { id:40, category:'bread', item_name:'Garlic Naan', price_per_plate:55, is_veg:1 },
      { id:41, category:'bread', item_name:'Paratha', price_per_plate:45, is_veg:1 },
      { id:42, category:'bread', item_name:'Roti', price_per_plate:35, is_veg:1 },
      { id:43, category:'bread', item_name:'Turkish Bread', price_per_plate:50, is_veg:1 },
      
      // MISCELLANEOUS
      { id:44, category:'misc', item_name:'Chilli Sauce', price_per_plate:20, is_veg:1 },
      { id:45, category:'misc', item_name:'Mint Sauce', price_per_plate:20, is_veg:1 },
      { id:46, category:'misc', item_name:'Tomato Sauce', price_per_plate:20, is_veg:1 },
      { id:47, category:'misc', item_name:'Green Sauce', price_per_plate:20, is_veg:1 },
      { id:48, category:'misc', item_name:'Plum Chutney', price_per_plate:25, is_veg:1 },
      { id:49, category:'misc', item_name:'Cucumber Raita', price_per_plate:30, is_veg:1 },
      { id:50, category:'misc', item_name:'Cucumber Tomato Salad', price_per_plate:35, is_veg:1 },
      { id:51, category:'misc', item_name:'Special Salad', price_per_plate:40, is_veg:1 },
      
      // DESSERTS
      { id:52, category:'desserts', item_name:'Fini Kheer', price_per_plate:90, is_veg:1 },
      { id:53, category:'desserts', item_name:'Fruit Pastry', price_per_plate:110, is_veg:1 },
      { id:54, category:'desserts', item_name:'Fruit Salad', price_per_plate:90, is_veg:1 },
      { id:55, category:'desserts', item_name:'Fruit Sherbets', price_per_plate:80, is_veg:1 },
      { id:56, category:'desserts', item_name:'Gajar Halwa', price_per_plate:85, is_veg:1 },
      { id:57, category:'desserts', item_name:'Gulab Jaman', price_per_plate:70, is_veg:1 },
      
      // ICE CREAM
      { id:58, category:'ice_cream', item_name:'Strawberry Ice Cream', price_per_plate:80, is_veg:1 },
      { id:59, category:'ice_cream', item_name:'Vanilla Ice Cream', price_per_plate:75, is_veg:1 },
      { id:60, category:'ice_cream', item_name:'Chocolate Ice Cream', price_per_plate:85, is_veg:1 },
      { id:61, category:'ice_cream', item_name:'Mango Ice Cream', price_per_plate:90, is_veg:1 },
      
      // DRINKS
      { id:62, category:'drinks', item_name:'Chocolate Fountain', price_per_plate:100, is_veg:1 },
      { id:63, category:'drinks', item_name:'Fruit Display', price_per_plate:95, is_veg:1 },
      { id:64, category:'drinks', item_name:'Hot Mocktail', price_per_plate:60, is_veg:1 },
      { id:65, category:'drinks', item_name:'Mints', price_per_plate:40, is_veg:1 },
      { id:66, category:'drinks', item_name:'Pan Supari', price_per_plate:50, is_veg:1 },
    ];
    console.log('✅ Demo items created:', demoItems.length, 'total items');
    this.allMenuItems = demoItems; // Store for later reference
    this.renderCateringMenu(demoItems, [1, 2, 3, 15, 16, 21, 25, 30, 34, 38, 44, 52, 58, 62]);
  },

  toggleMenuItem(id, el) {
    // Check if user is logged in as a user
    if (!this.currentUser) {
      this.showToast('Please login as a User to select menu items.', 'error');
      return;
    }
    
    if (this.currentUser.login_type !== 'user') {
      this.showToast('Menu selection is only available in User login.', 'error');
      return;
    }
    
    // Convert to number for consistency
    const numId = parseInt(id);
    
    // Toggle the item in the selected list
    const idx = this.selectedMenuIds.findIndex(item => parseInt(item) === numId);
    if (idx === -1) {
      this.selectedMenuIds.push(numId);
    } else {
      this.selectedMenuIds.splice(idx, 1);
    }
    
    // Update visual state
    const isSelected = this.selectedMenuIds.some(item => parseInt(item) === numId);
    el.style.background = isSelected ? '#ffe0f0' : 'transparent';
    
    // Update checkbox
    const checkbox = el.querySelector('input[type="checkbox"]');
    if (checkbox) checkbox.checked = isSelected;
    
    this.updateMenuCount();
  },

  updateMenuCount() {
    const el = document.getElementById('selectedMenuCount');
    if (el) el.textContent = this.selectedMenuIds.length + ' items selected';
  },

  addSelectedMenuToList() {
    console.log('🍽️ addSelectedMenuToList called');
    
    if (!this.currentUser) {
      this.showToast('Please login as a User to add items to your list.', 'error');
      return;
    }
    
    if (this.currentUser.login_type !== 'user') {
      this.showToast('This feature is only available for User login.', 'error');
      return;
    }
    
    if (this.selectedMenuIds.length === 0) {
      this.showToast('Please select at least one menu item before adding to list.', 'error');
      return;
    }
    
    console.log('✅ Adding menu items to list. selectedMenuIds:', this.selectedMenuIds);
    console.log('📋 allMenuItems count:', this.allMenuItems.length);
    
    // Add selected menu items to myList
    let addedCount = 0;
    this.selectedMenuIds.forEach(itemId => {
      const numId = parseInt(itemId);
      console.log(`🔍 Looking for item ID ${numId}...`);
      
      const menuItem = this.allMenuItems.find(item => {
        return parseInt(item.id) === numId;
      });
      
      if (menuItem) {
        const itemName = menuItem.item_name;
        console.log(`✅ Found: ${itemName}`);
        
        if (!this.myList.includes(itemName)) {
          this.myList.push(itemName);
          addedCount++;
          console.log(`✅ Added to myList: ${itemName}`);
        } else {
          console.log(`⚠️ Already in myList: ${itemName}`);
        }
      } else {
        console.warn(`❌ Item ID ${numId} not found in allMenuItems`);
      }
    });
    
    // Save and update
    console.log('📋 myList before save:', this.myList);
    this.saveMyList();
    this.updateMyListUI();
    console.log('📋 myList after save:', this.myList);
    
    this.showToast(`Added ${addedCount} items to your list! 📋`, 'success');
  },

  async saveMenuSelection(e) {
    // Get the button element if passed as event
    let button = null;
    if (e && e.target && e.target.tagName === 'BUTTON') {
      button = e.target;
    }
    
    // Try to find the button by class/text
    if (!button) {
      const buttons = document.querySelectorAll('button.btn-success');
      for (let btn of buttons) {
        if (btn.textContent.includes('Save My Menu')) {
          button = btn;
          break;
        }
      }
    }
    
    // Disable button during save
    const originalText = button ? button.textContent : '💾 Save My Menu';
    if (button) {
      button.disabled = true;
      button.textContent = '⏳ Saving...';
    }
    
    try {
      // Check if user is logged in as a user
      console.log('💾 saveMenuSelection called, currentUser:', this.currentUser);
      
      if (!this.currentUser) {
        console.warn('❌ User not logged in');
        this.showToast('Please login as a User to save your menu selection.', 'error');
        return;
      }
      
      if (this.currentUser.login_type !== 'user') {
        console.warn('❌ Wrong login type:', this.currentUser.login_type);
        this.showToast('Menu saving is only available for User login.', 'error');
        return;
      }
      
      console.log('💾 saveMenuSelection called, selectedMenuIds:', this.selectedMenuIds);
      
      if (this.selectedMenuIds.length === 0) {
        console.log('❌ No menu items selected');
        this.showToast('Please select at least one menu item before saving.', 'error');
        return;
      }

      const fd = new FormData();
      fd.append('action', 'save_menu');
      this.selectedMenuIds.forEach(id => fd.append('menu_ids[]', id));
      
      console.log('📤 Sending to server:', {action: 'save_menu', menu_ids: this.selectedMenuIds, currentUser: this.currentUser});
      
      const res = await fetch('php/events.php', { method: 'POST', body: fd, credentials: 'include' });
      console.log('📥 Response status:', res.status, 'Content-Type:', res.headers.get('content-type'));
      
      const responseText = await res.text();
      console.log('📥 Raw response:', responseText);
      
      let data;
      try {
        data = JSON.parse(responseText);
      } catch (parseErr) {
        console.error('❌ JSON parse error:', parseErr, 'Response was:', responseText);
        // If response starts with redirect or error page
        if (responseText.includes('<') || responseText.includes('Location')) {
          this.showToast('Session expired or server error. Please login again.', 'error');
        } else {
          this.showToast('Server error: ' + responseText.substring(0, 100), 'error');
        }
        return;
      }
      
      console.log('📥 Response data:', data);
      
      if (data.success) {
        console.log('💾 Save success! allMenuItems count:', this.allMenuItems.length);
        console.log('💾 selectedMenuIds:', this.selectedMenuIds);
        console.log('💾 First item in allMenuItems:', this.allMenuItems[0]);
        
        // Add selected menu items to myList
        this.selectedMenuIds.forEach(itemId => {
          const numId = parseInt(itemId);
          console.log(`🔍 Looking for item ID ${numId} (from ${itemId}) in allMenuItems...`);
          const menuItem = this.allMenuItems.find(item => parseInt(item.id) === numId);
          if (menuItem) {
            const itemName = menuItem.item_name;
            console.log(`✅ Found: ${itemName}`);
            // Avoid duplicates
            if (!this.myList.includes(itemName)) {
              this.myList.push(itemName);
              console.log(`✅ Added to myList: ${itemName}`);
            } else {
              console.log(`⚠️ Already in myList: ${itemName}`);
            }
          } else {
            console.warn(`❌ Item ID ${numId} not found in allMenuItems`);
          }
        });
        
        console.log('📋 myList before save:', this.myList);
        // Save to localStorage and update UI
        this.saveMyList();
        this.updateMyListUI();
        
        console.log('📋 myList after save:', this.myList);
        this.showToast(`Menu saved successfully! 🍛 (${this.selectedMenuIds.length} items selected)`, 'success');
        console.log('✅ Menu saved with items:', this.selectedMenuIds);
        console.log('✅ My List updated:', this.myList);
      } else {
        const errMsg = data.message || 'Failed to save menu.';
        console.error('❌ Save menu error:', errMsg, data);
        this.showToast(errMsg, 'error');
      }
    } catch (error) {
      console.error('❌ Save menu exception:', error);
      this.showToast('Error saving menu: ' + error.message, 'error');
    } finally {
      // Re-enable button
      if (button) {
        button.disabled = false;
        button.textContent = originalText;
      }
    }
  },

  // ---- SEATING CONFIGURATION ----
  selectSeatType(type, element) {
    // Visual feedback for seat type selection
    document.querySelectorAll('.seat-type-card').forEach(card => {
      card.style.borderColor = '#ddd';
      card.style.background = 'white';
    });
    element.style.borderColor = '#D946A6';
    element.style.background = '#FFE0F0';
    element.style.boxShadow = '0 0 15px rgba(217, 70, 166, 0.3)';
  },

  updateBrideSeatSelection() {
    // Only allow selection for user panel, not for wedding planner
    const isPlanner = this.currentUser && this.currentUser.login_type === 'planner';
    if (isPlanner) {
      this.showToast('❌ Seat selection is only available in User Panel', 'error');
      // Uncheck the radio button if planner tried to select
      document.querySelectorAll('input[name="bride_seat"]').forEach(r => r.checked = false);
      return;
    }
    
    const allBrideRadios = document.querySelectorAll('input[name="bride_seat"]');
    allBrideRadios.forEach(radio => {
      const label = radio.closest('label');
      if (radio.checked) {
        label.style.borderColor = '#D946A6';
        label.style.background = '#FFE0F0';
      } else {
        label.style.borderColor = '#ddd';
        label.style.background = 'transparent';
      }
    });
    console.log('👰 Bride seat selected:', document.querySelector('input[name="bride_seat"]:checked')?.value);
  },

  updateGroomSeatSelection() {
    // Only allow selection for user panel, not for wedding planner
    const isPlanner = this.currentUser && this.currentUser.login_type === 'planner';
    if (isPlanner) {
      this.showToast('❌ Seat selection is only available in User Panel', 'error');
      // Uncheck the radio button if planner tried to select
      document.querySelectorAll('input[name="groom_seat"]').forEach(r => r.checked = false);
      return;
    }
    
    const allGroomRadios = document.querySelectorAll('input[name="groom_seat"]');
    allGroomRadios.forEach(radio => {
      const label = radio.closest('label');
      if (radio.checked) {
        label.style.borderColor = '#D946A6';
        label.style.background = '#FFE0F0';
      } else {
        label.style.borderColor = '#ddd';
        label.style.background = 'transparent';
      }
    });
    console.log('🤵 Groom seat selected:', document.querySelector('input[name="groom_seat"]:checked')?.value);
  },

  updateBackdropSelection() {
    // Only allow selection for user panel, not for wedding planner
    const isPlanner = this.currentUser && this.currentUser.login_type === 'planner';
    if (isPlanner) {
      this.showToast('❌ Backdrop selection is only available in User Panel', 'error');
      // Uncheck the radio button if planner tried to select
      document.querySelectorAll('input[name="backdrop_style"]').forEach(r => r.checked = false);
      return;
    }
    
    const allBackdropRadios = document.querySelectorAll('input[name="backdrop_style"]');
    allBackdropRadios.forEach(radio => {
      const label = radio.closest('label');
      if (radio.checked) {
        label.style.borderColor = '#D946A6';
        label.style.background = '#FFE0F0';
      } else {
        label.style.borderColor = '#ddd';
        label.style.background = 'transparent';
      }
    });
    console.log('🎪 Backdrop style selected:', document.querySelector('input[name="backdrop_style"]:checked')?.value);
  },

  saveSeatingPlan() {
    // Only allow saving for user panel, not for wedding planner
    const isPlanner = this.currentUser && this.currentUser.login_type === 'planner';
    if (isPlanner) {
      this.showToast('❌ Seating plan saving is only available in User Panel', 'error');
      return;
    }
    
    const brideSeat = document.querySelector('input[name="bride_seat"]:checked')?.value;
    const groomSeat = document.querySelector('input[name="groom_seat"]:checked')?.value;
    const backdropStyle = document.querySelector('input[name="backdrop_style"]:checked')?.value;

    console.log('Seating plan validation:', { brideSeat, groomSeat, backdropStyle });

    // Provide specific error messages
    if (!brideSeat) {
      this.showToast('❌ Please select Bride\'s Seat Type', 'error');
      return;
    }
    if (!groomSeat) {
      this.showToast('❌ Please select Groom\'s Seat Type', 'error');
      return;
    }
    if (!backdropStyle) {
      this.showToast('❌ Please select Backdrop Style', 'error');
      return;
    }

    const seatTypeLabels = {
      'normal_chair': 'Normal Chair',
      'sofa_set': 'Sofa Set',
      'round_table': 'Round Table'
    };

    const backdropLabels = {
      'floral_wall': 'Floral Wall',
      'fabric_drape': 'Fabric Drape',
      'geometric': 'Geometric Frame',
      'led_panel': 'LED Panel'
    };

    try {
      const fd = new FormData();
      fd.append('action', 'save_seating');
      fd.append('bride_seat', brideSeat);
      fd.append('groom_seat', groomSeat);
      fd.append('backdrop_style', backdropStyle);
      
      // In demo mode, just show success
      this.showToast(`✅ Seating Plan Saved!\n👰 Bride: ${seatTypeLabels[brideSeat]}\n🤵 Groom: ${seatTypeLabels[groomSeat]}\n🎪 Backdrop: ${backdropLabels[backdropStyle]}`, 'success');
      
      // Try to save to server
      fetch('php/events.php', { method: 'POST', body: fd, credentials: 'include' }).catch(() => {});
    } catch (error) {
      this.showToast('Seating plan saved! 💒', 'success');
      console.error('Seating plan save error:', error);
    }
  },

  // ---- LOAD GIFTS ----
  async loadGifts() {
    try {
      const res  = await fetch('php/events.php?action=gifts', {
        credentials: 'include'
      });
      const data = await res.json();
      this.renderGifts(data.gifts || [], data.total || 0);
    } catch {
      this.renderDemoGifts();
    }
  },

  renderGifts(gifts, total) {
    const container = document.getElementById('giftList');
    const totalEl   = document.getElementById('giftTotal');
    if (totalEl) totalEl.textContent = '₹' + parseFloat(total).toLocaleString('en-IN');
    if (!container) return;

    if (!gifts.length) {
      container.innerHTML = '<p style="color:var(--text-light);text-align:center;padding:30px">No gifts recorded yet 🎁</p>';
      return;
    }
    container.innerHTML = gifts.map(g => `
      <div class="card" style="display:flex;align-items:center;gap:15px;padding:15px">
        <span style="font-size:2rem">${g.gift_type === 'shagun' ? '🧧' : g.gift_type === 'cash' ? '💵' : '🎁'}</span>
        <div>
          <strong>${g.description || g.gift_type}</strong>
          <div style="color:var(--text-light);font-size:0.85rem">${g.guest_name || 'Anonymous'} • ${new Date(g.received_at).toLocaleDateString()}</div>
        </div>
        <div style="margin-left:auto;font-family:'Playfair Display';font-weight:700;color:var(--pink-main)">₹${parseFloat(g.amount).toLocaleString('en-IN')}</div>
      </div>
    `).join('');
  },

  renderDemoGifts() {
    const demoGifts = [
      { gift_type:'shagun', description:'Envelope from Brother', guest_name:'Amit Sharma', amount:51000, received_at: new Date() },
      { gift_type:'cash',   description:'Cash gift from Groom Family', guest_name:'Sunita Mehta', amount:101000, received_at: new Date() },
      { gift_type:'item',   description:'Gold Necklace Set', guest_name:'Kavita Singh', amount:25000, received_at: new Date() },
    ];
    this.renderGifts(demoGifts, 177000);
  },

  // ---- MANDAP SELECTION ----
  selectMandap(style, el) {
    // Only allow selection for user panel, not for wedding planner
    const isPlanner = this.currentUser && this.currentUser.login_type === 'planner';
    if (isPlanner) {
      this.showToast('❌ Mandap selection is only available in User Panel', 'error');
      return;
    }
    
    document.querySelectorAll('.mandap-option').forEach(o => o.classList.remove('selected'));
    el.classList.add('selected');
    this.selectedMandap = style;
    document.getElementById('selectedMandapName') && (document.getElementById('selectedMandapName').textContent = style);
    this.showToast(`${style} selected! 💒`, 'info');
  },

  async saveMandap() {
    if (!this.canAddToList()) return;
    if (!this.selectedMandap) { this.showToast('Please select a Mandap style.', 'error'); return; }
    try {
      const fd = new FormData();
      fd.append('action', 'save_mandap');
      fd.append('mandap_style', this.selectedMandap);
      fd.append('flower_theme', document.getElementById('flowerTheme')?.value || '');
      fd.append('color_scheme', document.getElementById('colorScheme')?.value || '');
      fd.append('notes', document.getElementById('mandapNotes')?.value || '');
      await fetch('php/events.php', { 
        method: 'POST', 
        body: fd,
        credentials: 'include'
      });
      this.showToast('Mandap selection saved! 💒', 'success');
    } catch {
      this.showToast('Mandap saved! 💒 (demo mode)', 'success');
    }
  },

  // ---- MODAL HELPERS ----
  bindModalEvents() {
    document.querySelectorAll('.modal-overlay').forEach(overlay => {
      overlay.addEventListener('click', (e) => {
        if (e.target === overlay) overlay.classList.remove('active');
      });
    });

    const lbClose = document.getElementById('lbClose');
    if (lbClose) lbClose.addEventListener('click', () => {
      document.getElementById('lightbox').classList.remove('active');
    });
  },

  openModal(id) {
    console.log('🪟 openModal called:', id);
    const modal = document.getElementById(id);
    if (modal) {
      console.log('✅ Modal found, adding active class');
      // Refresh myList UI before opening booking modal
      if (id === 'modal-booking') {
        console.log('📋 Refreshing myList UI for booking modal');
        this.updateMyListUI();
      }
      modal.classList.add('active');
    } else {
      console.error('❌ Modal NOT found:', id);
    }
  },

  closeModal(id) {
    console.log('🪟 closeModal called:', id);
    const modal = document.getElementById(id);
    if (modal) {
      console.log('✅ Modal found, removing active class');
      modal.classList.remove('active');
    } else {
      console.error('❌ Modal NOT found:', id);
    }
  },

  // ---- LOGOUT ----
  async logout() {
    console.log('🔓 LOGOUT BUTTON CLICKED');
    
    if (!confirm('Are you sure you want to logout?')) {
      return;
    }
    
    // Notify server with proper form data
    try {
      const formData = new FormData();
      formData.append('action', 'logout');
      
      // Use fetch with keepalive for reliable logout even if page closes
      await fetch('php/auth_simple.php', {
        method: 'POST',
        body: formData,
        credentials: 'include',
        keepalive: true
      });
      console.log('✅ Server logout request sent');
    } catch(e) {
      console.log('⚠️ Server logout notification failed:', e);
    }
    
    // Clear client-side data
    this.currentUser = null;
    this.myList = [];
    localStorage.clear();
    sessionStorage.clear();
    
    // Reload page
    console.log('🔄 Reloading page after logout...');
    window.location.replace('index.html');
  },

  // ---- TOAST NOTIFICATIONS ----
  showToast(message, type = 'success') {
    const container = document.getElementById('toastContainer');
    if (!container) return;
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.textContent = message;
    container.appendChild(toast);
    setTimeout(() => { toast.style.opacity = '0'; toast.style.transform = 'translateX(100px)'; toast.style.transition = '0.3s'; }, 3000);
    setTimeout(() => toast.remove(), 3400);
  },

  // ---- EVENT FORM SUBMISSION ----
  async handleEventSubmit(e) {
    e.preventDefault();
    console.log('📝 handleEventSubmit triggered');
    const fd = new FormData(e.target);
    const mode = document.getElementById('eventId')?.value ? 'update' : 'create';
    fd.append('action', mode);

    try {
      const res = await fetch('php/events.php', { 
        method: 'POST', 
        body: fd, 
        credentials: 'include' 
      });
      
      const data = await res.json();
      console.log('📥 Server response:', data);
      
      if (data.success) {
        this.showToast('Event saved successfully! 🌸', 'success');
        this.closeModal('modal-event');
        // Add event to myList for booking card
        const eventType = fd.get('event_type');
        const eventDate = fd.get('event_date');
        const venue = fd.get('venue');
        // Compose a display string (customize as needed)
        let display = eventType.charAt(0).toUpperCase() + eventType.slice(1);
        if (eventDate) display += ' - ' + eventDate;
        if (venue) display += ' @ ' + venue;
        if (!this.myList.includes(display)) {
          this.myList.push(display);
          this.saveMyList();
          this.updateMyListUI();
        }
        // Refresh dashboard immediately
        await this.loadDashboard();
        // If we're not already on the events tab, switch to it
        if (typeof showTab === 'function') {
          showTab('dash', 'events');
        }
      } else {
        this.showToast(data.message || 'Error saving event', 'error');
      }
    } catch (err) {
      console.error('❌ Save error:', err);
      // Fallback for demo/local mode
      this.showToast('Event saved! (Demo mode)', 'success');
      this.closeModal('modal-event');
      // Add event to myList for booking card (demo mode)
      const eventType = fd.get('event_type');
      const eventDate = fd.get('event_date');
      const venue = fd.get('venue');
      let display = eventType.charAt(0).toUpperCase() + eventType.slice(1);
      if (eventDate) display += ' - ' + eventDate;
      if (venue) display += ' @ ' + venue;
      if (!this.myList.includes(display)) {
        this.myList.push(display);
        this.saveMyList();
        this.updateMyListUI();
      }
      await this.loadDashboard();
    }
  },
  // ---- MY LIST (PACKAGE BUILDER) ----
  loadMyList() {
    try {
      const stored = localStorage.getItem('aura_my_list');
      if (stored) this.myList = JSON.parse(stored);
    } catch (e) {
      this.myList = [];
    }
    this.updateMyListUI();
  },

  saveMyList() {
    localStorage.setItem('aura_my_list', JSON.stringify(this.myList));
    this.updateMyListUI();
  },

  canAddToList() {
    console.log('🔍 canAddToList check:', { currentUser: this.currentUser, login_type: this.currentUser?.login_type });
    
    if (!this.currentUser) {
      console.warn('❌ No currentUser');
      this.showToast('Please login as a User to add items to your list.', 'error');
      return false;
    }
    if (this.currentUser.login_type !== 'user') {
      console.warn('❌ Wrong login type:', this.currentUser.login_type);
      this.showToast('Add to List is only available for User login.', 'error');
      return false;
    }
    console.log('✅ Can add to list');
    return true;
  },

  addToListFromLightbox() {
    if (!this.canAddToList()) return;
    
    if (this.currentLightboxItem) {
      if (!this.myList.includes(this.currentLightboxItem)) {
        this.myList.push(this.currentLightboxItem);
        this.saveMyList();
        this.showToast('Added to your list! 📋', 'success');
      } else {
        this.showToast('Already in your list.', 'info');
      }
      this.closeModal('lightbox');
    }
  },

  removeFromMyList(index) {
    this.myList.splice(index, 1);
    this.saveMyList();
    this.updateMyListUI();
  },

  addCateringToList() {
    // Get catering values
    const totalGuests = parseInt(document.getElementById('calcGuests')?.value) || 0;
    const vegGuests = parseInt(document.getElementById('calcTotalVegGuests')?.value) || 0;
    const nonVegGuests = parseInt(document.getElementById('calcTotalNonVegGuests')?.value) || 0;
    
    if (totalGuests === 0) {
      this.showToast('Please enter total guests to add catering to list.', 'error');
      return;
    }
    
    // Create catering summary string
    const cateringSummary = `🍽️ Catering: ${totalGuests} Total Guests (${vegGuests} Veg, ${nonVegGuests} Non-Veg)`;
    
    console.log('🍽️ Adding catering to list:', cateringSummary);
    
    // Check if catering already in list
    const cateringExists = this.myList.some(item => item.includes('Catering:'));
    
    if (cateringExists) {
      console.log('⚠️ Catering already in list, removing old entry');
      // Remove old catering entry
      this.myList = this.myList.filter(item => !item.includes('Catering:'));
    }
    
    // Add new catering entry
    this.myList.push(cateringSummary);
    console.log('✅ Catering added to myList:', cateringSummary);
    console.log('📋 Current myList:', this.myList);
    
    // Save and update UI
    this.saveMyList();
    this.updateMyListUI();
    
    this.showToast('Catering details added to your list! 📋', 'success');
  },

  updateMyListUI() {
    console.log('🎨 updateMyListUI called, myList:', this.myList);
    const countEl = document.getElementById('cartCount');
    if (countEl) {
      countEl.textContent = `(${this.myList.length})`;
      console.log('✅ Updated cartCount to:', this.myList.length);
    } else {
      console.warn('⚠️ cartCount element not found');
    }
    
    const itemCountEl = document.getElementById('itemCount');
    if (itemCountEl) {
      itemCountEl.textContent = this.myList.length;
      console.log('✅ Updated itemCount to:', this.myList.length);
    } else {
      console.warn('⚠️ itemCount element not found');
    }

    const summaryEl = document.getElementById('myListSummary');
    console.log('🔍 Looking for myListSummary element...', summaryEl);
    if (summaryEl) {
      if (this.myList.length === 0) {
        console.log('📋 myList is empty, showing empty message');
        summaryEl.innerHTML = '<li style="color:var(--text-light); text-align:center;">Your list is empty. Explore and add items!</li>';
      } else {
        console.log('📋 myList has items, rendering:', this.myList);
        summaryEl.innerHTML = this.myList.map((item, idx) => `
          <li style="display:flex;justify-content:space-between;align-items:center;background:white;padding:10px 15px;border-radius:10px;">
            <span style="font-weight:600;color:var(--text-dark)">${item}</span>
            <button type="button" class="btn btn-sm btn-outline" style="border-color:#ff4d4f;color:#ff4d4f;padding:2px 8px;" onclick="AuraWedding.removeFromMyList(${idx})">✕</button>
          </li>
        `).join('');
        console.log('✅ Updated myListSummary with', this.myList.length, 'items');
      }
    } else {
      console.error('❌ myListSummary element not found!');
    }
  },

  // ---- BOOKING SUBMISSION ----
  async submitBooking(e) {
    e.preventDefault();
    if (!document.getElementById('bookTerms').checked) {
      this.showToast('You must accept the terms and conditions.', 'error');
      return;
    }
    
    const btn = e.target.querySelector('[type="submit"]');
    btn.disabled = true;
    btn.textContent = 'Submitting...';
    
    try {
      // Prepare booking data in the format expected by php/book.php
      const formData = new FormData();
      formData.append('client_name', document.getElementById('bookName').value);
      formData.append('client_email', document.getElementById('bookEmail').value);
      formData.append('client_phone', document.getElementById('bookPhone').value);
      formData.append('selected_items', JSON.stringify(this.myList || []));
      formData.append('terms_accepted', document.getElementById('bookTerms').checked ? 'on' : 'off');
      
      // Create abort controller for timeout (8 seconds)
      const controller = new AbortController();
      const timeoutId = setTimeout(() => controller.abort(), 8000);
      
      // Send to backend API with credentials
      const res = await fetch('php/book.php', { 
        method: 'POST',
        body: formData,
        credentials: 'include',
        signal: controller.signal 
      });
      clearTimeout(timeoutId);
      
      const data = await res.json();
      
      if (data.success) {
        console.log('✅ Booking saved to database:', data);
        
        this.showToast('Booking submitted successfully! 🎉', 'success');
        this.myList = [];
        this.saveMyList();
        e.target.reset();
        this.closeModal('modal-booking');
        btn.disabled = false;
        btn.textContent = 'Confirm & Submit Booking';
        
        // Navigate to dashboard to show booking
        setTimeout(() => {
          this.navigateTo('dashboard');
        }, 500);
      } else {
        this.showToast(data.message || 'Submission failed.', 'error');
        btn.disabled = false;
        btn.textContent = 'Confirm & Submit Booking';
      }
    } catch (err) {
      console.error('❌ Booking submission error:', err);
      this.showToast('❌ Booking submission failed. Please check your connection and try again.', 'error');
      btn.disabled = false;
      btn.textContent = 'Confirm & Submit Booking';
    }
  },

  async loadBookings() {
    console.log('📋 Loading bookings from database...');
    const bookingsList = document.getElementById('bookingsList');
    if (!bookingsList) {
      console.error('❌ bookingsList element not found');
      return;
    }

    try {
      // Fetch bookings from server API
      const response = await fetch('php/bookings.php?action=list', {
        method: 'GET',
        credentials: 'include'
      });
      
      const data = await response.json();
      console.log('📋 API Response:', data);
      
      if (!data.success || !data.bookings || data.bookings.length === 0) {
        bookingsList.innerHTML = '<div style="text-align:center;padding:40px;color:var(--text-light)"><p>No bookings yet. Start planning your wedding!</p></div>';
        return;
      }

      const bookings = data.bookings;
      
      // Display bookings in a table format
      const tableHTML = `
        <table style="width:100%;border-collapse:collapse;background:white;border-radius:10px;overflow:hidden;box-shadow:0 4px 15px rgba(0,0,0,0.1)">
          <thead>
            <tr style="background:linear-gradient(135deg, #e85d75 0%, #ec4899 100%);color:white;font-weight:bold">
              <th style="padding:15px;text-align:left;border:none">Booking ID</th>
              <th style="padding:15px;text-align:left;border:none">Client Name</th>
              <th style="padding:15px;text-align:left;border:none">Email</th>
              <th style="padding:15px;text-align:left;border:none">Phone</th>
              <th style="padding:15px;text-align:center;border:none">Items</th>
              <th style="padding:15px;text-align:center;border:none">Status</th>
              <th style="padding:15px;text-align:left;border:none">Submitted Date</th>
            </tr>
          </thead>
          <tbody>
            ${bookings.map((booking, idx) => {
              const selectedItems = typeof booking.selected_items === 'string' 
                ? JSON.parse(booking.selected_items) 
                : (booking.selected_items || []);
              
              const statusColor = booking.status === 'accepted' ? '#4caf50' : 
                                  booking.status === 'rejected' ? '#f44336' : 
                                  booking.status === 'pending' ? '#ff9800' : '#999';
              
              const createdDate = new Date(booking.created_at).toLocaleDateString('en-IN');
              
              return `
                <tr style="border-bottom:1px solid #eee;hover-background:#f5f5f5" onmouseover="this.style.background='#f5f5f5'" onmouseout="this.style.background=''">
                  <td style="padding:15px;font-weight:bold;color:#e85d75">#${booking.id}</td>
                  <td style="padding:15px">${booking.client_name}</td>
                  <td style="padding:15px;color:#666;font-size:0.9rem">${booking.client_email}</td>
                  <td style="padding:15px;color:#666;font-size:0.9rem">${booking.client_phone}</td>
                  <td style="padding:15px;text-align:center">
                    <span style="background:#e3f2fd;color:#1976d2;padding:4px 8px;border-radius:4px;font-size:0.85rem;font-weight:bold">
                      ${selectedItems.length}
                    </span>
                  </td>
                  <td style="padding:15px;text-align:center">
                    <span style="background:${statusColor};color:white;padding:6px 12px;border-radius:20px;font-weight:600;font-size:0.85rem;text-transform:capitalize">
                      ${booking.status}
                    </span>
                  </td>
                  <td style="padding:15px;color:#999;font-size:0.9rem">${createdDate}</td>
                </tr>
              `;
            }).join('')}
          </tbody>
        </table>
      `;
      
      // Add details section below table
      const detailsHTML = bookings.map((booking, idx) => {
        const selectedItems = typeof booking.selected_items === 'string' 
          ? JSON.parse(booking.selected_items) 
          : (booking.selected_items || []);
        
        return `
          <div style="margin-top:30px;background:white;padding:20px;border-radius:10px;box-shadow:0 4px 15px rgba(0,0,0,0.1)" id="booking-details-${booking.id}">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:15px;border-bottom:2px solid #f0f0f0;padding-bottom:15px">
              <h3 style="color:#333;margin:0">📋 Booking #${booking.id} Details</h3>
              <button type="button" onclick="document.getElementById('booking-details-${booking.id}').style.display='none'" style="background:none;border:none;cursor:pointer;font-size:20px;color:#999">✕</button>
            </div>
            
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
              <div>
                <h5 style="color:#666;margin-bottom:10px;text-transform:uppercase;font-size:0.8rem">Client Information</h5>
                <div style="background:#f9f9f9;padding:15px;border-radius:8px">
                  <p style="margin:0 0 8px 0"><strong>Name:</strong> ${booking.client_name}</p>
                  <p style="margin:0 0 8px 0"><strong>Email:</strong> ${booking.client_email}</p>
                  <p style="margin:0"><strong>Phone:</strong> ${booking.client_phone}</p>
                </div>
              </div>
              
              <div>
                <h5 style="color:#666;margin-bottom:10px;text-transform:uppercase;font-size:0.8rem">Booking Status</h5>
                <div style="background:#f9f9f9;padding:15px;border-radius:8px">
                  <p style="margin:0 0 8px 0"><strong>Status:</strong> <span style="background:#ff9800;color:white;padding:4px 8px;border-radius:4px;text-transform:capitalize">${booking.status}</span></p>
                  <p style="margin:0 0 8px 0"><strong>Submitted:</strong> ${new Date(booking.created_at).toLocaleString('en-IN')}</p>
                  <p style="margin:0"><strong>Last Updated:</strong> ${new Date(booking.updated_at).toLocaleString('en-IN')}</p>
                </div>
              </div>
            </div>
            
            <div style="margin-top:15px">
              <h5 style="color:#666;margin:15px 0 10px 0;text-transform:uppercase;font-size:0.8rem">🍽️ Selected Items (${selectedItems.length})</h5>
              <div style="display:grid;gap:8px">
                ${selectedItems.map(item => `
                  <div style="background:#FFF3E0;padding:12px;border-radius:8px;border-left:4px solid #ff9800;color:#333">
                    ${item}
                  </div>
                `).join('')}
              </div>
            </div>
            
            ${booking.admin_notes ? `
              <div style="margin-top:15px;background:#e8f5e9;padding:15px;border-radius:8px;border-left:4px solid #4caf50">
                <h5 style="color:#2e7d32;margin:0 0 8px 0">📝 Admin Notes</h5>
                <p style="margin:0;color:#333">${booking.admin_notes}</p>
              </div>
            ` : ''}
          </div>
        `;
      }).join('');
      
      bookingsList.innerHTML = tableHTML + detailsHTML;
      
      // Add collapse/expand functionality
      const tableRows = bookingsList.querySelectorAll('tbody tr');
      tableRows.forEach(row => {
        row.style.cursor = 'pointer';
        row.addEventListener('click', function() {
          const bookingId = this.querySelector('td').textContent.replace('#', '');
          const details = document.getElementById('booking-details-' + bookingId);
          if (details) {
            details.style.display = details.style.display === 'none' ? 'block' : 'none';
          }
        });
      });
      
      console.log('✅ Bookings displayed successfully');
    } catch (err) {
      console.error('❌ Error loading bookings:', err);
      bookingsList.innerHTML = '<div style="color:#d32f2f;padding:20px;background:#ffebee;border-radius:8px">Error loading bookings: ' + err.message + '</div>';
    }
  },
};

// ============================================================
// WEDDING PLANNER - Multi-Step Booking System
// ============================================================
const PlannerApp = {
  // ---- STATE ----
  bookingId: null,
  selectedEvents: [],
  eventDetails: {},
  currentStep: 1,

  // Event configuration with Haldi design options
  eventConfig: {
    haldi: { name: 'Haldi', emoji: '🌼', color: '#e67e22' },
    mehendi: { name: 'Mehendi', emoji: '🌿', color: '#27ae60' },
    sangeet: { name: 'Sangeet', emoji: '🎵', color: '#ff6b9d' },
    ring_ceremony: { name: 'Ring Ceremony', emoji: '💍', color: '#c0392b' },
    vivah: { name: 'Vivah', emoji: '💒', color: '#8e44ad' },
    reception: { name: 'Reception', emoji: '🎉', color: '#f39c12' }
  },

  // Haldi design options based on the images provided
  haldiDesigns: {
    'outdoor_garden': {
      name: '🌞 Outdoor Garden Haldi',
      description: 'Beautiful garden setup with natural flowers and daylight ambiance',
      themes: ['Marigold & Rose', 'Jasmine & White Flowers', 'Mixed Seasonal Flowers'],
      price: '₹30,000 - ₹50,000'
    },
    'poolside_glamour': {
      name: '🏊 Poolside Glamour',
      description: 'Elegant poolside ceremony with yellow/gold color scheme',
      themes: ['Gold & White', 'Yellow & Cream', 'Gold & Pink'],
      price: '₹40,000 - ₹60,000'
    },
    'traditional_mandap': {
      name: '🏛️ Traditional Mandap',
      description: 'Classic Rajasthani-style Haldi with ornate decorations',
      themes: ['Gold & Red', 'Orange & Gold', 'Yellow & Multi-color'],
      price: '₹35,000 - ₹55,000'
    },
    'minimalist_modern': {
      name: '✨ Minimalist Modern',
      description: 'Sleek contemporary design with geometric patterns and minimalist decor',
      themes: ['White & Gold', 'Yellow & White', 'Cream & Champagne'],
      price: '₹35,000 - ₹50,000'
    },
    'floral_canopy': {
      name: '🌸 Floral Canopy',
      description: 'Stunning floral backdrop with hanging flowers and drapes',
      themes: ['Yellow & Orange Flowers', 'White & Yellow', 'Multi-color Blooms'],
      price: '₹45,000 - ₹65,000'
    },
    'intimate_indoor': {
      name: '🏮 Intimate Indoor',
      description: 'Cozy indoor setup with warm lighting and elegant furnishings',
      themes: ['Gold & Warm Tones', 'Lantern & Lights', 'Rustic Elegance'],
      price: '₹25,000 - ₹40,000'
    }
  },

  // ---- INIT ----
  init() {
    this.bindEventButtons();
    this.bindStepNavigation();
  },

  bindEventButtons() {
    document.querySelectorAll('.planner-event-option').forEach(btn => {
      btn.addEventListener('click', (e) => {
        e.preventDefault();
        const event = btn.dataset.event;
        this.toggleEvent(event, btn);
      });
    });
  },

  bindStepNavigation() {
    // Navigation buttons are already bound via onclick handlers in HTML
  },

  // ---- STEP 1: EVENT SELECTION ----
  toggleEvent(eventType, element) {
    const idx = this.selectedEvents.indexOf(eventType);
    if (idx > -1) {
      this.selectedEvents.splice(idx, 1);
      element.classList.remove('selected');
    } else {
      this.selectedEvents.push(eventType);
      element.classList.add('selected');
    }
    this.updateProgressIndicator();
  },

  updateProgressIndicator() {
    const progress = document.querySelector('.planner-progress');
    if (progress) {
      const stepPercent = Math.min(25 + (this.selectedEvents.length * 5), 24);
      progress.style.width = stepPercent + '%';
    }
  },

  // ---- STEP 2: EVENT DETAILS FORM GENERATION ----
  goToStep(step) {
    // Validate current step before moving
    if (step > this.currentStep) {
      if (!this.validateCurrentStep()) return;
    }

    this.currentStep = step;
    document.querySelectorAll('.planner-step').forEach(s => s.classList.remove('active'));
    document.getElementById(`planner-step-${step}`)?.classList.add('active');

    if (step === 2) this.generateEventForms();
    if (step === 3) this.generateReview();

    // Scroll to planner
    const planner = document.querySelector('.wedding-planner-section');
    if (planner) planner.scrollIntoView({ behavior: 'smooth' });
  },

  validateCurrentStep() {
    if (this.currentStep === 1) {
      if (this.selectedEvents.length === 0) {
        alert('Please select at least one event to continue.');
        return false;
      }
      // Create booking if not exists
      if (!this.bookingId) {
        this.createBooking();
      }
    }
    return true;
  },

  async createBooking() {
    try {
      const formData = new FormData();
      formData.append('action', 'create_booking');

      const res = await fetch('php/booking_planner.php', {
        method: 'POST',
        body: formData,
        credentials: 'include'
      });
      const data = await res.json();

      if (data.success) {
        this.bookingId = data.booking_id;
      } else {
        alert('Error creating booking: ' + data.message);
      }
    } catch (err) {
      console.log('Booking creation attempted');
    }
  },

  generateEventForms() {
    const container = document.getElementById('planner-events-form');
    if (!container) return;
    
    container.innerHTML = '';

    this.selectedEvents.forEach(eventType => {
      const config = this.eventConfig[eventType];
      const section = document.createElement('div');
      section.className = 'card card-bubbly';
      section.style.marginBottom = '20px';
      section.style.borderTop = `4px solid ${config.color}`;

      let formHTML = `
        <h3 style="color: ${config.color}; margin-bottom: 20px;">
          ${config.emoji} ${config.name}
        </h3>

        <div class="form-group">
          <label>Date *</label>
          <input type="date" class="form-control event-date" data-event="${eventType}" required>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Start Time</label>
            <input type="time" class="form-control event-time" data-event="${eventType}">
          </div>
          <div class="form-group">
            <label>Venue *</label>
            <input type="text" class="form-control event-venue" data-event="${eventType}" placeholder="Venue name or address" required>
          </div>
        </div>
      `;

      // Add Haldi-specific design selection
      if (eventType === 'haldi') {
        formHTML += `
          <div style="background: #fff9e6; border-radius: 10px; padding: 15px; margin: 15px 0; border-left: 4px solid #e67e22;">
            <h4 style="color: #e67e22; margin: 0 0 15px 0;">🎨 Choose Your Haldi Design Theme</h4>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 12px;">
        `;

        Object.entries(this.haldiDesigns).forEach(([key, design]) => {
          formHTML += `
            <div class="haldi-design-option" data-design="${key}" 
                 onclick="PlannerApp.selectHaldiDesign(this, '${key}')"
                 style="padding: 12px; border: 2px solid #ddd; border-radius: 8px; cursor: pointer; transition: all 0.3s; text-align: center;">
              <div style="font-size: 1.5rem; margin-bottom: 5px;">${design.name.split(' ')[0]}</div>
              <div style="font-size: 0.75rem; color: #666; font-weight: 600;">${design.name.split(' ').slice(1).join(' ')}</div>
              <div style="font-size: 0.7rem; color: #999; margin-top: 5px;">${design.price}</div>
            </div>
          `;
        });

        formHTML += `
            </div>
            <input type="hidden" class="haldi-design-selected" data-event="haldi" value="">
            <p id="haldiDesignDesc" style="margin-top: 15px; padding: 10px; background: white; border-radius: 6px; font-size: 0.9rem; color: #666;"></p>
          </div>
        `;
      }

      formHTML += `
        <div class="form-group">
          <label>Number of Guests</label>
          <input type="number" class="form-control event-guests" data-event="${eventType}" min="0" value="0">
        </div>

        <div class="form-group">
          <label>Hotel Rooms Needed</label>
          <input type="number" class="form-control event-rooms" data-event="${eventType}" min="0" value="0">
        </div>

        <div class="form-group">
          <label>Special Notes (Optional)</label>
          <textarea class="form-control event-notes" data-event="${eventType}" rows="2" placeholder="Any special requests or notes..."></textarea>
        </div>
      `;

      section.innerHTML = formHTML;
      container.appendChild(section);
    });
  },

  selectHaldiDesign(element, designKey) {
    // Remove previous selection
    document.querySelectorAll('.haldi-design-option').forEach(el => {
      el.style.borderColor = '#ddd';
      el.style.background = 'white';
    });

    // Mark selected
    element.style.borderColor = '#e67e22';
    element.style.background = '#fff9e6';

    // Store selection
    const selectedInput = document.querySelector('.haldi-design-selected');
    if (selectedInput) selectedInput.value = designKey;

    // Show description
    const design = this.haldiDesigns[designKey];
    const descEl = document.getElementById('haldiDesignDesc');
    if (descEl) {
      descEl.innerHTML = `
        <strong>${design.name}</strong><br>
        <span style="color: #555;">${design.description}</span><br>
        <span style="color: #999; font-size: 0.85rem; margin-top: 8px; display: block;">
          <strong>Color Themes:</strong> ${design.themes.join(', ')}
        </span>
      `;
    }
  },

  // ---- STEP 3: REVIEW ----
  generateReview() {
    const container = document.getElementById('planner-review-content');
    if (!container) return;

    let html = '<div style="background: #f9f9f9; border-radius: 10px; padding: 20px;">';

    // ---- SECTION 1: SELECTED EVENTS ----
    html += `
      <div style="margin-bottom: 25px;">
        <h4 style="color: var(--pink-main); margin-bottom: 15px; padding-bottom: 10px; border-bottom: 2px solid var(--pink-main);">
          📅 Selected Events
        </h4>
        <div style="display: flex; flex-wrap: wrap; gap: 10px;">
    `;
    
    this.selectedEvents.forEach(eventType => {
      const config = this.eventConfig[eventType];
      html += `
        <div style="background: white; padding: 10px 15px; border-radius: 8px; border-left: 4px solid ${config.color};">
          ${config.emoji} ${config.name}
        </div>
      `;
    });
    
    html += '</div></div>';

    // ---- SECTION 2: EVENT DETAILS ----
    html += `
      <div style="margin-bottom: 25px;">
        <h4 style="color: var(--pink-main); margin-bottom: 15px; padding-bottom: 10px; border-bottom: 2px solid var(--pink-main);">
          🎯 Event Details
        </h4>
    `;

    this.selectedEvents.forEach(eventType => {
      const config = this.eventConfig[eventType];
      const dateInput = document.querySelector(`.event-date[data-event="${eventType}"]`);
      const timeInput = document.querySelector(`.event-time[data-event="${eventType}"]`);
      const venueInput = document.querySelector(`.event-venue[data-event="${eventType}"]`);
      const guestsInput = document.querySelector(`.event-guests[data-event="${eventType}"]`);
      const roomsInput = document.querySelector(`.event-rooms[data-event="${eventType}"]`);
      const notesInput = document.querySelector(`.event-notes[data-event="${eventType}"]`);

      const date = dateInput?.value ? new Date(dateInput.value).toLocaleDateString('en-IN', { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' }) : '❌ Not set';
      const time = timeInput?.value || '—';
      const venue = venueInput?.value || '❌ Not set';
      const guests = guestsInput?.value || '0';
      const rooms = roomsInput?.value || '0';
      const notes = notesInput?.value || '';

      html += `
        <div style="background: white; padding: 15px; border-radius: 8px; margin-bottom: 12px; border-left: 4px solid ${config.color};">
          <h5 style="color: ${config.color}; margin: 0 0 12px 0;">${config.emoji} ${config.name}</h5>
          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; font-size: 0.9rem;">
            <div><strong>📅 Date:</strong> ${date}</div>
            <div><strong>⏰ Time:</strong> ${time || 'Not specified'}</div>
            <div><strong>📍 Venue:</strong> ${venue}</div>
            <div><strong>👥 Guests:</strong> ${guests}</div>
            <div><strong>🛏️ Rooms:</strong> ${rooms}</div>
      `;

      if (notes) {
        html += `<div style="grid-column: 1 / -1;"><strong>📝 Notes:</strong> ${notes}</div>`;
      }

      // Show Haldi design if selected
      if (eventType === 'haldi') {
        const selectedDesign = document.querySelector('.haldi-design-selected')?.value;
        if (selectedDesign && this.haldiDesigns[selectedDesign]) {
          const design = this.haldiDesigns[selectedDesign];
          html += `<div style="grid-column: 1 / -1; margin-top: 10px; padding-top: 10px; border-top: 1px solid #eee;">
                    <strong>🎨 Haldi Design:</strong> ${design.name}<br>
                    <span style="color: #666; font-size: 0.85rem;">${design.description}</span>
                   </div>`;
        }
      }

      html += `
          </div>
        </div>
      `;
    });

    html += '</div>';

    // ---- SECTION 3: COUPLE & CLIENT INFORMATION (Preview) ----
    const brideName = document.getElementById('plannerBrideName')?.value || '';
    const groomName = document.getElementById('plannerGroomName')?.value || '';
    const clientName = document.getElementById('plannerClientName')?.value || '';
    const clientEmail = document.getElementById('plannerClientEmail')?.value || '';
    const clientPhone = document.getElementById('plannerClientPhone')?.value || '';

    html += `
      <div>
        <h4 style="color: var(--pink-main); margin-bottom: 15px; padding-bottom: 10px; border-bottom: 2px solid var(--pink-main);">
          💍 Couple & Contact Information
        </h4>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
          <div style="background: white; padding: 15px; border-radius: 8px; border-left: 4px solid #c0392b;">
            <h5 style="color: #c0392b; margin: 0 0 10px 0;">👰 Bride's Details</h5>
            <div style="font-size: 0.9rem;">
              <strong>Name:</strong> ${brideName || '<span style="color: #999;">Not provided yet</span>'}
            </div>
          </div>
          <div style="background: white; padding: 15px; border-radius: 8px; border-left: 4px solid #8e44ad;">
            <h5 style="color: #8e44ad; margin: 0 0 10px 0;">🤵 Groom's Details</h5>
            <div style="font-size: 0.9rem;">
              <strong>Name:</strong> ${groomName || '<span style="color: #999;">Not provided yet</span>'}
            </div>
          </div>
          <div style="background: white; padding: 15px; border-radius: 8px; border-left: 4px solid #27ae60; grid-column: 1 / -1;">
            <h5 style="color: #27ae60; margin: 0 0 10px 0;">📞 Organizer/Contact Information</h5>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; font-size: 0.9rem;">
              <div><strong>Name:</strong> ${clientName || '<span style="color: #999;">Not provided yet</span>'}</div>
              <div><strong>Email:</strong> ${clientEmail || '<span style="color: #999;">Not provided yet</span>'}</div>
              <div><strong>Phone:</strong> ${clientPhone || '<span style="color: #999;">Not provided yet</span>'}</div>
            </div>
          </div>
        </div>
        <div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 12px; border-radius: 6px; margin-top: 15px; font-size: 0.85rem; color: #856404;">
          <strong>ℹ️ Note:</strong> Bride, Groom, and contact information will be confirmed in the next step.
        </div>
      </div>
    `;

    html += '</div>';
    container.innerHTML = html;
  },

  // ---- STEP 4: SUBMIT ----
  async submitBooking() {
    const brideName = document.getElementById('plannerBrideName').value;
    const groomName = document.getElementById('plannerGroomName').value;
    const clientName = document.getElementById('plannerClientName').value;
    const clientEmail = document.getElementById('plannerClientEmail').value;
    const clientPhone = document.getElementById('plannerClientPhone').value;
    const termsAccepted = document.getElementById('plannerTerms').checked;

    if (!brideName || !groomName || !clientName || !clientEmail || !clientPhone) {
      alert('Please fill all required fields.');
      return;
    }

    if (!termsAccepted) {
      alert('Please accept terms and conditions.');
      return;
    }

    // Save all event details first
    for (const eventType of this.selectedEvents) {
      const dateInput = document.querySelector(`.event-date[data-event="${eventType}"]`);
      const timeInput = document.querySelector(`.event-time[data-event="${eventType}"]`);
      const venueInput = document.querySelector(`.event-venue[data-event="${eventType}"]`);
      const guestsInput = document.querySelector(`.event-guests[data-event="${eventType}"]`);
      const roomsInput = document.querySelector(`.event-rooms[data-event="${eventType}"]`);
      const notesInput = document.querySelector(`.event-notes[data-event="${eventType}"]`);

      if (!dateInput?.value || !venueInput?.value) {
        alert(`Please complete ${this.eventConfig[eventType].name} details.`);
        return;
      }

      let notes = notesInput?.value || '';
      
      // Add Haldi design info to notes
      if (eventType === 'haldi') {
        const selectedDesign = document.querySelector('.haldi-design-selected')?.value;
        if (selectedDesign && this.haldiDesigns[selectedDesign]) {
          notes += ` [Design: ${this.haldiDesigns[selectedDesign].name}]`;
        }
      }

      try {
        const formData = new FormData();
        formData.append('action', 'save_event_details');
        formData.append('booking_id', this.bookingId);
        formData.append('event_type', eventType);
        formData.append('event_date', dateInput.value);
        formData.append('event_time', timeInput?.value || '00:00');
        formData.append('venue', venueInput.value);
        formData.append('guest_count', guestsInput?.value || '0');
        formData.append('room_count', roomsInput?.value || '0');
        formData.append('notes', notes);

        const res = await fetch('php/booking_planner.php', {
          method: 'POST',
          body: formData,
          credentials: 'include'
        });
        const data = await res.json();

        if (!data.success) {
          if (data.conflict) {
            alert(`❌ Venue conflict! ${venueInput.value} is already booked on ${dateInput.value}.`);
            return;
          }
          alert(`Error saving ${this.eventConfig[eventType].name}: ` + data.message);
          return;
        }
      } catch (err) {
        console.log('Event save attempted');
      }
    }

    // Submit booking
    try {
      const formData = new FormData();
      formData.append('action', 'submit_booking');
      formData.append('booking_id', this.bookingId);
      formData.append('bride_name', brideName);
      formData.append('groom_name', groomName);
      formData.append('client_name', clientName);
      formData.append('client_email', clientEmail);
      formData.append('client_phone', clientPhone);
      formData.append('terms_accepted', termsAccepted ? '1' : '0');

      const res = await fetch('php/booking_planner.php', {
        method: 'POST',
        body: formData,
        credentials: 'include'
      });
      const data = await res.json();

      if (data.success) {
        AuraWedding.showToast('✅ Booking submitted successfully! Admin will review shortly.', 'success');
        // Redirect to dashboard
        setTimeout(() => {
          window.location.href = 'user.php';
        }, 2000);
      } else {
        alert('❌ Submission failed: ' + data.message);
      }
    } catch (err) {
      AuraWedding.showToast('✅ Booking submitted! (Demo Mode)', 'success');
      setTimeout(() => {
        window.location.href = 'index.html';
      }, 2000);
    }
  }
};

// ============================================================
// PLAYLIST MANAGER - Sangeet Playlist Management
// ============================================================
const PlaylistManager = {
  currentWeddingId: null,
  currentPlaylistId: null,
  playlists: [],
  isInitialized: false,

  // Init
  async init() {
    // Load playlists for demo (don't require user login)
    this.loadPlaylists();
  },

  // Load all playlists for this wedding
  async loadPlaylists() {
    try {
      // First, initialize playlists if not done
      if (!this.isInitialized) {
        const initRes = await fetch('php/init_playlists.php', { credentials: 'include' });
        const initData = await initRes.json();
        if (initData.success) {
          this.isInitialized = true;
          console.log('Playlists initialized:', initData);
        }
      }

      // Now load the playlists
      const res = await fetch('php/playlists.php?action=list&event_type=sangeet', { credentials: 'include' });
      const data = await res.json();

      if (data.success) {
        this.playlists = data.playlists || [];
        this.renderPlaylists();
      }
    } catch (err) {
      console.log('Loading playlists (offline mode)');
      this.renderPlaylists();
    }
  },

  // Render playlist cards
  renderPlaylists() {
    const container = document.getElementById('playlistsContainer');
    if (!container) return;

    if (this.playlists.length === 0) {
      container.innerHTML = '<p style="text-align:center;color:#999;padding:40px;font-size:1.1rem">No playlists yet. Create one to get started! 🎶</p>';
      return;
    }

    container.innerHTML = this.playlists.map(pl => `
      <div class="playlist-card" style="background:#fff;border:1px solid #e0e0e0;border-radius:12px;padding:20px;margin-bottom:20px;box-shadow:0 2px 8px rgba(0,0,0,0.1)">
        <div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:15px">
          <div>
            <h4 style="margin:0 0 8px 0;color:#001a4d;font-size:1.3rem">🎵 ${pl.playlist_name}</h4>
            ${pl.mood ? `<span style="display:inline-block;background:#ff6b9d;color:#fff;padding:4px 12px;border-radius:20px;font-size:0.85rem;margin-bottom:10px">${pl.mood}</span>` : ''}
            ${pl.description ? `<p style="margin:8px 0;color:#666;font-size:0.95rem">${pl.description}</p>` : ''}
            <div style="color:#999;font-size:0.9rem">
              <span>🎶 ${pl.song_count || 0} songs</span> • <span>📅 ${new Date(pl.created_at).toLocaleDateString()}</span>
            </div>
          </div>
          <div style="display:flex;gap:8px">
            <button class="btn" style="padding:8px 16px;background:#ff6b9d;color:#fff;border:none;border-radius:6px;cursor:pointer;font-size:0.9rem" onclick="PlaylistManager.openPlaylist(${pl.id})">Edit</button>
            <button class="btn" style="padding:8px 16px;background:#f0f0f0;color:#333;border:none;border-radius:6px;cursor:pointer;font-size:0.9rem" onclick="PlaylistManager.deletePlaylist(${pl.id})">Delete</button>
          </div>
        </div>

        <!-- Songs List -->
        <div style="border-top:1px solid #f0f0f0;padding-top:15px">
          <h5 style="color:#001a4d;margin-top:0;margin-bottom:12px">📜 Songs in this playlist:</h5>
          <div id="songs-${pl.id}" style="max-height:400px;overflow-y:auto">
            <p style="text-align:center;color:#999;padding:20px">Loading songs...</p>
          </div>
        </div>
      </div>
    `).join('');

    // Load songs for each playlist
    this.playlists.forEach(pl => {
      this.loadPlaylistSongs(pl.id);
    });
  },

  // Load songs for a specific playlist
  async loadPlaylistSongs(playlistId) {
    try {
      const res = await fetch(`php/playlists.php?action=get&id=${playlistId}`, { credentials: 'include' });
      const data = await res.json();

      const container = document.getElementById(`songs-${playlistId}`);
      if (!container) return;

      if (data.success && data.playlist.songs && data.playlist.songs.length > 0) {
        container.innerHTML = data.playlist.songs.map((song, index) => `
          <div style="display:flex;align-items:center;padding:10px;border-bottom:1px solid #f5f5f5;hover:background:#f9f9f9" onmouseover="this.style.background='#f9f9f9'" onmouseout="this.style.background='transparent'">
            <input type="checkbox" style="margin-right:12px;cursor:pointer;width:18px;height:18px" data-song-id="${song.id}" data-playlist-id="${playlistId}">
            <div style="flex:1;min-width:0">
              <div style="font-weight:500;color:#001a4d;margin-bottom:4px">♪ ${song.song_name}</div>
              <div style="display:flex;gap:12px;font-size:0.85rem;color:#666">
                ${song.artist_name ? `<span>👤 ${song.artist_name}</span>` : ''}
                ${song.genre ? `<span>🎸 ${song.genre}</span>` : ''}
                ${song.duration_seconds ? `<span>⏱️ ${Math.floor(song.duration_seconds / 60)}:${String(song.duration_seconds % 60).padStart(2, '0')}</span>` : ''}
              </div>
            </div>
          </div>
        `).join('');
      } else {
        container.innerHTML = '<p style="text-align:center;color:#999;padding:20px">No songs in this playlist yet</p>';
      }
    } catch (err) {
      console.log('Loading songs (demo mode):', err);
      const container = document.getElementById(`songs-${playlistId}`);
      if (container) {
        container.innerHTML = '<p style="text-align:center;color:#999;padding:20px">Demo mode - no songs loaded</p>';
      }
    }
  },

  // Show create playlist modal
  showCreatePlaylistModal() {
    if (!AuraWedding.currentUser) {
      AuraWedding.openModal('modal-login-select');
      return;
    }
    AuraWedding.openModal('modal-create-playlist');
  },

  // Create new playlist
  async createPlaylist(e) {
    e.preventDefault();

    const name = document.getElementById('playlistName')?.value;
    const eventType = document.getElementById('playlistEventType')?.value || 'sangeet';
    const mood = document.getElementById('playlistMood')?.value;
    const description = document.getElementById('playlistDescription')?.value;

    if (!name) {
      document.getElementById('playlistNameErr').textContent = 'Playlist name is required';
      return;
    }

    try {
      const formData = new FormData();
      formData.append('action', 'create');
      formData.append('playlist_name', name);
      formData.append('event_type', eventType);
      formData.append('mood', mood);
      formData.append('description', description);

      const res = await fetch('php/playlists.php', {
        method: 'POST',
        body: formData,
        credentials: 'include'
      });
      const data = await res.json();

      if (data.success) {
        AuraWedding.showToast('✅ Playlist created successfully!', 'success');
        document.getElementById('createPlaylistForm').reset();
        AuraWedding.closeModal('modal-create-playlist');
        this.loadPlaylists();
      } else {
        AuraWedding.showToast('❌ ' + data.message, 'error');
      }
    } catch (err) {
      console.log('Creating playlist (demo mode)');
      AuraWedding.showToast('✅ Playlist created! (Demo Mode)', 'success');
      this.loadPlaylists();
    }
  },

  // Open playlist to manage songs
  async openPlaylist(playlistId) {
    if (!AuraWedding.currentUser) {
      AuraWedding.openModal('modal-login-select');
      return;
    }

    this.currentPlaylistId = playlistId;

    try {
      const res = await fetch(`php/playlists.php?action=get&id=${playlistId}`, { credentials: 'include' });
      const data = await res.json();

      if (data.success) {
        this.displayPlaylistDetails(data.playlist);
        AuraWedding.openModal('modal-manage-playlist');
      }
    } catch (err) {
      console.log('Loading playlist details (demo mode)');
      const playlist = this.playlists.find(p => p.id === playlistId);
      if (playlist) {
        playlist.songs = [];
        this.displayPlaylistDetails(playlist);
        AuraWedding.openModal('modal-manage-playlist');
      }
    }
  },

  // Display playlist details in modal
  displayPlaylistDetails(playlist) {
    document.getElementById('managePlaylistTitle').textContent = `🎵 ${playlist.playlist_name}`;
    document.getElementById('managPlaylistName').value = playlist.playlist_name;
    document.getElementById('managPlaylistMood').value = playlist.mood || '';
    document.getElementById('managPlaylistDesc').value = playlist.description || '';
    document.getElementById('deletePlaylistBtn').onclick = () => this.deletePlaylist(playlist.id);
    document.getElementById('songCount').textContent = (playlist.songs || []).length;

    const songsList = document.getElementById('songsList');
    if (!playlist.songs || playlist.songs.length === 0) {
      songsList.innerHTML = '<p style="text-align:center;color:#999">No songs yet. Add one below!</p>';
    } else {
      songsList.innerHTML = playlist.songs.map(song => `
        <div class="song-item">
          <div class="song-info">
            <div class="song-title">♪ ${song.song_name}</div>
            <div class="song-details">
              ${song.artist_name ? `<span>${song.artist_name}</span>` : ''}
              ${song.genre ? `<span>${song.genre}</span>` : ''}
              ${song.duration_seconds ? `<span>${Math.floor(song.duration_seconds / 60)}:${String(song.duration_seconds % 60).padStart(2, '0')}</span>` : ''}
            </div>
          </div>
          <button class="song-remove-btn" onclick="PlaylistManager.removeSong(${song.id}, ${this.currentPlaylistId})">✕</button>
        </div>
      `).join('');
    }
  },

  // Add song to playlist
  async addSongToPlaylist() {
    if (!this.currentPlaylistId) return;

    const songName = document.getElementById('newSongName')?.value;
    const artistName = document.getElementById('newSongArtist')?.value;
    const genre = document.getElementById('newSongGenre')?.value;
    const duration = document.getElementById('newSongDuration')?.value;
    const notes = document.getElementById('newSongNotes')?.value;

    if (!songName) {
      AuraWedding.showToast('❌ Song name is required', 'error');
      return;
    }

    try {
      const formData = new FormData();
      formData.append('action', 'add_song');
      formData.append('playlist_id', this.currentPlaylistId);
      formData.append('song_name', songName);
      formData.append('artist_name', artistName);
      formData.append('genre', genre);
      formData.append('duration_seconds', duration || 0);
      formData.append('notes', notes);

      const res = await fetch('php/playlists.php', {
        method: 'POST',
        body: formData,
        credentials: 'include'
      });
      const data = await res.json();

      if (data.success) {
        AuraWedding.showToast('✅ Song added to playlist!', 'success');
        document.getElementById('newSongName').value = '';
        document.getElementById('newSongArtist').value = '';
        document.getElementById('newSongGenre').value = '';
        document.getElementById('newSongDuration').value = '';
        document.getElementById('newSongNotes').value = '';
        this.openPlaylist(this.currentPlaylistId);
      }
    } catch (err) {
      console.log('Adding song (demo mode)');
      AuraWedding.showToast('✅ Song added! (Demo Mode)', 'success');
      this.openPlaylist(this.currentPlaylistId);
    }
  },

  // Remove song from playlist
  async removeSong(songId, playlistId) {
    if (!confirm('Remove this song from the playlist?')) return;

    try {
      const formData = new FormData();
      formData.append('action', 'remove_song');
      formData.append('song_id', songId);

      const res = await fetch('php/playlists.php', {
        method: 'POST',
        body: formData,
        credentials: 'include'
      });
      const data = await res.json();

      if (data.success) {
        AuraWedding.showToast('✅ Song removed!', 'success');
        this.openPlaylist(playlistId);
      }
    } catch (err) {
      console.log('Removing song (demo mode)');
      AuraWedding.showToast('✅ Song removed! (Demo Mode)', 'success');
      this.openPlaylist(playlistId);
    }
  },

  // Delete playlist
  async deletePlaylist(playlistId) {
    if (!playlistId) playlistId = this.currentPlaylistId;
    if (!confirm('Delete this entire playlist?')) return;

    try {
      const formData = new FormData();
      formData.append('action', 'delete');
      formData.append('id', playlistId);

      const res = await fetch('php/playlists.php', {
        method: 'POST',
        body: formData,
        credentials: 'include'
      });
      const data = await res.json();

      if (data.success) {
        AuraWedding.showToast('✅ Playlist deleted!', 'success');
        AuraWedding.closeModal('modal-manage-playlist');
        this.loadPlaylists();
      }
    } catch (err) {
      console.log('Deleting playlist (demo mode)');
      AuraWedding.showToast('✅ Playlist deleted! (Demo Mode)', 'success');
      this.loadPlaylists();
    }
  },

  // ---- WEDDING PLANNER REVIEW ----
  async loadPlannerReview() {
    console.log('📋 Loading planner review data...');
    try {
      // Fetch ALL available bookings for planner to review
      console.log('📝 Fetching all bookings for planner review...');

      const summaryRes = await fetch(`php/booking_planner.php?action=list`, {
        credentials: 'include'
      });

      if (!summaryRes.ok) {
        throw new Error(`HTTP ${summaryRes.status}`);
      }

      const summaryData = await summaryRes.json();
      console.log('📥 Bookings data:', summaryData);

      if (!summaryData.success || !summaryData.bookings || summaryData.bookings.length === 0) {
        console.warn('⚠️ No bookings found');
        document.getElementById('plannerBrideName').textContent = 'No bookings';
        document.getElementById('plannerGroomName').textContent = 'No bookings';
        document.getElementById('plannerWeddingDate').textContent = '-';
        document.getElementById('plannerTotalBudget').textContent = '₹0';
        document.getElementById('plannerEventsList').innerHTML = '<p style="color:var(--text-light)">No bookings submitted yet</p>';
        return;
      }

      // Use the first booking for display (or you can let user select)
      const booking = summaryData.bookings[0];
      const bookingId = booking.id;
      console.log('📝 Using booking ID:', bookingId);

      // Fetch full booking summary with events and guests
      const detailRes = await fetch(`php/booking_planner.php?action=get_summary&id=${bookingId}`, {
        credentials: 'include'
      });

      const detailData = await detailRes.json();
      console.log('📥 Booking detail data:', detailData);

      if (!detailData.success || !detailData.booking) {
        console.warn('⚠️ Could not load full booking details');
        // Display basic booking info
        document.getElementById('plannerBrideName').textContent = booking.bride_name || '-';
        document.getElementById('plannerGroomName').textContent = booking.groom_name || '-';
        document.getElementById('plannerWeddingDate').textContent = '-';
        document.getElementById('plannerTotalBudget').textContent = '₹0';
        return;
      }

      // Extract booking details
      const bookingDetail = detailData.booking;
      const events = detailData.events || [];

      // Display wedding details
      document.getElementById('plannerBrideName').textContent = bookingDetail.bride_name || booking.bride_name || '-';
      document.getElementById('plannerGroomName').textContent = bookingDetail.groom_name || booking.groom_name || '-';
      
      // Try to get wedding date from events
      let weddingDate = '-';
      const vivahEvent = events.find(e => e.event_type === 'vivah');
      if (vivahEvent && vivahEvent.event_date) {
        weddingDate = new Date(vivahEvent.event_date).toLocaleDateString('en-IN');
      }
      document.getElementById('plannerWeddingDate').textContent = weddingDate;

      // Calculate total budget from events (placeholder - you can modify this)
      document.getElementById('plannerTotalBudget').textContent = '₹' + (bookingDetail.total_budget ? bookingDetail.total_budget.toLocaleString('en-IN') : '0');

      // Render events from booking data
      if (events && events.length > 0) {
        this.renderPlannerEvents(events);
      } else {
        document.getElementById('plannerEventsList').innerHTML = '<p style="color:var(--text-light)">No events scheduled</p>';
      }

      // Render guests from booking data
      if (detailData.guests) {
        this.renderPlannerGuestsFromData(detailData.guests, events);
      }

      // Load mandap, catering, and seating if available
      try {
        const mRes = await fetch('php/events.php?action=mandap', {
          credentials: 'include'
        });
        const mData = await mRes.json();
        if (mData.mandap && mData.mandap.mandap_style) {
          document.getElementById('plannerMandapContent').innerHTML = `<p style="font-size:1.2rem;color:var(--pink-main);font-weight:700">${mData.mandap.mandap_style}</p>`;
        } else {
          document.getElementById('plannerMandapContent').innerHTML = '<p style="color:var(--text-light)">Not selected yet</p>';
        }
      } catch {
        document.getElementById('plannerMandapContent').innerHTML = '<p style="color:var(--text-light)">Not selected yet</p>';
      }

      // Load catering
      try {
        const cRes = await fetch('php/events.php?action=menu', {
          credentials: 'include'
        });
        const cData = await cRes.json();
        if (cData.selected && cData.selected.length > 0) {
          this.renderPlannerCatering(cData.menu, cData.selected);
        } else {
          this.renderDemoPlannerCatering();
        }
      } catch {
        this.renderDemoPlannerCatering();
      }

      // Load seating
      this.loadPlannerSeating();

      console.log('✅ Planner review loaded successfully');
    } catch (err) {
      console.error('❌ Error loading planner review:', err);
      this.showToast('Wedding data loaded (demo mode)', 'info');
    }
  },

  async loadPlannerBookings() {
    console.log('📋 Loading bookings for wedding planner...');
    try {
      const res = await fetch('php/bookings.php?action=list', {
        credentials: 'include'
      });
      console.log('📡 Response status:', res.status);
      
      const data = await res.json();
      console.log('📥 Response data:', data);
      
      const container = document.getElementById('plannerBookingsList');
      if (!container) {
        console.error('❌ plannerBookingsList element not found');
        return;
      }
      
      if (data.success && data.bookings && data.bookings.length > 0) {
        console.log('✅ Found', data.bookings.length, 'bookings');
        this.renderPlannerBookings(data.bookings);
      } else if (data.success) {
        console.log('ℹ️ No bookings found');
        container.innerHTML = '<div style="text-align:center;padding:40px;color:var(--text-light)"><p>📭 No bookings submitted yet. Bookings from users will appear here.</p></div>';
      } else {
        console.error('❌ API returned error:', data.message);
        container.innerHTML = '<div style="text-align:center;padding:40px;color:#d32f2f"><p>⚠️ ' + (data.message || 'Error loading bookings') + '</p></div>';
      }
    } catch (err) {
      console.error('❌ Error loading bookings:', err);
      const container = document.getElementById('plannerBookingsList');
      if (container) {
        container.innerHTML = '<div style="text-align:center;padding:40px;color:#d32f2f"><p>❌ Error loading bookings: ' + err.message + '</p></div>';
      }
    }
  },

  renderPlannerBookings(bookings) {
    console.log('🎯 Rendering', bookings.length, 'bookings for planner');
    const container = document.getElementById('plannerBookingsList');
    if (!container) {
      console.error('❌ plannerBookingsList element not found');
      return;
    }

    if (bookings.length === 0) {
      container.innerHTML = '<div style="text-align:center;padding:40px;color:var(--text-light)"><p>No bookings submitted yet.</p></div>';
      return;
    }

    container.innerHTML = bookings.map(booking => {
      const statusColors = {
        pending: '#FF9800',
        accepted: '#4CAF50',
        rejected: '#f44336',
        cancelled: '#9E9E9E'
      };
      const statusColor = statusColors[booking.status] || '#FF9800';
      
      // Parse selected_items - it might be a JSON string or already an array
      let selectedItems = [];
      if (typeof booking.selected_items === 'string') {
        try {
          selectedItems = JSON.parse(booking.selected_items);
        } catch (e) {
          console.error('Failed to parse selected_items:', booking.selected_items);
          selectedItems = [];
        }
      } else if (Array.isArray(booking.selected_items)) {
        selectedItems = booking.selected_items;
      }
      
      return `
        <div style="background:white;border-radius:15px;padding:25px;box-shadow:0 4px 15px rgba(0,0,0,0.1);border-left:5px solid ${statusColor};margin-bottom:20px">
          <div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:15px;flex-wrap:wrap;gap:10px">
            <div>
              <h4 style="color:#333;margin:0 0 5px 0">🎯 Booking #${booking.id}</h4>
              <p style="color:var(--text-light);font-size:0.9rem;margin:0">📅 ${booking.created_at}</p>
            </div>
            <span style="background:${statusColor};color:white;padding:8px 16px;border-radius:20px;font-weight:600;font-size:0.85rem;text-transform:capitalize">
              ${booking.status}
            </span>
          </div>
          
          <div style="background:#f8f9fa;padding:15px;border-radius:10px;margin-bottom:15px">
            <h5 style="color:#333;margin:0 0 10px 0">👤 Client Information</h5>
            <div style="display:grid;gap:8px;font-size:0.95rem">
              <p style="margin:0"><strong>Name:</strong> ${booking.client_name || 'N/A'}</p>
              <p style="margin:0"><strong>Email:</strong> <a href="mailto:${booking.client_email}" style="color:var(--pink-main);text-decoration:none">${booking.client_email || 'N/A'}</a></p>
              <p style="margin:0"><strong>Phone:</strong> <a href="tel:${booking.client_phone}" style="color:var(--pink-main);text-decoration:none">${booking.client_phone || 'N/A'}</a></p>
            </div>
          </div>
          
          <div style="background:#FFF3E0;padding:15px;border-radius:10px;margin-bottom:15px">
            <h5 style="color:#333;margin:0 0 10px 0">🍽️ Selected Items (${selectedItems.length})</h5>
            <div style="display:grid;gap:8px">
              ${selectedItems.map(item => `
                <div style="background:white;padding:10px;border-radius:8px;border-left:3px solid var(--pink-main)">
                  ${item}
                </div>
              `).join('')}
            </div>
          </div>

          <div style="display:flex;gap:10px;flex-wrap:wrap">
            <button class="btn btn-sm" style="background:#4CAF50;color:white;border:none;border-radius:8px;padding:10px 16px;cursor:pointer;font-weight:600" onclick="AuraWedding.updateBookingStatus(${booking.id}, 'accepted')">
              ✓ Accept
            </button>
            <button class="btn btn-sm" style="background:#f44336;color:white;border:none;border-radius:8px;padding:10px 16px;cursor:pointer;font-weight:600" onclick="AuraWedding.updateBookingStatus(${booking.id}, 'rejected')">
              ✕ Reject
            </button>
          </div>
        </div>
      `;
    }).join('');

    console.log('✅ Bookings rendered successfully');
  },

  async updateBookingStatus(bookingId, newStatus) {
    console.log(`Updating booking ${bookingId} to ${newStatus}...`);
    try {
      const res = await fetch('php/bookings.php?action=update_status', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          booking_id: bookingId,
          status: newStatus,
          admin_notes: ''
        }),
        credentials: 'include'
      });

      const data = await res.json();
      if (data.success) {
        this.showToast(`Booking ${newStatus}! ✓`, 'success');
        // Reload bookings to refresh the list
        this.loadPlannerBookings();
      } else {
        this.showToast(data.message || 'Failed to update booking', 'error');
      }
    } catch (err) {
      console.error('Error updating booking:', err);
      this.showToast('Error updating booking. Please try again.', 'error');
    }
  },

  renderPlannerEvents(events) {
    const container = document.getElementById('plannerEventsList');
    container.innerHTML = events.map(e => `
      <div style="background:white;padding:15px;border-radius:10px;border-left:4px solid var(--pink-main)">
        <strong style="font-size:1.1rem;text-transform:capitalize">${e.event_type}</strong>
        <p style="color:var(--text-light);margin:5px 0;font-size:0.9rem">
          📅 ${new Date(e.event_date).toLocaleDateString('en-IN')} | 📍 ${e.venue || 'TBD'}
        </p>
        <p style="color:var(--text-light);margin:5px 0;font-size:0.85rem">
          ${e.event_time ? e.event_time.substring(0, 5) : '--:--'} | 👥 ${e.guest_count || 0} guests | 🛏️ ${e.room_count || 0} rooms
        </p>
      </div>
    `).join('');
  },

  renderDemoPlannerEvents() {
    const container = document.getElementById('plannerEventsList');
    const demoEvents = [
      { event_type: 'Haldi', event_date: '2025-04-10', venue: 'Grand Ballroom', time_start: '15:00', time_end: '18:00' },
      { event_type: 'Mehendi', event_date: '2025-04-15', venue: 'Lawn Garden', time_start: '10:00', time_end: '22:00' },
      { event_type: 'Sangeet', event_date: '2025-04-18', venue: 'Convention Hall', time_start: '18:00', time_end: '23:30' },
      { event_type: 'Vivaha', event_date: '2025-04-20', venue: 'Main Venue', time_start: '09:00', time_end: '14:00' }
    ];
    this.renderPlannerEvents(demoEvents);
  },

  renderPlannerGuests(guests) {
    const totalEl = document.getElementById('plannerTotalGuests');
    const confirmedEl = document.getElementById('plannerConfirmedGuests');
    const pendingEl = document.getElementById('plannerPendingGuests');
    const listEl = document.getElementById('plannerGuestsList');

    totalEl.textContent = guests.length;
    confirmedEl.textContent = guests.filter(g => g.rsvp_status === 'confirmed').length;
    pendingEl.textContent = guests.filter(g => g.rsvp_status === 'pending').length;

    listEl.innerHTML = guests.map(g => `
      <div style="background:white;padding:12px;border-radius:8px;border-left:3px solid ${g.rsvp_status === 'confirmed' ? '#56ab2f' : g.rsvp_status === 'declined' ? '#e74c3c' : '#856404'};display:flex;justify-content:space-between;align-items:center;font-size:0.9rem">
        <div style="flex:1">
          <strong>${g.name}</strong>
          <p style="color:var(--text-light);margin:3px 0;font-size:0.85rem">${g.relation || ''} • ${g.side || 'both'}</p>
        </div>
        <span style="background:${g.rsvp_status === 'confirmed' ? '#e8f5e9' : g.rsvp_status === 'declined' ? '#ffebee' : '#fff3cd'};color:${g.rsvp_status === 'confirmed' ? '#56ab2f' : g.rsvp_status === 'declined' ? '#e74c3c' : '#856404'};padding:4px 12px;border-radius:20px;font-weight:600;font-size:0.85rem;text-transform:capitalize">
          ${g.rsvp_status}
        </span>
      </div>
    `).join('');
  },

  renderPlannerGuestsFromData(guestsByEvent, events) {
    const totalEl = document.getElementById('plannerTotalGuests');
    const confirmedEl = document.getElementById('plannerConfirmedGuests');
    const pendingEl = document.getElementById('plannerPendingGuests');
    const listEl = document.getElementById('plannerGuestsList');

    // Flatten guests from all events
    const allGuests = [];
    if (guestsByEvent && typeof guestsByEvent === 'object') {
      for (const eventId in guestsByEvent) {
        const guestList = guestsByEvent[eventId];
        if (Array.isArray(guestList)) {
          allGuests.push(...guestList);
        }
      }
    }

    if (allGuests.length === 0) {
      totalEl.textContent = '0';
      confirmedEl.textContent = '0';
      pendingEl.textContent = '0';
      listEl.innerHTML = '<p style="color:var(--text-light);text-align:center">No guests added yet</p>';
      return;
    }

    totalEl.textContent = allGuests.length;
    confirmedEl.textContent = allGuests.filter(g => g.rsvp_status === 'confirmed').length;
    pendingEl.textContent = allGuests.filter(g => g.rsvp_status === 'pending').length;

    listEl.innerHTML = allGuests.map(g => `
      <div style="background:white;padding:12px;border-radius:8px;border-left:3px solid ${g.rsvp_status === 'confirmed' ? '#56ab2f' : g.rsvp_status === 'declined' ? '#e74c3c' : '#856404'};display:flex;justify-content:space-between;align-items:center;font-size:0.9rem">
        <div style="flex:1">
          <strong>${g.guest_name || g.name}</strong>
          <p style="color:var(--text-light);margin:3px 0;font-size:0.85rem">${g.guest_email || ''}</p>
        </div>
        <span style="background:${g.rsvp_status === 'confirmed' ? '#e8f5e9' : g.rsvp_status === 'declined' ? '#ffebee' : '#fff3cd'};color:${g.rsvp_status === 'confirmed' ? '#56ab2f' : g.rsvp_status === 'declined' ? '#e74c3c' : '#856404'};padding:4px 12px;border-radius:20px;font-weight:600;font-size:0.85rem;text-transform:capitalize">
          ${g.rsvp_status || 'pending'}
        </span>
      </div>
    `).join('');
  },

  renderDemoPlannerGuests() {
    const demoGuests = [
      { name: 'Priya Sharma', relation: 'Friend', side: 'bride', rsvp_status: 'confirmed' },
      { name: 'Amit Patel', relation: 'Cousin', side: 'groom', rsvp_status: 'confirmed' },
      { name: 'Neha Gupta', relation: 'Sister', side: 'bride', rsvp_status: 'pending' },
      { name: 'Rajesh Kumar', relation: 'Uncle', side: 'both', rsvp_status: 'confirmed' },
      { name: 'Anjali Verma', relation: 'Colleague', side: 'bride', rsvp_status: 'pending' }
    ];
    this.renderPlannerGuests(demoGuests);
  },

  renderPlannerCatering(menu, selected) {
    const selectedItems = menu.filter(m => selected.includes(m.id));
    if (!selectedItems.length) {
      document.getElementById('plannerCateringContent').innerHTML = '<p style="color:var(--text-light);padding:20px;text-align:center">No menu items selected yet</p>';
      return;
    }
    
    document.getElementById('plannerCateringContent').innerHTML = `
      <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(180px, 1fr));gap:12px">
        ${selectedItems.map(item => `
          <div style="background:white;padding:12px;border-radius:8px;border:2px solid var(--pink-soft);text-align:center">
            <p style="font-weight:600;margin:0 0 5px 0;font-size:0.95rem">${item.item_name}</p>
            <p style="color:var(--text-light);font-size:0.85rem;margin:0 0 5px 0">${item.category}</p>
            <p style="font-weight:700;color:var(--pink-main);margin:0">₹${item.price_per_plate}</p>
            <span style="font-size:0.75rem;color:${item.is_veg ? '#27ae60' : '#e74c3c'};font-weight:600">${item.is_veg ? '🌱 Veg' : '🍗 Non-Veg'}</span>
          </div>
        `).join('')}
      </div>
    `;
  },

  renderDemoPlannerCatering() {
    const demoItems = [
      { item_name: 'Paneer Tikka', category: 'appetizers', price_per_plate: '350', is_veg: true },
      { item_name: 'Tandoori Chicken', category: 'appetizers', price_per_plate: '450', is_veg: false },
      { item_name: 'Butter Chicken', category: 'main_course', price_per_plate: '500', is_veg: false },
      { item_name: 'Chana Masala', category: 'main_course', price_per_plate: '350', is_veg: true },
      { item_name: 'Biryani', category: 'main_course', price_per_plate: '400', is_veg: false },
      { item_name: 'Gulab Jamun', category: 'desserts', price_per_plate: '150', is_veg: true }
    ];
    
    document.getElementById('plannerCateringContent').innerHTML = `
      <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(180px, 1fr));gap:12px">
        ${demoItems.map(item => `
          <div style="background:white;padding:12px;border-radius:8px;border:2px solid var(--pink-soft);text-align:center">
            <p style="font-weight:600;margin:0 0 5px 0;font-size:0.95rem">${item.item_name}</p>
            <p style="color:var(--text-light);font-size:0.85rem;margin:0 0 5px 0">${item.category}</p>
            <p style="font-weight:700;color:var(--pink-main);margin:0">₹${item.price_per_plate}</p>
            <span style="font-size:0.75rem;color:${item.is_veg ? '#27ae60' : '#e74c3c'};font-weight:600">${item.is_veg ? '🌱 Veg' : '🍗 Non-Veg'}</span>
          </div>
        `).join('')}
      </div>
    `;
  },

  loadPlannerSeating() {
    // Get from localStorage since seating is stored client-side
    const brideSeat = localStorage.getItem('brideSeat') || '-';
    const groomSeat = localStorage.getItem('groomSeat') || '-';
    const backdrop = localStorage.getItem('backdropStyle') || '-';

    document.getElementById('plannerBrideSeat').textContent = this.formatSeatName(brideSeat);
    document.getElementById('plannerGroomSeat').textContent = this.formatSeatName(groomSeat);
    document.getElementById('plannerBackdropStyle').textContent = this.formatBackdropName(backdrop);
  },

  formatSeatName(seat) {
    const names = {
      'normal_chair': '🪑 Normal Chair',
      'sofa_set': '🛋️ Sofa Set',
      'round_table': '🔵 Round Table'
    };
    return names[seat] || '-';
  },

  acceptBooking() {
    const isPlanner = this.currentUser && this.currentUser.login_type === 'planner';
    if (!isPlanner) {
      this.showToast('❌ This action is only available for Wedding Planners', 'error');
      return;
    }

    if (!this.currentUser.wedding_id) {
      this.showToast('❌ Wedding ID not found', 'error');
      return;
    }

    const confirmDialog = confirm('Are you sure you want to ACCEPT this booking? The couple will be notified.');
    if (!confirmDialog) return;

    // Send accept request to server
    fetch('php/events.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `action=accept_booking&wedding_id=${this.currentUser.wedding_id}`
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        this.showToast('✅ Booking ACCEPTED! Couple has been notified.', 'success');
        setTimeout(() => {
          this.loadPlannerReview();
        }, 1500);
      } else {
        this.showToast('❌ ' + (data.message || 'Failed to accept booking'), 'error');
      }
    })
    .catch(err => {
      console.error('Accept booking error:', err);
      this.showToast('❌ Error accepting booking', 'error');
    });
  },

  declineBooking() {
    const isPlanner = this.currentUser && this.currentUser.login_type === 'planner';
    if (!isPlanner) {
      this.showToast('❌ This action is only available for Wedding Planners', 'error');
      return;
    }

    const reason = document.getElementById('declineReason').value.trim();
    if (!reason) {
      this.showToast('❌ Please provide a reason for declining', 'error');
      return;
    }

    if (!this.currentUser.wedding_id) {
      this.showToast('❌ Wedding ID not found', 'error');
      return;
    }

    // Send decline request to server
    fetch('php/events.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `action=decline_booking&wedding_id=${this.currentUser.wedding_id}&reason=${encodeURIComponent(reason)}`
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        this.showToast('✅ Booking DECLINED. Couple has been notified.', 'success');
        this.closeModal('modal-decline-booking');
        document.getElementById('declineReason').value = '';
        setTimeout(() => {
          this.loadPlannerReview();
        }, 1500);
      } else {
        this.showToast('❌ ' + (data.message || 'Failed to decline booking'), 'error');
      }
    })
    .catch(err => {
      console.error('Decline booking error:', err);
      this.showToast('❌ Error declining booking', 'error');
    });
  },

  formatBackdropName(backdrop) {
    const names = {
      'floral_wall': '🌸 Floral Wall',
      'fabric_drape': '🧵 Fabric Drape',
      'geometric': '🔷 Geometric',
      'led_panel': '💡 LED Panel'
    };
    return names[backdrop] || '-';
  }
};

// ---- TAB SWITCHING FUNCTION ----

function showTab(page, tabName, btnElement) {
  // Hide all tab content for the page
  document.querySelectorAll(`#page-${page} .tab-content`).forEach(tab => {
    tab.classList.remove('active');
  });
  
  // Remove active class from all tab buttons for the page
  document.querySelectorAll(`#page-${page} .tab-btn`).forEach(btn => {
    btn.classList.remove('active');
  });
  
  // Show selected tab content
  const tabId = `tab-${page}-${tabName}`;
  const tabEl = document.getElementById(tabId);
  if (tabEl) {
    tabEl.classList.add('active');
  }
  
  // Mark the clicked button as active
  if (btnElement) {
    btnElement.classList.add('active');
  }
}

// ---- START ----
console.log('🌸 app.js loaded, adding DOMContentLoaded listener...');

document.addEventListener('DOMContentLoaded', () => {
  console.log('🌸 DOMContentLoaded fired in app.js');
  console.log('🌸 Calling AuraWedding.init()...');
  AuraWedding.init();
  console.log('🌸 Calling PlannerApp.init()...');
  PlannerApp.init();
  console.log('🌸 Calling PlaylistManager.init()...');
  PlaylistManager.init();
  console.log('🌸 All apps initialized!');
});
