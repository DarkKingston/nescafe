# Dispatchers

## What are dispatchers?

Dispatchers are the parts of the notification system that are
responsible for sending out notifications.

For example you could have an e-mail dispatcher, that sends an e-mail
to the user. Or you could have a dispatcher for push notifications or
even an integration for Slack or Microsoft Teams.

Dispatchers are used in the [Active Dispatch Flow](03_workflows.md#active-dispatch-flow)
which you can read more about in the documentation of workflows.


## Getting started

To use dispatchers, you first have to enable the
`notification_system_dispatch` module, which is the base for all
kinds of dispatchers.

Next enable some dispatchers. (See [Available dispatchers](#available-dispatchers))

Then head over to the [Dispatcher Module Configuration](06_1_dispatcher_module_configuration.md)
documentation to learn how to configure the
`notification_system_dispatch` module.


## Available dispatchers

The notification system comes with two sub-modules with ready made
dispatchers. On the corresponding documentation pages, you can learn
how to setup those:

- [E-Mail Dispatcher](06_2_1_dispatcher_mail.md)
- [Web Push Dispatcher](06_2_2_dispatcher_web_push.md)


## Custom dispatchers

Dispatchers are plugins, so it is possible for every module developer
to create a dispatcher for the notification system.

To learn how to do this, head over to the documentation on
[Creating a custom dispatcher](06_4_custom_dispatcher.md).
