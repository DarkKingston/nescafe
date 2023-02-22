CONTENTS OF THIS FILE
---------------------

 * Introduction
 * Requirements
 * Installation
 * Configuration

INTRODUCTION
------------

This module enables push notifications for anonymous and authenticated users. On the one hand, it allows users to subscribe and receive push notifications by means of the Firebase Cloud Messaging service. On the other, it allows editors to create and schedule notifications to be sent to subscribed users.


REQUIREMENTS
------------

This module requires the following modules:

* Firebase (https://www.drupal.org/project/firebase)
* Smart Date (https://www.drupal.org/project/smart_date)
* Notification System (https://www.drupal.org/project/notification_system)


INSTALLATION
------------

* Install as you would normally install a contributed Drupal module. Visit
   https://www.drupal.org/node/1897420 for further information.


CONFIGURATION
-------------
1. Create a new Firebase project in https://console.firebase.google.com/ and setup credentials
2. Configure Firebase Push Notification: `/admin/config/system/firebase`
3. Configure FCM Notification dispatcher by pasting credentials from the Google Console in the following URL: `/admin/config/system/notification-system-dispatch`
4. If your website uses the [Require Login](https://www.drupal.org/project/require_login) module, you need to edit the Require Login settings (`/admin/config/people/require-login`) and add the following paths in the **Excluded paths** section:
```
/firebase-messaging-sw.js
/manifest.json
/ln-notification/subscription
```
5. If your website uses the [Security Kit](https://www.drupal.org/project/seckit) module, you need to edit the Security Kit settings (`/admin/config/system/seckit`) and add `https://*.googleapis.com` in the **connect-src** setting
6. Create a new Firebase Notification in the following URL: `/admin/content/fcm-notification`
7. Place block **FCM Popup** in your desired region of the Block Layout: `/admin/structure/block`
8. It's possible to review stored Firebase tokens in `/admin/config/lightnest/ln-notification`