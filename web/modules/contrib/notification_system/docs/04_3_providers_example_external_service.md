# Provider Example 3: Notifications from an external service

## The scenario

We assume that we want to display notifications from our companys
print server. The notifications would be something like "The paper
tray is empty" or "The ink cartridge is empty".

## getTypes

First we have to define the types of notifications:

```php
public function getTypes() {
  return [
    'paper_empty',
    'ink_empty',
  ];
}
```

You could also fetch these types directly from the print server, but
please make sure they are cached, as these types are queried often
and each HTTP-Request makes the overally process slower.

## getNotifications

Next we have to fetch the notifications itself. We assume that the
fictional `PrinterServerService` can load all printers a user has
access to and returns the notifications of this printer:

```php
public function getNotifications(AccountInterface $user, $includeReadNotifications = FALSE) {
  $notifications = [];

  $printers = $this->printerServerService->getPrintersOfUser($user);

  foreach ($printers as $printer) {
    $printerNotifications = $printer->getNotifications();

    foreach ($printerNotifications as $printerNotification) {
      $notifications[] = new \Drupal\notification_system\model\Notification(
        $this->id(),                                   // The provider ID
        $printer->id . '_' . $printerNotification->id, // A unique id for this notification
        $printerNotification->type,                    // The type of the notification
        [$user->id()],                                 // This notification targets only one user (The one who requested it)
        $printerNotification->timestamp,               // The timestamp
        $printerNotification->title,                   // The title
      );
    }
  }

  return $notifications;
}
```

## load

Next we have to implement the load function. This function will be
called with the notification id we specified when creating the
notification.

Here we will load the notification data from the printer server and
convert it to a notification again.

```php
public function load($notificationId) {
  // We split up the notification id to extract its values.
  $parts = explode('_', $notificationId);
  $printerId = $parts[0];
  $printerNotificationId = $parts[1];

  // We load the data from the printer service
  $printer = $this->printerServerService->loadPrinter($printerId);
  $printerNotification = $print->loadNotification($printerNotificationId)

  // We build the notification model
  $notification = new \Drupal\notification_system\model\Notification(
    $this->id(),
    $printer->id . '_' . $printerNotification->id,
    $printerNotification->type,
    [$user->id()],
    $printerNotification->timestamp,
    $printerNotification->title,
  );

  // And return it
  return $notification;
}
```

## markAsRead

The printer server has an endpoint to mark notifications as resolved.

We call this endpoint in this method:

```php
public function markAsRead(AccountInterface $user, string $notificationId) {
  // We split up the notification id to extract its values.
  $parts = explode('_', $notificationId);
  $printerId = $parts[0];
  $printerNotificationId = $parts[1];

  $this->printerServerService->loadPrinter($printerId);
  $printer->resolveIssue($printerNotificationId);

  return TRUE;
}
```
