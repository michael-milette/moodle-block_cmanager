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
 * @copyright  2018 Kyle Goslin, Daniel McSweeney (Institute of Technology Blanchardstown)
 * @copyright  2021-2022 TNG Consulting Inc., Daniel Keaman
 * @copyright  2023 TNG Consulting Inc.
 * @author     Kyle Goslin, Daniel McSweeney
 * @author     Daniel Keaman
 * @author     Michael Milette
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("../../../config.php");
global $CFG;
require_once("$CFG->libdir/formslib.php");
require_login();

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url('/blocks/cmanager/admin/comment.php');

// Navigation Bar.
$PAGE->navbar->ignore_active();
$PAGE->navbar->add(get_string('cmanagerDisplay', 'block_cmanager'), new moodle_url('/blocks/cmanager/cmanager_admin.php'));
$PAGE->navbar->add(get_string('currentrequests', 'block_cmanager'), new moodle_url('/cmanager_admin.php'));
$PAGE->navbar->add(get_string('addviewcomments', 'block_cmanager'));
$PAGE->set_heading(get_string('addviewcomments', 'block_cmanager'));
$PAGE->set_title(get_string('addviewcomments', 'block_cmanager'));
echo $OUTPUT->header();

$context = context_system::instance();
if (!has_capability('block/cmanager:addcomment', $context)) {
    throw new \moodle_exception('cannotcomment', 'block_cmanager');
}

if (isset($_GET['id'])) {
    $mid = required_param('id', PARAM_INT);
    $_SESSION['mid'] = $mid;
} else {
    $mid = $_SESSION['mid'];
}

$type = optional_param('type', '', PARAM_TEXT);
if (!empty($type)) {
    $_SESSION['type'] = $type;
} else {
    $type = '';
    $type = $_SESSION['type'];
}

$backlink = '';
if ($type == 'adminarch') {
    $backlink = '../cmanager_admin_arch.php';
} else if ($type == 'adminq') {
    $backlink = '../cmanager_admin.php';
}

$PAGE->set_url('/blocks/cmanager/admin/comment.php', array('id' => $mid));

class block_cmanager_comment_form extends moodleform {

    public function definition() {
        global $mid;
        global $DB;
        global $backlink;

        $mform =& $this->_form; // Don't forget the underscore.

        // Page description text.
        $mform->addElement('html', '<p><a href="' . $backlink . '" class="btn btn-default"><img src="../icons/back.png" alt=""> '
                . get_string('back', 'block_cmanager') . '</a></p>');
        $mform->addElement('html', '<p>' . get_string('comments_Forward', 'block_cmanager') . '.</p>');

        // Add a comment box.
        $mform->addElement('html', '
            <textarea id="newcomment" name="newcomment" rows="5" cols="60"></textarea><br>
            <input class="btn btn-default mt-3" type="submit" value="' . get_string('comments_PostComment', 'block_cmanager') . '">
        ');

        // Previous comments.
        $wherequery = "instanceid = '$mid'  ORDER BY id DESC";
        $modrecords = $DB->get_recordset_select('block_cmanager_comments', $wherequery);
        $htmloutput = '<h2 class="h4 mt-3 p-2" style="border: 1px #000000 solid; width:100%; background: #E0E0E0">'
                . get_string('comments_comment', 'block_cmanager') . '</h2>';
        foreach ($modrecords as $record) {
            $createdbyid = $record->createdbyid;
            $username = $DB->get_field_select('user', 'username', "id = '$createdbyid'");
            $htmloutput .= '<p><strong>' . get_string('comments_date', 'block_cmanager') . ':</strong> ' . $record->dt . '</p>';
            $htmloutput .= '<p><strong>' . get_string('comments_author', 'block_cmanager') . ':</strong> ' . $username . '</p>';
            $htmloutput .= '<p><strong>' . get_string('comments_comment', 'block_cmanager') . ':</strong> ' . $record->message
                    . '</p>';
            $htmloutput .= '<hr>';
        }
        $mform->addElement('html', $htmloutput);
    }
}

// Name of the form you defined in file above.
$mform = new block_cmanager_comment_form();

if ($mform->is_cancelled()) {
    echo "<script>window.location='" . $backlink."';</script>";
    die;
} else if (empty($fromform = $mform->get_data())) {
    $mform->focus();
    $mform->set_data($mform);
    $mform->display();
    echo $OUTPUT->footer();
}
if ($_POST) {
    global $USER, $CFG, $DB, $mid;

    $userid = $USER->id;

    $newrec = new stdClass();
    $newrec->instanceid = $mid;
    $newrec->createdbyid = $userid;
    $newrec->message = $_POST['newcomment'];
    $newrec->dt = date("Y-m-d H:i:s");
    $DB->insert_record('block_cmanager_comments', $newrec, false);

    // Send an email to everyone concerned.
    require_once('../cmanager_email.php');
    $message = required_param('newcomment', PARAM_TEXT);

    // Get all user id's from the record.
    $currentrecord = $DB->get_record('block_cmanager_records', array('id' => $mid));

    $userids = ''; // Used to store all the user IDs for the people we need to email.
    $userids = $currentrecord->createdbyid; // Add the current user.

    // Get info about the current object.

    // Send email to the user.
    $replacevalues = array();
    $replacevalues['[course_code'] = $currentrecord->modcode;
    $replacevalues['[course_name]'] = $currentrecord->modname;
    $replacevalues['[e_key]'] = '';
    $replacevalues['[full_link]'] = $CFG->wwwroot . '/blocks/cmanager/comment.php?id=' . $mid;
    $replacevalues['[loc]'] = '';
    $replacevalues['[req_link]'] = $CFG->wwwroot . '/blocks/cmanager/view_summary.php?id=' . $mid;

    block_cmanager_email_comment_to_user($message, $userids, $mid, $replacevalues);
    block_cmanager_email_comment_to_admin($message, $mid, $replacevalues);

    echo "<script> window.location = 'comment.php?type=" . $type . "&id=$mid';</script>";
}
