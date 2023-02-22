# The `NewNotificationEvent`

Sometimes it is possible, that you want to send out a notification, but
don't want to save it.

In example, this could be the case when you write a webhook
that is called from an external application (like a ticket system), when
a tickets status is updated.

Then you have to throw the `NewNotificationEvent` programmatically, to
trigger the [Active Dispatch Flow](03_workflows.md#active-dispatch-flow):

```php
use Drupal\notification_system\Event\NewNotificationEvent;

$notification = new \Drupal\notification_system\model\Notification(
  'example',
  '123abc',
  'ticket_status_changed',
  [1],
  time(),
  'The ticket status was changed',
);

$event = new NewNotificationEvent($notification);

/** @var \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher */
$eventDispatcher = \Drupal::service('event_dispatcher');

$eventDispatcher->dispatch(NewNotificationEvent::EVENT_NAME, $event);
```

Note that you have to create a [Notification Model](02_notification_model.md),
not a Notification Entity.

After dispatching the event, the flow is started and the notification
will be sent out.
