# Provider Example 2: Query notifications from the Drupal system

## The scenario
We have a ticket system module in drupal. Users can
open tickets and a staff employee will handle their requests and then
close the ticket.

The tickets are nodes with the custom `ticket` content type.

We assume that our notification center should display all open tickets
that are created by the current user.

This is how to implement it:

## getTypes

First we have to define the types of notifications. In this case it is
just one type:

```php
public function getTypes() {
  return [
    'open_ticket',
  ];
}
```

## getNotifications

In the `getNotifications` method we first have to query the open
tickets and then generate notifications from them:


```php
public function getNotifications(AccountInterface $user, $includeReadNotifications = FALSE) {
  $notifications = [];

  $nodeStorage = \Drupal::entityTypeManager()->getStorage('node');

  $results = $nodeStorage->getQuery()
    ->condition('type', 'ticket')
    ->condition('uid', $user->id())
    ->condition('field_status', 'open')
    ->execute();

  /** @var \Drupal\node\NodeInterface[] $openTickets */
  $openTickets = $nodeStorage->loadMultiple($results);

  foreach ($openTickets as $ticket) {
    $notifications[] = new \Drupal\notification_system\model\Notification(
      $this->id(),                                     // The provider ID
      $ticket->id(),                                   // A unique id for this notification
      'open_ticket',                                   // The type of the notification
      [$user->id()],                                   // This notification targets only one user (The one who requested it)
      $ticket->getCreatedTime(),                       // The timestamp
      $this->t('Open ticket: ') . $ticket->getTitle(), // We compose the title
      $ticket->body->summary,                          // Use the summary of the body as body for the notification
      $ticket->toUrl(),                                // Add a link to the node
      TRUE                                             // We make this notification sticky, because it will be shown until the ticket is closed
    );
  }

  return $notifications;
}
```


## load

This method is called with the notification id, which we have set to
the ticket node id before.

We load the ticket node and convert it to a notification:

```php
public function load($notificationId) {
  $nodeId = $notificationId;

  $nodeStorage = \Drupal::entityTypeManager()->getStorage('node');

  $ticket = $nodeStorage->load($nodeId);

  $notification = new \Drupal\notification_system\model\Notification(
    $this->id(),
    $ticket->id(),
    'open_ticket',
    [$user->id()],
    $ticket->getCreatedTime(),
    $this->t('Open ticket: ') . $ticket->getTitle(),
    $ticket->body->summary,
    $ticket->toUrl(),
    TRUE
  );

  return $notification;
}
```


## markAsRead

The ticket notifications cannot be marked as read.

So we just return an error message, if someone still tries to.

```php
public function markAsRead(AccountInterface $user, string $notificationId) {
  return 'This provider does not support marking notifications as read';
}
```
