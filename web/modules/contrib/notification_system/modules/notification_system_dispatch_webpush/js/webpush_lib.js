class NotificationSystemDispatchWebpush {
  constructor(applicationServerKey, serviceWorkerUrl, apiUrl, appleEnabled, appleServiceUrl, appleUserTokenUrl, appleWebPushId) {
    this.applicationServerKey = applicationServerKey;
    this.serviceWorkerUrl = serviceWorkerUrl;
    this.apiUrl = apiUrl;

    this.appleEnabled = appleEnabled;
    this.appleServiceUrl = appleServiceUrl;
    this.appleUserTokenUrl = appleUserTokenUrl;
    this.appleWebPushId = appleWebPushId;
    this.appleUserToken = false;

    this.state = null; // The current notification state. Could be one of "enabled", "disabled", "incompatible" or "computing".

    this._eventListeners = [];

    this._initializePromise = false;

    this.mode = 'default'; // The push mode. "default" means W3C WebPush. "safari" means Apple Web Push on Safari for MacOS.
    this.unsubscribeSupported = true; // If this variable is false, the browser does not support unsubscribing.

    if ('safari' in window && 'pushNotification' in window.safari) {
      this.mode = 'safari';
      this.unsubscribeSupported = false;
    }
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

        // Safari handling.
        if (this.mode === 'safari') {
          // Load the user_token and store it in the service, because we need it later.
          // Wait until this is finished, because else the promise could resolve before the token is loaded...
          fetch(this.appleUserTokenUrl)
            .then(response => response.json())
            .then(data => {
              this.appleUserToken = data.user_token;

              let pushResult = window.safari.pushNotification.permission(this.appleWebPushId);

              let state = this._updateStateFromSafariPushResult(pushResult);

              if (state === 'disabled' || state === 'enabled') {
                resolve();
              } else {
                reject(state);
              }
            });
          return;
        }


        // Regular Web Push handling.
        if (!('serviceWorker' in navigator)) {
          console.warn('Service workers are not supported by this browser');
          this.state = 'incompatible';
          this._fire('stateChange', 'incompatible');
          reject('service_worker_not_supported')
          return;
        }

        if (!('PushManager' in window)) {
          console.warn('Push notifications are not supported by this browser');
          this.state = 'incompatible';
          this._fire('stateChange', 'incompatible');
          reject('push_not_supported_by_browser');
          return;
        }

        if (!('showNotification' in ServiceWorkerRegistration.prototype)) {
          console.warn('Notifications are not supported by this browser');
          this.state = 'incompatible';
          this._fire('stateChange', 'incompatible');
          reject('push_not_supported_by_browser');
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

  subscribe() {
    this.state = 'computing';
    this._fire('stateChange', 'computing');

    // Handle Safari.
    if (this.mode === 'safari') {
      window.safari.pushNotification.requestPermission(
        this.appleServiceUrl,
        this.appleWebPushId,
        {
          user_token: this.appleUserToken,
        },
        (pushResult) => {
          this._updateStateFromSafariPushResult(pushResult);
        }
      );

      return;
    }


    // Handle regular Web Push.
    this.checkNotificationPermission()
      .then(() => {
        return navigator.serviceWorker.ready
      })
      .then(serviceWorkerRegistration => {
        return serviceWorkerRegistration.pushManager.subscribe({
          userVisibleOnly: true,
          applicationServerKey: this._urlBase64ToUint8Array(this.applicationServerKey),
        })
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
      throw new Error('Unsubscribe not supported in safari. Check the NotificationSystemDispatchWebPush.unsubscribeSupported variable')
    }

    // To unsubscribe from push messaging, you need to get the subscription object
    navigator.serviceWorker.ready
      .then(serviceWorkerRegistration => serviceWorkerRegistration.pushManager.getSubscription())
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
        return this._push_sendSubscriptionToServer(subscription, 'DELETE');
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
        return Notification.requestPermission().then(result => {
          if (result !== 'granted') {
            reject(new Error('Bad permission result'));
          } else {
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

        // Keep your server in sync with the latest endpoint
        return this._push_sendSubscriptionToServer(subscription, 'PATCH');
      })
      .then(subscription => {
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
    const key = subscription.getKey('p256dh');
    const token = subscription.getKey('auth');
    const contentEncoding = (PushManager.supportedContentEncodings || ['aesgcm'])[0];

    function encodeKey(buffer) {
      return btoa(String.fromCharCode(...new Uint8Array(buffer)));
    }

    function getPushSubscriptionPayload(pushSubscription) {
      return ['p256dh', 'auth'].reduce((accumulator, key) => {
        const value = pushSubscription.getKey(key);

        accumulator[key] = value ? encodeKey(value) : null;

        return accumulator;
      }, {
        user_agent: navigator.userAgent,
        utc_offset: new Date().getTimezoneOffset() / 60,
        encoding: (PushManager.supportedContentEncodings || ['aesgcm'])[0],
        endpoint: pushSubscription.endpoint,
      });
    }

    return fetch(this.apiUrl, {
      method,
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(getPushSubscriptionPayload(subscription)),
    }).then(() => subscription);
  }

  _urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
    const base64 = (base64String + padding).replace(/\-/g, '+').replace(/_/g, '/');

    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);

    for (let i = 0; i < rawData.length; ++i) {
      outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
  }

  _updateStateFromSafariPushResult(pushResult) {
    switch (pushResult.permission) {
      case 'default':
        this.state = 'disabled';
        this._fire('stateChange', 'disabled');
        return 'disabled';

      case 'denied':
        console.warn('Notifications are denied by the user');
        this._fire('stateChange', 'incompatible');
        this.state = 'incompatible'
        return 'push_denied_by_user';

      case 'granted':
        this.state = 'enabled';
        this._fire('stateChange', 'enabled');
        return 'enabled';
    }
  }
}

(function ($, Drupal, drupalSettings) {
  drupalSettings = drupalSettings || {
    notificationSystemDispatchWebpush: {
      applicationServerKey: null,
      serviceWorkerUrl: '/notification-system-dispatch-webpush-serviceworker.js',
      apiUrl: '/web-push-api/subscription',
    }
  };

  Drupal.notificationSystemDispatchWebpush = new NotificationSystemDispatchWebpush(
    drupalSettings.notificationSystemDispatchWebpush.applicationServerKey,
    drupalSettings.notificationSystemDispatchWebpush.serviceWorkerUrl,
    drupalSettings.notificationSystemDispatchWebpush.apiUrl,
    drupalSettings.notificationSystemDispatchWebpush.appleEnabled,
    drupalSettings.notificationSystemDispatchWebpush.appleServiceUrl,
    drupalSettings.notificationSystemDispatchWebpush.appleUserTokenUrl,
    drupalSettings.notificationSystemDispatchWebpush.appleWebPushId);
})(jQuery, Drupal, drupalSettings);
