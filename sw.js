// Service worker de Chrysalide.
// À CHAQUE MISE À JOUR d'un fichier du site, augmenter VERSION (v1 → v2 → v3…).
// Les fiches ne sont pas concernées : elles restent dans le navigateur, pas dans ce cache.
const VERSION = "v4";
const CACHE = "chrysalide-" + VERSION;
const FILES = [
  "./",
  "./index.html",
  "./manifest.webmanifest",
  "./icon-192.png",
  "./icon-512.png",
  "./icon-maskable-512.png",
  "./apple-touch-icon.png",
  "./favicon-32.png"
];

self.addEventListener("install", (e) => {
  e.waitUntil(caches.open(CACHE).then((c) => c.addAll(FILES)).then(() => self.skipWaiting()));
});

self.addEventListener("activate", (e) => {
  e.waitUntil(
    caches.keys()
      .then((keys) => Promise.all(keys.filter((k) => k.startsWith("chrysalide-") && k !== CACHE).map((k) => caches.delete(k))))
      .then(() => self.clients.claim())
  );
});

self.addEventListener("fetch", (e) => {
  const req = e.request;
  if (req.method !== "GET" || new URL(req.url).origin !== location.origin) return;


  const url = new URL(req.url);
  const scope = new URL(self.registration.scope);
  const isApp = url.pathname === scope.pathname || url.pathname === scope.pathname + "index.html";

  // L'API et l'admin ne passent jamais par le cache.
  if (url.pathname.endsWith(".php")) return;

  // La page elle-même : réseau d'abord (pour recevoir les mises à jour), cache si hors ligne.
  if (req.mode === "navigate" && isApp) {
    e.respondWith(
      fetch(req)
        .then((res) => {
          if (res.ok) {
            const copy = res.clone();
            caches.open(CACHE).then((c) => c.put("./index.html", copy));
          }
          return res;
        })
        .catch(() => caches.match("./index.html"))
    );
    return;
  }

  // Icônes et manifeste : cache d'abord, réseau sinon.
  e.respondWith(caches.match(req).then((hit) => hit || fetch(req)));
});
