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

namespace local_coursestartnotify\task;

use advanced_testcase;
use core\task\manager;

/**
 * Scheduled task discovery regression tests.
 *
 * @package    local_coursestartnotify
 * @copyright  2026 Eduardo Kraus
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_coursestartnotify\task\send_notifications
 */
final class send_notifications_test extends advanced_testcase {

    /**
     * Moodle must be able to autoload and instantiate the task from db/tasks.php.
     */
    public function test_scheduled_task_is_discoverable(): void {
        $classname = '\\local_coursestartnotify\\task\\send_notifications';

        $this->assertTrue(class_exists($classname));
        $this->assertTrue(is_subclass_of($classname, \core\task\scheduled_task::class));

        $tasks = manager::load_default_scheduled_tasks_for_component('local_coursestartnotify');

        $this->assertCount(1, $tasks);
        $this->assertInstanceOf(send_notifications::class, $tasks[0]);
        $this->assertSame(
            get_string('task_sendnotifications', 'local_coursestartnotify'),
            $tasks[0]->get_name()
        );
    }
}
