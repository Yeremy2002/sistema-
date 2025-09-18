// Casa Vieja Hotel Service Worker
// Version 1.0.0

const CACHE_NAME = 'casa-vieja-v1.0.0';
const STATIC_CACHE = 'casa-vieja-static-v1.0.0';
const DYNAMIC_CACHE = 'casa-vieja-dynamic-v1.0.0';

// Files to cache immediately
const STATIC_FILES = [
  '/',
  '/index.html',
  '/styles.css',
  '/script.js',
  '/config.js',
  '/manifest.json',
  '/images/logo.svg',
  '/images/hero-mountain.svg',
  'https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap',
  'https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css'
];

// Network-first resources (API calls, dynamic content)
const NETWORK_FIRST = [
  '/api/',
  'https://wa.me/',
  'https://maps.google.com/',
  'https://www.google.com/maps/'
];

// Cache-first resources (images, fonts)
const CACHE_FIRST = [
  '/images/',
  'https://fonts.gstatic.com/',
  'https://cdn.jsdelivr.net/'
];

// Install event - cache static files
self.addEventListener('install', event => {
  console.log('[SW] Installing service worker');
  
  event.waitUntil(
    Promise.all([
      caches.open(STATIC_CACHE).then(cache => {
        console.log('[SW] Caching static files');
        return cache.addAll(STATIC_FILES);
      }),
      self.skipWaiting()
    ])
  );
});

// Activate event - clean old caches
self.addEventListener('activate', event => {
  console.log('[SW] Activating service worker');
  
  event.waitUntil(
    Promise.all([
      // Clean old caches
      caches.keys().then(cacheNames => {
        return Promise.all(
          cacheNames.map(cacheName => {
            if (cacheName !== STATIC_CACHE && cacheName !== DYNAMIC_CACHE) {
              console.log('[SW] Deleting old cache:', cacheName);
              return caches.delete(cacheName);
            }
          })
        );
      }),
      self.clients.claim()
    ])
  );
});

// Fetch event - implement caching strategies
self.addEventListener('fetch', event => {
  const { request } = event;
  const url = new URL(request.url);
  
  // Skip non-GET requests
  if (request.method !== 'GET') {
    return;
  }
  
  // Skip Chrome extensions
  if (url.protocol === 'chrome-extension:') {
    return;
  }
  
  // Network-first strategy for API calls
  if (NETWORK_FIRST.some(pattern => request.url.includes(pattern))) {
    event.respondWith(networkFirst(request));
    return;
  }
  
  // Cache-first strategy for static assets
  if (CACHE_FIRST.some(pattern => request.url.includes(pattern))) {
    event.respondWith(cacheFirst(request));
    return;
  }
  
  // Stale-while-revalidate for HTML and main resources
  event.respondWith(staleWhileRevalidate(request));
});

// Network-first strategy
async function networkFirst(request) {
  try {
    const networkResponse = await fetch(request);
    
    if (networkResponse.ok) {
      const cache = await caches.open(DYNAMIC_CACHE);
      cache.put(request, networkResponse.clone());
    }
    
    return networkResponse;
  } catch (error) {
    console.log('[SW] Network failed, trying cache:', request.url);
    const cachedResponse = await caches.match(request);
    
    if (cachedResponse) {
      return cachedResponse;
    }
    
    // Return offline fallback for API calls
    if (request.url.includes('/api/')) {
      return new Response(
        JSON.stringify({ 
          success: false, 
          message: 'Sin conexión. Por favor, intenta más tarde.',
          offline: true 
        }),
        {
          status: 503,
          statusText: 'Service Unavailable',
          headers: { 'Content-Type': 'application/json' }
        }
      );
    }
    
    throw error;
  }
}

// Cache-first strategy
async function cacheFirst(request) {
  const cachedResponse = await caches.match(request);
  
  if (cachedResponse) {
    return cachedResponse;
  }
  
  try {
    const networkResponse = await fetch(request);
    
    if (networkResponse.ok) {
      const cache = await caches.open(STATIC_CACHE);
      cache.put(request, networkResponse.clone());
    }
    
    return networkResponse;
  } catch (error) {
    console.log('[SW] Cache and network failed for:', request.url);
    throw error;
  }
}

// Stale-while-revalidate strategy
async function staleWhileRevalidate(request) {
  const cache = await caches.open(DYNAMIC_CACHE);
  const cachedResponse = await cache.match(request);
  
  // Fetch in background to update cache
  const fetchPromise = fetch(request).then(networkResponse => {
    if (networkResponse.ok) {
      cache.put(request, networkResponse.clone());
    }
    return networkResponse;
  }).catch(error => {
    console.log('[SW] Network failed for:', request.url);
    return null;
  });
  
  // Return cached version immediately if available
  if (cachedResponse) {
    return cachedResponse;
  }
  
  // Otherwise wait for network
  return fetchPromise || caches.match('/index.html');
}

// Background sync for form submissions
self.addEventListener('sync', event => {
  if (event.tag === 'reservation-form') {
    event.waitUntil(syncReservationForms());
  }
});

// Sync reservation forms when back online
async function syncReservationForms() {
  console.log('[SW] Syncing reservation forms');
  
  // This would handle offline form submissions
  // Implementation depends on offline storage strategy
  
  try {
    // Get pending forms from IndexedDB
    const pendingForms = await getPendingForms();
    
    for (const form of pendingForms) {
      try {
        const response = await fetch('/api/reservas', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify(form.data)
        });
        
        if (response.ok) {
          // Remove from pending queue
          await removePendingForm(form.id);
          console.log('[SW] Synced form:', form.id);
        }
      } catch (error) {
        console.log('[SW] Failed to sync form:', form.id, error);
      }
    }
  } catch (error) {
    console.log('[SW] Sync failed:', error);
  }
}

// Placeholder functions for IndexedDB operations
async function getPendingForms() {
  // Implement IndexedDB logic to retrieve pending forms
  return [];
}

async function removePendingForm(formId) {
  // Implement IndexedDB logic to remove synced form
  return true;
}

// Push notification handling (for future use)
self.addEventListener('push', event => {
  if (event.data) {
    const data = event.data.json();
    const options = {
      body: data.body || 'Nueva notificación de Casa Vieja Hotel',
      icon: '/images/logo.svg',
      badge: '/images/logo.svg',
      vibrate: [200, 100, 200],
      data: {
        url: data.url || '/',
        timestamp: Date.now()
      },
      actions: [
        {
          action: 'view',
          title: 'Ver',
          icon: '/images/logo.svg'
        },
        {
          action: 'dismiss',
          title: 'Cerrar'
        }
      ]
    };
    
    event.waitUntil(
      self.registration.showNotification(data.title || 'Casa Vieja Hotel', options)
    );
  }
});

// Handle notification clicks
self.addEventListener('notificationclick', event => {
  event.notification.close();
  
  if (event.action === 'view' || !event.action) {
    const url = event.notification.data?.url || '/';
    
    event.waitUntil(
      clients.matchAll({ type: 'window' }).then(clientList => {
        // Check if there's already a window/tab open with the target URL
        for (const client of clientList) {
          if (client.url === url && 'focus' in client) {
            return client.focus();
          }
        }
        
        // No existing window, open a new one
        if (clients.openWindow) {
          return clients.openWindow(url);
        }
      })
    );
  }
});

console.log('[SW] Service worker script loaded');