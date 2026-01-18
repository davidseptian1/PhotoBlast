// Minimal service worker: no caching, always network.
// This exists to prevent stale cached pages during development/kiosk usage.

const VERSION = '2026-01-02-1';

self.addEventListener('install', (event) => {
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  event.waitUntil(self.clients.claim());
});

self.addEventListener('fetch', (event) => {
  event.respondWith(fetch(event.request));
});
