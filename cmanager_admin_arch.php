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
require_once('../../course/lib.php');
require_once('lib/displayLists.php');
require_once('lib/boot.php');

// Navigation Bar.
$PAGE->navbar->ignore_active();
$PAGE->navbar->add(get_string('cmanagerDisplay', 'block_cmanager'), new moodle_url('/blocks/cmanager/cmanager_admin.php'));
$PAGE->navbar->add(get_string('allarchivedrequests', 'block_cmanager'));

$PAGE->set_url('/blocks/cmanager/cmanager_admin.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_heading(get_string('allarchivedrequests', 'block_cmanager'));
$PAGE->set_title(get_string('allarchivedrequests', 'block_cmanager'));
echo $OUTPUT->header();

$context = context_system::instance();

if (!has_capability('block/cmanager:approverecord', $context)) {
    throw new \moodle_exception('cannotviewrecords', 'block_cmanager');
}

?>
<script src="js/jquery/jquery-3.3.1.min.js"></script>
<script src="js/jquery/jquery-ui.1.12.1.min.js"></script>
<script type="text/javascript">
var deleteRec = 0;
function cancelConfirm(id,langString) {
    deleteRec = id;
    console.log("deleting rec" + deleteRec);
    $("#delete_modal").modal();
}

/**
 * This function is used to save the text from the categories when they are changed.
 */
function saveChangedCategory(fieldvalue, recordId){

    $.post("ajax_functions.php", { type: 'updatecategory', value: fieldvalue, recId: recordId },
        function(data) {
            alert("Changes have been saved!");
        });
}

</script>
<style>
    tr:nth-child(odd) { background-color:#eee; }
    tr:nth-child(even) { background-color:#fff; }
</style>
<?php

/**
 * Admin Arch.
 *
 * Display admin arch page.
 * @package    block_cmanager
 * @copyright  2018 Kyle Goslin, Daniel McSweeney
 * @copyright  2021-2023 TNG Consulting Inc.
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_cmanager_adminarch_form extends moodleform {

    public function definition() {
        global $CFG;
        global $DB;
        $mform =& $this->_form; // Don't forget the underscore.

        $selectquery = "status = 'PENDING' ORDER BY id ASC";

        // If search is enabled then use the search parameters.
        if ($_POST && isset($_POST['search'])) {
            $searchtext = required_param('searchtext', PARAM_TEXT);
            $searchtype = required_param('searchtype', PARAM_TEXT);

            if (!empty($searchtext) && !empty($searchtype)) {

                if ($searchtype == 'code') {
                    $selectquery = "modcode LIKE '%{$searchtext}%'";
                } else if ($searchtype == 'title') {
                    $selectquery = "modname LIKE '%{$searchtext}%'";
                } else if ($searchtype == 'requester') {
                    $selectquery = "createdbyid = (SELECT id FROM ".$CFG->prefix."user WHERE firstname LIKE '%{$searchtext}%'
                        OR lastname LIKE '%{$searchtext}%' OR username LIKE '%{$searchtext}%')";
                }
            }
        }

        echo "
        <script>
        // Open the selected archived request page
        function goToPage(){
            var page = document.getElementById('pageNumber');
            window.location = 'cmanager_admin_arch.php?view=history&p=' + page.value;
        }
        </script>";

        // Arch Requests dropdown.
        $page1fieldname1 = $DB->get_field_select('block_cmanager_config', 'value', "varname=''");
        $page1fieldname2 = $DB->get_field_select('block_cmanager_config', 'value', "varname=page1_fieldname2'");

        $additionalsearchquery = '';

        if ($_POST && isset($_POST['archsearch'])) {
            $archsearchtext = required_param('archsearchtext', PARAM_TEXT);
            $archsearchtype = required_param('archsearchtype', PARAM_TEXT);

            if (!empty($archsearchtext) && !empty($archsearchtype)) {
                if ($archsearchtype == 'code') {
                    $additionalsearchquery = " AND modcode LIKE '%{$archsearchtext}%'";
                } else if ($archsearchtype == 'title') {
                    $additionalsearchquery = " AND modname LIKE '%{$archsearchtext}%'";
                } else if ($archsearchtype == 'requester') {
                    $additionalsearchquery = " AND createdbyid = (SELECT id FROM " . $CFG->prefix . "user"
                        . " WHERE firstname LIKE '%{$archsearchtext}%'"
                        . " OR lastname LIKE '%{$archsearchtext}%'"
                        . " OR username LIKE '%{$archsearchtext}%')";
                }
            }
        }

        $numberofrecords = $DB->count_records_sql("SELECT count(id) FROM " . $CFG->prefix .
                "block_cmanager_records WHERE status = 'COMPLETE' OR status = 'REQUEST DENIED'" . $additionalsearchquery);
        $numberofpages = ceil($numberofrecords / 10) - 1;

        $selectedoption = '';
        $archrequestsdropdown = ' <br>View Page: <select onchange="goToPage();" name="pageNumber" id="pageNumber">';

        $i = 1;
        while ($i <= $numberofpages) {
            if (isset($_GET['p'])) {
                if (required_param('p', PARAM_INT) == $i) {
                    $selectedoption = 'selected = "yes"';
                }
            }
            $archrequestsdropdown .= '<option ' .$selectedoption .' value="' . $i. '">' . $i. '</option>';
            $i++;
            $selectedoption = '';
        }

        if (!($numberofrecords % 2)) {
            if (isset($_GET['p'])) {
                if (required_param('p', PARAM_INT) == $i) {
                    $selectedoption = 'selected = "yes"';
                }
            }
            $archrequestsdropdown .= '<option '. $selectedoption.'="' . $i. '"> ' . $i.'</option>';
        }

        $archrequestsdropdown .= '</select>';

        // If a page number is selected.
        if (isset($_GET['p'])) {
            $selectedpagenumber = required_param('p', PARAM_INT);
            $fromlimit = ($selectedpagenumber - 1) * 10;
            $tolimit = $fromlimit + 10;
        } else {
            $fromlimit = 0;
            $tolimit = 10;
        }

        $pendinglist = $DB->get_records_sql("SELECT * FROM ". $CFG->prefix ."block_cmanager_records
                WHERE status = 'COMPLETE' OR status = 'REQUEST DENIED'" . $additionalsearchquery . "
                order by id desc LIMIT $fromlimit, $tolimit");

        $outputhtml = '';
        $outputhtml .= $archrequestsdropdown;
        $outputhtml .= '<h2>' . get_string('archivedrequests', 'block_cmanager') . '</h2>';
        $outputhtml .= block_cmanager_display_admin_list($pendinglist, true, false, false, 'admin_arch');
        $mform->addElement('html', $outputhtml);

    } // Close the function.

}  // Close the class.

$mform = new block_cmanager_adminarch_form();

if ($_POST && isset($_POST['archsearch'])) {

    $archsearchtext = required_param('archsearchtext', PARAM_TEXT);
    $archsearchtype = required_param('archsearchtype', PARAM_TEXT);

    echo "<script>document.getElementById('archsearchtext').value = '$archsearchtext'; ";
    echo "
        var desiredValue = '$archsearchtype';
        var el = document.getElementById('archsearchtype');
        for(var i=0; i<el.options.length; i++) {
          if ( el.options[i].value == desiredValue ) {
            el.selectedIndex = i;
            break;
          }
        }
        </script>
    ";
}

$mform->focus();
$mform->display();
echo $OUTPUT->footer();

// Modal for deleting requests.
echo generategenericconfirm('delete_modal', get_string('alert', 'block_cmanager') ,
        get_string('configure_delete', 'block_cmanager'),
        get_string('yesDeleteRecords', 'block_cmanager'));

?>
<script>
// Delete request ok button click handler.
$("#okdelete_modal").click(function() {
 window.location = "deleteRequest.php?t=adminarch&&id=" + deleteRec;
});
</script>
<script src="js/bootstrap.min.js"/>
