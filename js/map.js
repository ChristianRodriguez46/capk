(function () {
  'use strict';

  /* --------------------------------------------------
     CONSTANTS
  -------------------------------------------------- */
  const KERN_BOUNDS = L.latLngBounds([34.70, -119.90], [36.00, -117.60]);
  const KERN_CENTER = [35.37, -119.02];

  /* --------------------------------------------------
     MAP INIT
  -------------------------------------------------- */
  const map = L.map('map', {
    zoomControl: false,
    attributionControl: true
  });

  L.control.zoom({ position: 'bottomright' }).addTo(map);

  L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
  }).addTo(map);

  requestAnimationFrame(() => requestAnimationFrame(() => map.invalidateSize()));

  /* --------------------------------------------------
     MARKER ICONS
  -------------------------------------------------- */
  function makeIcon(num = '', active = false) {
    return L.divIcon({
      className: '',
      html: `<div class="map-marker${active ? ' map-marker--active' : ''}">
               <span class="map-marker__label">${num}</span>
             </div>`,
      iconSize: [32, 42],
      iconAnchor: [16, 42],
      popupAnchor: [0, -44]
    });
  }

  /* --------------------------------------------------
     STATE
  -------------------------------------------------- */
  let markerLayer = L.layerGroup().addTo(map);
  let currentResults = [];
  let activeCardIndex = -1;
  let activePill = null;
  let userLatLng = null;

  /* --------------------------------------------------
     PAGE LOAD — decide initial map view
  -------------------------------------------------- */
  if (CAPK_CONFIG.hasUrlParams) {
    map.fitBounds(KERN_BOUNDS);
    handleSearch(CAPK_CONFIG.urlSearch);
  } else {
    map.fitBounds(KERN_BOUNDS);
    tryGeolocate(false);
  }

  /* --------------------------------------------------
     GEOLOCATION
  -------------------------------------------------- */
  function tryGeolocate(showFeedback = true) {
    if (!navigator.geolocation) {
      if (showFeedback) alert('Geolocation is not supported by your browser.');
      return;
    }
    navigator.geolocation.getCurrentPosition(
      function (pos) {
        const { latitude: lat, longitude: lng } = pos.coords;

        userLatLng = { lat, lng };

        const pt = L.latLng(lat, lng);
        if (KERN_BOUNDS.pad(0.3).contains(pt)) {
          map.setView([lat, lng], 12);
        }

        if (showFeedback) {
          L.circleMarker([lat, lng], {
            radius: 8,
            fillColor: '#1d6f7a',
            color: '#fff',
            weight: 3,
            fillOpacity: 1
          }).addTo(map)
            .bindPopup(
              '<div class="popup-here">📍 You are here</div>',
              { minWidth: 10, maxWidth: 160, className: 'popup-here-wrapper' }
            )
            .openPopup();
        }

        if (currentResults.length > 0) {
          renderResults(currentResults.map(function(r) {
            const clean = Object.assign({}, r);
            delete clean._marker;
            delete clean._distKm;
            return clean;
          }));
        }

        positionNearMeBtn();
      },
      function () {
        if (showFeedback) {
          alert('Could not get your location. Make sure location is enabled for this site.');
          map.fitBounds(KERN_BOUNDS);
        }
      }
    );
  }

  function positionNearMeBtn() {
    if (window.innerWidth > 768) return;
    const bar = document.querySelector('.search-bar');
    const btn = document.getElementById('btn-near-me');
    if (bar && btn) {
      const barBottom = bar.getBoundingClientRect().bottom;
      const mapTop    = document.getElementById('map').getBoundingClientRect().top;
      btn.style.setProperty('--near-me-top', (barBottom - mapTop + 10) + 'px');
    }
  }
  window.addEventListener('resize', positionNearMeBtn);
  requestAnimationFrame(positionNearMeBtn);

  document.getElementById('btn-near-me').addEventListener('click', function () {
    tryGeolocate(true);
  });

  /* --------------------------------------------------
     SEARCH — input + button + pills
  -------------------------------------------------- */
  const searchInput = document.getElementById('search-input');
  const searchBtn   = document.getElementById('search-btn');
  const searchClear = document.getElementById('search-clear');

  searchBtn.addEventListener('click', function () {
    handleSearch(searchInput.value.trim());
  });

  searchInput.addEventListener('keydown', function (e) {
    if (e.key === 'Enter') handleSearch(searchInput.value.trim());
  });

  searchInput.addEventListener('input', function () {
    searchClear.classList.toggle('visible', this.value.length > 0);
    if (activePill && this.value !== activePill.dataset.keyword) {
      deactivatePill();
    }
  });

  searchClear.addEventListener('click', function () {
    searchInput.value = '';
    searchClear.classList.remove('visible');
    deactivatePill();
    clearResults();
  });

  document.querySelectorAll('.pill').forEach(function (pill) {
    pill.addEventListener('click', function () {
      if (this === activePill) {
        deactivatePill();
        clearResults();
        return;
      }
      deactivatePill();
      activePill = this;
      this.classList.add('active');
      this.setAttribute('aria-pressed', 'true');
      const kw = this.dataset.keyword;
      searchInput.value = kw;
      searchClear.classList.add('visible');
      handleSearch(kw);
    });
  });

  function deactivatePill() {
    if (activePill) {
      activePill.classList.remove('active');
      activePill.setAttribute('aria-pressed', 'false');
      activePill = null;
    }
  }

  /* --------------------------------------------------
     HANDLE SEARCH
     TODO: replace mock below with real fetch:
       fetch(`${CAPK_CONFIG.searchEndpoint}?q=${encodeURIComponent(query)}&county=Kern`)
         .then(r => r.json())
         .then(data => renderResults(data.results))
         .catch(() => showErrorState());
  -------------------------------------------------- */
  function handleSearch(query) {
    if (!query) return;

    showLoadingState();

    setTimeout(function () {
      const mock = getMockResults(query);
      renderResults(mock);
    }, 600);
  }

  /* --------------------------------------------------
     MOCK DATA — replace entirely when search.php is wired up
  -------------------------------------------------- */
  function getMockResults(query) {
    const all = [
      {
        id: 1,
        program_name: 'Brenda Jean\'s Food Pantry',
        agency_name:  'Community Action Partnership of Kern',
        description:  'Provides a food pantry. Food supply may vary. Walk-in, first come first served every Tuesday.',
        address:      '2015 Brundage Lane, Bakersfield, CA 93304',
        phone:        '(661) 336-5236',
        lat: 35.336, lng: -119.040
      },
      {
        id: 2,
        program_name: 'The River Bakersfield',
        agency_name:  'Community Action Partnership of Kern',
        description:  'Provides a hot lunch Monday through Friday. Open to all members of the public.',
        address:      '928 17th Street, Bakersfield, CA 93301',
        phone:        '(661) 326-8049',
        lat: 35.375, lng: -119.021
      },
      {
        id: 3,
        program_name: 'Saint Vincent de Paul',
        agency_name:  'Saint Vincent de Paul Society',
        description:  'Provides day services for the homeless population. Offers showers, hygiene kits, clothing assistance.',
        address:      '300 Baker Street, Bakersfield, CA 93305',
        phone:        '(661) 325-7522',
        lat: 35.372, lng: -119.015
      },
      {
        id: 4,
        program_name: 'Food Bank of Kern County',
        agency_name:  'Food Bank of Kern County',
        description:  'Distributes food to agencies and individuals. Serves all Kern County zip codes.',
        address:      '1525 Business Park Drive, Bakersfield, CA 93301',
        phone:        '(661) 322-9897',
        lat: 35.381, lng: -119.048
      },
      {
        id: 5,
        program_name: 'WIC — Kern County Public Health',
        agency_name:  'Kern County Public Health Services Department',
        description:  'WIC provides healthy foods, nutrition education, breastfeeding support for pregnant and postpartum women, infants, and children up to age 5.',
        address:      '1800 Mount Vernon Ave, Bakersfield, CA 93306',
        phone:        '(661) 321-3000',
        lat: 35.385, lng: -118.980
      }
    ];

    const q = query.toLowerCase();
    const filtered = all.filter(function (r) {
      return (
        r.program_name.toLowerCase().includes(q) ||
        r.agency_name.toLowerCase().includes(q) ||
        r.description.toLowerCase().includes(q)
      );
    });
    return filtered.length > 0 ? filtered : all;
  }

  /* --------------------------------------------------
     RENDER RESULTS — cards + markers
  -------------------------------------------------- */
  function renderResults(results) {
    activeCardIndex = -1;

    if (userLatLng) {
      results = results.slice().sort(function (a, b) {
        return haversineKm(userLatLng.lat, userLatLng.lng, a.lat, a.lng)
             - haversineKm(userLatLng.lat, userLatLng.lng, b.lat, b.lng);
      });
      results.forEach(function (r) {
        r._distKm = haversineKm(userLatLng.lat, userLatLng.lng, r.lat, r.lng);
      });
    }

    currentResults = results;
    markerLayer.clearLayers();
    openPanel();

    if (!results || results.length === 0) {
      showEmptyState();
      return;
    }

    updateCount(results.length);

    const desktopList = document.getElementById('results-list');
    const mobileList  = document.getElementById('mobile-results-list');
    desktopList.innerHTML = '';
    mobileList.innerHTML  = '';

    const markerRefs = [];

    results.forEach(function (r, i) {
      const displayNum = i + 1;

      desktopList.appendChild(buildCard(r, i, displayNum));
      mobileList.appendChild(buildCard(r, i, displayNum));

      if (r.lat && r.lng) {
        const marker = L.marker([r.lat, r.lng], { icon: makeIcon(displayNum, false) });
        marker.bindPopup(buildPopupHTML(r, displayNum), { maxWidth: 260 });
        marker.on('click', function () { highlightCard(i); });
        marker.addTo(markerLayer);
        markerRefs.push({ marker, index: i });
      }
    });

    markerRefs.forEach(function (m) {
      currentResults[m.index]._marker = m.marker;
    });

    const layers = markerLayer.getLayers();
    if (layers.length > 0) {
      map.fitBounds(L.featureGroup(layers).getBounds(), { padding: [80, 80] });
    }
  }

  function haversineKm(lat1, lng1, lat2, lng2) {
    if (!lat2 || !lng2) return Infinity;
    const R = 6371;
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLng = (lng2 - lng1) * Math.PI / 180;
    const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
              Math.cos(lat1 * Math.PI/180) * Math.cos(lat2 * Math.PI/180) *
              Math.sin(dLng/2) * Math.sin(dLng/2);
    return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
  }

  /* --------------------------------------------------
     BUILD CARD
  -------------------------------------------------- */
  function buildCard(r, index, displayNum) {
    const card = document.createElement('a');
    card.href  = `/program.php?id=${r.id}`;
    card.className = 'result-card';
    card.setAttribute('role', 'listitem');
    card.setAttribute('aria-label', `#${displayNum} ${r.program_name} — ${r.agency_name}`);
    card.dataset.index = index;

    const distStr = r._distKm != null
      ? (r._distKm < 1.6
          ? (r._distKm * 1000 / 1609 * 5280).toFixed(0) + ' ft away'
          : (r._distKm / 1.609).toFixed(1) + ' mi away')
      : '';

    card.innerHTML = `
      <div class="result-card__header">
        <div class="result-card__num" aria-hidden="true">${displayNum}</div>
        <div class="result-card__header-text">
          <div class="result-card__agency">${escHtml(r.agency_name)}</div>
          <div class="result-card__name">${escHtml(r.program_name)}</div>
        </div>
        ${distStr ? `<div class="result-card__dist">${distStr}</div>` : ''}
      </div>
      <div class="result-card__desc">${escHtml(r.description)}</div>
      <div class="result-card__meta">
        ${r.address ? `<span class="result-card__meta-item">
          <svg aria-hidden="true" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 13-8 13s-8-7-8-13a8 8 0 1 1 16 0z"/><circle cx="12" cy="10" r="3"/></svg>
          ${escHtml(r.address)}
        </span>` : ''}
        ${r.phone ? `<span class="result-card__meta-item">
          <svg aria-hidden="true" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.18 2 2 0 0 1 3.6 1h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.6a16 16 0 0 0 6.29 6.29l.96-.85a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
          ${escHtml(r.phone)}
        </span>` : ''}
      </div>
      <span class="result-card__cta">
        View Details
        <svg aria-hidden="true" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
      </span>
    `;

    card.addEventListener('click', function (e) {
      e.preventDefault();
      highlightCard(index);
      window.location.href = this.href;
    });

    card.addEventListener('mouseenter', function () {
      const r = currentResults[index];
      if (r && r._marker) r._marker.setIcon(makeIcon(index + 1, true));
    });
    card.addEventListener('mouseleave', function () {
      if (index === activeCardIndex) return;
      const r = currentResults[index];
      if (r && r._marker) r._marker.setIcon(makeIcon(index + 1, false));
    });

    return card;
  }

  /* --------------------------------------------------
     BUILD POPUP HTML
  -------------------------------------------------- */
  function buildPopupHTML(r, displayNum) {
    return `
      <div class="map-popup">
        <div class="map-popup__num">#${displayNum}</div>
        <div class="map-popup__agency">${escHtml(r.agency_name)}</div>
        <div class="map-popup__name">${escHtml(r.program_name)}</div>
        ${r.address ? `<div class="map-popup__address">${escHtml(r.address)}</div>` : ''}
        <a href="/program.php?id=${r.id}" class="map-popup__link">
          View Details
          <svg aria-hidden="true" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </a>
      </div>`;
  }

  /* --------------------------------------------------
     HIGHLIGHT CARD + MARKER
  -------------------------------------------------- */
  function highlightCard(index) {
    if (activeCardIndex >= 0) {
      document.querySelectorAll(`.result-card[data-index="${activeCardIndex}"]`).forEach(function (el) {
        el.classList.remove('highlighted');
      });
      const prev = currentResults[activeCardIndex];
      if (prev && prev._marker) prev._marker.setIcon(makeIcon(activeCardIndex + 1, false));
    }

    activeCardIndex = index;

    document.querySelectorAll(`.result-card[data-index="${index}"]`).forEach(function (el) {
      el.classList.add('highlighted');
      el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    });

    const r = currentResults[index];
    if (r && r._marker) {
      r._marker.setIcon(makeIcon(index + 1, true));
      r._marker.openPopup();
      const sheet = document.getElementById('bottom-sheet');
      if (sheet && !sheet.classList.contains('expanded')) {
        sheet.classList.remove('peek');
        sheet.classList.add('expanded');
      }
    }
  }

  /* --------------------------------------------------
     PANEL OPEN / CLOSE
  -------------------------------------------------- */
  function openPanel() {
    const panel = document.getElementById('results-panel');
    const sheet = document.getElementById('bottom-sheet');

    panel.removeAttribute('hidden');
    requestAnimationFrame(function () { panel.classList.add('open'); });
    requestAnimationFrame(() => requestAnimationFrame(() => map.invalidateSize()));

    sheet.removeAttribute('hidden');
    requestAnimationFrame(function () {
      sheet.classList.remove('expanded');
      sheet.classList.add('peek');
    });
  }

  function closePanel() {
    const panel = document.getElementById('results-panel');
    const sheet = document.getElementById('bottom-sheet');

    panel.classList.remove('open');
    panel.addEventListener('transitionend', function hide() {
      panel.setAttribute('hidden', '');
      panel.removeEventListener('transitionend', hide);
    });
    requestAnimationFrame(() => requestAnimationFrame(() => map.invalidateSize()));

    sheet.classList.remove('peek', 'expanded');
    sheet.addEventListener('transitionend', function hide() {
      sheet.setAttribute('hidden', '');
      sheet.removeEventListener('transitionend', hide);
    });
  }

  document.getElementById('results-panel-close').addEventListener('click', closePanel);
  document.getElementById('bottom-sheet-close').addEventListener('click', closePanel);

  /* --------------------------------------------------
     BOTTOM SHEET DRAG-TO-SNAP
  -------------------------------------------------- */
  (function () {
    const sheet  = document.getElementById('bottom-sheet');
    const handle = sheet.querySelector('.bottom-sheet__handle');

    let sheetH  = 0;
    let startY  = 0;
    let startTY = 0;
    let dragging = false;

    function getTranslateY() {
      const matrix = new DOMMatrix(window.getComputedStyle(sheet).transform);
      return matrix.m42;
    }

    function snapTo(state) {
      sheet.classList.remove('dragging', 'peek', 'expanded');
      sheet.classList.add(state);
    }

    function onDragStart(e) {
      sheetH   = sheet.offsetHeight;
      startY   = e.touches ? e.touches[0].clientY : e.clientY;
      startTY  = getTranslateY();
      dragging = true;
      sheet.classList.add('dragging');
    }

    function onDragMove(e) {
      if (!dragging) return;
      const y   = e.touches ? e.touches[0].clientY : e.clientY;
      const newTY = Math.max(0, Math.min(sheetH, startTY + (y - startY)));
      sheet.style.transform = `translateY(${newTY}px)`;
    }

    function onDragEnd(e) {
      if (!dragging) return;
      dragging = false;
      sheet.style.transform = '';
      const dy = (e.changedTouches ? e.changedTouches[0].clientY : e.clientY) - startY;

      if (dy < -40)      snapTo('expanded');
      else if (dy > 60)  snapTo('peek');
      else               snapTo(sheet.classList.contains('expanded') ? 'peek' : 'expanded');
    }

    handle.addEventListener('touchstart', onDragStart, { passive: true });
    window.addEventListener('touchmove',  onDragMove,  { passive: true });
    window.addEventListener('touchend',   onDragEnd);
    handle.addEventListener('mousedown',  onDragStart);
    window.addEventListener('mousemove',  onDragMove);
    window.addEventListener('mouseup',    onDragEnd);

    sheet.querySelector('.bottom-sheet__header').addEventListener('click', function () {
      if (!sheet.classList.contains('expanded')) snapTo('expanded');
    });
  })();

  /* --------------------------------------------------
     LOADING / EMPTY / ERROR STATES
  -------------------------------------------------- */
  function showLoadingState() {
    openPanel();
    const skeletons = Array(4).fill(0).map(function () {
      return `<div class="skeleton skeleton-card" style="margin:.5rem 1rem"></div>`;
    }).join('');
    document.getElementById('results-list').innerHTML = skeletons;
    document.getElementById('mobile-results-list').innerHTML = skeletons;
    updateCount('Searching…');
  }

  function showEmptyState() {
    const html = `
      <div class="results-panel__empty">
        <svg aria-hidden="true" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35M11 8v6M8 11h6"/>
        </svg>
        <p>No programs found for that search.<br>Try a different keyword or category.</p>
      </div>`;
    document.getElementById('results-list').innerHTML = html;
    document.getElementById('mobile-results-list').innerHTML = html;
    updateCount('0 results');
  }

  function showErrorState() {
    const html = `<div class="results-panel__empty"><p>Something went wrong. Please try again.</p></div>`;
    document.getElementById('results-list').innerHTML = html;
    document.getElementById('mobile-results-list').innerHTML = html;
    updateCount('Error');
  }

  function clearResults() {
    closePanel();
    markerLayer.clearLayers();
    currentResults = [];
    activeCardIndex = -1;
  }

  function updateCount(val) {
    const txt = typeof val === 'number' ? `${val} program${val !== 1 ? 's' : ''} found` : val;
    document.getElementById('results-count').textContent = txt;
    document.getElementById('results-panel-title').textContent = 'Results';
    document.getElementById('bottom-sheet-title').textContent = txt;
  }

  /* --------------------------------------------------
     MOBILE NAV HAMBURGER
  -------------------------------------------------- */
  const hamburger = document.getElementById('mobile-menu-toggle');
  const drawer    = document.getElementById('mobile-nav-drawer');

  hamburger.addEventListener('click', function () {
    const expanded = this.getAttribute('aria-expanded') === 'true';
    this.setAttribute('aria-expanded', String(!expanded));
    this.setAttribute('aria-label', expanded ? 'Open navigation menu' : 'Close navigation menu');
    if (expanded) drawer.setAttribute('hidden', '');
    else          drawer.removeAttribute('hidden');
  });

  /* --------------------------------------------------
     MOBILE SEARCH BAR COLLAPSE TOGGLE
  -------------------------------------------------- */
  const collapseBtn = document.getElementById('search-collapse-btn');
  const searchBar   = document.querySelector('.search-bar');

  if (collapseBtn) {
    collapseBtn.addEventListener('click', function () {
      const isCollapsed = searchBar.classList.toggle('collapsed');
      this.setAttribute('aria-expanded', String(!isCollapsed));
      this.setAttribute('aria-label', isCollapsed ? 'Expand search options' : 'Collapse search options');
      requestAnimationFrame(positionNearMeBtn);
    });
  }

  /* --------------------------------------------------
     UTILITY
  -------------------------------------------------- */
  function escHtml(str) {
    if (!str) return '';
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
  }

})();
