class lnNotification {
  constructor(serviceWorkerUrl, apiUrl, firebaseConfig, vapidKey, firebaseVersion, firebaseEndpoint) {
    this.serviceWorkerUrl = serviceWorkerUrl;
    this.apiUrl = apiUrl;
    this.firebaseConfig = firebaseConfig;
    this.vapidKey = vapidKey;
    this.firebaseVersion = firebaseVersion;
    this.firebaseEndpoint = firebaseEndpoint;

    this.state = null; // The current notification state. Could be one of "enabled", "disabled", "incompatible" or "computing".

    this._eventListeners = [];

    this._initializePromise = false;

    this.mode = 'default'; // The push mode. "default" means W3C WebPush. "safari" means Apple Web Push on Safari for MacOS.
    this.unsubscribeSupported = true; // If this variable is false, the browser does not support unsubscribing.
  }

  on(eventName, callback) {
    if (!this._eventListeners[eventName]) {
      this._eventListeners[eventName] = [];
    }

    this._eventListeners[eventName].push(callback);
  }

  _fire(eventName, payload) {
    if (this._eventListeners[eventName]) {
      this._eventListeners[eventName].forEach(callback => {
        callback(payload);
      });
    }
  }

  initialize() {
    if (!this._initializePromise) {
      this._initializePromise = new Promise((resolve, reject) => {

        if (typeof this._firebaseApp === "undefined") {
          this._firebaseApp = firebase.initializeApp(this.firebaseConfig);
          this._messaging = firebase.messaging();
          this._messaging.onMessage(function (payload) {
            const title = payload.notification.title;
            const options = {
              body: payload.notification.body,
              icon: payload.notification.image,
              data: payload.data
            };
            var notification = new Notification(title, options);
            notification.onclick = (event) => {
              event.preventDefault(); // prevent the browser from focusing the Notification's tab
              notification.close();
              window.open(payload.data.open_url, '_blank');
            };
          });
        }


        // Regular Web Push handling.
        if (!firebase.messaging.isSupported()) {
          console.warn('Service workers are not supported by this browser');
          this.state = 'incompatible';
          this._fire('stateChange', 'incompatible');
          reject('service_worker_not_supported')
          return;
        }

        // Check the current Notification permission.
        // If its denied, the button should appears as such, until the user changes the permission manually
        if (Notification.permission === 'denied') {
          console.warn('Notifications are denied by the user');
          this.state = 'incompatible';
          this._fire('stateChange', 'incompatible');
          reject('push_denied_by_user');
          return;
        }

        // try to register serviceworker
        navigator.serviceWorker.register(this.serviceWorkerUrl, {
          scope: '/',
        }).then(
          () => {
            console.log('[SW] Service worker has been registered');
            this._push_updateSubscription();
            resolve();
          },
          e => {
            console.error('[SW] Service worker registration failed', e);
            this.state = 'incompatible';
            this._fire('stateChange', 'incompatible');
            reject('service_worker_registration_failed');
          }
        );
      });
    }

    return this._initializePromise;
  }

  getRegistrationToken(serviceWorkerRegistration) {
    // Get registration token. Initially this makes a network call, once retrieved
    // subsequent calls to getToken will return from cache.
    if (serviceWorkerRegistration) {
      return this._messaging.getToken({
        serviceWorkerRegistration: serviceWorkerRegistration,
        vapidKey: this.vapidKey
      });
    }

    return null;
  }

  subscribe() {
    this.state = 'computing';
    this._fire('stateChange', 'computing');

    // Handle regular Web Push.
    this.checkNotificationPermission()
      .then(() => {
        return navigator.serviceWorker.ready
      })
      .then(serviceWorkerRegistration => {
        return this.getRegistrationToken(serviceWorkerRegistration)
      })
      .then(subscription => {
        // Subscription was successful
        // create subscription on your server
        return this._push_sendSubscriptionToServer(subscription, 'POST');
      })
      .then(subscription => {
        this.state = 'enabled';
        this._fire('stateChange', 'enabled');
      }) // update your UI
      .catch(e => {
        if (Notification.permission === 'denied') {
          // The user denied the notification permission which
          // means we failed to subscribe and the user will need
          // to manually change the notification permission to
          // subscribe to push messages
          console.warn('Notifications are denied by the user.');
          this.state = 'incompatible';
          this._fire('stateChange', 'incompatible');
        } else {
          // A problem occurred with the subscription; common reasons
          // include network errors or the user skipped the permission
          console.error('Impossible to subscribe to push notifications', e);
          this.state = 'disabled';
          this._fire('stateChange', 'disabled');
        }
      });
  }

  unsubscribe() {
    this.state = 'computing';
    this._fire('stateChange', 'computing');

    if (this.mode === 'safari') {
      throw new Error('Unsubscribe not supported in safari. Check the lnNotification.unsubscribeSupported variable')
    }

    // To unsubscribe from push messaging, you need to get the subscription object
    navigator.serviceWorker.ready
      .then(serviceWorkerRegistration => this.getRegistrationToken(serviceWorkerRegistration))
      .then(subscription => {
        // Check that we have a subscription to unsubscribe
        if (!subscription) {
          // No subscription object, so set the state
          // to allow the user to subscribe to push
          this.state = 'disabled';
          this._fire('stateChange', 'disabled');
          return;
        }
        
        // We have a subscription, unsubscribe
        // Remove push subscription from server
        return this._push_sendSubscriptionToServer(token, 'DELETE');
      })
      .then(subscription => subscription.unsubscribe())
      .then(() => {
        this.state = 'disabled';
        this._fire('stateChange', 'disabled');
      })
      .catch(e => {
        // We failed to unsubscribe, this can lead to
        // an unusual state, so it may be best to remove
        // the users data from your data store and
        // inform the user that you have done so
        console.error('Error when unsubscribing the user', e);
        this.state = 'disabled';
        this._fire('stateChange', 'disabled');
      });
  }

  checkNotificationPermission() {
    return new Promise((resolve, reject) => {
      if (Notification.permission === 'denied') {
        return reject(new Error('Push messages are blocked.'));
      }

      if (Notification.permission === 'granted') {
        return resolve();
      }

      if (Notification.permission === 'default') {
        return Notification.requestPermission().then(permission => {
          if (permission !== 'granted') {
            reject(new Error('Bad permission result'));
          } else {
            navigator.serviceWorker.ready.then(p => {
              p.pushManager.getSubscription().then(subscription => {
                if (subscription === null) {
                  //If there is no notification subscription, register.
                  let re = p.pushManager.subscribe({
                    applicationServerKey: this.vapidKey,
                    userVisibleOnly: true
                  })
                }
              })
            });
            resolve();
          }
        });
      }

      return reject(new Error('Unknown permission'));
    });
  }

  _push_updateSubscription() {
    navigator.serviceWorker.ready
      .then(serviceWorkerRegistration => serviceWorkerRegistration.pushManager.getSubscription())
      .then(subscription => {
        
        if (!subscription) {
          // We aren't subscribed to push, so set UI to allow the user to enable push
          this.state = 'disabled';
          this._fire('stateChange', 'disabled');
          return false;
        }
        return navigator.serviceWorker.ready;
      })
      .then(serviceWorkerRegistration => this.getRegistrationToken(serviceWorkerRegistration))
      .then(subscription => {
        // Keep your server in sync with the latest endpoint
        this._push_sendSubscriptionToServer(subscription, 'PATCH');
        if (subscription) {
          // Set your UI to show they have subscribed for push messages
          this.state = 'enabled';
          this._fire('stateChange', 'enabled');
        }
      })
      .catch(e => {
        console.error('Error when updating the subscription', e);
      });
  }

  _push_sendSubscriptionToServer(subscription, method) {

    return fetch(this.apiUrl, {
      method,
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        token: subscription,
        user_agent: navigator.userAgent,
        utc_offset: new Date().getTimezoneOffset() / 60
      }),
    }).then(() => subscription);
  }
}

(function ($, Drupal, drupalSettings) {
  Drupal.lnNotification = new lnNotification(
    drupalSettings.lnNotification.serviceWorkerUrl,
    drupalSettings.lnNotification.apiUrl,
    drupalSettings.lnNotification.firebaseConfig,
    drupalSettings.lnNotification.vapidKey,
    drupalSettings.lnNotification.firebaseVersion,
    drupalSettings.lnNotification.firebaseEndpoint);
})(jQuery, Drupal, drupalSettings);
