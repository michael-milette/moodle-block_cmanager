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
 * @copyright  2021-2023 TNG Consulting Inc.
 * @author     Kyle Goslin, Daniel McSweeney
 * @author     Michael Milette
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("../../config.php");
require_once("$CFG->libdir/formslib.php");
require_once("lib.php");
require_login();
global $CFG, $DB, $USER;

// Navigation Bar.
$PAGE->navbar->ignore_active();
$PAGE->navbar->add(get_string('cmanagerDisplay', 'block_cmanager'), new moodle_url('/blocks/cmanager/module_manager.php'));
$PAGE->navbar->add(get_string('courseexists', 'block_cmanager'));
$PAGE->set_url('/blocks/cmanager/course_exists.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_heading(get_string('courseexists', 'block_cmanager'));
$PAGE->set_title(get_string('courseexists', 'block_cmanager'));

// Main variable for storing the current session id.
$currentess = '00';
$currentess = $_SESSION['cmanager_session'];
?>
<link rel="stylesheet" type="text/css" href="css/main.css" />

<?php

class block_cmanager_course_exists_form extends moodleform {

    public function definition() {

        global $DB, $currentess;

        $norequestcontrol = !empty(get_config('block_cmanager', 'norequestcontrol'));

        $currentrecord = $DB->get_record('block_cmanager_records', array('id' => $currentess));
        $mform =& $this->_form; // Don't forget the underscore!

        // Page description text.
        $mform->addElement('html', '<p>' . get_string('modexists', 'block_cmanager') . '</p>');

        $mform->addElement('html', '<table class="table table-striped">');

        // Get out record.
        $currentrecord = $DB->get_record('block_cmanager_records', array('id' => $currentess));

        $modcode = $currentrecord->modcode;
        $modmode = $currentrecord->modmode;

        $spacecheck = substr($modcode, 0, 4) . ' ' . substr($modcode, 4, strlen($modcode));

        $selectquery = "shortname LIKE '%$modcode%'

                        OR (shortname LIKE '%$spacecheck%'
                        AND shortname LIKE '%$modmode%')
                        OR shortname LIKE '%$spacecheck%'";

        $allrecords = $DB->get_recordset_select('course', $select = $selectquery);

        // Table heading.
        $showactions = empty($norequestcontrol) ? '<th>' . get_string('actions', 'block_cmanager') . '</th>' : '';
        $mform->addElement('html', '
            <tr>
                <th>' . get_string('modcode', 'block_cmanager') . '</th>
                <th>' . get_string('modname', 'block_cmanager') . '</th>
                <th>' . get_string('catlocation', 'block_cmanager'). '</th>
                <th>' . get_string('lecturingstaff', 'block_cmanager')  . '</th>
                ' . $showactions . '
            </tr>
        ');

        foreach ($allrecords as $record) {

            // Get the full category name.
            $categoryname = $DB->get_record('course_categories', array('id' => $record->category));

             // Get lecturer info.
            $lecturershtml = block_cmanager_get_lecturers($record->id);

            // Check if the category name is blank.
            if (!empty($categoryname->name)) {
                $catlocation = $categoryname->name;
            } else {
                $catlocation = '&nbsp';
            }

            $showactions = empty($norequestcontrol) ? '<td><a href="requests/request_control.php?id=' . $record->id . '">' .
                    get_string('request_requestControl', 'block_cmanager') . '</a></td>' : '';
            $mform->addElement('html', '
                <tr>
                    <th>' . format_string($record->shortname) . '</th>
                    <td>' . format_string($record->fullname) .'</td>
                    <td>' . format_string($catlocation) . '</td>
                    <td>' . $lecturershtml . '</td>
                    ' . $showactions . '
                </tr>
            ');
        }

        $mform->addElement('html', '</table>');

        // Button: None of these? Continue.
        if (empty($norequestcontrol)) {
            $showactions = 'course_new.php?status=None';
        } else {
            $showactions = 'course_request.php?mode=1';
        }
        $mform->addElement('html', '<p><a class="btn btn-default" href="' . $showactions . '">' .
                get_string('noneofthese', 'block_cmanager') . '</a></p>');

        $mform->closeHeaderBefore('buttonar');
    }
}

$mform = new block_cmanager_course_exists_form(); // Name of the form you defined in file above.
if (!$mform->is_cancelled() || empty($fromform = $mform->get_data())) {
    echo $OUTPUT->header();
    $mform->focus();
    $mform->set_data($mform);
    $mform->display();
    echo $OUTPUT->footer();
}
