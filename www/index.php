<?php
/**
 * index.php — CAPK 2-1-1 Kern County Resource Directory
 * Landing page + search page (combined)
 * Handles URL params passed from capk.org redirects (iCarol-style query strings)
 */

// Parse incoming URL params from CAPK website redirects
// e.g. ?Search=Soup+Kitchens&County=Kern&City=-1&StateProvince=CA
$url_search  = isset($_GET['Search'])        ? trim($_GET['Search'])        : '';
$url_county  = isset($_GET['County'])        ? trim($_GET['County'])        : '';
$url_city    = isset($_GET['City'])          ? trim($_GET['City'])          : '';
$url_state   = isset($_GET['StateProvince']) ? trim($_GET['StateProvince']) : 'CA';
$url_sort    = isset($_GET['sort'])          ? trim($_GET['sort'])          : 'Proximity';

// Normalize city — iCarol passes '-1' for "all cities"
if ($url_city === '-1') $url_city = '';

// Flag: did we arrive with search params?
$has_url_params = !empty($url_search) || !empty($url_county) || !empty($url_city);

$active_nav = '211';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Find local support services in Kern County — food, health, housing, utilities, and more. Search the CAPK 2-1-1 resource directory.">
  <title>2-1-1 Kern County Resource Directory — CAPK</title>

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&family=Source+Sans+3:wght@300;400;600&display=swap" rel="stylesheet">

  <!-- Leaflet CSS -->
  <link rel="stylesheet"
    href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
    crossorigin=""/>

  <!-- App styles -->
  <link rel="stylesheet" href="css/main.css">
  <link rel="stylesheet" href="css/map.css">

</head>
<body>
  <!-- ===================================================
       TOPBAR
  =================================================== -->
  <div class="topbar" role="navigation" aria-label="Utility navigation">
    <a href="/es/" class="topbar__link" lang="es" aria-label="Ver en Español">Español</a>
    <div class="topbar__divider" aria-hidden="true"></div>
    <a href="https://www.capk.org/donate/"
       class="topbar__link topbar__btn topbar__btn--donate"
       target="_blank" rel="noopener noreferrer"
       aria-label="Donate Now (opens in new tab)">
      Donate Now
    </a>
    <a href="https://www.capk.org/newsletter/"
       class="topbar__link topbar__btn topbar__btn--newsletter"
       target="_blank" rel="noopener noreferrer"
       aria-label="Newsletter Signup (opens in new tab)">
      Newsletter Signup
    </a>
  </div>

  <!-- ===================================================
       HEADER
  =================================================== -->
  <header class="site-header" role="banner">
    <div class="site-header__inner">

      <!-- Logo -->
      <a href="/" class="site-header__logo" aria-label="CAPK — Go to homepage">
        <img
          src="https://www.capk.org/wp-content/themes/capk-new/images/logo.svg"
          alt="CAPK | Community Action Partnership of Kern"
          width="152"
          height="68"
          onerror="this.onerror=null; this.src='https://www.capk.org/wp-content/themes/capk-new/images/logo.png'">
      </a>

      <!-- 211 Badge -->
      <div class="badge-211" aria-label="2-1-1 Kern County">
        <span class="badge-211__number" aria-hidden="true">2·1·1</span>
        <span class="badge-211__label"  aria-hidden="true">Kern County</span>
      </div>

      <!-- Desktop nav -->
      <nav class="site-header__nav" aria-label="Main navigation">

        <a href="https://www.capk.org/about/"    class="site-header__nav-link">About</a>
        <a href="https://www.capk.org/programs/" class="site-header__nav-link">Programs</a>

        <!-- 2-1-1 Kern County — has dropdown -->
        <div class="site-header__nav-item">

          <button
            class="site-header__nav-link site-header__nav-link--active site-header__dropdown-trigger"
            id="dropdown-211-trigger"
            aria-haspopup="true"
            aria-expanded="false"
            aria-controls="dropdown-211"
            aria-current="page">
            2-1-1 Kern County
            <svg class="site-header__dropdown-chevron"
                 aria-hidden="true"
                 width="12" height="12"
                 fill="none" stroke="currentColor"
                 stroke-width="2.5" viewBox="0 0 24 24">
              <path d="M6 9l6 6 6-6"/>
            </svg>
          </button>

          <ul
            class="site-header__dropdown"
            id="dropdown-211"
            role="menu"
            aria-labelledby="dropdown-211-trigger"
            hidden>

            <li role="none">
              <a href="/index.php"
                 class="site-header__dropdown-item"
                 role="menuitem">
                <svg aria-hidden="true" width="15" height="15"
                     fill="none" stroke="currentColor"
                     stroke-width="2" viewBox="0 0 24 24">
                  <circle cx="11" cy="11" r="8"/>
                  <path d="m21 21-4.35-4.35"/>
                </svg>
                Resource Directory
              </a>
            </li>

            <li role="none">
              <a href="/login.php"
                 class="site-header__dropdown-item"
                 role="menuitem">
                <svg aria-hidden="true" width="15" height="15"
                     fill="none" stroke="currentColor"
                     stroke-width="2" viewBox="0 0 24 24">
                  <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
                  <polyline points="10 17 15 12 10 7"/>
                  <line x1="15" y1="12" x2="3" y2="12"/>
                </svg>
                Portal Login
              </a>
            </li>

            <li role="none" class="site-header__dropdown-divider" aria-hidden="true"></li>

            <li role="none">
              <a href="/admin_dashboard.php"
                 class="site-header__dropdown-item"
                 role="menuitem">
                <svg aria-hidden="true" width="15" height="15"
                     fill="none" stroke="currentColor"
                     stroke-width="2" viewBox="0 0 24 24">
                  <rect x="3"  y="3"  width="7" height="7" rx="1"/>
                  <rect x="14" y="3"  width="7" height="7" rx="1"/>
                  <rect x="3"  y="14" width="7" height="7" rx="1"/>
                  <rect x="14" y="14" width="7" height="7" rx="1"/>
                </svg>
                Admin Portal
              </a>
            </li>

          </ul>
        </div><!-- /.site-header__nav-item -->

        <a href="https://www.capk.org/get-involved/" class="site-header__nav-link">Get Involved</a>
        <a href="https://www.capk.org/locations/"    class="site-header__nav-link">Locations</a>
        <a href="https://www.capk.org/employment/"   class="site-header__nav-link">Employment</a>
        <a href="https://www.capk.org/public-info/"  class="site-header__nav-link">Public Info</a>
        <a href="https://www.capk.org/contact/"      class="site-header__nav-link">Contact</a>

      </nav>

      <!-- Mobile hamburger -->
      <button
        class="site-header__hamburger"
        id="mobile-menu-toggle"
        aria-label="Open navigation menu"
        aria-expanded="false"
        aria-controls="mobile-nav-drawer">
        <span aria-hidden="true"></span>
        <span aria-hidden="true"></span>
        <span aria-hidden="true"></span>
      </button>

    </div><!-- /.site-header__inner -->

    <!-- Mobile nav drawer -->
    <nav class="site-header__nav-drawer"
         id="mobile-nav-drawer"
         hidden
         aria-label="Mobile navigation">

      <a href="https://www.capk.org/about/"    class="site-header__nav-link">About</a>
      <a href="https://www.capk.org/programs/" class="site-header__nav-link">Programs</a>

      <!-- 2-1-1 group — flat list with teal left border, no nested dropdown -->
      <div class="site-header__nav-drawer-group">
        <span class="site-header__nav-drawer-group-label">2-1-1 Kern County</span>
        <a href="/index.php"
           class="site-header__nav-link site-header__nav-link--active site-header__nav-link--sub"
           aria-current="page">
          Resource Directory
        </a>
        <a href="/login.php"
           class="site-header__nav-link site-header__nav-link--sub">
          Portal Login
        </a>
        <a href="/agency_dashboard.php"
           class="site-header__nav-link site-header__nav-link--sub">
          Agency Portal
        </a>
        <a href="/admin_dashboard.php"
           class="site-header__nav-link site-header__nav-link--sub">
          Admin Portal
        </a>
      </div>

      <a href="https://www.capk.org/get-involved/" class="site-header__nav-link">Get Involved</a>
      <a href="https://www.capk.org/locations/"    class="site-header__nav-link">Locations</a>
      <a href="https://www.capk.org/employment/"   class="site-header__nav-link">Employment</a>
      <a href="https://www.capk.org/public-info/"  class="site-header__nav-link">Public Info</a>
      <a href="https://www.capk.org/contact/"      class="site-header__nav-link">Contact</a>

    </nav>

    <!-- Crimson accent bar -->
    <div class="site-header__accent-bar" aria-hidden="true"></div>

  </header>

  <!-- ===================================================
       MAIN CONTENT — MAP + SEARCH
  =================================================== -->
  <main id="main-content" class="map-section" aria-label="Resource map and search">

    <!-- Leaflet map canvas -->
    <div id="map"
         role="application"
         aria-label="Interactive map of Kern County community resources"
         tabindex="0">
    </div>

    <!-- Floating search bar -->
    <div class="search-bar" role="search" aria-label="Search for resources">

      <div class="search-bar__top">

        <svg class="search-bar__icon" aria-hidden="true"
             width="18" height="18" viewBox="0 0 24 24"
             fill="none" stroke="currentColor"
             stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="11" cy="11" r="8"/>
          <path d="m21 21-4.35-4.35"/>
        </svg>

        <label for="search-input" class="sr-only">
          Search for food, housing, health, and other resources
        </label>
        <input
          type="search"
          id="search-input"
          class="search-bar__input"
          placeholder="Search food, housing, health resources…"
          autocomplete="off"
          autocorrect="off"
          spellcheck="false"
          aria-label="Search resources"
          aria-autocomplete="list"
          aria-controls="results-list"
          value="<?= htmlspecialchars($url_search) ?>">

        <!-- Clear button -->
        <button
          class="search-bar__clear <?= !empty($url_search) ? 'visible' : '' ?>"
          id="search-clear"
          aria-label="Clear search"
          type="button">
          <svg aria-hidden="true" width="14" height="14" viewBox="0 0 24 24"
               fill="none" stroke="currentColor"
               stroke-width="2.5" stroke-linecap="round">
            <path d="M18 6 6 18M6 6l12 12"/>
          </svg>
        </button>

        <!-- Search submit button -->
        <button class="search-bar__btn" id="search-btn" type="button" aria-label="Search">
          <svg aria-hidden="true" width="15" height="15" viewBox="0 0 24 24"
               fill="none" stroke="currentColor"
               stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="11" cy="11" r="8"/>
            <path d="m21 21-4.35-4.35"/>
          </svg>
          <span>Search</span>
        </button>

        <!-- Collapse toggle — mobile only -->
        <button
          class="search-bar__collapse-btn"
          id="search-collapse-btn"
          aria-label="Collapse search options"
          aria-expanded="true"
          aria-controls="search-collapsible"
          type="button">
          <svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24"
               fill="none" stroke="currentColor"
               stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="m18 15-6-6-6 6"/>
          </svg>
        </button>

      </div><!-- /.search-bar__top -->

      <!-- Collapsible pills row -->
      <div class="search-bar__collapsible" id="search-collapsible">
        <div class="search-bar__pills" role="list" aria-label="Quick category filters">
          <button class="pill" role="listitem" data-keyword="Fresh Food"              aria-pressed="false"><span class="pill__icon" aria-hidden="true">🥦</span> Fresh Food</button>
          <button class="pill" role="listitem" data-keyword="Soup Kitchens"           aria-pressed="false"><span class="pill__icon" aria-hidden="true">🍲</span> Soup Kitchens</button>
          <button class="pill" role="listitem" data-keyword="Food Vouchers"           aria-pressed="false"><span class="pill__icon" aria-hidden="true">🎟️</span> Food Vouchers</button>
          <button class="pill" role="listitem" data-keyword="WIC"                     aria-pressed="false"><span class="pill__icon" aria-hidden="true">👶</span> WIC</button>
          <button class="pill" role="listitem" data-keyword="Specialty Food Providers" aria-pressed="false"><span class="pill__icon" aria-hidden="true">🌿</span> Specialty Food</button>
          <button class="pill" role="listitem" data-keyword="Brown Bag Food Program"  aria-pressed="false"><span class="pill__icon" aria-hidden="true">🛍️</span> Brown Bag</button>
          <button class="pill" role="listitem" data-keyword="Post Disaster Food Services" aria-pressed="false"><span class="pill__icon" aria-hidden="true">🆘</span> Disaster Food</button>
          <button class="pill" role="listitem" data-keyword="Food Pantries"           aria-pressed="false"><span class="pill__icon" aria-hidden="true">📦</span> Food Pantries</button>
          <button class="pill" role="listitem" data-keyword="CalFresh"                aria-pressed="false"><span class="pill__icon" aria-hidden="true">🌾</span> CalFresh</button>
        </div>
      </div><!-- /.search-bar__collapsible -->

    </div><!-- /.search-bar -->

    <!-- Near Me button -->
    <button class="btn-near-me" id="btn-near-me" type="button"
            aria-label="Show resources near my location">
      <svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24"
           fill="none" stroke="currentColor"
           stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="12" cy="12" r="3"/>
        <path d="M12 2v3m0 14v3M2 12h3m14 0h3"/>
      </svg>
      <span>Near Me</span>
    </button>

    <!-- ===================================================
         DESKTOP RESULTS PANEL
    =================================================== -->
    <aside class="results-panel" id="results-panel" aria-label="Search results" hidden>
      <div class="results-panel__header">
        <div>
          <div class="results-panel__title" id="results-panel-title">Results</div>
          <div class="results-panel__count"
               id="results-count"
               aria-live="polite"
               aria-atomic="true"></div>
        </div>
        <button class="results-panel__close"
                id="results-panel-close"
                aria-label="Close results panel"
                type="button">
          <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24"
               fill="none" stroke="currentColor"
               stroke-width="2" stroke-linecap="round">
            <path d="M18 6 6 18M6 6l12 12"/>
          </svg>
        </button>
      </div>
      <div class="results-panel__list"
           id="results-list"
           role="list"
           aria-label="Program results"
           tabindex="0">
      </div>
    </aside>

    <!-- ===================================================
         MOBILE BOTTOM SHEET
    =================================================== -->
    <div class="bottom-sheet" id="bottom-sheet"
         role="region" aria-label="Search results" hidden>
      <div class="bottom-sheet__handle" aria-hidden="true"></div>
      <div class="bottom-sheet__header">
        <div class="bottom-sheet__title" id="bottom-sheet-title">Results</div>
        <button class="results-panel__close"
                id="bottom-sheet-close"
                aria-label="Close results"
                type="button">
          <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24"
               fill="none" stroke="currentColor"
               stroke-width="2" stroke-linecap="round">
            <path d="M18 6 6 18M6 6l12 12"/>
          </svg>
        </button>
      </div>
      <div class="bottom-sheet__list"
           id="mobile-results-list"
           role="list"
           aria-label="Program results">
      </div>
    </div>

  </main>

  <!-- ===================================================
       SCRIPTS
  =================================================== -->

  <!-- Leaflet JS -->
  <script
    src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
    integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
    crossorigin="">
  </script>

  <!-- PHP config passed to JS -->
  <script>
    const CAPK_CONFIG = {
      urlSearch:      <?= json_encode($url_search) ?>,
      urlCounty:      <?= json_encode($url_county) ?>,
      urlCity:        <?= json_encode($url_city) ?>,
      urlSort:        <?= json_encode($url_sort) ?>,
      hasUrlParams:   <?= json_encode($has_url_params) ?>,
      searchEndpoint: '/search.php'
    };
  </script>

  <script src="js/map.js"></script>
  <script src="js/nav.js"></script>

</body>
</html>