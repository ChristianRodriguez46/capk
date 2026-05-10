# Leaflet.js 1.9.4 —  Reference Guide

A reusable reference for any project using Leaflet.js with OpenStreetMap tiles.
Leaflet is a lightweight, mobile-friendly JavaScript mapping library.
Official docs: https://leafletjs.com/reference.html

---

## 1. CDN INCLUDES

Paste in your HTML `<head>`. CSS MUST come before the JS.

```html
<link rel="stylesheet"
  href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
  integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
  crossorigin=""/>

<script
  src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
  integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
  crossorigin=""></script>
```

If you use npm instead: `npm install leaflet`
Then import: `import L from 'leaflet';`

---

## 2. MAP CONTAINER

```html
<div id="map"></div>
```

```css
/* The container MUST have an explicit height or the map renders as a blank 0px box */
#map {
  height: 400px;   /* any fixed value, or 100vh, or 100% if parent has a height */
  width: 100%;
}
```

---

## 3. INITIALIZING THE MAP

### Option A — Center on a specific coordinate + zoom level
```javascript
// L.map(elementId).setView([latitude, longitude], zoomLevel)
// Zoom: 0 = whole world, 10 = city level, 13 = neighborhood, 18 = street level
const map = L.map('map').setView([51.505, -0.09], 13);
```

### Option B — Fit to a bounding box (shows a specific region)
```javascript
// L.latLngBounds([SW corner], [NE corner])
const bounds = L.latLngBounds([34.70, -119.90], [36.00, -117.60]);
map.fitBounds(bounds);

// Add padding so markers near the edge are not clipped
map.fitBounds(bounds, { padding: [20, 20] });
```

### Map options (pass as second argument to L.map)
```javascript
const map = L.map('map', {
  center: [51.505, -0.09],
  zoom: 13,
  zoomControl: true,        // show +/- buttons (default: true)
  scrollWheelZoom: true,    // zoom with mouse wheel (default: true)
  dragging: true,           // allow panning (default: true)
  minZoom: 3,
  maxZoom: 19
});
```

---

## 4. TILE LAYER

A tile layer provides the actual map imagery. You must add at least one.

### OpenStreetMap (free, no API key needed)
```javascript
L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
  maxZoom: 19,
  attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
}).addTo(map);
```

### Other common providers (all require their own API keys)
```javascript
// Mapbox
L.tileLayer('https://api.mapbox.com/styles/v1/{id}/tiles/{z}/{x}/{y}?access_token={accessToken}', {
  attribution: '© Mapbox © OpenStreetMap',
  id: 'mapbox/streets-v11',
  tileSize: 512,
  zoomOffset: -1,
  accessToken: 'YOUR_TOKEN'
}).addTo(map);
```

IMPORTANT: Attribution is legally required for OpenStreetMap and most other tile providers.
Leaflet displays attribution automatically when you pass it in the options.

---

## 5. ZOOM CONTROL

```javascript
// Default position is 'topleft'
// Other options: 'topright', 'bottomleft', 'bottomright'

// Move the default zoom control
const map = L.map('map', { zoomControl: false });  // disable default
L.control.zoom({ position: 'bottomleft' }).addTo(map);  // add at new position

// Or reposition after init
const map = L.map('map');
map.zoomControl.setPosition('bottomright');
```

---

## 6. MARKERS

### Default marker
```javascript
const marker = L.marker([51.5, -0.09]).addTo(map);

// With options
const marker = L.marker([51.5, -0.09], {
  draggable: true,
  title: 'Tooltip text on hover',
  opacity: 0.8
}).addTo(map);
```

### Custom icon (image file)
```javascript
const customIcon = L.icon({
  iconUrl: 'path/to/marker.png',
  iconSize: [32, 32],        // width, height in px
  iconAnchor: [16, 32],      // point of the icon that sits on the coordinate (bottom-center)
  popupAnchor: [0, -32]      // where the popup opens relative to the iconAnchor
});

const marker = L.marker([51.5, -0.09], { icon: customIcon }).addTo(map);
```

### Custom div icon (pure CSS, no image file needed)
```javascript
const divIcon = L.divIcon({
  className: '',             // set to '' to prevent Leaflet's default white box styles
  html: '<div class="my-marker"></div>',
  iconSize: [28, 28],
  iconAnchor: [14, 28],
  popupAnchor: [0, -30]
});

const marker = L.marker([51.5, -0.09], { icon: divIcon }).addTo(map);
```

```css
/* Example: a teardrop-shaped CSS marker */
.my-marker {
  width: 28px;
  height: 28px;
  background: #1d6f7a;
  border-radius: 50% 50% 50% 0;
  transform: rotate(-45deg);
  border: 2px solid #fff;
  box-shadow: 0 2px 6px rgba(0,0,0,0.3);
}
```

### Updating a marker's icon dynamically
```javascript
marker.setIcon(newIcon);
```

### Removing a marker
```javascript
marker.remove();           // remove from map
map.removeLayer(marker);   // same thing, alternative syntax
```

---

## 7. POPUPS

### Bind a popup to a marker (opens when user clicks the marker)
```javascript
marker.bindPopup('<b>Hello!</b><br>I am a popup.');

// Open the popup immediately on page load
marker.bindPopup('Hello!').openPopup();

// Update popup content later
marker.getPopup().setContent('Updated content');
```

### Standalone popup (not attached to a marker)
```javascript
const popup = L.popup()
  .setLatLng([51.513, -0.09])
  .setContent('<p>I am a standalone popup.</p>')
  .openOn(map);   // use openOn() — it auto-closes any previously open popup
                  // use addTo(map) if you want multiple popups open simultaneously
```

### Popup options
```javascript
marker.bindPopup('Hello!', {
  maxWidth: 300,
  minWidth: 100,
  maxHeight: 200,    // adds a scrollbar if content is taller
  closeButton: true,
  autoClose: true,   // close when another popup opens (default: true)
  className: 'my-custom-popup'
});
```

### Popup events
```javascript
marker.on('popupopen', function() { console.log('popup opened'); });
marker.on('popupclose', function() { console.log('popup closed'); });
```

---

## 8. TOOLTIPS

Tooltips appear on hover (unlike popups which open on click).

```javascript
marker.bindTooltip('I appear on hover');

// Permanent tooltip (always visible, like a label)
marker.bindTooltip('Always visible', { permanent: true, direction: 'top' });

// Direction options: 'top', 'bottom', 'left', 'right', 'center', 'auto'
```

---

## 9. EVENTS

### Map events
```javascript
map.on('click', function(e) {
  console.log('Clicked at:', e.latlng);     // e.latlng is a LatLng object
  console.log('Lat:', e.latlng.lat);
  console.log('Lng:', e.latlng.lng);
});

map.on('zoomend', function() {
  console.log('Current zoom:', map.getZoom());
});

map.on('moveend', function() {
  console.log('Center:', map.getCenter());
  console.log('Bounds:', map.getBounds());
});
```

### Marker events
```javascript
marker.on('click', function(e) { console.log('Marker clicked'); });
marker.on('mouseover', function() { /* hover start */ });
marker.on('mouseout', function() {  /* hover end   */ });
marker.on('dragend', function(e) {
  console.log('Dragged to:', e.target.getLatLng());
});
```

### Remove an event listener
```javascript
function myHandler(e) { console.log(e.latlng); }
map.on('click', myHandler);
map.off('click', myHandler);   // remove specific handler
map.off('click');              // remove ALL click handlers
```

---

## 10. LAYER GROUPS

Use layer groups to manage sets of markers together (add/remove all at once).

```javascript
// Create a group and add it to the map
const markerGroup = L.layerGroup().addTo(map);

// Add markers to the group
L.marker([51.5, -0.09]).addTo(markerGroup);
L.marker([51.51, -0.1]).addTo(markerGroup);

// Remove all markers in the group (without removing the group itself)
markerGroup.clearLayers();

// Remove the group from the map entirely
markerGroup.remove();

// Fit map to show all markers in a group
const featureGroup = L.featureGroup(markerGroup.getLayers());
map.fitBounds(featureGroup.getBounds(), { padding: [40, 40] });
```

### Feature group (like layer group, but supports getBounds and collective events)
```javascript
const fg = L.featureGroup([marker1, marker2, marker3]).addTo(map);
map.fitBounds(fg.getBounds());
fg.on('click', function(e) { console.log('A layer in the group was clicked'); });
```

---

## 11. SHAPES

### Circle
```javascript
L.circle([51.508, -0.11], {
  color: 'red',          // stroke color
  fillColor: '#f03',     // fill color
  fillOpacity: 0.5,
  radius: 500            // radius in METERS (not pixels)
}).addTo(map);
```

### Rectangle
```javascript
L.rectangle([[51.49, -0.08], [51.5, -0.06]], {
  color: 'blue',
  weight: 2
}).addTo(map);
```

### Polygon
```javascript
L.polygon([
  [51.509, -0.08],
  [51.503, -0.06],
  [51.51, -0.047]
], { color: 'green' }).addTo(map);
```

### Polyline (a line, not a closed shape)
```javascript
L.polyline([
  [51.505, -0.09],
  [51.51, -0.1],
  [51.51, -0.12]
], { color: 'red', weight: 4 }).addTo(map);
```

---

## 12. GEOLOCATION (BROWSER "NEAR ME")

IMPORTANT: Always trigger geolocation from a user action (button click).
Never call it automatically on page load — browsers may block it and users find it intrusive.

### Using the native browser API
```javascript
document.getElementById('locate-btn').addEventListener('click', function() {
  if (!navigator.geolocation) {
    alert('Geolocation is not supported by your browser.');
    return;
  }

  navigator.geolocation.getCurrentPosition(
    function(position) {
      const lat = position.coords.latitude;
      const lng = position.coords.longitude;
      const accuracy = position.coords.accuracy;  // in meters

      map.setView([lat, lng], 13);

      L.marker([lat, lng])
        .addTo(map)
        .bindPopup('You are within ' + accuracy + ' meters of this point.')
        .openPopup();
    },
    function(error) {
      // error.code values:
      // 1 = PERMISSION_DENIED
      // 2 = POSITION_UNAVAILABLE
      // 3 = TIMEOUT
      console.warn('Geolocation error:', error.message);
    }
  );
});
```

### Using Leaflet's built-in locate method
```javascript
map.locate({ setView: true, maxZoom: 13 });

map.on('locationfound', function(e) {
  L.marker(e.latlng).addTo(map);
  L.circle(e.latlng, { radius: e.accuracy }).addTo(map);
});

map.on('locationerror', function(e) {
  console.warn('Location error:', e.message);
});
```

---

## 13. FIXING MAP SIZE AFTER LAYOUT CHANGES

If the map container's size changes after the map was initialized (a panel opens,
a tab switches, a CSS transition completes), tiles may not fill the container correctly.
Fix it by calling invalidateSize() after the layout settles.

```javascript
map.invalidateSize();

// If called right after a CSS transition, wrap in double requestAnimationFrame
// so the browser finishes painting before Leaflet recalculates
requestAnimationFrame(() => {
  requestAnimationFrame(() => {
    map.invalidateSize();
  });
});

// Or use a short setTimeout if the transition duration is known
setTimeout(() => map.invalidateSize(), 300);
```

---

## 14. USEFUL MAP METHODS

```javascript
// Read current state
map.getZoom()          // current zoom level (number)
map.getCenter()        // current center (LatLng object)
map.getBounds()        // current visible bounds (LatLngBounds object)

// Change the view
map.setView([lat, lng], zoom)
map.setZoom(12)
map.panTo([lat, lng])
map.flyTo([lat, lng], zoom)         // smooth animated pan + zoom
map.flyToBounds(bounds)             // smooth animated fit to bounds
map.fitBounds(bounds, { padding: [20, 20] })

// Convert between screen pixels and map coordinates
map.latLngToLayerPoint(latlng)      // LatLng -> pixel point
map.layerPointToLatLng(point)       // pixel point -> LatLng
map.containerPointToLatLng(point)   // container pixel -> LatLng

// Check if a point is in the current view
map.getBounds().contains([lat, lng])   // returns true or false
```

---

## 15. ACCESSIBILITY NOTES

```html
<!-- Give the map container a descriptive label and ARIA role -->
<div id="map"
     role="application"
     aria-label="Interactive map showing locations">
</div>
```

- Always provide the same information in a visible text list as is shown on the map.
  Screen reader users cannot navigate the canvas.
- Zoom and pan controls get accessible labels from Leaflet automatically.
- Any custom buttons (locate, reset view, etc.) need their own aria-label attributes.
- Do not rely solely on the map for navigation — treat it as a visual supplement.

---

## 16. COMMON MISTAKES

| Mistake                                         | Fix                                                                                        |
|-------------------------------------------------|--------------------------------------------------------------------------------------------|
| Map renders blank / 0px tall                    | Add explicit height to the container in CSS                                                |
| Tiles do not load                               | Confirm Leaflet CSS is included BEFORE the JS script tag                                   |
| Map is grey / tiles missing after layout change | Call map.invalidateSize() after the container resizes                                      |
| Popup opens off-screen                          | Set popupAnchor correctly on the icon, or pan the map before opening                       |
| Multiple popups open at once unintentionally    | Use .openOn(map) instead of .addTo(map)                                                    |
| Geolocation blocked by browser                  | Always trigger from a user gesture, never on page load                                     |
| Markers accumulate on repeated searches         | Call layerGroup.clearLayers() before adding new markers                                    |
| fitBounds throws an error                       | Confirm the bounds object or feature group actually contains layers before calling         |
| Map works on desktop but breaks on mobile       | Add <meta name="viewport" content="width=device-width, initial-scale=1"> to your HTML head |

---

## 17. QUICK FULL EXAMPLE (complete working page)

```html
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Leaflet Map</title>

  <link rel="stylesheet"
    href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
    crossorigin=""/>

  <style>
    html, body { margin: 0; padding: 0; height: 100%; }
    #map { height: 100vh; width: 100%; }
  </style>
</head>
<body>

<div id="map" role="application" aria-label="Interactive map"></div>

<script
  src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
  integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
  crossorigin=""></script>

<script>
  // 1. Initialize the map
  const map = L.map('map').setView([51.505, -0.09], 13);

  // 2. Add the tile layer
  L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
  }).addTo(map);

  // 3. Add a marker with a popup
  const marker = L.marker([51.5, -0.09]).addTo(map);
  marker.bindPopup('<b>Hello World!</b><br>I am a popup.').openPopup();

  // 4. Open a popup wherever the user clicks
  map.on('click', function(e) {
    L.popup()
      .setLatLng(e.latlng)
      .setContent('You clicked at ' + e.latlng.toString())
      .openOn(map);
  });
</script>

</body>
</html>
```