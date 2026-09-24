<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * English strings for local_coursestartnotify.
 *
 * @package    local_coursestartnotify
 * @copyright  2026 Eduardo Kraus
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['body'] = 'Custom message';
$string['body_desc'] = 'Leave empty to use the translated default. Supported placeholders: {firstname}, {coursename}, {startdate}, {courseurl}.';
$string['enabled'] = 'Enable notifications';
$string['enabled_desc'] = 'Send a notification to active enrolled users when the course start date is reached.';
$string['lookbackhours'] = 'Look-back window (hours)';
$string['lookbackhours_desc'] = 'Courses that started within this many hours are checked on each run. Successful sends are logged and are not duplicated. The installation time is always respected, so installing the plugin does not notify historical courses.';
$string['messageprovider:coursestart'] = 'Course start notification';
$string['notificationbody'] = 'Hello {$a->firstname},\\n\\nThe course "{$a->coursename}" has reached its start date ({$a->startdate}) and is now ready for you.\\n\\nAccess the course: {$a->courseurl}';
$string['notificationsubject'] = 'Your course {$a->coursename} is now available';
$string['notifyhidden'] = 'Notify for hidden courses';
$string['notifyhidden_desc'] = 'If enabled, users may be notified even when the course itself is hidden. Leave disabled unless course visibility is controlled separately at launch time.';
$string['pluginname'] = 'Course start notifications';
$string['privacy:metadata:log'] = 'Stores which course start notifications were sent so the same user is not notified repeatedly.';
$string['privacy:metadata:log:courseid'] = 'The course that generated the notification.';
$string['privacy:metadata:log:startdate'] = 'The course start date that generated this notification.';
$string['privacy:metadata:log:timesent'] = 'The time the notification was sent.';
$string['privacy:metadata:log:userid'] = 'The user who received the notification.';
$string['privacy:path'] = 'Course start notifications';
$string['retentiondays'] = 'Send log retention (days)';
$string['retentiondays_desc'] = 'How long to keep records used to prevent duplicate notifications. Minimum: 30 days.';
$string['subject'] = 'Custom subject';
$string['subject_desc'] = 'Leave empty to use the translated default. Supported placeholders: {firstname}, {coursename}, {startdate}, {courseurl}.';
$string['task_sendnotifications'] = 'Send course start notifications';
