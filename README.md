# Course start notifications (`local_coursestartnotify`)

Local plugin for Moodle that notifies active enrolled users when a course reaches its configured start date.

## Behaviour

- Scheduled task runs every 5 minutes by default.
- Only courses whose `startdate` has already arrived are considered.
- Historical courses from before the plugin installation are never notified.
- Hidden courses are ignored by default and can be enabled in the plugin settings.
- Active enrolled users are notified through Moodle's Message API.
- Email and popup notifications are enabled by default, but users can disable either channel in notification preferences.
- Each successful send is recorded by `courseid + userid + startdate`, preventing duplicate messages.
- If the course start date is changed to a new future date, the new date is treated as a new occurrence and can generate a new notification.
- Failed sends are retried while the course remains inside the configured look-back window.

## Configuration

Go to:

    Site administration > Plugins > Local plugins > Course start notifications

The default notification text is translated using the recipient's language. If a custom subject or body is configured, these placeholders are available:

- `{firstname}`
- `{coursename}`
- `{startdate}`
- `{courseurl}`


## Testing the task manually

From the Moodle project root (the directory containing `admin/cli/`), list
the tasks to confirm that the task is registered:

    php admin/cli/scheduled_task.php --list

To run notifications immediately, use the task name with **single** backslashes
inside the shell's single quotes:

    php admin/cli/scheduled_task.php --execute='\local_coursestartnotify\task\send_notifications'

Executing the task can send real notifications to enrolled users. The normal
Moodle cron still needs to run for automatic delivery.
