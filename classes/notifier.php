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

namespace local_coursestartnotify;

use context_course;
use core\message\message;
use core_user;
use moodle_url;

/**
 * Course start notification service.
 *
 * @package    local_coursestartnotify
 * @copyright  2026 Eduardo Kraus
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class notifier {

    /** @var string Log table name. */
    private const LOG_TABLE = 'local_coursestartnotify_log';

    /**
     * Finds courses that have just started and notifies their active enrolled users.
     */
    public function run(): void {
        global $DB, $SITE;

        if (!get_config('local_coursestartnotify', 'enabled')) {
            mtrace('Course start notifications are disabled.');
            return;
        }

        $now = time();
        $installedat = (int) get_config('local_coursestartnotify', 'installedat');
        if ($installedat <= 0) {
            $installedat = $now;
            set_config('installedat', $installedat, 'local_coursestartnotify');
        }

        $lookbackhours = max(1, (int) get_config('local_coursestartnotify', 'lookbackhours'));
        $since = max($installedat, $now - ($lookbackhours * HOURSECS));
        $notifyhidden = (bool) get_config('local_coursestartnotify', 'notifyhidden');

        $params = [
            'siteid' => $SITE->id,
            'since' => $since,
            'now' => $now,
        ];
        $select = 'id <> :siteid AND startdate > 0 AND startdate >= :since AND startdate <= :now';
        if (!$notifyhidden) {
            $select .= ' AND visible = 1';
        }

        $courses = $DB->get_records_select('course', $select, $params, 'startdate ASC, id ASC');
        if (!$courses) {
            $this->purge_old_logs($now);
            mtrace('No newly started courses found.');
            return;
        }

        foreach ($courses as $course) {
            $this->notify_course($course);
        }

        $this->purge_old_logs($now);
    }

    /**
     * Sends a notification to all active enrolled users of one course.
     *
     * @param \stdClass $course Course record.
     */
    private function notify_course(\stdClass $course): void {
        global $DB;

        $context = context_course::instance($course->id, IGNORE_MISSING);
        if (!$context) {
            return;
        }

        $coursename = format_string($course->fullname, true, ['context' => $context]);
        $pagesize = 500;
        $offset = 0;
        $foundusers = false;

        mtrace('Course ' . $course->id . ' (' . $coursename . '): checking active enrolled users.');

        do {
            $users = get_enrolled_users($context, '', 0, 'u.*', 'u.id ASC', $offset, $pagesize, true);
            if (!$users) {
                break;
            }

            $foundusers = true;
            foreach ($users as $user) {
                if ($user->deleted || $user->suspended || isguestuser($user)) {
                    continue;
                }

                $conditions = [
                    'courseid' => $course->id,
                    'userid' => $user->id,
                    'startdate' => $course->startdate,
                ];
                if ($DB->record_exists(self::LOG_TABLE, $conditions)) {
                    continue;
                }

                try {
                    $messageid = $this->send_to_user($course, $context, $user, $coursename);
                    if ($messageid) {
                        $record = (object) $conditions;
                        $record->timesent = time();
                        $DB->insert_record(self::LOG_TABLE, $record);
                        mtrace('  Sent to user ' . $user->id . '.');
                    } else {
                        mtrace('  Message API did not send notification to user ' . $user->id . '.');
                    }
                } catch (\Throwable $exception) {
                    mtrace('  Failed for user ' . $user->id . ': ' . $exception->getMessage());
                }
            }

            $offset += $pagesize;
        } while (count($users) === $pagesize);

        if (!$foundusers) {
            mtrace('Course ' . $course->id . ': no active enrolled users.');
        }
    }

    /**
     * Sends one Moodle notification.
     *
     * @param \stdClass $course Course record.
     * @param context_course $context Course context.
     * @param \stdClass $user Recipient.
     * @param string $coursename Formatted course name.
     * @return int|false Message id or false.
     */
    private function send_to_user(
        \stdClass $course,
        context_course $context,
        \stdClass $user,
        string $coursename
    ) {
        $courseurl = new moodle_url('/course/view.php', ['id' => $course->id]);
        $startdate = userdate(
            $course->startdate,
            get_string('strftimedatetime', 'langconfig'),
            $user->timezone
        );

        $data = (object) [
            'firstname' => $user->firstname,
            'coursename' => $coursename,
            'startdate' => $startdate,
            'courseurl' => $courseurl->out(false),
        ];

        $subject = $this->get_subject($user, $data);
        $body = $this->get_body($user, $data);

        $notification = new message();
        $notification->component = 'local_coursestartnotify';
        $notification->name = 'coursestart';
        $notification->userfrom = core_user::get_noreply_user();
        $notification->userto = $user;
        $notification->subject = $subject;
        $notification->fullmessage = $body;
        $notification->fullmessageformat = FORMAT_PLAIN;
        $notification->fullmessagehtml = '<p>' . nl2br(s($body)) . '</p>';
        $notification->smallmessage = shorten_text($body, 255);
        $notification->notification = 1;
        $notification->contexturl = $courseurl->out(false);
        $notification->contexturlname = $coursename;

        return message_send($notification);
    }

    /**
     * Gets the subject in the user's language or applies the configured template.
     *
     * @param \stdClass $user Recipient.
     * @param \stdClass $data Template data.
     * @return string
     */
    private function get_subject(\stdClass $user, \stdClass $data): string {
        $configured = trim((string) get_config('local_coursestartnotify', 'subject'));
        if ($configured !== '') {
            return $this->replace_placeholders($configured, $data);
        }

        return get_string_manager()->get_string(
            'notificationsubject',
            'local_coursestartnotify',
            $data,
            $user->lang
        );
    }

    /**
     * Gets the body in the user's language or applies the configured template.
     *
     * @param \stdClass $user Recipient.
     * @param \stdClass $data Template data.
     * @return string
     */
    private function get_body(\stdClass $user, \stdClass $data): string {
        $configured = trim((string) get_config('local_coursestartnotify', 'body'));
        if ($configured !== '') {
            return $this->replace_placeholders($configured, $data);
        }

        return get_string_manager()->get_string(
            'notificationbody',
            'local_coursestartnotify',
            $data,
            $user->lang
        );
    }

    /**
     * Replaces supported placeholders in administrator-defined templates.
     *
     * @param string $text Template.
     * @param \stdClass $data Template data.
     * @return string
     */
    private function replace_placeholders(string $text, \stdClass $data): string {
        return strtr($text, [
            '{firstname}' => $data->firstname,
            '{coursename}' => $data->coursename,
            '{startdate}' => $data->startdate,
            '{courseurl}' => $data->courseurl,
        ]);
    }

    /**
     * Removes old send logs. Old courses are outside the look-back window, so this is safe.
     *
     * @param int $now Current timestamp.
     */
    private function purge_old_logs(int $now): void {
        global $DB;

        $retentiondays = max(30, (int) get_config('local_coursestartnotify', 'retentiondays'));
        $cutoff = $now - ($retentiondays * DAYSECS);
        $DB->delete_records_select(self::LOG_TABLE, 'timesent < :cutoff', ['cutoff' => $cutoff]);
    }
}
