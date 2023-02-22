# What is a Notification?

A notification is a **bit of information**, that is **actively
displayed or sent** to users. (Unlike blog posts for example, that
are passively consumed by users)

A notification could be the information that new content is available
, an open task to do, confirmations of completed actions, ...

Via the notification_system, any kind of notification can be
implemented. There are no limits.

Notifications can originate from Drupal itself, or can be fetched
from external systems (like Outlook, Ticket System, ...). The
[Notification Model](02_notification_model.md) is not a drupal entity
and does not have to be saved in the database (But you can save it
there with the [Database Notifications submodule](04_1_providers_example_database.md)).

Notifications can be sent to all users, a specific group or users or
even single users. Every notification can be personalised.

Specific types of a notification can be marked as read.

## Examples for notifications
- New blog post of a category the user subscribed to
- Another user commented on a blog post, the current user is author
  of
- New E-Mail in Outlook
- A ticket you opened in the ticket system has been resolved
- You were assigned to a test in Moodle
- There was a new covid regulation, that you have to sign
- ...
