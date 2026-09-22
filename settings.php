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
 * Admin settings for local_coursestartnotify.
 *
 * @package    local_coursestartnotify
 * @copyright  2026 Eduardo Kraus
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage(
        'local_coursestartnotify',
        get_string('pluginname', 'local_coursestartnotify')
    );

    $ADMIN->add('localplugins', $settings);

    $settings->add(new admin_setting_configcheckbox(
        'local_coursestartnotify/enabled',
        get_string('enabled', 'local_coursestartnotify'),
        get_string('enabled_desc', 'local_coursestartnotify'),
        1
    ));

    $settings->add(new admin_setting_configtext(
        'local_coursestartnotify/lookbackhours',
        get_string('lookbackhours', 'local_coursestartnotify'),
        get_string('lookbackhours_desc', 'local_coursestartnotify'),
        168,
        PARAM_INT
    ));

    $settings->add(new admin_setting_configcheckbox(
        'local_coursestartnotify/notifyhidden',
        get_string('notifyhidden', 'local_coursestartnotify'),
        get_string('notifyhidden_desc', 'local_coursestartnotify'),
        0
    ));

    $settings->add(new admin_setting_configtext(
        'local_coursestartnotify/subject',
        get_string('subject', 'local_coursestartnotify'),
        get_string('subject_desc', 'local_coursestartnotify'),
        '',
        PARAM_TEXT
    ));

    $settings->add(new admin_setting_configtextarea(
        'local_coursestartnotify/body',
        get_string('body', 'local_coursestartnotify'),
        get_string('body_desc', 'local_coursestartnotify'),
        ''
    ));

    $settings->add(new admin_setting_configtext(
        'local_coursestartnotify/retentiondays',
        get_string('retentiondays', 'local_coursestartnotify'),
        get_string('retentiondays_desc', 'local_coursestartnotify'),
        730,
        PARAM_INT
    ));
}
