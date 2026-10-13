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

## Installation

Copy the directory to `local/coursestartnotify` inside Moodle's public web code.
For Moodle versions using a `public/` subdirectory (including Moodle 5.2),
the path relative to the Moodle project root is:

    public/local/coursestartnotify

For older installations without `public/`, use:

    local/coursestartnotify

Alternatively, download `local_coursestartnotify.zip` from the GitHub release assets. Its root directory is `coursestartnotify/`, ready for Moodle's plugin installer.

Then complete the Moodle upgrade through Site administration or CLI.

## Configuration

Go to:

    Site administration > Plugins > Local plugins > Course start notifications

The default notification text is translated using the recipient's language. If a custom subject or body is configured, these placeholders are available:

- `{firstname}`
- `{coursename}`
- `{startdate}`
- `{courseurl}`

## Troubleshooting "Failed to load task"

If the Moodle upgrade reports `Failed to load task: \local_coursestartnotify\task\send_notifications`,
the scheduled task manager could not autoload the PHP class. The task is declared
in `db/tasks.php` and its file must exist at
`classes/task/send_notifications.php` inside the installed plugin directory.

From the **Moodle project root** (the directory containing `admin/cli/`),
for an installation that uses `public/`:

    test -f public/local/coursestartnotify/classes/task/send_notifications.php
    php -l public/local/coursestartnotify/classes/task/send_notifications.php
    php admin/cli/purge_caches.php
    php admin/cli/upgrade.php --non-interactive
    php admin/cli/scheduled_task.php --list

For Moodle installations without `public/`, remove `public/` from the file
paths above. Run CLI commands as the same system account that normally manages
Moodle, and make sure the deployed PHP files are readable by the web server.

If the task still cannot be found, verify the exact deployed plugin directory,
check for incomplete deployments or stale PHP OPcache in the web server, and
confirm that `classes/task/send_notifications.php` matches the release
being installed. Restart or reset the PHP runtime's OPcache if necessary.
Do not edit Moodle core files or delete task rows from the database to mask
this warning.

## Testing the task manually

From the Moodle project root (the directory containing `admin/cli/`), list
the tasks to confirm that the task is registered:

    php admin/cli/scheduled_task.php --list

To run notifications immediately, use the task name with **single** backslashes
inside the shell's single quotes:

    php admin/cli/scheduled_task.php --execute='\local_coursestartnotify\task\send_notifications'

Executing the task can send real notifications to enrolled users. The normal
Moodle cron still needs to run for automatic delivery.
