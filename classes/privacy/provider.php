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

namespace local_coursestartnotify\privacy;

use context;
use context_course;
use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\transform;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;

/**
 * Privacy provider for local_coursestartnotify.
 *
 * @package    local_coursestartnotify
 * @copyright  2026 Eduardo Kraus
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\plugin\provider,
    \core_privacy\local\request\core_userlist_provider {

    /**
     * Describes stored personal data.
     *
     * @param collection $collection Metadata collection.
     * @return collection
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table(
            'local_coursestartnotify_log',
            [
                'courseid' => 'privacy:metadata:log:courseid',
                'userid' => 'privacy:metadata:log:userid',
                'startdate' => 'privacy:metadata:log:startdate',
                'timesent' => 'privacy:metadata:log:timesent',
            ],
            'privacy:metadata:log'
        );

        return $collection;
    }

    /**
     * Returns contexts containing data for a user.
     *
     * @param int $userid User id.
     * @return contextlist
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $contextlist = new contextlist();
        $sql = "SELECT ctx.id
                  FROM {context} ctx
                  JOIN {local_coursestartnotify_log} l ON l.courseid = ctx.instanceid
                 WHERE ctx.contextlevel = :contextlevel
                   AND l.userid = :userid";
        $contextlist->add_from_sql($sql, [
            'contextlevel' => CONTEXT_COURSE,
            'userid' => $userid,
        ]);
        return $contextlist;
    }

    /**
     * Lists users with sent notifications in the requested course context.
     *
     * @param userlist $userlist Users in this context.
     */
    public static function get_users_in_context(userlist $userlist): void {
        $context = $userlist->get_context();
        if (!$context instanceof context_course) {
            return;
        }

        $sql = "SELECT DISTINCT l.userid
                  FROM {local_coursestartnotify_log} l
                 WHERE l.courseid = :courseid";
        $userlist->add_from_sql('userid', $sql, ['courseid' => $context->instanceid]);
    }

    /**
     * Exports data for the approved contexts.
     *
     * @param approved_contextlist $contextlist Approved context list.
     */
    public static function export_user_data(approved_contextlist $contextlist): void {
        global $DB;

        $userid = $contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {
            if (!$context instanceof context_course) {
                continue;
            }

            $records = $DB->get_records(
                'local_coursestartnotify_log',
                ['courseid' => $context->instanceid, 'userid' => $userid],
                'timesent ASC'
            );
            if (!$records) {
                continue;
            }

            $data = [];
            foreach ($records as $record) {
                $data[] = (object) [
                    'courseid' => $record->courseid,
                    'startdate' => transform::datetime($record->startdate),
                    'timesent' => transform::datetime($record->timesent),
                ];
            }

            writer::with_context($context)->export_data(
                [get_string('privacy:path', 'local_coursestartnotify')],
                (object) ['notifications' => $data]
            );
        }
    }

    /**
     * Deletes data for only the approved users in one course context.
     *
     * @param approved_userlist $userlist Approved users and context.
     */
    public static function delete_data_for_users(approved_userlist $userlist): void {
        global $DB;

        $context = $userlist->get_context();
        $userids = $userlist->get_userids();
        if (!$context instanceof context_course || !$userids) {
            return;
        }

        [$insql, $params] = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED, 'userid');
        $params['courseid'] = $context->instanceid;
        $DB->delete_records_select(
            'local_coursestartnotify_log',
            "courseid = :courseid AND userid $insql",
            $params
        );
    }

    /**
     * Deletes all plugin data for a context.
     *
     * @param context $context Context.
     */
    public static function delete_data_for_all_users_in_context(context $context): void {
        global $DB;

        if ($context instanceof context_course) {
            $DB->delete_records('local_coursestartnotify_log', ['courseid' => $context->instanceid]);
        }
    }

    /**
     * Deletes user data in approved contexts.
     *
     * @param approved_contextlist $contextlist Approved context list.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist): void {
        global $DB;

        $userid = $contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {
            if ($context instanceof context_course) {
                $DB->delete_records('local_coursestartnotify_log', [
                    'courseid' => $context->instanceid,
                    'userid' => $userid,
                ]);
            }
        }
    }
}
