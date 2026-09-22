# Course start notifications (`local_coursestartnotify`)

Local plugin for Moodle that notifies active enrolled users when a course reaches its configured start date.

## Behaviour

- Scheduled task runs every 5 minutes by default.
- Only courses whose `startdate` has already arrived are considered.
- Historical courses from before the plugin installation are never notified.
- Hidden courses are ignored by default and can be enabled in the plugin settings.
- Active enrolled users are notified through Moodle's Message API.
- Email is forced for this notification provider; popup notifications are enabled by default.
- Each successful send is recorded by `courseid + userid + startdate`, preventing duplicate messages.
- If the course start date is changed to a new future date, the new date is treated as a new occurrence and can generate a new notification.
- Failed sends are retried while the course remains inside the configured look-back window.

## Installation

Copy the directory to:

    local/coursestartnotify

Then complete the Moodle upgrade through Site administration or CLI.

## Configuration

Go to:

    Site administration > Plugins > Local plugins > Course start notifications

The default notification text is translated using the recipient's language. If a custom subject or body is configured, these placeholders are available:

- `{firstname}`
- `{coursename}`
- `{startdate}`
- `{courseurl}`

## Testing the task manually

From the Moodle root:

    php admin/cli/scheduled_task.php --execute='\\local_coursestartnotify\\task\\send_notifications'

The server cron still needs to run normally for automatic delivery.
