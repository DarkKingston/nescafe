self.addEventListener('push', function (event) {
  if (!(self.Notification && self.Notification.permission === 'granted')) {
    return;
  }

  const showNotification = data => {
    const title = data.title;
    return self.registration.showNotification(title, data);
  };

  if (event.data) {
    const data = event.data.json();
    event.waitUntil(showNotification(data));
  }
});


self.addEventListener('notificationclick', function (event) {
  console.log('notificationclick triggered');
  if (event.notification.data['open_url']) {
    let url = event.notification.data['open_url'];
    console.log('clicked notification contains "open_url" in data. So opening this url now: ' + url);

    event.notification.close(); // Android needs explicit close.
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
  }
});
