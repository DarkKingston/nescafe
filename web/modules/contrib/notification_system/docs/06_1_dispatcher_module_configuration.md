# Dispatcher Module Configuration

After enabling the `notification_system_dispatch` module, you can go
to `/admin/config/system/notification-system-dispatch`.

There you can find the configuration form which allows you to specify
some settings:


## Notification Bundling

Notifications can be bundled. When bundling is enabled, the users
have the option to decide if they want to receive the notifications
immediately or as a daily or weekly summary.

When this is disabled, all notifications are dispatched immediately

Users can choose their preference in the [Dispatcher UserSettings Block](XX).


## Whitelist

When the whitelist is enabled, you can specify a list of user
accounts that can receive notifications. All notifications
to users that are not specified in this list will be discarded in the
dispatch process.

You can use this for testing purposes (in example in a development
environment).

This setting is stored via the [Drupal State API](https://www.drupal.org/docs/8/api/state-api/overview)
and therefore not exported via config export.


## Default dispatchers

Here you can select which dispatchers a new user should have enabled.

If the user has not changed his preferences, he will always have the
dispatchers selected here enabled (even if you change it later).

If you see no dispatchers here, make sure to enable some modules that
have dispacher plugins.


## Forced Notifications

Notifications can be marked as "forced" (See [Notification Model](02_notification_model.md#forced)).

Forced notifications will bypass the user settings and will always be
sent to the dispatcher selected here, immediately. Even when the user
has disabled the dispatcher.

If no dispatcher is selected here, forced notifications will be sent
like any other notification.

You can use forced notifications for important updates that have to
be delivered to the users, for example a notification about changes
to your privacy policy.


## Dispatcher specific settings

At the end of the settings form you find the settings for individual
dispatchers.

These could be API keys or email templates. Head over to the
documentation of the dispatchers to learn more.
