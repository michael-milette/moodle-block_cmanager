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
 * @copyright  2021-2022 TNG Consulting Inc., Daniel Keanan
 * @copyright  2023 TNG Consulting Inc.
 * @author     Kyle Goslin, Daniel McSweeney
 * @author     Daniel Keanan
 * @author     Michael Milette
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG, $DB;

require_once("../../config.php");
require_once("$CFG->libdir/formslib.php");
require_once('lib/displayLists.php');
require_login();

// Navigation Bar.
$PAGE->navbar->ignore_active();
$PAGE->navbar->add(get_string('cmanagerDisplay', 'block_cmanager'), new moodle_url('/blocks/cmanager/module_manager.php'));
$PAGE->navbar->add(get_string('myarchivedrequests', 'block_cmanager'));
$PAGE->set_url('/blocks/cmanager/module_manager.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_heading(get_string('myarchivedrequests', 'block_cmanager'));
$PAGE->set_title(get_string('myarchivedrequests', 'block_cmanager'));

echo $OUTPUT->header();
$context = context_system::instance();

// Check permissions.
if (!has_capability('block/cmanager:viewrecord', $context)) {
    throw new \moodle_exception('cannotviewrecords', 'block_cmanager');
}
?>
<script src="js/jquery/jquery-3.3.1.min.js"></script>
<script type="text/javascript">
function cancelConfirm(id,langString) {
    var answer = confirm(langString)
    if (answer) {
        window.location = "deleteRequest.php?id=" + id;
    }
}
</script>

<style>
    tr:nth-child(odd)   { background-color:#eee; }
    tr:nth-child(even)  { background-color:#fff; }
</style>
<?php
/**
 * History manager.
 * The management front end for the modules which have been processed in the past.
 *
 * @package    block_cmanager
 * @copyright  2018 Kyle Goslin, Daniel McSweeney
 * @copyright  2021-2023 TNG Consulting Inc., Daniel Keaman
 * @copyright  2023 TNG Consulting Inc.
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_cmanager_module_manager_history_form extends moodleform {

    public function definition() {

        global $DB, $USER;

        $mform =& $this->_form; // Don't forget the underscore!
        $mform->addElement('html', '<p>' . get_string('cmanagerWelcome', 'block_cmanager') . '</p>');
        $mform->addElement('html', '<p><input class="btn btn-default" type="button" value="'
                . get_string('cmanagerRequestBtn', 'block_cmanager')
                . '" onclick="window.location.href=\'course_request.php?mode=1\'"></p>');

        $uid = $USER->id;

        $selquery = "createdbyid = $uid AND status = 'COMPLETE' OR createdbyid = $uid "
                . "AND status = 'REQUEST DENIED' ORDER BY id DESC";
        $pendinglist = $DB->get_recordset_select('block_cmanager_records', $select = $selquery);

        $outputhtml = '';
        $modshtml = block_cmanager_display_admin_list($pendinglist, true, false, false, 'user_history');

        $outputhtml .= '<div id="existingrequest" style="border-bottom:1px solid black;height:300px;background:transparent"></div>';
        $outputhtml = $modshtml;
        $mform->addElement('html', $outputhtml);

    } // Close the function.

} // Close the class.

$mform = new block_cmanager_module_manager_history_form();

if ($mform->is_cancelled()) {
    echo "<script>window.location='module_manager.php';</script>";
    die;
} else if (empty($fromform = $mform->get_data())) {
    $mform->focus();
    $mform->display();
}

echo $OUTPUT->footer();
