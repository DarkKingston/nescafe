# Provider Example 1: Database notifications

## The scenario

We have a blog where users can subscribe different blog categories.

Users get notifications everytime a new blog post is created in one of
their subscribed categories.

## The provider

In this example we don't want to use a completely blank provider.

For notifications that are stored in the drupal database, we can enable
the `notification_system_database` sub-module, which will handle most
of the work for us.

The module provides a custom entity type `Notification` that we use to
create and store our notifications.

We still have to create a provider plugin, but this is just responsible
for providing the types of notifications we create.

So instead of extending the class `NotificationProviderPluginBase`, we
extend the `DbNotificationProviderPluginBase` class.

Create a new file `ExampleNotificationProvider.php` in your modules
folder `src/Plugin/DbNotificationProvider` that looks like this:

```php
namespace Drupal\my_module\Plugin\DbNotificationProvider;

use Drupal\notification_system_database\DbNotificationProviderPluginBase;

/**
 * @DbNotificationProvider (
 *   id = "example",
 *   label = @Translation("Example"),
 *   description = @Translation("Generates demo notifications.")
 * )
 */
class ExampleNotificationProvider extends DbNotificationProviderPluginBase {
  public function getTypes() {
    // We just have a single static type.
    return [
      'new_blog_post'
    ];
  }
}
```

## Creating notifications

We use the [hook_ENTITY_TYPE_insert](https://api.drupal.org/api/drupal/core%21lib%21Drupal%21Core%21Entity%21entity.api.php/function/hook_ENTITY_TYPE_insert/9.2.x)
for creating new notifications.

We assume we have a custom service called `BlogSubscriptionService`
that allows us to get all users that have subscribed to a category.

```php
function my_module_node_insert(\Drupal\Core\Entity\EntityInterface $entity) {
  /** @var \Drupal\node\NodeInterface $node */
  $node = $entity;
  if ($node->bundle() == 'blog_post') {
    /** @var int[] $userIds */
    $userIds = $this->blogSubscriptionService->getSubscribedUsers($node->get('field_category')->target_id);

    if (count($userIds) == 0) {
      // No users have subscribed, so we don't have to send a notification.
      return;
    }

    $notification = Drupal\notification_system_database\Entity\Notification::create([
      'user_id' => $userIds,                                   // The audience of the notification are all subscribed users.
      'provider_id' => 'example',                              // This is the id of our provider. Make sure that it exists.
      'notification_type' => 'new_blog_post',                  // The type of the notification
      'created' => $node->getCreatedTime(),                    // The notification timestamp
      'title' => $this->t('New blog post'),                    // The title of the notification
      'body' => $node->label(),                                // We use the node title as the body of the notification
      'link' => $node->toUrl()->setAbsolute(TRUE)->toString()  // Set the url absolute, to get a full link if it is displayed in an e-mail for example.
    ]);

    // We save the notification to the database.
    $notification->save();
  }
}
```

When the notification entity is saved, a `NewNotificationEvent` is
automatically emitted. This will trigger the [Active Dispatch Flow](03_workflows.md#active-dispatch-flow).
