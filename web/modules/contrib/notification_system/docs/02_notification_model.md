# The Notification Model

Whenever a module passes around notification data, this happens in a
specific PHP class: `Drupal\notification_system\model\Notification`.

This class acts as a dumb data class without functionality. It just
holds the data.

## Properties of a Notification

This are the properties, that every notifications have (regardless
where it comes from...)

### provider

_string (required)_

This field holds the id of the [Provider](04_providers.md) that
created the notification.


### id

_string (required)_

This field holds the id of the notification. This id has to be unique
per provider.

For example:

`Provider A` could have a notification with the id `example-123`and
`Provider B` could also have a notification with the id `example-123`.

But `Provider B` cannot have another notification with the id
`example-123`, as this would not be unique.


### type

_string (required)_

Every notification has to specify, which type (category) it belongs
to. Based on this type, the notification will be later mapped into
a notification group that is displayed in the notification center.

Examples for the types:
- new_blog_post
- new_comment
- e_mail

You can only specify types, that the provider lists in ::getTypes().

See the [Providers documentation](04_providers.md).


### users

_int[] (required)_

An array of user ids, this notification is for.

You may ask: "Do i have to list all user ids of the system when i
want to create a notification for all users?" And the answer is: Yes.

This is necessary because notifications are timely relevant. It may
be that a new user registers on your website, but he shouldn't see
this notification. To handle this case, you have to basically provide
a snapshot of all users that are currently on your platform.

You don't have to worry about users getting deleted. The
notification_system handles this for you.


### timestamp

_int (required)_

A unix timestamp that points to the creation time of the notification.


### title

_string (required)_

A short text, that holds the message that will be sent to the user


### body

_string (optional)_

Additional text for the notification.

This field can contain HTML, but beware that some [dispatchers](06_dispatchers.md)
may filter out your formatting.


### link

_`Drupal\Core\Url` (optional)_

A notification can have a link that points to more information or
a page where you can resolve the issue, or similar.

Links are optional and have to be a Drupal Url.


### sticky

_boolean (optional, default: FALSE)_

Sticky notifications cannot be marked as read. You could use that in
example for To-Dos of a user that disappear automatically when the
user has completed them.


### priority

_int (optional, default: NotificationInterface::PRIORITY_MEDIUM)_

Priorities are used for sorting. In the notification center,
notifications with higher priority are displayed before
notifications with lower priority.

See [Ranking](XX_ranking.md).

Possible values:
- `NotificationInterface::PRIORITY_HIGHEST`
- `NotificationInterface::PRIORITY_HIGH`
- `NotificationInterface::PRIORITY_MEDIUM` (default)
- `NotificationInterface::PRIORITY_LOW`
- `NotificationInterface::PRIORITY_LOWESt`


### forced

_boolean (optional, default: FALSE)_

See [Forced Notifications](06_1_dispatcher_module_configuration.md#forced-notifications) in the
dispatcher documentation.


## Readable Notifications

If the provider of a notification allows marking notifications as
read, the notification class also has to implement the following
interface: `Drupal\notification_system\model\ReadableNotificationInterface`.

The interface adds the method `isReadBy(int user)`, which returns
`true` or `false`.
