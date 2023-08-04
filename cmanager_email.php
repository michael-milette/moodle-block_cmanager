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

defined('MOODLE_INTERNAL') || die();

// Require cfg was here.
require_once($CFG->dirroot . "/lib/moodlelib.php");
require_once('lib.php');

global $DB;
$senderemailaddress = $DB->get_field('block_cmanager_config', 'value', array("varname" => 'emailsender'), IGNORE_MULTIPLE);

$emailsender = new stdClass();
$emailsender->id = 1;
$emailsender->email = $senderemailaddress;
$emailsender->maildisplay = true;

/**
 * Preform a search and replace for any value tags which were entered by the admin.
 */
function block_cmanager_convert_tags_to_values($email, $replacevalues) {
    // Course code: [course_code].
    $coursecodeadded = str_replace('[course_code]', $replacevalues['[course_code'], $email);

    // Course name: [course_name].
    $coursenameadded = str_replace('[course_name]', $replacevalues['[course_name]'], $coursecodeadded);

    // Enrolment key: [e_key].
    $enrollkeyadded = str_replace('[e_key]', $replacevalues['[e_key]'], $coursenameadded);

    // Full URL to module: [full_link].
    $fullurladded = str_replace('[full_link]', $replacevalues['[full_link]'], $enrollkeyadded);

    $reqlinkadded = str_replace('[req_link]', $replacevalues['[req_link]'], $fullurladded);

    // Location in catalog: [loc].
    $locationadded = str_replace('[loc]', $replacevalues['[loc]'], $reqlinkadded);

    $newemail = $locationadded;

    return $newemail;
}

/**
 * When a new course is approved email the user.
 */
function block_cmanager_new_course_approved_mail_user($uids, $currentmodinfo) {

    global $DB, $senderemailaddress;

    $uidarray = explode(' ', $uids);

    foreach ($uidarray as $singleid) {
        $emailinguserobject = $DB->get_record('user', array('id' => $singleid));
        $subject = get_string('emailSubj_userApproved', 'block_cmanager');
        $rec = $DB->get_record('block_cmanager_config', array('varname' => 'approveduseremail'));

        if (strlen(trim($rec->value)) > 0) { // Are there characters in the field.
            $messagetext = block_cmanager_convert_tags_to_values($rec->value, $currentmodinfo);
            email_to_user($emailinguserobject, $senderemailaddress, $subject,
                    format_text($messagetext), $messagehtml = '', $attachment = '',
                    $attachname = '', true, $replyto = '', $replytoname = '', $wordwrapwidth = 79);
        }
    }

} // Function.

/**
 * When a new course is approved, email the admin(s).
 */
function block_cmanager_new_course_approved_mail_admin($currentmodinfo) {

    global $DB;

    // Get each admin email.
    $wherequery = "varname = 'adminemail'";
    $modrecords = $DB->get_recordset_select('block_cmanager_config', $wherequery);

    $adminemail = $DB->get_field('block_cmanager_config', 'value', array('varname' => 'approvedadminemail') , IGNORE_MULTIPLE);

    if (strlen(trim($adminemail)) > 0) { // Are there characters in the field.
        $messagetext = block_cmanager_convert_tags_to_values($adminemail, $currentmodinfo);
        // Send an email to each admin.
        foreach ($modrecords as $rec) {
            $to = $rec->value;
            $subject = get_string('emailSubj_adminApproved', 'block_cmanager');

            block_cmanager_send_email_to_address($to, $subject, format_text($messagetext));
        } // End for loop.

    } // End if.
}// End function.

/**
 * Requesting a new module, email admin(s).
 */
function blockcmanagerrequestnewmodemailadmins($currentmodinfo) {

    global $DB;

    // Get each admin email.
    $wherequery = "varname = 'adminemail'";
    $modrecords = $DB->get_records_select('block_cmanager_config', $wherequery);
    $adminemail = $DB->get_record('block_cmanager_config', array('varname' => 'requestnewmoduleadmin'));

    if (strlen(trim($adminemail->value)) > 0) { // Are there characters in the field.
        $messagetext = block_cmanager_convert_tags_to_values($adminemail->value, $currentmodinfo);
        // Send an email to each admin.
        foreach ($modrecords as $rec) {
            $to = $rec->value;
            $subject = get_string('emailSubj_adminNewRequest', 'block_cmanager');

            block_cmanager_send_email_to_address($to, $subject, format_text($messagetext));
        } // End for.

    } // End if.

}// End function.

/**
 * Requesting a new module, email user
 */
function blockcmanagerrequestnewmodemailuser($uid, $currentmodinfo) {

    global $emailsender, $DB;

    $emailinguserobject = $DB->get_record('user', array('id' => $uid));
    $subject = get_string('emailSubj_userNewRequest', 'block_cmanager');
    $useremailmessage = $DB->get_record('block_cmanager_config', array('varname' => 'requestnewmoduleuser'));

    if (strlen(trim($useremailmessage->value)) > 0) { // Are there characters in the field.
        $messagetext = block_cmanager_convert_tags_to_values($useremailmessage->value, $currentmodinfo);
        email_to_user($emailinguserobject, $emailsender, $subject, format_text($messagetext), $messagehtml = '', $attachment = '',
                $attachname = '', true, $replyto = '', $replytoname = '', $wordwrapwidth = 79);
    } // End if.

} // End function.

/**
 *  Send an email out to an address external to anything to do with Moodle.
 */
function block_cmanager_send_email_to_address($to, $subject, $text) {

    global $senderemailaddress;

    $emailinguserobject = new stdClass();
    $emailinguserobject->id = 1;
    $emailinguserobject->email = $to;
    $emailinguserobject->maildisplay = true;
    $emailinguserobject->username = '';
    $emailinguserobject->mailformat = 1;
    $emailinguserobject->firstnamephonetic = '';
    $emailinguserobject->lastnamephonetic = '';
    $emailinguserobject->middlename = '';
    $emailinguserobject->alternatename = '';
    $emailinguserobject->firstname = get_string('admin');
    $emailinguserobject->lastname = '';

    email_to_user($emailinguserobject, $senderemailaddress, $subject, $text, $messagehtml = '', $attachment = '',
            $attachname = '', true, $replyto = '', $replytoname = '', $wordwrapwidth = 79);
}

/**
 * Email a comment out to a user.
 */
function block_cmanager_email_comment_to_user($message, $uid, $mid, $currentmodinfo) {

    global $emailsender, $DB;

    $emailinguserobject = $DB->get_record('user', array('id' => $uid));
    $commentforuser = $DB->get_field('block_cmanager_config', 'value', array('varname' => 'commentemailuser'), IGNORE_MULTIPLE);

    if (strlen(trim($commentforuser)) > 0) { // Are there characters in the field.
        $additionalsignature = block_cmanager_convert_tags_to_values($commentforuser, $currentmodinfo);
        $from = $emailsender->email;
        $subject = get_string('emailSubj_userNewComment', 'block_cmanager');
        $messagetext = get_string('emailSubj_Comment', 'block_cmanager') . ":

$message

$additionalsignature
";
        email_to_user($emailinguserobject, $from, $subject, format_text($messagetext), $messagehtml = '', $attachment = '',
                   $attachname = '', true, $replyto = '', $replytoname = '', $wordwrapwidth = 79);

    }
}

/**
 * Email a comment to an admin.
 */
function block_cmanager_email_comment_to_admin($message, $mid, $currentmodinfo) {

    global $emailsender, $DB;

    // Get each admin email.
    $adminemailaddresses = $DB->get_recordset_select('block_cmanager_config', "varname = 'adminemail'");
    // Comment for admin.
    $commentforadmin = $DB->get_field('block_cmanager_config', 'value', array('varname' => 'commentemailadmin'), IGNORE_MULTIPLE);

    if (strlen(trim($commentforadmin)) > 0) { // Are there characters in the field.
        $additionalsignature = block_cmanager_convert_tags_to_values($commentforadmin, $currentmodinfo);

        // Send an email to each admin.
        foreach ($adminemailaddresses as $rec) {
            $to = $rec->value;
            $subject = get_string('emailSubj_adminNewComment', 'block_cmanager');
            $messagetext = get_string('emailSubj_Comment', 'block_cmanager')."

$message

$additionalsignature
";
            block_cmanager_send_email_to_address($to, $subject, format_text($messagetext));
        }
    }
}

/**
 * When a module has been denied, send an email to the admin.
 */
function block_cmanager_send_deny_email_admin($message, $mid, $currentmodinfo) {

    global $emailsender, $DB;

    // Get each admin email.
    $modrecords = $DB->get_records('block_cmanager_config', array('varname' => 'adminemail'));
    $adminemail = $DB->get_record('block_cmanager_config', array('varname' => 'modulerequestdeniedadmin'));
    if (strlen(trim($adminemail->value)) > 0) { // Are there characters in the field.

        // Send an email to each admin.
        foreach ($modrecords as $rec) {
            $to = $rec->value;

            $from = $emailsender->email;
            $subject = get_string('emailSubj_adminDeny', 'block_cmanager');

            $messagetext = $message;
            $messagetext .= '';

            $messagetext .= block_cmanager_convert_tags_to_values($adminemail->value, $currentmodinfo);
            block_cmanager_send_email_to_address($to, $subject, format_text($messagetext));

        } // End loop.

    } // End if.

} // End function.


/**
 * Once a module has been denied, send an email to the user.
 */
function block_cmanager_send_deny_email_user($message, $userid, $mid, $currentmodinfo) {

    global $emailsender, $DB;

    $emailinguserobject = $DB->get_record('user', array('id' => $userid));
    $from = $emailsender->email;
    $subject = get_string('emailSubj_userDeny', 'block_cmanager');
    $useremail = $DB->get_record('block_cmanager_config', array('varname' => 'modulerequestdenieduser'));

    if (strlen(trim($useremail->value)) > 0) {// Are there characters in the field.
        $messagetext = $message;
        $messagetext .= '';
        $messagetext .= block_cmanager_convert_tags_to_values($useremail->value, $currentmodinfo);
        email_to_user($emailinguserobject, $from, $subject, format_text($messagetext), $messagehtml = '', $attachment = '',
                $attachname = '', true, $replyto = '', $replytoname = '', $wordwrapwidth = 79);
    }

}

/**
 * When a lecturer requests control of a module.
 */
function block_cmanager_handover_email_lecturers($courseid, $currentuserid, $custommessage) {

    global $USER, $CFG, $emailsender, $DB;
    $teacherids = '';

    // Send an email to the module owner.
    // Get a list of all the lecturers.
    if (!$course = $DB->get_record("course", array('id' => $courseid))) {
        error("That's an invalid course id");
    }

    // Get the teacher ids.
    $teacherids = block_cmanager_get_lecturer_ids_space_sep($courseid);

    // Collect info on the person who made the request.
    $requester = $DB->get_record('user', array('id' => $currentuserid));
    $requesteremail = $requester->email;

    $teacherids;
    $assignedlectureremails = '';

    // For each teacher id, email them.
    $idarray = explode(" ", $teacherids);

    // Email each of the people who are associated with the course.
    $adminemail = $DB->get_record('block_cmanager_config', array('varname' => 'handoveruser'));

    if (!empty((trim($adminemail->value)))) { // Are there characters in the field?
        $customsig = $adminemail->value;
        foreach ($idarray as $singleid) {
            if (empty($singleid)) {
                continue;
            }
            $emailinguserobject = $DB->get_record('user', array('id' => $singleid));
            $assignedlectureremails .= ' ' . $emailinguserobject->email;
            $from = $emailsender->email;
            $subject = get_string('emailSubj_teacherHandover', 'block_cmanager');
            $messagetext = PHP_EOL . PHP_EOL . get_string('emailSubj_pleasecontact', 'block_cmanager') . ': ' . $requesteremail
                    . PHP_EOL . PHP_EOL . $custommessage . PHP_EOL . PHP_EOL . $customsig;
            email_to_user($emailinguserobject, $from, $subject, format_text($messagetext), $messagehtml = '', $attachment = '',
                    $attachname = '', $usetrueaddress = true, $replyto = '', $replytoname = '', $wordwrapwidth = 79);
        }
    }

    // Email the person who made the request.
    $currentuseremailinguserobject = $DB->get_record('user', array('id' => $USER->id));
    $adminemail = $DB->get_record('block_cmanager_config', array('varname' => 'handovercurrent'));

    if (strlen(trim($adminemail->value)) > 0) { // Are there characters in the field.
        $customsig = $adminemail->value;
        $from = $emailsender->email;
        $subject = get_string('emailSubj_teacherHandover', 'block_cmanager');
        $messagetext = PHP_EOL . get_string('emailSubj_mailSent1', 'block_cmanager') . ': ' . $assignedlectureremails
                . PHP_EOL . PHP_EOL . $custommessage .  PHP_EOL .  PHP_EOL . $customsig . PHP_EOL;

        email_to_user($currentuseremailinguserobject, $from, $subject, format_text($messagetext), $messagehtml = '',
                $attachment = '', $attachname = '', $usetrueaddress = true, $replyto = '', $replytoname = '', $wordwrapwidth = 79);
    }

    // Send an email to each admin.

    $wherequery = "varname = 'adminemail'";
    $modrecords = $DB->get_recordset_select('block_cmanager_config', $wherequery);

    foreach ($modrecords as $rec) {
        $to = $rec->value;
        $from = $emailsender->email;
        $subject = get_string('emailSubj_teacherHandover', 'block_cmanager');

        $adminemail = $DB->get_record('block_cmanager_config', array('varname' => 'handoveradmin'));

        if (strlen(trim($adminemail->value)) > 0) { // Are there characters in the field.
            $customsig = $adminemail->value;
            $messagetext = PHP_EOL .  PHP_EOL . $custommessage . PHP_EOL
                    . get_string('emailSubj_teacherHandover', 'block_cmanager') . ': ' . $requesteremail . PHP_EOL
                    . $customsig . PHP_EOL;
            $headers = get_string('emailSubj_From', 'block_cmanager') . $from;
            $userobj = new stdClass();
            $userobj->email = $to;

            block_cmanager_send_email_to_address($to, $subject, format_text($messagetext));
        }
    }
}
