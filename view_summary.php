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
require_once('lib/displayLists.php');

// Navigation Bar.
$PAGE->navbar->ignore_active();
$PAGE->navbar->add(get_string('cmanagerDisplay', 'block_cmanager'), new moodle_url('/blocks/cmanager/module_manager.php'));
$PAGE->navbar->add(get_string('viewsummary', 'block_cmanager'));

$PAGE->set_url('/blocks/cmanager/view_summary.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_heading(get_string('viewsummary', 'block_cmanager'));
$PAGE->set_title(get_string('viewsummary', 'block_cmanager'));
echo $OUTPUT->header();

if (isset($_GET['id'])) {
    $mid = required_param('id', PARAM_INT);
    $_SESSION['mid'] = $mid;
} else {
    $mid = $_SESSION['mid'];
}

/**
 * Course request form
 *
 * @package    block_cmanager
 * @copyright  2018 Kyle Goslin, Daniel McSweeney
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_cmanager_view_summary_form extends moodleform {

    public function definition() {

        global $mid;

        $rec = $DB->get_record('block_cmanager_records', array('id' => $mid));
        $mform =& $this->_form; // Don't forget the underscore!

        // Page description text.
        $mform->addElement('html', '<p><a href="module_manager.php" class="btn btn-default"><img src="icons/back.png" alt=""> '
                . get_string('back', 'block_cmanager') . '</a></p>');

        $rec = $DB->get_recordset_select('block_cmanager_records', 'id = ' . $mid);
        $displaymodhtml = block_cmanager_display_admin_list($rec, false, false, false, '');

        $mform->addElement('html', '' . $displaymodhtml . '');
        $mform->addElement('html', '<p></p>&nbsp;');
        $wherequery = "instanceid = '$mid' ORDER BY id DESC";
        $modrecords = $DB->get_recordset_select('block_cmanager_comments', $wherequery);
        $htmloutput = '';

        foreach ($modrecords as $record) {
            // Get the username of the person.
            $username = $DB->get_field('user', 'username', array('id' => $record->createdbyid));

            $htmloutput .= '<tr>';
            $htmloutput .= ' <td>' . $record->dt . '</td>';
            $htmloutput .= ' <td>' . $record->message . '</td>';
            $htmloutput .= ' <td>' . $username .'</td>';
            $htmloutput .= '</tr>';
        }

        $mform->addElement('html', '<h2>' . get_string('comments') . '</h2>');
        $mform->addElement('html', '
            <table class="table-striped w-75">
                <tr>
                    <th>'.get_string('comments_date', 'block_cmanager').'</td>
                    <th>'.get_string('comments_message', 'block_cmanager').'</td>
                    <th>'.get_string('comments_from', 'block_cmanager').'</td>
                </tr>
                ' . $htmloutput . '
            </table>
        ');
    }
}

$mform = new block_cmanager_view_summary_form();

if (!$mform->is_cancelled() || empty($fromform = $mform->get_data())) {
    $mform->focus();
    $mform->set_data($mform);
    $mform->display();
    echo $OUTPUT->footer();
}
