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
global $CFG, $USER, $DB;
require_once("$CFG->libdir/formslib.php");

$context = context_system::instance();
if (!has_capability('block/cmanager:approverecord', $context)) {
    throw new \moodle_exception('cannotapproverecords', 'block_cmanager');
}

/**
 * Approving module.
 * Main interface for approving a module.
 * @package    block_cmanager
 * @copyright  2018 Kyle Goslin, Daniel McSweeney
 * @copyright  2021-2023 TNG Consulting Inc.
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_cmanager_approve_module_form extends moodleform {

    public function definition() {
        global $DB;

        $mform =& $this->_form; // Don't forget the underscore.
        $mform->addElement('header', 'mainheader', get_string('approverequest_Title', 'block_cmanager'));

        $mid = required_param('mid', PARAM_INT);

        $currentrecord = $DB->get_record('block_cmanager_records', array('id' => $mid));

        $newcourserecord = new stdClass();
        $newcourserecord->MAX_FILE_SIZE = "2097152";
        $newcourserecord->category = $currentrecord->cate;
        $newcourserecord->fullname = $currentrecord->modname;
        $newcourserecord->shortname = "CF1011X1171";
        $newcourserecord->idnumber = "s";
        $newcourserecord->summary = "asdasd";
        $newcourserecord->format = "weeks";
        $newcourserecord->numsections = "10";
        $newcourserecord->startdate = "1307664000";
        $newcourserecord->hiddensections = "0";
        $newcourserecord->newsitems = "5";
        $newcourserecord->showgrades = "1";
        $newcourserecord->showreports = "0";
        $newcourserecord->maxbytes = "2097152";
        $newcourserecord->metacourse = "0";
        $newcourserecord->enrol = "";
        $newcourserecord->defaultrole = "0";
        $newcourserecord->enrollable = "1";
        $newcourserecord->enrolstartdate = "1307577600";
        $newcourserecord->enrolstartdisabled = "1";
        $newcourserecord->enrolenddate = "1307577600";
        $newcourserecord->enrolenddisabled = "1";
        $newcourserecord->enrolperiod = "0";
        $newcourserecord->expirynotify = "0";
        $newcourserecord->notifystudents = "0";
        $newcourserecord->expirythreshold = "864000";
        $newcourserecord->groupmode = "0";
        $newcourserecord->groupmodeforce = "0";
        $newcourserecord->visible = "1";
        $newcourserecord->enrolpassword = "";
        $newcourserecord->guest = "0";
        $newcourserecord->lang = "";
        $newcourserecord->restrictmodules = "0";
        $newcourserecord->role1 = "";
        $newcourserecord->role2 = "";
        $newcourserecord->role3 = "";
        $newcourserecord->role4 = "";
        $newcourserecord->role5 = "";
        $newcourserecord->role6 = "";
        $newcourserecord->role7 = "";
        $newcourserecord->teacher = "Teacher";
        $newcourserecord->teachers = "Teachers";
        $newcourserecord->student = "Student";
        $newcourserecord->students = "Students";

        $type = optional_param('type', 0, PARAM_TEXT);
        $htmloutput = '';

        $htmloutput .= '<center>';
        if ($type == '1') {
            create_course($newcourserecord);
            $htmloutput .= get_string('approverequest_New', 'block_cmanager');
        }

        if ($type == '2') {
            $htmloutput .= get_string('approverequest_Process', 'block_cmanager');
        }

        $htmloutput .= '</center>';

        $mform->addElement('html', '<p><center>'
            . '<div name="addedmodules" id="addedmodules" align="left" style="border: 1px grey solid; width:700px;"> '
            . $htmloutput . '<p></p>&nbsp;<p></p>&nbsp;</div></center>');
    }  // Close the function.

}  // Close the class.

// Name of the form you defined in file above.
$mform = new block_cmanager_approve_module_form();

if (!$mform->is_cancelled() || empty(($fromform = $mform->get_data()))) {

    // This branch is executed if the form is submitted but the data doesn't validate and the form should be redisplayed
    // or on the first display of the form.

    // Setup strings for heading.
    print_header_simple($streditinga, '',
            "<a href=\"$CFG->wwwroot/mod/$module->name/index.php?id=$course->id\">$strmodulenameplural</a> ->
            $strnav $streditinga", $mform->focus(), "", false);

    // Notice use of $mform->focus() above which puts the cursor in the first form field or the first field with an error.

    // Put data you want to fill out in the form into array $toform here.
    $mform->set_data($toform);
    $mform->display();
    print_footer($course);

}
