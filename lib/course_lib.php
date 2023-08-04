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
 * @copyright  2023 TNG Consulting Inc.
 * @author     Kyle Goslin, Daniel McSweeney
 * @author     Michael Milette
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG, $DB;

if ($CFG->branch < 36) {
    require_once($CFG->libdir . '/coursecatlib.php');
}

/**
 * Building up the new course object.
 */
class block_cmanager_new_course extends stdClass {

    public $returnto = 'topcat';
    public $category = 1;
    public $fullname = '';
    public $shortname = '';
    public $idnumber = '';
    public $summaryeditor = array("text" => "", "format" => "1", "itemid" => '');
    public $summaryformat = FORMAT_HTML;
    public $summary = '';
    public $format = '';
    public $numsections = '10';
    public $startdate = '1336003200';
    public $hiddensections = '0';
    public $newsitems = '5';
    public $showgrades = '1';
    public $showreports = '0';
    public $maxbytes = '2097152';
    public $enrolgueststatus0 = 1;
    public $groupmode = 0;
    public $groupmodeforce = '';
    public $defaultgroupingid = '';
    public $visible = '1';
    public $lang = '';
    public $enablecompletion = '';
    public $completionstartonenrol = '';
    public $restrictmodules = '';
    public $role1 = '';
    public $role2 = '';
    public $mformshowadvancedlast = '';
    public $role3 = '';
    public $role4 = '';
    public $role5 = '';
    public $role6 = '';
    public $role7 = '';
    public $role8 = '';
    public $role9 = '';
}

/**
 * Create a new Module on the Moodle installation based upon the ID of the record in the course request system.
 *
 */
function blockcmanagercreatenewcoursebyrecordid($mid, $sendmail) {

    global $CFG, $DB;
    require_once("$CFG->libdir/formslib.php");
    require_once('../../../course/lib.php');
    require_once($CFG->libdir.'/completionlib.php');

    global $context;

    // Create an object to hold our new course information.
    $newcourse = new block_cmanager_new_course();

    // Starting course creation process.
    // Step 1/5.
    $event = \block_cmanager\event\course_process::create(array(
        'objectid' => '',
        'other' => ' ' . get_string('stepnumber', 'block_cmanager'). ' 1/5 - ' . get_string('startingcoursecreation', 'block_cmanager'),
        'context' => $context,
    ));
    $event->trigger();

    $newcourse->coursecat = 1;
    $newcourse->format = get_config('moodlecourse', 'format');
    // Get the default timestamp for new courses.
    $timestampstartdate = $DB->get_field('block_cmanager_config', 'value', array('varname' => 'startdate'), IGNORE_MULTIPLE);
    $newcourse->startdate = $timestampstartdate;

    $newcourse->newsitems = get_config('moodlecourse', 'newsitems');
    $newcourse->showgrades = get_config('moodlecourse', 'showgrades');
    $newcourse->showreports = get_config('moodlecourse', 'showreports');
    $newcourse->maxbytes = get_config('moodlecourse', 'maxbytes');

    // Formatting.
    $newcourse->numsections = get_config('moodlecourse', 'numsections');
    $newcourse->hiddensections = get_config('moodlecourse', 'hiddensections');

    // Groups.
    $newcourse->groupmode = get_config('moodlecourse', 'groupmode');
    $newcourse->groupmodeforce = get_config('moodlecourse', 'groupmodeforce');

    // Completion.
    $newcourse->enablecompletion = get_config('moodlecourse', 'enablecompletion');

    // Visible.
    $newcourse->visible = get_config('moodlecourse', 'visible');
    $newcourse->lang = get_config('moodlecourse', 'lang');

    // Is course mode enabled (page 1 optional dropdown).
    $mode = $DB->get_field('block_cmanager_config', 'value', array('varname' => 'page1_field3status'));

    // What naming mode is operating.
    $naming = $DB->get_field('block_cmanager_config', 'value', array('varname' => 'naming'), IGNORE_MULTIPLE);

    // What short naming format is operating.
    $snaming = $DB->get_field('block_cmanager_config', 'value', array('varname' => 'snaming'), IGNORE_MULTIPLE);

    // Get the record for the request.
    $rec = $DB->get_record('block_cmanager_records', array('id' => $mid));

    // Build up a course record based on the request.

    if (empty($rec->cate)) {
        $newcourse->category = $CFG->defaultrequestcategory;
    } else {
        $newcourse->category = $rec->cate;
    }

    // Fields we are carrying across.
    if ($mode == "enabled" && $snaming == 2) {
        $newshortname = $rec->modcode . ' - ' . $rec->modmode;
    } else {
        $newshortname = $rec->modcode;
    }

    $newcourse->shortname = $newshortname;

    // Course naming.
    switch ($naming) {
        case 1:
            $newcourse->fullname = $rec->modname;
            break;
        case 2:
            $newcourse->fullname = $rec->modcode . ' - '. $rec->modname;
            break;
        case 3:
            $newcourse->fullname = $rec->modname . ' ('. $rec->modcode . ')'; // Fullname, shortname.
            break;
        case 4:
            $newcourse->fullname = $rec->modcode . ' - '. $rec->modname . ' (' . date("Y") . ')'; // Shortname, fullname (year).
            break;
        case 5:
            $newcourse->fullname = $rec->modname . ' (' . date("Y") . ')';
            break;
    }

    // Enrollment key.
    // if the key thats been set, otherwise auto gen a key.
    if (isset($rec->modkey)) {
        $modkey = $rec->modkey;
    } else {
        $modkey = rand(999, 5000);
    }

    // Editor options.

    $editoroptions = array('maxfiles' => EDITOR_UNLIMITED_FILES, 'maxbytes' => $CFG->maxbytes,
            'trusttext' => false, 'noclean' => true);

    // Create the course using the data gathered.
    $course = create_course($newcourse, $editoroptions);

    //
    // Step 2 -- Enroll Creator.
    //
    if (!empty($CFG->creatornewroleid)) {
        // Deal with course creators - enrol them internally with default role.
        $status = enrol_try_internal_enrol($course->id, $rec->createdbyid, $CFG->creatornewroleid);

        if (!$status) {
            $event = \block_cmanager\event\course_process::create(array(
                    'objectid' => $objid,
                    'other' => ' ' . get_string('stepnumber', 'block_cmanager') . ' 2/5 - ' . get_string('failedtoenrolcreator', 'block_cmanager'),
                    'context' => $context,
            ));
            $event->trigger();
        } else {
            $event = \block_cmanager\event\course_process::create(array(
                    'objectid' => $objid,
                    'other' => ' ' . get_string('stepnumber', 'block_cmanager') . ' 2/5 -' . get_string('enrolledcrator', 'block_cmanager'),
                    'context' => $context,
            ));
            $event->trigger();
        }

    }

    // Check to see if auto create enrollment keys is enabled. If this option is set, add an enrollment key.
    $autokey = $DB->get_field_select('block_cmanager_config', 'value', "varname = 'autokey'");

    if ($autokey == 0 || $autokey == 1) {

         // Add enrollnent key.
        $enrollmentrecord = new stdClass();
        $enrollmentrecord->enrol = 'self';
        $enrollmentrecord->status = 0;
        $enrollmentrecord->courseid = $course->id;
        $enrollmentrecord->sortorder = 3;
        $enrollmentrecord->name = '';
        $enrollmentrecord->enrolperiod = 0;
        $enrollmentrecord->enrolenddate = 0;
        $enrollmentrecord->expirynotify = 0;
        $enrollmentrecord->expirythreshold = 0;
        $enrollmentrecord->notifyall = 0;
        $enrollmentrecord->password = $modkey;
        $enrollmentrecord->cost = null;
        $enrollmentrecord->currency = null;
        $enrollmentrecord->roleid = 5;
        $enrollmentrecord->customint1 = 0;
        $enrollmentrecord->customint2 = 0;
        $enrollmentrecord->customint3 = 0;
        $enrollmentrecord->customint4 = 1;

        if ($CFG->version >= 2013051400) {
            $enrollmentrecord->customint5 = null;
            $enrollmentrecord->customint6 = 1;
        }

        $enrollmentrecord->customchar1 = null;
        $enrollmentrecord->customchar2 = null;
        $enrollmentrecord->customdec1 = null;
        $enrollmentrecord->customdec2 = null;
        $enrollmentrecord->customtext1 = '';
        $enrollmentrecord->customtext2 = null;
        $enrollmentrecord->timecreated = time();
        $enrollmentrecord->timemodified = time();

        $enrolres = $DB->insert_record('enrol', $enrollmentrecord);
        //
        // Step 3 ---- enrollment key.
        //
        if (!$enrolres) {

            global $context;
            $event = \block_cmanager\event\course_process::create(array(
                'objectid' => $objid,
                'context' => $context,
                'other' => ' ' . get_string('stepnumber', 'block_cmanager') . ' 3/5 - ' . get_string('keyaddsuccess', 'block_cmanager'),
            ));
            $event->trigger();
        } else {
            global $context;
            $event = \block_cmanager\event\course_process::create(array(
                'objectid' => $objid,
                'context' => $context,
                'other' => ' ' . get_string('stepnumber', 'block_cmanager') . ' 3/5 - '. get_string('keyaddfail', 'block_cmanager'),
            ));
            $event->trigger();

        }
    }

    if ($sendmail == true) {
        block_cmanager_send_emails($course->id, $newcourse->shortname, $newcourse->fullname, $modkey, $mid);
    }

    // Step 4 - Updating the course record status.
    $event = \block_cmanager\event\course_process::create(array(
        'objectid' => $objid,
        'context' => $context,
        'other' => ' ' . get_string('stepnumber', 'block_cmanager').' 4/5 - ' . get_string('updatingrecstatus', 'block_cmanager'),
    ));
    $event->trigger();

    // Update the record to say that it is now complete.
    $updatedrecord = new stdClass();
    $updatedrecord->id = $rec->id;
    $updatedrecord->status = 'COMPLETE';
    $DB->update_record('block_cmanager_records', $updatedrecord);

    // Make a log entry to say the course has been created.
    // Step 5/5.
    $event = \block_cmanager\event\course_created::create(array(
        'objectid' => $objid,
        'context' => $context,
        'other' => ' '. get_string('stepnumber', 'block_cmanager').' 5/5 - course ID ' . $course->id . '',
    ));
    $event->trigger();

    // Return the ID which will be redirected to when finished.
     return $course->id;

}

/**
 * Send emails to everyone that is related to this module.
 *
 */
function block_cmanager_send_emails($courseid, $modcode, $modname, $modkey, $mid) {

    global $CFG, $DB;

    // Send an email to everyone concerned.
    require_once('../cmanager_email.php');

    // Get all user id's from the record.
    $currentrecord = $DB->get_record('block_cmanager_records', array('id' => $mid));
    $userids = '';
    $userids = $currentrecord->createdbyid; // Add the current user.

    $replacevalues = array();
    $replacevalues['[course_code'] = $modcode;
    $replacevalues['[course_name]'] = $modname;
    $replacevalues['[e_key]'] = $modkey;
    $replacevalues['[full_link]'] = $CFG->wwwroot .'/course/view.php?id=' . $courseid;
    $replacevalues['[loc]'] = 'Location: ' . '';
    $replacevalues['[req_link]'] = $CFG->wwwroot .'/blocks/cmanager/view_summary.php?id=' . $courseid;

    // Email the user.
    block_cmanager_new_course_approved_mail_user($userids, $replacevalues);
    // Email the admin.
    block_cmanager_new_course_approved_mail_admin($replacevalues);

}
