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

require_once("../../../config.php");
global $CFG, $DB;
require_once("$CFG->libdir/formslib.php");
require_login();
require_once('../validate_admin.php');

$PAGE->set_url('/blocks/cmanager/history/delete.php');
$PAGE->set_context(context_system::instance());

// Navigation Bar.
$PAGE->navbar->ignore_active();
$PAGE->navbar->add(get_string('cmanagerDisplay', 'block_cmanager'), new moodle_url('/blocks/cmanager/cmanager_admin.php'));
$PAGE->navbar->add(get_string('configurecoursemanagersettings', 'block_cmanager'),
        new moodle_url('/blocks/cmanager/cmanager_confighome.php'));
$PAGE->navbar->add(get_string('configureadminsettings', 'block_cmanager'),
        new moodle_url('/blocks/cmanager/cmanager_adminsettings.php'));
$PAGE->navbar->add(get_string('historynav', 'block_cmanager'));

$type = optional_param('delete', '', PARAM_TEXT);
switch ($type) {
    case 'all':
        $pagetitle = get_string('deleteAllRequests', 'block_cmanager');
        break;
    case 'archonly':
        $pagetitle = get_string('deleteOnlyArch', 'block_cmanager');
        break;
    default:
        $pagetitle = get_string('pluginname', 'block_cmanager');
}
$PAGE->set_heading($pagetitle);
$PAGE->set_title($pagetitle);
echo $OUTPUT->header();

/**
 * DELETE
 *
 * Delete a record
 * @package    block_cmanager
 * @copyright  2018 Kyle Goslin, Daniel McSweeney
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_cmanager_delete_form extends moodleform {

    public function definition() {

        $mform =& $this->_form;

        if (isset($_GET['delete'])) {
            $type = required_param('delete', PARAM_TEXT);

            // Back Button.
            $cancelbutton = ' &nbsp; <a class="btn btn-default" href="../cmanager_adminsettings.php">'
                    . get_string('cancel') . '</a>';

            if ($type == 'all') {
                $mform->addElement('html', '<p>' . get_string('sureDeleteAll', 'block_cmanager') . '</p>');
                $mform->addElement('html', '<p><input class="btn btn-primary" type="submit" value="'
                        . get_string('yesDeleteRecords', 'block_cmanager') . '" name="deleteall">' . $cancelbutton . '</p>');
            } else if ($type == 'archonly') {
                $mform->addElement('html', '<p>' . get_string('sureOnlyArch', 'block_cmanager'). '</p>');
                $mform->addElement('html', '<p><input class="btn btn-primary" type="submit" value="'
                        . get_string('yesDeleteRecords', 'block_cmanager') .'" name="archonly">' . $cancelbutton . '</p>');
            }
        }

        if (isset($_POST['deleteall']) || isset($_POST['archonly'])) {
            $mform->addElement('html', '<p>'.get_string('recordsHaveBeenDeleted', 'block_cmanager')
                    . '<br>&nbsp<p></p>&nbsp<p></p><a href="../cmanager_adminsettings.php">'
                    . get_string('clickHereToReturn', 'block_cmanager') . '</a></p>');
        }

    }

}

$mform = new block_cmanager_delete_form();

if (isset($_POST['deleteall'])) {

    $DB->delete_records('block_cmanager_records', array('status' => 'COMPLETE'));
    $DB->delete_records('block_cmanager_records', array('status' => 'REQUEST DENIED'));
    $DB->delete_records('block_cmanager_records', array('status' => 'PENDING'));
    $DB->delete_records('block_cmanager_records', array('status' => null));

} else if (isset($_POST['archonly'])) {

    $DB->delete_records('block_cmanager_records', array('status' => 'COMPLETE'));
    $DB->delete_records('block_cmanager_records', array('status' => 'REQUEST DENIED'));
    $DB->delete_records('block_cmanager_records', array('status ' => null));
}

$mform->focus();
$mform->set_data($mform);
$mform->display();

echo $OUTPUT->footer();
