<?php
// This file is part of Course Request Manager for Moodle - https://moodle.org/
//
// Course Request Manager is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Course Request Manager is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * COURSE REQUEST MANAGER BLOCK FOR MOODLE
 *
 * @package    block_cmanager
 * @copyright  2012-2018 Kyle Goslin, Daniel McSweeney (Institute of Technology Blanchardstown)
 * @copyright  2021-2022 TNG Consulting Inc., Daniel Keaman
 * @copyright  2023 TNG Consulting Inc.
 * @author     Kyle Goslin, Daniel McSweeney
 * @author     Daniel Keaman
 * @author     Michael Milette
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Return HTML displaying the names of lecturers linked to email addresses.
 *
 */
function block_cmanager_get_lecturers($courseid) {

    global $DB, $CFG;

    if (! $course = $DB->get_record("course", array("id" => $courseid))) {
        echo get_string('lib_error_invalid_c', 'block_cmanager');
        die;
    }

    $contextid = $DB->get_field('context', 'id', array('instanceid' => $courseid, 'contextlevel' => 50),
            $strictness = IGNORE_MULTIPLE);
    $userids = $DB->get_records('role_assignments', array('roleid' => '3' , 'contextid' => $contextid));

    $lecturerhtml = '';

    foreach ($userids as $singleuser) {
        $user = $DB->get_record('user', array('id' => $singleuser->userid), $fields = '*', $strictness = IGNORE_MULTIPLE);
        $lecturerhtml .= '<i class="fa fa-envelope-o" aria-hidden="true"></i>
                <a href="mailto:' . $user->email . '">' . $user->firstname . ' ' . $user->lastname . '</a><br>';
    }

    return $lecturerhtml;
}

/**
 * Get a collection of teacher ids (role 3).
 *
 * for a specific course, separated by spaces.
 */
function block_cmanager_get_lecturer_ids_space_sep($courseid) {

    global $DB, $CFG;

    if (! $course = $DB->get_record("course", array("id" => $courseid))) {
         echo get_string('lib_error_invalid_c', 'block_cmanager');
        die;
    }

    $contextid = $DB->get_field('context', 'id', array ('instanceid' => $courseid, 'contextlevel' => 50),
            $strictness = IGNORE_MULTIPLE);
    $userids = $DB->get_records('role_assignments', array('roleid' => '3' , 'contextid' => $contextid));

    $lecturerhtml = '';

    foreach ($userids as $singleuser) {
        $userrecord = $DB->get_record('user', array('id' => $singleuser->userid), $fields = '*', $strictness = IGNORE_MULTIPLE);
        $lecturerhtml .= $userrecord->id . ' ';
    }

    return $lecturerhtml;

}
