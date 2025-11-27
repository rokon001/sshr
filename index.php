<!DOCTYPE html>
<html lang="hr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title data-translate="page-title-home">Start Smart HR - Moderan Web Design</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
  <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
  <script>
    // Cloudflare Turnstile configuration
    window.turnstileWidgetId = null;
    const turnstileSiteKey = '0x4AAAAAACAsbbl9JPV5qKN3';

    // Function to get current theme
    function getCurrentTheme() {
      return document.documentElement.getAttribute('data-theme') || 'light';
    }

    // Function to get current language
    function getCurrentLanguage() {
      return document.documentElement.getAttribute('data-lang') || 'hr';
    }

    // Function to initialize Turnstile
    window.initTurnstile = function() {
      const turnstileElement = document.querySelector('.cf-turnstile');
      
      if (!turnstileElement) {
        console.error('Turnstile element not found');
        return;
      }

      if (typeof turnstile === 'undefined') {
        console.error('Turnstile API not loaded');
        return;
      }

      // Remove existing widget if any
      if (window.turnstileWidgetId !== null) {
        try {
          turnstile.remove(window.turnstileWidgetId);
        } catch (e) {
          console.log('Turnstile widget removal failed, continuing...');
        }
        turnstileElement.innerHTML = '';
      }

      // Get current theme and language
      const theme = getCurrentTheme();
      const lang = getCurrentLanguage();

      console.log('Initializing Turnstile with theme:', theme, 'language:', lang);

      try {
        // Render Turnstile widget
        window.turnstileWidgetId = turnstile.render(turnstileElement, {
          sitekey: turnstileSiteKey,
          theme: theme,
          language: lang,
          callback: function(token) {
            console.log('Turnstile success');
            if (typeof window.onTurnstileSuccess === 'function') {
              window.onTurnstileSuccess(token);
            }
          },
          'error-callback': function() {
            console.log('Turnstile error');
            if (typeof window.onTurnstileError === 'function') {
              window.onTurnstileError();
            }
          },
          'expired-callback': function() {
            console.log('Turnstile expired');
            if (typeof window.onTurnstileExpired === 'function') {
              window.onTurnstileExpired();
            }
          }
        });
        console.log('Turnstile widget initialized with ID:', window.turnstileWidgetId);
      } catch (error) {
        console.error('Error initializing Turnstile:', error);
      }
    };

    // Function to update Turnstile theme and language
    window.updateTurnstile = function() {
      if (window.turnstileWidgetId !== null && typeof turnstile !== 'undefined') {
        const theme = getCurrentTheme();
        const lang = getCurrentLanguage();
        
        try {
          turnstile.remove(window.turnstileWidgetId);
        } catch (e) {
          console.log('Turnstile widget removal failed, continuing...');
        }
        
        window.turnstileWidgetId = null;
        
        // Reinitialize with new theme and language
        setTimeout(() => {
          window.initTurnstile();
        }, 100);
      }
    };

    // Initialize Turnstile when DOM is ready
    function initializeTurnstileWhenReady() {
      // Wait for both DOM and Turnstile script to be ready
      if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
          waitForTurnstile();
        });
      } else {
        waitForTurnstile();
      }
    }

    function waitForTurnstile() {
      if (typeof turnstile !== 'undefined') {
        // Small delay to ensure DOM is fully ready
        setTimeout(function() {
          window.initTurnstile();
        }, 100);
      } else {
        // Wait for Turnstile script to load
        let attempts = 0;
        const maxAttempts = 50; // 5 seconds max wait
        const checkTurnstile = setInterval(function() {
          attempts++;
          if (typeof turnstile !== 'undefined') {
            clearInterval(checkTurnstile);
            window.initTurnstile();
          } else if (attempts >= maxAttempts) {
            clearInterval(checkTurnstile);
            console.error('Turnstile script failed to load');
          }
        }, 100);
      }
    }

    // Start initialization
    initializeTurnstileWhenReady();

  </script>
</head>
<body>
  <!-- Header & Navigation -->
  <header class="header">
    <nav class="nav">
      <div class="nav__container">
        <!-- Logo -->
        <div class="nav__logo">
          <a href="index.php" class="nav__logo-link">
            <span class="nav__logo-text">Start Smart HR</span>
          </a>
        </div>
        
        <!-- Desktop Navigation -->
        <ul class="nav__menu nav__menu--desktop">
          <li class="nav__item">
            <a href="#hero" class="nav__link" data-translate="nav-home">Početna</a>
          </li>
          <li class="nav__item">
            <a href="#packages" class="nav__link" data-translate="nav-packages">Paketi</a>
          </li>
          <li class="nav__item">
            <a href="#contact" class="nav__link" data-translate="nav-contact">Kontakt</a>
          </li>
          <li class="nav__item">
            <a href="#faq" class="nav__link" data-translate="nav-faq">FAQ</a>
          </li>
        </ul>
        
        <!-- Controls -->
        <div class="nav__controls">
          <a href="about.php" class="nav__link nav__link--about" data-translate="nav-about">O nama</a>
          <div class="nav__lang-selector">
            <button class="nav__lang-flag active" data-lang="hr" title="Hrvatski">
              <span class="flag-symbol">HR</span>
            </button>
            <button class="nav__lang-flag" data-lang="en" title="English">
              <span class="flag-symbol">EN</span>
            </button>
          </div>
          <button class="nav__theme-toggle" id="theme-toggle" aria-label="Toggle dark mode">
            <svg class="theme-icon theme-icon--light" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="5"></circle>
              <line x1="12" y1="1" x2="12" y2="3"></line>
              <line x1="12" y1="21" x2="12" y2="23"></line>
              <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
              <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
              <line x1="1" y1="12" x2="3" y2="12"></line>
              <line x1="21" y1="12" x2="23" y2="12"></line>
              <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
              <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
            </svg>
            <svg class="theme-icon theme-icon--dark" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
            </svg>
          </button>
          <button class="nav__toggle" id="nav-toggle" aria-label="Toggle menu">
            <span class="nav__toggle-line"></span>
            <span class="nav__toggle-line"></span>
            <span class="nav__toggle-line"></span>
          </button>
        </div>
      </div>
      
      <!-- Mobile Navigation -->
      <div class="nav__mobile d-md-none" id="nav-mobile">
        <div class="nav__mobile-header">
          <span class="nav__mobile-title">Menu</span>
          <button class="nav__close" id="nav-close" aria-label="Close menu">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <line x1="18" y1="6" x2="6" y2="18"></line>
              <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
          </button>
        </div>
        <div class="nav__menu--mobile">
          <a href="#hero" class="nav__link" data-translate="nav-home">Početna</a>
          <a href="#packages" class="nav__link" data-translate="nav-packages">Paketi</a>
          <a href="#contact" class="nav__link" data-translate="nav-contact">Kontakt</a>
          <a href="#faq" class="nav__link" data-translate="nav-faq">FAQ</a>
          <a href="about.php" class="nav__link" data-translate="nav-about">O nama</a>
        </div>
      </div>
    </nav>
  </header>

  <!-- Hero Section -->
  <section class="hero" id="hero">
    <div class="hero__container">
      <div class="hero__content">
        <div class="hero__text">
          <div class="hero__subtitle">
            <span class="hero__subtitle-text" data-translate="hero-subtitle">Dobrodošli u budućnost</span>
            <div class="hero__subtitle-line"></div>
          </div>
          
          <h1 class="hero__title">
            <span class="hero__title-line" data-translate="hero-title-1">Profesionalni</span>
            <span class="hero__title-line hero__title-line--accent" data-translate="hero-title-2">Web Dizajn</span>
            <span class="hero__title-line hero__title-line--small" data-translate="hero-title-3">za vaš uspjeh</span>
          </h1>
          
          <p class="hero__description" data-translate="hero-description">
            Transformirajte svoje ideje u digitalnu stvarnost. 
            Kreirajmo zajedno web stranice koje privlače, konvertiraju i rastu.
          </p>
          
          <div class="hero__features">
            <div class="hero__feature">
              <div class="hero__feature-icon">⚡</div>
              <span data-translate="hero-feature-1">Brza izrada</span>
            </div>
            <div class="hero__feature">
              <div class="hero__feature-icon">📱</div>
              <span data-translate="hero-feature-2">Responzivni dizajn</span>
            </div>
            <div class="hero__feature">
              <div class="hero__feature-icon">🎨</div>
              <span data-translate="hero-feature-3">Moderan dizajn</span>
            </div>
          </div>
          
          <div class="hero__actions">
            <a href="#packages" class="btn btn--primary btn--large">
              <span class="btn__text" data-translate="hero-cta">Pogledajte pakete</span>
              <div class="btn__ripple"></div>
            </a>
            <a href="#contact" class="btn btn--outline btn--large">
              <span class="btn__text" data-translate="hero-contact">Kontaktirajte nas</span>
            </a>
          </div>
        </div>
        
        <div class="hero__visual">
          <div class="hero__image-wrapper">
            <div class="hero__image-container">
              <img src="images/first.png" alt="Web Dizajn" class="hero__image">
              <div class="hero__image-overlay">
                <div class="hero__overlay-content">
                  <h3>Vaš novi web</h3>
                  <p>Prilagođen vašim potrebama</p>
                </div>
              </div>
            </div>
            
          </div>
        </div>
      </div>
    </div>
    
    <div class="hero__scroll-indicator">
      <a href="#packages" class="scroll-indicator">
        <div class="scroll-indicator__arrow">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M7 10l5 5 5-5"/>
          </svg>
        </div>
      </a>
    </div>
  </section>

  <!-- Packages Section -->
  <section class="packages" id="packages">
    <div class="packages__container">
      <div class="packages__header">
        <h2 class="packages__title" data-translate="packages-title">Naši paketi</h2>
        <p class="packages__subtitle" data-translate="packages-subtitle">Odaberite paket koji najbolje odgovara vašim potrebama</p>
        <div class="packages__cta">
          <div class="cta__badge" data-translate="cta-badge">🔥 AKCIJA!</div>
          <p class="cta__text" data-translate="cta-text">
            <strong>50% POPUST na sve pakete!</strong> Ograničeno vrijeme - ne propustite ovu priliku!
          </p>
        </div>
      </div>
      
      <div class="packages__grid">
        <!-- Osnovna Stranica -->
        <div class="package-card">
          <div class="package-card__discount-banner" data-translate="discount-banner">50% POPUST</div>
          <div class="package-card__header">
            <div class="package-card__image">
              <img src="images/osnovna_primjer.JPG" alt="Osnovna Stranica">
              <div class="package-card__overlay"></div>
            </div>
            <div class="package-card__badge" data-translate="badge-basic">Osnovni</div>
          </div>
          <div class="package-card__content">
            <h3 class="package-card__title" data-translate="package-basic-title">Osnovna Stranica</h3>
            <div class="package-card__eta" data-translate="package-basic-eta">ETA: 24-48 sati</div>
            <p class="package-card__description" data-translate="package-basic-desc">
              Pojednostavljeno rješenje za vaš prvi online korak. Idealno za osobne projekte i male tvrtke.
            </p>
            <ul class="package-card__features">
              <li class="feature-item">
                <span class="feature-item__icon">✓</span>
                <span class="feature-item__text" data-translate="feature-responsive">Responzivan dizajn</span>
              </li>
              <li class="feature-item">
                <span class="feature-item__icon">✓</span>
                <span class="feature-item__text" data-translate="feature-fast-loading">Brzo učitavanje</span>
              </li>
              <li class="feature-item">
                <span class="feature-item__icon">✓</span>
                <span class="feature-item__text" data-translate="feature-seo">SEO optimizacija</span>
              </li>
              <li class="feature-item">
                <span class="feature-item__icon">✓</span>
                <span class="feature-item__text" data-translate="feature-social">Integracija društvenih mreža</span>
              </li>
              <li class="feature-item">
                <span class="feature-item__icon">✓</span>
                <span class="feature-item__text" data-translate="feature-bugs-security">7 dana besplatnih bug fixova i sigurnosnih ažuriranja</span>
              </li>
            </ul>
            <div class="package-card__pricing">
              <div class="pricing">
                <div class="pricing__row">
                  <span class="pricing__original">€600</span>
                  <span class="pricing__amount">€300</span>
                </div>
                <div class="pricing__period" data-translate="pricing-once">jednokratno</div>
              </div>
            </div>
            <div class="package-card__actions">
              <a href="https://osnovna-stranica-1.netlify.app/" class="btn btn--primary btn--full" target="_blank" rel="noopener noreferrer">
                <span class="btn__text" data-translate="package-visit-first">Posjetite prvi primjer stranice</span>
              </a>
              <a href="https://osnovna-stranica-2.netlify.app/" class="btn btn--primary btn--full" target="_blank" rel="noopener noreferrer">
                <span class="btn__text" data-translate="package-visit-second">Posjetite drugi primjer stranice</span>
              </a>
              <button class="btn btn--outline toggle-details" onclick="toggleDetails('basic-details')" data-translate-hover="package-details-hover">
                <span class="btn__text" data-translate="package-details">Detalji</span>
              </button>
            </div>
            <div class="package-card__details" id="basic-details">
              <div class="details">
                <h4 class="details__title" data-translate="details-title">Dodatni detalji:</h4>
                <ul class="details__list">
                  <li data-translate="details-basic-1">Jednostavna navigacija</li>
                  <li data-translate="details-basic-2">Kontakt forma</li>
                  <li data-translate="details-basic-3">Osnovne animacije</li>
                  <li data-translate="details-basic-4">Prilagođen layout</li>
                </ul>
              </div>
            </div>
            <div class="package-card__actions package-card__actions--optional">
              <button class="btn btn--outline toggle-optional" onclick="toggleOptional('basic-optional')">
                <span class="btn__text" data-translate="package-optional-services">Dodatne mjesečne usluge</span>
              </button>
            </div>
            <div class="package-card__optional" id="basic-optional">
              <div class="optional-services">
                <h4 class="optional-services__title" data-translate="optional-services-title">Dodatne mjesečne usluge:</h4>
                <div class="optional-service">
                  <h5 class="optional-service__name" data-translate="optional-service-maintenance">Održavanje stranice</h5>
                  <span class="optional-service__price" data-translate="pricing-by-agreement">(po dogovoru)</span>
                  <p class="optional-service__description" data-translate="optional-service-maintenance-desc">Redovite sigurnosne ažuriranje, zaštita od hakiranja, DDoS zaštita, SSL certifikati, backup rješenja, monitoring performansi, optimizacija brzine, ažuriranje plugina i frameworka, tehnička podrška 24/7</p>
                </div>
                <div class="optional-service">
                  <h5 class="optional-service__name" data-translate="optional-service-content-changes">Osnovne promjene sadržaja</h5>
                  <span class="optional-service__price">50€<span data-translate="pricing-month">/mjesec</span></span>
                  <p class="optional-service__description" data-translate="optional-service-content-changes-desc">Promjene teksta i slika na vašoj stranici</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Profesionalna Stranica -->
        <div class="package-card package-card--featured">
          <div class="package-card__discount-banner" data-translate="discount-banner">50% POPUST</div>
          <div class="package-card__header">
            <div class="package-card__image">
              <img src="images/profesionalna_stranica.png" alt="Profesionalna Stranica">
              <div class="package-card__overlay"></div>
            </div>
            <div class="package-card__badge package-card__badge--featured" data-translate="badge-recommended">Preporučeno</div>
          </div>
          <div class="package-card__content">
            <h3 class="package-card__title" data-translate="package-pro-title">Profesionalna Stranica</h3>
            <div class="package-card__eta" data-translate="package-pro-eta">ETA: 72 sata</div>
            <p class="package-card__description" data-translate="package-pro-desc">
              Napredno rješenje s modernim CMS-om i interaktivnim elementima, idealno za srednje i velike tvrtke.
            </p>
            <ul class="package-card__features">
              <li class="feature-item">
                <span class="feature-item__icon">✓</span>
                <span class="feature-item__text" data-translate="feature-advanced-cms">Napredni CMS</span>
              </li>
              <li class="feature-item">
                <span class="feature-item__icon">✓</span>
                <span class="feature-item__text" data-translate="feature-interactive">Interaktivni elementi</span>
              </li>
              <li class="feature-item">
                <span class="feature-item__icon">✓</span>
                <span class="feature-item__text" data-translate="feature-blog-gallery">Integracija bloga i galerije</span>
              </li>
              <li class="feature-item">
                <span class="feature-item__icon">✓</span>
                <span class="feature-item__text" data-translate="feature-security">Sigurnosni protokoli</span>
              </li>
              <li class="feature-item">
                <span class="feature-item__icon">✓</span>
                <span class="feature-item__text" data-translate="feature-bugs-security">7 dana besplatnih bug fixova i sigurnosnih ažuriranja</span>
              </li>
            </ul>
            <div class="package-card__pricing">
              <div class="pricing">
                <div class="pricing__row">
                  <span class="pricing__original">€1000</span>
                  <span class="pricing__amount">€500</span>
                </div>
                <div class="pricing__period" data-translate="pricing-once">jednokratno</div>
              </div>
            </div>
            <div class="package-card__actions">
              <a href="https://profesionalnastranica.pythonanywhere.com/" class="btn btn--primary btn--full" target="_blank" rel="noopener noreferrer">
                <span class="btn__text" data-translate="package-visit-site">Posjetite stranicu</span>
              </a>
              <button class="btn btn--outline toggle-details" onclick="toggleDetails('pro-details')" data-translate-hover="package-details-hover">
                <span class="btn__text" data-translate="package-details">Detalji</span>
              </button>
            </div>
            <div class="package-card__details" id="pro-details">
              <div class="details">
                <h4 class="details__title" data-translate="details-title">Dodatni detalji:</h4>
                <ul class="details__list">
                  <li data-translate="details-pro-1">Responsiv dizajn s naprednom animacijom</li>
                  <li data-translate="details-pro-2">CMS integracija</li>
                  <li data-translate="details-pro-3">Blog i galerija</li>
                  <li data-translate="details-pro-4">Brza optimizacija performansi</li>
                </ul>
              </div>
            </div>
            <div class="package-card__actions package-card__actions--optional">
              <button class="btn btn--outline toggle-optional" onclick="toggleOptional('pro-optional')">
                <span class="btn__text" data-translate="package-optional-services">Dodatne mjesečne usluge</span>
              </button>
            </div>
            <div class="package-card__optional" id="pro-optional">
              <div class="optional-services">
                <h4 class="optional-services__title" data-translate="optional-services-title">Dodatne mjesečne usluge:</h4>
                <div class="optional-service">
                  <h5 class="optional-service__name" data-translate="optional-service-maintenance">Održavanje stranice</h5>
                  <span class="optional-service__price" data-translate="pricing-by-agreement">(po dogovoru)</span>
                  <p class="optional-service__description" data-translate="optional-service-maintenance-desc">Redovite sigurnosne ažuriranje, zaštita od hakiranja, DDoS zaštita, SSL certifikati, backup rješenja, monitoring performansi, optimizacija brzine, ažuriranje plugina i frameworka, tehnička podrška 24/7</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Premium Stranica -->
        <div class="package-card">
          <div class="package-card__discount-banner"  data-translate="discount-banner">50% POPUST </div>
          <div class="package-card__header">
            <div class="package-card__image">
              <img src="images/comingsoon.jpg" alt="Premium Stranica">
              <div class="package-card__overlay"></div>
            </div>
            <div class="package-card__badge package-card__badge--premium">Premium</div>
          </div>
          <div class="package-card__content">
            <h3 class="package-card__title" data-translate="package-premium-title">Premium Stranica</h3>
            <div class="package-card__eta" data-translate="package-premium-eta">ETA: 7 dana</div>
            <p class="package-card__description" data-translate="package-premium-desc">
              Kompletno rješenje s najnovijim tehnologijama, prilagođeno kompleksnim zahtjevima i integracijama.
            </p>
            <ul class="package-card__features">
              <li class="feature-item">
                <span class="feature-item__icon">✓</span>
                <span class="feature-item__text" data-translate="feature-ecommerce">E-commerce integracija</span>
              </li>
              <li class="feature-item">
                <span class="feature-item__icon">✓</span>
                <span class="feature-item__text" data-translate="feature-multi-user">Višekorisnički sustav (Login/Signup)</span>
              </li>
              <li class="feature-item">
                <span class="feature-item__icon">✓</span>
                <span class="feature-item__text" data-translate="feature-payment">Integracija plaćanja</span>
              </li>
              <li class="feature-item">
                <span class="feature-item__icon">✓</span>
                <span class="feature-item__text" data-translate="feature-analytics">Napredna analitika</span>
              </li>
              <li class="feature-item">
                <span class="feature-item__icon">✓</span>
                <span class="feature-item__text" data-translate="feature-bugs-security">7 dana besplatnih bug fixova i sigurnosnih ažuriranja</span>
              </li>
            </ul>
            <div class="package-card__pricing">
              <div class="pricing">
                <div class="pricing__row">
                  <span class="pricing__original">€2000</span>
                  <span class="pricing__amount">€1000</span>
                </div>
                <div class="pricing__period" data-translate="pricing-once">jednokratno</div>
              </div>
            </div>
            <div class="package-card__actions">
              <a href="https://example.com" class="btn btn--primary btn--full" target="_blank" rel="noopener noreferrer">
                <span class="btn__text" data-translate="package-visit-site">Posjetite stranicu</span>
              </a>
              <button class="btn btn--outline toggle-details" onclick="toggleDetails('premium-details')" data-translate-hover="package-details-hover">
                <span class="btn__text" data-translate="package-details">Detalji</span>
              </button>
            </div>
            <div class="package-card__details" id="premium-details">
              <div class="details">
                <h4 class="details__title" data-translate="details-title">Dodatni detalji:</h4>
                <ul class="details__list">
                  <li data-translate="details-premium-1">Prilagođene API integracije</li>
                  <li data-translate="details-premium-2">Višekorisnički sustav (Login/Signup)</li>
                  <li data-translate="details-premium-3">Personalizirani dizajn</li>
                  <li data-translate="details-premium-4">Vrhunska korisnička podrška</li>
                </ul>
              </div>
            </div>
            <div class="package-card__actions package-card__actions--optional">
              <button class="btn btn--outline toggle-optional" onclick="toggleOptional('premium-optional')">
                <span class="btn__text" data-translate="package-optional-services">Dodatne mjesečne usluge</span>
              </button>
            </div>
            <div class="package-card__optional" id="premium-optional">
              <div class="optional-services">
                <h4 class="optional-services__title" data-translate="optional-services-title">Dodatne mjesečne usluge:</h4>
                <div class="optional-service">
                  <h5 class="optional-service__name" data-translate="optional-service-maintenance">Održavanje stranice</h5>
                  <span class="optional-service__price" data-translate="pricing-by-agreement">(po dogovoru)</span>
                  <p class="optional-service__description" data-translate="optional-service-maintenance-desc">Redovite sigurnosne ažuriranje, zaštita od hakiranja, DDoS zaštita, SSL certifikati, backup rješenja, monitoring performansi, optimizacija brzine, ažuriranje plugina i frameworka, tehnička podrška 24/7</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Custom Stranica -->
        <div class="package-card">
          <div class="package-card__header">
            <div class="package-card__image">
              <img src="images/comingsoon.jpg" alt="Custom Stranica">
              <div class="package-card__overlay"></div>
            </div>
            <div class="package-card__badge package-card__badge--custom" data-translate="badge-custom">Custom</div>
          </div>
          <div class="package-card__content">
            <h3 class="package-card__title" data-translate="package-custom-title">Custom Projekt</h3>
            <div class="package-card__eta" data-translate="package-custom-eta">ETA: Po dogovoru</div>
            <p class="package-card__description" data-translate="package-custom-desc">
              Potpuno prilagođeno rješenje za velike projekte s jedinstvenim zahtjevima. Kontaktirajte nas za detalje.
            </p>
            <ul class="package-card__features">
              <li class="feature-item">
                <span class="feature-item__icon">✓</span>
                <span class="feature-item__text" data-translate="feature-custom-design">Potpuno prilagođen dizajn</span>
              </li>
              <li class="feature-item">
                <span class="feature-item__icon">✓</span>
                <span class="feature-item__text" data-translate="feature-custom-functionality">Prilagođena funkcionalnost</span>
              </li>
              <li class="feature-item">
                <span class="feature-item__icon">✓</span>
                <span class="feature-item__text" data-translate="feature-dedicated-support">Dedicirani tim za podršku</span>
              </li>
              <li class="feature-item">
                <span class="feature-item__icon">✓</span>
                <span class="feature-item__text" data-translate="feature-scalable">Skalabilna arhitektura</span>
              </li>
              <li class="feature-item">
                <span class="feature-item__icon">✓</span>
                <span class="feature-item__text" data-translate="feature-bugs-security">7 dana besplatnih bug fixova i sigurnosnih ažuriranja</span>
              </li>
            </ul>
            <div class="package-card__pricing">
            </div>
            <div class="package-card__actions">
              <a href="#contact" class="btn btn--primary btn--full">
                <span class="btn__text" data-translate="package-contact-us">Kontaktirajte nas</span>
              </a>
              <button class="btn btn--outline toggle-details" onclick="toggleDetails('custom-details')" data-translate-hover="package-details-hover">
                <span class="btn__text" data-translate="package-details">Detalji</span>
              </button>
            </div>
            <div class="package-card__details" id="custom-details">
              <div class="details">
                <h4 class="details__title" data-translate="details-title">Dodatni detalji:</h4>
                <ul class="details__list">
                  <li data-translate="details-custom-1">Individualna konzultacija</li>
                  <li data-translate="details-custom-2">Prilagođene integracije</li>
                  <li data-translate="details-custom-3">Napredna sigurnost</li>
                  <li data-translate="details-custom-4">Kontinuirana podrška</li>
                </ul>
              </div>
            </div>
            <div class="package-card__actions package-card__actions--optional">
              <button class="btn btn--outline toggle-optional" onclick="toggleOptional('custom-optional')">
                <span class="btn__text" data-translate="package-optional-services">Dodatne mjesečne usluge</span>
              </button>
            </div>
            <div class="package-card__optional" id="custom-optional">
              <div class="optional-services">
                <h4 class="optional-services__title" data-translate="optional-services-title">Dodatne mjesečne usluge:</h4>
                <div class="optional-service">
                  <h5 class="optional-service__name" data-translate="optional-service-maintenance">Održavanje stranice</h5>
                  <span class="optional-service__price" data-translate="pricing-by-agreement">(po dogovoru)</span>
                  <p class="optional-service__description" data-translate="optional-service-maintenance-desc">Redovite sigurnosne ažuriranje, zaštita od hakiranja, DDoS zaštita, SSL certifikati, backup rješenja, monitoring performansi, optimizacija brzine, ažuriranje plugina i frameworka, tehnička podrška 24/7</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Contact Section -->
  <section class="contact" id="contact">
    <div class="contact__container">
      <div class="contact__content">
        <div class="contact__info">
          <div class="contact__header">
            <h2 class="contact__title" data-translate="contact-title">Kontaktirajte Nas</h2>
            <p class="contact__subtitle" data-translate="contact-subtitle">
              Imate pitanje ili želite započeti projekt? Javite nam se i rado ćemo vam pomoći!
            </p>
          </div>
          <div class="contact__details">
            <div class="contact__item">
              <div class="contact__icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                  <polyline points="22,6 12,13 2,6"></polyline>
                </svg>
              </div>
              <div class="contact__text">
                <h3 class="contact__label" data-translate="contact-email">Email</h3>
                <p><a href="mailto:info@startsmarthr.eu" class="contact__link">info@startsmarthr.eu</a></p>
              </div>
            </div>
            <div class="contact__item">
              <div class="contact__icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                </svg>
              </div>
              <div class="contact__text">
                <h3 class="contact__label" data-translate="contact-phone">Telefon</h3>
                <p><a href="tel:+385996105673" class="contact__link">+385 99 610 5673</a></p>
                <p><a href="tel:+385958374220" class="contact__link">+385 95 837 4220</a></p>
              </div>
            </div>
            <div class="contact__item">
              <div class="contact__icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                  <circle cx="12" cy="10" r="3"></circle>
                </svg>
              </div>
              <div class="contact__text">
                <h3 class="contact__label" data-translate="contact-location">Lokacija</h3>
                <p class="contact__location">Zagreb, Hrvatska</p>
              </div>
            </div>
          </div>
        </div>
        <div class="contact__form">
          <form class="form" id="contact-form" novalidate>
            <div class="form__group">
              <input type="text" class="form__input" id="name" name="name" placeholder=" " required>
              <label class="form__label" data-translate="contact-form-name">Vaše ime</label>
              <div class="form__error" id="name-error"></div>
            </div>
            <div class="form__group">
              <input type="email" class="form__input" id="email" name="email" placeholder=" " required>
              <label class="form__label" data-translate="contact-form-email">Vaša email adresa</label>
              <div class="form__error" id="email-error"></div>
            </div>
            <div class="form__group">
              <input type="tel" class="form__input" id="phone" name="phone" placeholder=" ">
              <label class="form__label" data-translate="contact-form-phone">Broj telefona</label>
              <div class="form__error" id="phone-error"></div>
            </div>
            <div class="form__group">
              <textarea class="form__input form__input--textarea" id="message" name="message" placeholder=" " required></textarea>
              <label class="form__label" data-translate="contact-form-message">Vaša poruka</label>
              <div class="form__error" id="message-error"></div>
            </div>
            
            <!-- Robot Check -->
            <div class="form__group">
              <div class="robot-check">
                <div class="cf-turnstile"></div>
                <div class="form__error" id="robot-error"></div>
              </div>
            </div>
            
            <!-- GDPR Consent -->
            <div class="form__group">
              <div class="form__checkbox">
                <input type="checkbox" class="form__checkbox-input" id="gdpr-consent" name="gdpr-consent" required>
                <label class="form__checkbox-label" for="gdpr-consent">
                  <span class="form__checkbox-text" data-translate="contact-form-gdpr">Slažem se s <a href="privacy.php" target="_blank" class="form__link" onclick="event.stopPropagation()">Politikom privatnosti</a> i <a href="terms.php" target="_blank" class="form__link" onclick="event.stopPropagation()">Uvjetima korištenja</a> te dozvoljavam obradu mojih osobnih podataka u svrhu odgovora na moj upit.</span>
                </label>
              </div>
              <div class="form__error" id="gdpr-error"></div>
            </div>
            <button type="submit" class="btn btn--primary btn--full">
              <span class="btn__text" data-translate="contact-form-submit">Pošalji poruku</span>
              <div class="btn__ripple"></div>
            </button>
          </form>
        </div>
      </div>
    </div>
  </section>

  <!-- FAQ Section -->
  <section class="faq" id="faq">
    <div class="faq__container">
      <div class="faq__header">
        <h2 class="faq__title" data-translate="faq-title">Često postavljana pitanja</h2>
        <p class="faq__subtitle" data-translate="faq-subtitle">Odgovorili smo na najčešća pitanja o našim uslugama</p>
      </div>
      
      <div class="faq__content">
        <div class="faq__list">
          <!-- FAQ Item 1 -->
          <div class="faq__item">
            <button class="faq__question" data-translate="faq-q1">
              Koliko dugo traje izrada web stranice?
              <span class="faq__icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M6 9l6 6 6-6"/>
                </svg>
              </span>
            </button>
            <div class="faq__answer">
              <p data-translate="faq-a1">Vrijeme izrade ovisi o odabranom paketu: Osnovna stranica (24-48 sati), Profesionalna stranica (72 sata), Premium stranica (7 dana). Uključujemo i vrijeme za revizije i prilagodbe.</p>
            </div>
          </div>

          <!-- FAQ Item 2 -->
          <div class="faq__item">
            <button class="faq__question" data-translate="faq-q2">
              Mogu li naknadno dodavati sadržaj na stranicu?
              <span class="faq__icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M6 9l6 6 6-6"/>
                </svg>
              </span>
            </button>
            <div class="faq__answer">
              <p data-translate="faq-a2">Da! Profesionalni i Premium paketi uključuju CMS (Content Management System) koji vam omogućava jednostavno dodavanje i uređivanje sadržaja bez tehničkog znanja.</p>
            </div>
          </div>

          <!-- FAQ Item 3 -->
          <div class="faq__item">
            <button class="faq__question" data-translate="faq-q3">
              Je li stranica optimizirana za mobilne uređaje?
              <span class="faq__icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M6 9l6 6 6-6"/>
                </svg>
              </span>
            </button>
            <div class="faq__answer">
              <p data-translate="faq-a3">Apsolutno! Sve naše stranice su potpuno responzivne i optimizirane za sve uređaje - desktop, tablet i mobilni telefon. Vaša stranica će izgledati savršeno na svim ekranima.</p>
            </div>
          </div>

          <!-- FAQ Item 4 -->
          <div class="faq__item">
            <button class="faq__question" data-translate="faq-q4">
              Što uključuje SEO optimizacija?
              <span class="faq__icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M6 9l6 6 6-6"/>
                </svg>
              </span>
            </button>
            <div class="faq__answer">
              <p data-translate="faq-a4">SEO optimizacija uključuje: optimizaciju meta tagova, brzinu učitavanja, strukturirane podatke, optimizaciju slika, mobile-friendly dizajn i osnovne SEO postavke za bolje rangiranje u Google pretraživaču.</p>
            </div>
          </div>

          <!-- FAQ Item 5 -->
          <div class="faq__item">
            <button class="faq__question" data-translate="faq-q5">
              Mogu li promijeniti dizajn nakon što je stranica gotova?
              <span class="faq__icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M6 9l6 6 6-6"/>
                </svg>
              </span>
            </button>
            <div class="faq__answer">
              <p data-translate="faq-a5">Da, možete zatražiti izmjene u dizajnu. Uključujemo 2 besplatne revizije u svaki paket. Dodatne izmjene se naplaćuju prema složenosti promjena.</p>
            </div>
          </div>

          <!-- FAQ Item 6 -->
          <div class="faq__item">
            <button class="faq__question" data-translate="faq-q6">
              Kako funkcionira hosting i domena?
              <span class="faq__icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M6 9l6 6 6-6"/>
                </svg>
              </span>
            </button>
            <div class="faq__answer">
              <p data-translate="faq-a6">Možemo vam pomoći s odabirom i postavljanjem hostinga i domene. Također nudimo hosting usluge s brzim i sigurnim serverima. Domena se može registrirati u vašem imenu ili našem.</p>
            </div>
          </div>

          <!-- FAQ Item 7 -->
          <div class="faq__item">
            <button class="faq__question" data-translate="faq-q7">
              Što ako nisam zadovoljan rezultatom?
              <span class="faq__icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M6 9l6 6 6-6"/>
                </svg>
              </span>
            </button>
            <div class="faq__answer">
              <p data-translate="faq-a7">Vaše zadovoljstvo je naš prioritet! Radimo s vama kroz cijeli proces i uključujemo revizije dok ne budete potpuno zadovoljni. Naš cilj je da imate web stranicu koja premašuje vaša očekivanja.</p>
            </div>
          </div>

          <!-- FAQ Item 8 -->
          <div class="faq__item">
            <button class="faq__question" data-translate="faq-q8">
              Nudite li podršku nakon izrade stranice?
              <span class="faq__icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M6 9l6 6 6-6"/>
                </svg>
              </span>
            </button>
            <div class="faq__answer">
              <p data-translate="faq-a8">Da! Nudimo 30 dana besplatne podrške nakon predaje stranice. Također imamo pakete za održavanje stranice koji uključuju redovite sigurnosne ažuriranja i tehničku podršku.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer class="footer">
    <div class="footer__container">
      <div class="footer__content">
        <div class="footer__brand">
          <div class="footer__logo">
            <h3 class="footer__title">Start Smart HR</h3>
            <div class="footer__logo-accent"></div>
          </div>
          <p class="footer__description" data-translate="footer-description">
            Transformirajte svoje ideje u digitalnu stvarnost. 
            Profesionalni web dizajn i razvoj na dohvat ruke.
          </p>
          <div class="footer__contact">
            <a href="mailto:info@startsmarthr.eu" class="footer__contact-link">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                <polyline points="22,6 12,13 2,6"></polyline>
              </svg>
              info@startsmarthr.eu
            </a>
            <a href="tel:+385996105673" class="footer__contact-link">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
              </svg>
              +385 99 610 5673
            </a>
            <a href="tel:+385958374220" class="footer__contact-link">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
              </svg>
              +385 95 837 4220
            </a>
          </div>
          <p class="footer__company-info">Start Smart, zajednički obrt za izradu i optimizaciju web stranica, vl. Mihael Kovačić i Roko Nevistić</p>
        </div>
        
        <div class="footer__sections">
          <div class="footer__section">
            <h4 class="footer__heading" data-translate="footer-services">Usluge</h4>
            <ul class="footer__links">
              <li><a href="#packages" class="footer__link" data-translate="footer-basic-page">Osnovna stranica</a></li>
              <li><a href="#packages" class="footer__link" data-translate="footer-pro-page">Profesionalna stranica</a></li>
              <li><a href="#packages" class="footer__link" data-translate="footer-premium-page">Premium stranica</a></li>
              <li><a href="#contact" class="footer__link" data-translate="footer-consultations">Konsultacije</a></li>
            </ul>
          </div>
          
          <div class="footer__section">
            <h4 class="footer__heading" data-translate="footer-navigation">Navigacija</h4>
            <ul class="footer__links">
              <li><a href="#hero" class="footer__link" data-translate="footer-home">Početna</a></li>
              <li><a href="#packages" class="footer__link" data-translate="footer-packages">Paketi</a></li>
              <li><a href="#contact" class="footer__link" data-translate="footer-contact">Kontakt</a></li>
              <li><a href="#faq" class="footer__link" data-translate="footer-faq">FAQ</a></li>
              <li><a href="about.php" class="footer__link" data-translate="footer-about">O nama</a></li>
            </ul>
          </div>
          
          <div class="footer__section">
            <h4 class="footer__heading" data-translate="footer-follow">Pratite nas</h4>
            <div class="footer__social">
              <a href="https://www.facebook.com/people/Start-Smart-HR/61581505773838/" target="_blank" class="footer__social-link" aria-label="Facebook">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                </svg>
              </a>
              <a href="https://www.instagram.com/startsmarthr.eu/" target="_blank" class="footer__social-link" aria-label="Instagram">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                </svg>
              </a>
            </div>
          </div>
        </div>
      </div>
      
      <div class="footer__bottom">
        <div class="footer__bottom-content">
          <p class="footer__copyright">&copy; <span id="current-year">2025</span> Start Smart, <span data-translate="footer-all-rights">Sva prava pridržana.</span></p>
          <div class="footer__legal">
            <a href="privacy.php" class="footer__legal-link" data-translate="footer-privacy">Privatnost</a>
            <a href="terms.php" class="footer__legal-link" data-translate="footer-terms">Uvjeti korištenja</a>
          </div>
        </div>
      </div>
    </div>
  </footer>

  <!-- Chat Widget -->
  <div id="chat-widget" class="chat-widget">
    <button id="chat-toggle" class="chat-toggle" aria-label="Open chat">
      <svg class="chat-icon-open" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
      </svg>
      <svg class="chat-icon-close" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none;">
        <line x1="18" y1="6" x2="6" y2="18"></line>
        <line x1="6" y1="6" x2="18" y2="18"></line>
      </svg>
      <span class="chat-badge" style="display:none;">0</span>
    </button>
    
    <div id="chat-box" class="chat-box" style="display:none;">
      <div class="chat-header">
        <div class="chat-header-info">
          <div class="chat-avatar">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
              <circle cx="12" cy="7" r="4"></circle>
            </svg>
          </div>
          <div>
            <h4>Start Smart HR</h4>
            <span class="chat-status">Online</span>
          </div>
        </div>
        <button id="chat-close" class="chat-close-btn" aria-label="Close chat">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="18" y1="6" x2="6" y2="18"></line>
            <line x1="6" y1="6" x2="18" y2="18"></line>
          </svg>
        </button>
      </div>
      
      <div id="chat-messages" class="chat-messages"></div>
      
      <form id="chat-form" class="chat-form">
        <input type="text" id="chat-input" class="chat-input" placeholder="Napišite poruku..." autocomplete="off" required>
        <button type="submit" class="chat-send" aria-label="Send message">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="22" y1="2" x2="11" y2="13"></line>
            <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
          </svg>
        </button>
      </form>
    </div>
  </div>

  <!-- JavaScript Section -->
  <script src="script.js"></script>
  <script src="chat-widget.js"></script>
</body>
</html>
