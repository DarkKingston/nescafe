# Providers

A provider is the part in the notification_system that makes the
notifications available.

A provider is a symfony plugin, that you can create in your module.

Providers are responsible for loading unread notifications of a user,
define the types of notifications possible and marking notifications
as read.

Providers can fetch the notifications from the drupal database, an
external service or do a live query in drupal.

## Creating a provider

In your module create the file `ExampleNotificationProvider.php` in
the folder `src/Plugin/NotificationProvider`.

The file should look like this:

```php
namespace Drupal\my_module\Plugin\NotificationProvider;

use Drupal\Core\Session\AccountInterface;
use Drupal\notification_system\NotificationProviderPluginBase;

/**
 * @NotificationProvider(
 *   id = "example",
 *   label = @Translation("Example"),
 *   description = @Translation("Generates demo notifications.")
 * )
 */
class ExampleNotificationProvider extends NotificationProviderPluginBase {
  // This function tells the notification system what types of
  // notifications your provider introduces.
  public function getTypes() {}

  // This function loads all notifications for a given user.
  public function getNotifications(AccountInterface $user, $includeReadNotifications = FALSE){}

  // This function loads a specific notification by its id.
  public function load($notificationId) {}

  // This function marks a notification as read.
  public function markAsRead(AccountInterface $user, string $notificationId) {}
}
```

## Implement the provider

Next we have to implement the methods. Depending on your use case,
this is different. Here you find 3 scenarios with examples:

1. [Database notifications](04_1_providers_example_database.md)
2. [Query notifications from the drupal system](04_2_providers_example_query_from_drupal.md)
3. [Notifications from an external service](04_3_providers_example_external_service.md)





