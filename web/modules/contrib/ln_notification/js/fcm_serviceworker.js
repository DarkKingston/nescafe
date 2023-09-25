importScripts("https://www.gstatic.com/firebasejs//*drupalSettings.firebaseVersion*//firebase-app-compat.js");
importScripts("https://www.gstatic.com/firebasejs//*drupalSettings.firebaseVersion*//firebase-messaging-compat.js");

var settings = {
  firebaseConfig: {}
};

// Don't remove this next line, @see FcmController::serviceWorker()
/*drupalSettings.serviceworker*/

firebase.initializeApp(settings.firebaseConfig);

const messaging = firebase.messaging();

messaging.onBackgroundMessage(function(payload) {
  console.log("[firebase-messaging-sw.js] Received background message ",payload);
  return self.registration.showNotification(
      payload.notification.title,
     {
        body: payload.notification.body,
        icon: payload.notification.image,
        data: payload.data
     }
  );
});

self.addEventListener('notificationclick', (event) => {
  event.notification.close()

  if (!event.notification.data.open_url) return

  const open_url = event.notification.data.open_url
  const url = new URL(open_url, self.location.origin).href

  event.waitUntil(
    clients.matchAll({type: 'window'}).then( windowClients => {
      // Check if there is already a window/tab open with the target URL
      for (var i = 0; i < windowClients.length; i++) {
        var client = windowClients[i];
        // If so, just focus it.
        if (client.url === url && 'focus' in client) {
          return client.focus();
        }
      }
      // If not, then open the target URL in a new window/tab.
      if (clients.openWindow) {
        return clients.openWindow(url);
      }
    })
  );
});

