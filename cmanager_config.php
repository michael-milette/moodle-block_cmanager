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

require_once("../../config.php");
global $CFG, $DB;
require_once("$CFG->libdir/formslib.php");

require_login();
require_once('validate_admin.php');
require_once('lib/boot.php');

$PAGE->set_url('/blocks/cmanager/cmanager_config.php');
$PAGE->set_context(context_system::instance());

// Navigation Bar.
$PAGE->navbar->ignore_active();
$PAGE->navbar->add(get_string('cmanagerDisplay', 'block_cmanager'), new moodle_url('/blocks/cmanager/cmanager_admin.php'));
$PAGE->navbar->add(get_string('configurecoursemanagersettings', 'block_cmanager'),
        new moodle_url('/blocks/cmanager/cmanager_confighome.php'));
$PAGE->navbar->add(get_string('emailConfig', 'block_cmanager'));
$PAGE->set_heading(get_string('configureemailsettings', 'block_cmanager'));
$PAGE->set_title(get_string('configureemailsettings', 'block_cmanager'));
echo $OUTPUT->header();


$context = context_system::instance();
if (!has_capability('block/cmanager:viewconfig', $context)) {
    throw new \moodle_exception('cannotviewconfig', 'block_cmanager');
}

?>
<head>
<link rel="stylesheet" type="text/css" href="styles.css" />
<script src="js/jquery/jquery-3.3.1.min.js"></script>
<script src="js/jquery/jquery-ui.1.12.1.min.js"></script>
<script>
function cancelConfirm(i,langString) {
    var answer = confirm(langString)
    if (answer) {
        window.location = "cmanager_config.php?t=d&&id=" + i;
    }
}

/**
 * This function is used to save the text from the
 * list of textareas using ajax.
 */
function saveChangedText(object, idname, langString){

    var fieldvalue = object.value;

    $.post("ajax_functions.php", { type: 'updatefield', value: fieldvalue, id: idname },
        function(data) {
            $("#saved").modal();
        }
    );
}
</script>
</head>

<?php
// If any records were set to be deleted.
if (isset($_GET['t']) && isset($_GET['id'])) {
    if (required_param('t', PARAM_TEXT) == 'd') {
        $deleteid = required_param('id', PARAM_INT);
        // Delete the record.
        $deletequery = "id = $deleteid";
        $DB->delete_records_select('block_cmanager_config', $deletequery);
        echo "<script>window.location='cmanager_config.php';</script>";
    }
}

/**
 * Config form form cmanager
 *
 * @package    block_cmanager
 * @copyright  2018 Kyle Goslin, Daniel McSweeney
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_cmanager_config_form extends moodleform {

    public function definition() {
        global $DB;

        $mform =& $this->_form; // Don't forget the underscore!

        // Back Button.
        $mform->addElement('html', '<p><a href="cmanager_confighome.php" class="btn btn-default"><img src="icons/back.png" alt=""> '
                . get_string('back', 'block_cmanager') .'</a></p>');

        // Email text box.
        $approvedtextrecord = $DB->get_record('block_cmanager_config', array('varname' => 'approved_text'));

        $emailtext = '';
        if ($approvedtextrecord != null) {
            $emailtext = $approvedtextrecord->value;
        }

        // Approved user email.
        $approveduseremail = $DB->get_record('block_cmanager_config', array('varname' => 'approveduseremail'));
        $approveduseremailvalue = '';
        if (!empty($approveduseremail)) {
            $approveduseremailvalue = stripslashes($approveduseremail->value);
        }

        // Approved admin email.
        $approvedadminemail = $DB->get_record('block_cmanager_config', array('varname' => 'approvedadminemail'));
        $approvedadminemailvalue = '';
        if (!empty($approvedadminemail)) {
            $approvedadminemailvalue = stripslashes($approvedadminemail->value);
        }

        // Request new module user.
        $requestnewmoduleuser = $DB->get_record('block_cmanager_config', array('varname' => 'requestnewmoduleuser'));
        $requestnewmoduleuservalue = '';
        if (!empty($requestnewmoduleuser)) {
            $requestnewmoduleuservalue = stripslashes($requestnewmoduleuser->value);
        }

        // Request new module admin.
        $requestnewmoduleadmin = $DB->get_record('block_cmanager_config', array('varname' => 'requestnewmoduleadmin'));
        $requestnewmoduleadminvalue = '';
        if (!empty($requestnewmoduleadmin)) {
            $requestnewmoduleadminvalue = stripslashes($requestnewmoduleadmin->value);
        }

        // Comment email admin.
        $commentemailadmin = $DB->get_record('block_cmanager_config', array('varname' => 'commentemailadmin'));
        $commentemailadminvalue = '';
        if (!empty($commentemailadmin)) {
            $commentemailadminvalue = stripslashes($commentemailadmin->value);
        }

        // Comment email user.
        $commentemailuser = $DB->get_record('block_cmanager_config', array('varname' => 'commentemailuser'));
        $commentemailuservalue = '';
        if (!empty($commentemailuser)) {
            $commentemailuservalue = stripslashes($commentemailuser->value);
        }

        // Request denied admin.
        $modulerequestdeniedadmin = $DB->get_record('block_cmanager_config', array('varname' => 'modulerequestdeniedadmin'));
        $modulerequestdeniedadminvalue = '';
        if (!empty($modulerequestdeniedadmin)) {
            $modulerequestdeniedadminvalue = stripslashes($modulerequestdeniedadmin->value);
        }

        // Request denied user.
        $modulerequestdenieduser = $DB->get_record('block_cmanager_config', array('varname' => 'modulerequestdenieduser'));
        $modulerequestdenieduservalue = '';
        if (!empty($modulerequestdenieduser)) {
            $modulerequestdenieduservalue = stripslashes($modulerequestdenieduser->value);
        }

        // Handover current.
        $handovercurrent = $DB->get_record('block_cmanager_config', array('varname' => 'handovercurrent'));
        $handovercurrentvalue = '';
        if (!empty($handovercurrent)) {
            $handovercurrentvalue = stripslashes($handovercurrent->value);
        }

        // Handover user.
        $handoveruser = $DB->get_record('block_cmanager_config', array('varname' => 'handoveruser'));
        $handoveruservalue = '';
        if (!empty($handoveruser)) {
            $handoveruservalue = stripslashes($handoveruser->value);
        }

        // Handover admin.
        $handoveradmin = $DB->get_record('block_cmanager_config', array('varname' => 'handoveradmin'));
        $handoveradminvalue = '';
        if (!empty($handoveradmin)) {
            $handoveradminvalue = stripslashes($handoveradmin->value);
        }

        $wherequery = "varname = 'admin_email'";
        $modrecords = $DB->get_recordset_select('block_cmanager_config', $wherequery);

        // Get the current values for naming and autoKey from the database and use in the setting of seleted values for dropdowns.
        $autokey = $DB->get_field_select('block_cmanager_config', 'value', "varname = 'autoKey'");
        $naming = $DB->get_field_select('block_cmanager_config', 'value', "varname = 'naming'");
        $snaming = $DB->get_field_select('block_cmanager_config', 'value', "varname = 'snaming'");
        $emailsender = $DB->get_field_select('block_cmanager_config', 'value', "varname = 'emailSender'");

        $selfcat = $DB->get_field_select('block_cmanager_config', 'value', "varname = 'selfcat'");

        $fragment1 = '
            <h3>' . get_string('emailConfigSectionHeader', 'block_cmanager') . '</h3>
            <p>' . get_string('emailConfigInfo', 'block_cmanager') . '</p>
            <div class="row">
                <div class="col-sm-1">' . get_string('config_addemail', 'block_cmanager') . '</div>
                <div class="col-sm-3">';

        foreach ($modrecords as $record) {
            $fragment1 .= '<div class="row">';
            $fragment1 .= '<div class="col-sm-9">' . $record->value . '</div>';
            $fragment1 .= '<div class="col-sm-3"><a onclick="cancelConfirm(' . $record->id . ', \''
                    . get_string('configure_deleteMail', 'block_cmanager') . '\')" href="#" aria-label="'
                    . get_string('formBuilder_confirmDelete', 'block_cmanager') . '" title="'
                    . get_string('formBuilder_confirmDelete', 'block_cmanager')
                    . '"><i class="icon fa fa-trash fa-fw" aria-hidden="true"></i></a></div>';
            $fragment1 .= '</div>';
        }
        $fragment1 .= '
                    <div class="row">
                        <div class="col-sm-12">
                            <input type="text" name="newemail" id="newemail"/>
                            <input class="btn btn-default" type="submit" name="addemailbutton" id="addemailbutton" value="'
                            . get_string('SaveEMail', 'block_cmanager') . '">
                        </div>
                    </div>
                </div>
            </div>

            <h3 class="mt-4">' . get_string('emailConfigContents', 'block_cmanager') . '</h3>
            <p>' . get_string('emailConfigHeader', 'block_cmanager') . '</p>
            <ul>
                <li>' . get_string('email_courseCode', 'block_cmanager') . ': <strong>[course_code]</strong></li>
                <li>' . get_string('email_courseName', 'block_cmanager') . ': <strong>[course_name]</strong></li>
                <li>' . get_string('email_enrolmentKey', 'block_cmanager') . ': <strong>[e_key]</strong></li>
                <li>' . get_string('email_fullURL', 'block_cmanager') . ': <strong>[full_link]</strong></li>
                <li>' . get_string('email_sumLink', 'block_cmanager') . ': <strong>[req_link]</strong></li>
            </ul>

            <h3 class="mt-4">' . get_string('email_newCourseApproved', 'block_cmanager') . ' - '
                . get_string('email_UserMail', 'block_cmanager') . '</h3>
                <p>' . get_string('configure_leaveblankmail', 'block_cmanager') . '</p>
                <textarea name="approveduseremail" id="approveduseremail"  style="width:70%; height: 250px;">'
                . $approveduseremailvalue . '</textarea><br>
                <input class="btn btn-default" type="button" value="' . get_string('SaveChanges', 'block_cmanager')
                . '" onClick="saveChangedText(approveduseremail, \'approveduseremail\',\''
                . get_string('ChangesSaved', 'block_cmanager') . '\')"/><br>

            <h3 class="mt-4">' . get_string('email_newCourseApproved', 'block_cmanager') . ' - '
                . get_string('email_AdminMail', 'block_cmanager') . '</h3>
                <p> ' . get_string('configure_leaveblankmail', 'block_cmanager') . '</p>
                <textarea name="approvedadminemail" id="approvedadminemail" style="width:70%; height: 250px;">'
                . $approvedadminemailvalue . '</textarea><br>
                <input class="btn btn-default" type="button" value="' . get_string('SaveChanges', 'block_cmanager')
                . '" onClick="saveChangedText(approvedadminemail, \'approvedadminemail\')"/><br>

            <h3 class="mt-4">' . get_string('email_requestNewModule', 'block_cmanager') . ' - '
                . get_string('email_UserMail', 'block_cmanager') . '</h3>
                <p>' . get_string('configure_leaveblankmail', 'block_cmanager') . '</p>
                <textarea name="requestnewmoduleuser" id="requestnewmoduleuser" style="width:70%; height: 250px;">'
                . $requestnewmoduleuservalue .'</textarea><br>
                <input class="btn btn-default" type="button" value="' . get_string('SaveChanges', 'block_cmanager')
                . '" onClick="saveChangedText(requestnewmoduleuser, \'requestnewmoduleuser\')"/><br>

            <h3 class="mt-4">' . get_string('email_requestNewModule', 'block_cmanager') . ' - '
                . get_string('email_AdminMail', 'block_cmanager') . '</h3>
                <p>' . get_string('configure_leaveblankmail', 'block_cmanager') . '</p>
                <textarea name="requestnewmoduleadmin" id="requestnewmoduleadmin" style="width:70%; height: 250px;">'
                . $requestnewmoduleadminvalue . '</textarea><br>
                <input class="btn btn-default" type="button" value="' . get_string('SaveChanges', 'block_cmanager')
                . '" onClick="saveChangedText(requestnewmoduleadmin, \'requestnewmoduleadmin\')"/>

            <h3 class="mt-4">' . get_string('email_commentNotification', 'block_cmanager') . ' - '
                . get_string('email_AdminMail', 'block_cmanager') . '</h3>
                <p>' . get_string('configure_leaveblankmail', 'block_cmanager') . '</p>
                <textarea name="commentemailadmin" id="commentemailadmin" style="width:70%; height: 250px;">'
                . $commentemailadminvalue . '</textarea><br>
                <input class="btn btn-default" type="button" value="' . get_string('SaveChanges', 'block_cmanager')
                . '" onClick="saveChangedText(commentemailadmin, \'commentemailadmin\')"/>

            <h3 class="mt-4">' . get_string('email_commentNotification', 'block_cmanager') . ' - '
                . get_string('email_UserMail', 'block_cmanager') . '</h3>
                <p>' . get_string('configure_leaveblankmail', 'block_cmanager') . '</p>
                <textarea name="commentemailuser" id="commentemailuser" style="width:70%; height: 250px;">'
                . $commentemailuservalue . '</textarea><br>
                <input class="btn btn-default" type="button" value="' . get_string('SaveChanges', 'block_cmanager')
                . '" onClick="saveChangedText(commentemailuser, \'commentemailuser\')"/>

            <h3 class="mt-4">' . get_string('email_requestDenied', 'block_cmanager') . ' - '
                . get_string('email_AdminMail', 'block_cmanager') . '</h3>
                <p>' . get_string('configure_leaveblankmail', 'block_cmanager') . '</p>
                <textarea name="modulerequestdeniedadmin" id="modulerequestdeniedadmin" style="width:70%; height: 250px;">'
                . $modulerequestdeniedadminvalue . '</textarea><br>
                <input class="btn btn-default" type="button" value="' . get_string('SaveChanges', 'block_cmanager')
                . '" onClick="saveChangedText(modulerequestdeniedadmin, \'modulerequestdeniedadmin\')"/>

            <h3 class="mt-4">' . get_string('email_requestDenied', 'block_cmanager') . ' - '
                . get_string('email_UserMail', 'block_cmanager') . '</h3>
                <p>' . get_string('configure_leaveblankmail', 'block_cmanager') . '</p>
                <textarea name="modulerequestdenieduser" id="modulerequestdenieduser" style="width:70%; height: 250px;">'
                . $modulerequestdenieduservalue . '</textarea><br>
                <input class="btn btn-default" type="button" value="' . get_string('SaveChanges', 'block_cmanager')
                . '" onClick="saveChangedText(modulerequestdenieduser, \'modulerequestdenieduser\')"/>

            <h3 class="mt-4">' . get_string('email_handover', 'block_cmanager') . ' - '
                . get_string('email_currentOwner', 'block_cmanager') . '</h3>
                <p>' . get_string('configure_leaveblankmail', 'block_cmanager') . '</p>
                <textarea name="handovercurrent" id="handovercurrent" style="width:70%; height: 250px;">'
                . $handovercurrentvalue . '</textarea><br>
                <input class="btn btn-default" type="button" value="' . get_string('SaveChanges', 'block_cmanager')
                . '" onClick="saveChangedText(handovercurrent, \'handovercurrent\')"/>

            <h3 class="mt-4">' . get_string('email_handover', 'block_cmanager') . ' - '
                . get_string('email_UserMail', 'block_cmanager') . '</h3>
                <p>' . get_string('configure_leaveblankmail', 'block_cmanager') . '</p>
                <textarea name="handoveruser" id="handoveruser" style="width:70%; height: 250px;">'
                . $handoveruservalue . '</textarea><br>
                <input class="btn btn-default" type="button" value="' . get_string('SaveChanges', 'block_cmanager')
                . '" onClick="saveChangedText(handoveruser, \'handoveruser\')"/>

            <h3 class="mt-4">' . get_string('email_handover', 'block_cmanager') . ' - '
                . get_string('email_AdminMail', 'block_cmanager') . '</h3>
                <p>' . get_string('configure_leaveblankmail', 'block_cmanager') . '</p>
                <textarea name="handoveradmin" id="handoveradmin" style="width:70%; height: 250px;">'
                . $handoveradminvalue . '</textarea><br>
                <input class="btn btn-default" type="button" value="' . get_string('SaveChanges', 'block_cmanager')
                . '" onClick="saveChangedText(handoveradmin, \'handoveradmin\')"/>
        ';

        $mainslider = '
        <p></p>
        &nbsp;
        <p></p>
        ' . $fragment1 . '

        ';

        // Add the main slider.
        $mform->addElement('html', $mainslider);
    }
}

$mform = new block_cmanager_config_form();

if ($mform->is_cancelled()) {
    echo "<script>window.location='../cmanager_admin.php';</script>";
    die;
} else if (isset($_POST['addemailbutton'])) {
    global $USER, $CFG;

    // Add an email address.
    $postemail = required_param('newemail', PARAM_EMAIL);
    if ($postemail != '' && block_cmanager_validate_email($postemail)) {
        $newrec = new stdClass();
        $newrec->varname = 'admin_email';
        $newrec->value = $postemail;
        $DB->insert_record('block_cmanager_config', $newrec);
    }
    echo "<script>window.location='cmanager_config.php';</script>";
    die;
} else {
    $mform->focus();
    $mform->set_data($mform);
    $mform->display();
    echo $OUTPUT->footer();
}

echo generategenericpop('saved', get_string('ChangesSaved', 'block_cmanager'),
        get_string('ChangesSaved', 'block_cmanager'),
        get_string('ok', 'block_cmanager') );

/**
 * Very basic funciton for validating an email address.
 * This should really be replaced with something a little better!
 */
function block_cmanager_validate_email($email) {

    $valid = true;

    if ($email == '') {
        $valid = false;
    }

    $pos = strpos($email, '.');
    if ($pos === false) {
        $valid = false;
    }

    $pos = strpos($email, '@');
    if ($pos === false) {
        $valid = false;
    }

    return $valid;

}
