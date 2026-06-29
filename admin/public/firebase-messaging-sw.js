// public/firebase-messaging-sw.js

importScripts(
  "https://www.gstatic.com/firebasejs/10.12.1/firebase-app-compat.js",
);
importScripts(
  "https://www.gstatic.com/firebasejs/10.12.1/firebase-messaging-compat.js",
);

importScripts("/api/firebase-env");

if (!self.firebaseConfig?.apiKey) {
  console.error("[SW] Firebase config missing — push notifications disabled");
} else {
  firebase.initializeApp(self.firebaseConfig);
  const messaging = firebase.messaging();

  messaging.onBackgroundMessage((payload) => {
    return self.clients
      .matchAll({ type: "window", includeUncontrolled: true })
      .then((clientList) => {
        if (clientList.some((client) => client.focused)) return;

        const title = payload.notification?.title ?? "New Notification";
        const options = {
          body: payload.notification?.body ?? "",
          icon: "/icons/icon-192x192.png",
          badge: "/icons/icon-72x72.png",
          tag: payload.data?.tag ?? "default",
          data: {
            ...payload.data,
            url: payload.data?.url ?? "/",
          },
        };

        return self.registration.showNotification(title, options);
      });
  });

  self.addEventListener("notificationclick", (event) => {
    event.notification.close();

    const targetUrl = event.notification.data?.url ?? "/";

    event.waitUntil(
      self.clients
        .matchAll({ type: "window", includeUncontrolled: true })
        .then(async (clientList) => {
          for (const client of clientList) {
            if (
              new URL(client.url).origin === self.location.origin &&
              "focus" in client
            ) {
              await client.focus();
              if ("navigate" in client) client.navigate(targetUrl);
              return;
            }
          }
          if (self.clients.openWindow) {
            return self.clients.openWindow(targetUrl);
          }
        }),
    );
  });
}

self.addEventListener("message", (event) => {
  if (event.data?.type === "SKIP_WAITING") {
    self.skipWaiting();
    return;
  }
  if (event.ports?.[0]) {
    event.ports[0].postMessage({ received: true });
  }
});

self.addEventListener("install", (event) => {
  self.skipWaiting();
});

self.addEventListener("activate", (event) => {
  event.waitUntil(self.clients.claim());
});
