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

use advanced_testcase;
use context_course;
use context_system;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\userlist;

/**
 * Course-level privacy request tests.
 *
 * @package    local_coursestartnotify
 * @copyright  2026 Eduardo Kraus
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_coursestartnotify\privacy\provider
 */
final class provider_test extends advanced_testcase {

    /**
     * An approved list must delete only those users in the selected course.
     */
    public function test_users_can_be_listed_and_deleted_by_course_context(): void {
        global $DB;

        $this->resetAfterTest();
        $course1 = $this->getDataGenerator()->create_course();
        $course2 = $this->getDataGenerator()->create_course();
        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();

        foreach ([
            [$course1->id, $user1->id],
            [$course1->id, $user2->id],
            [$course2->id, $user1->id],
        ] as [$courseid, $userid]) {
            $DB->insert_record('local_coursestartnotify_log', (object) [
                'courseid' => $courseid,
                'userid' => $userid,
                'startdate' => 1000,
                'timesent' => 2000,
            ]);
        }

        $context = context_course::instance($course1->id);
        $userlist = new userlist($context, 'local_coursestartnotify');
        provider::get_users_in_context($userlist);
        $this->assertEqualsCanonicalizing([$user1->id, $user2->id], $userlist->get_userids());

        $systemlist = new userlist(context_system::instance(), 'local_coursestartnotify');
        provider::get_users_in_context($systemlist);
        $this->assertEmpty($systemlist->get_userids());

        $approved = new approved_userlist($context, 'local_coursestartnotify', [$user1->id]);
        provider::delete_data_for_users($approved);

        $this->assertFalse($DB->record_exists('local_coursestartnotify_log', [
            'courseid' => $course1->id,
            'userid' => $user1->id,
        ]));
        $this->assertTrue($DB->record_exists('local_coursestartnotify_log', [
            'courseid' => $course1->id,
            'userid' => $user2->id,
        ]));
        $this->assertTrue($DB->record_exists('local_coursestartnotify_log', [
            'courseid' => $course2->id,
            'userid' => $user1->id,
        ]));
    }
}
