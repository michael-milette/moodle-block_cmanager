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

require_login();
require_once('../validate_admin.php');
require_once('../lib/boot.php');

// Navigation Bar.
$PAGE->navbar->ignore_active();
$PAGE->navbar->add(get_string('cmanagerDisplay', 'block_cmanager'), new moodle_url('/blocks/cmanager/cmanager_admin.php'));
$PAGE->navbar->add(get_string('configurecoursemanagersettings', 'block_cmanager'),
        new moodle_url('/blocks/cmanager/cmanager_confighome.php'));
$PAGE->navbar->add(get_string('formpage2builder', 'block_cmanager'));
$PAGE->set_url('/blocks/cmanager/formeditor/form_builder.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_heading(get_string('informationform', 'block_cmanager'));
require_once("$CFG->libdir/formslib.php");
$PAGE->set_title(get_string('informationform', 'block_cmanager'));
echo $OUTPUT->header();

$context = context_system::instance();
if (!has_capability('block/cmanager:viewconfig', $context)) {
    throw new \moodle_exception('cannotviewrecords', 'block_cmanager');
}
?>

<script>
    // From the dropdown menu of different forms that are avilable save the one the user has just selected.
    function saveSelectedForm(){
        window.onbeforeunload = null;
        var value = document.getElementById('selectform').value;

        $.ajaxSetup({async:false});
        $.post("ajax_functions.php", { type: 'saveselectedform', value: value},
                function(data) {
                    window.location = 'form_builder.php';
                // alert(data);
            });
    }

    // Delete a selected from from the list of available forms.
    var formId = 0;
    function deleteSelectedForm(confirmMsg,form){
        formId = form;
        $("#delete_modal").modal();

    }

    // After a user has entered the name for a new form page this function is called when the submit button is clicked.
    function addNewField(){
        window.onbeforeunload = null;
        var value = document.getElementById('newformname').value;
        if(value != ''){
            //$.ajaxSetup({async:false});
            $.post("ajax_functions.php", { type: 'addnewform', value: value},
                function(data) {
                    window.location = 'form_builder.php';
                });
        }
    }
</script>

<?php

if (isset($_GET['del'])) {
    $delid = required_param('del', PARAM_INT);
    $DB->delete_records_select('block_cmanager_config', "id = $delid");
    echo " <script>window.location = 'form_builder.php';</script> ";
}

class block_cmanager_builder_form extends moodleform {

    public function definition() {
        global $DB;
        $mform =& $this->_form; // Don't forget the underscore!

        $mform->addElement('html', '<p><a href="../cmanager_confighome.php" class="btn btn-default"><img src="../icons/back.png"'
                . ' alt=""> '.get_string('back', 'block_cmanager').'</a></p>');

        // Page description text.
        $mform->addElement('html', get_string('formBuilder_instructions', 'block_cmanager').'<ul>'
                . '<li>' . get_string('formBuilder_instructions1', 'block_cmanager') . '</li>'
                . '<li>' . get_string('formBuilder_instructions2', 'block_cmanager') . '</li>'
                . '<li>' . get_string('formBuilder_instructions3', 'block_cmanager') . '</li>'
                . '<li>' . get_string('formBuilder_instructions4', 'block_cmanager') . '</li>'
                . '<li>' . get_string('formBuilder_instructions5', 'block_cmanager') . '</li>'
                . '<li>' . get_string('formBuilder_instructions6', 'block_cmanager') . '</li>'
                . '</ul>');

        $mform->addElement('html', '<h2 class="mt-3">' . get_string('formBuilder_currentActiveForm', 'block_cmanager') . '</h2>');
        $mform->addElement('html', '<div>' . get_string('formBuilder_currentActiveFormInstructions', 'block_cmanager') . '</div>');

        $currentselectedform = $DB->get_field_select('block_cmanager_config', 'value', "varname = 'current_active_form_id'");

        $wherequery = "varname = 'page2form'";
        $formrows = $DB->get_recordset_select('block_cmanager_config', $wherequery);

        $selecthtml = get_string('formBuilder_selectDescription', 'block_cmanager')
                . ' <select onchange="saveSelectedForm()" id="selectform">';
        foreach ($formrows as $row) {
            $selected = '';
            if ($currentselectedform == $row->id) {
                $selected = 'selected';
            }
            $selecthtml .= '    <option '. $selected .' value="' .$row->id . '">' . $row->value.'</option>';
            $selected = '';
        }

        $selecthtml .= '</select>';
        $mform->addElement('html', $selecthtml);

        $wherequery = "varname = 'page2form'";
        $formrecords = $DB->get_recordset_select('block_cmanager_config', $wherequery);

        // Modal for deleting requests.
        $pop = generategenericconfirm('delete_modal', get_string('alert', 'block_cmanager') ,
                get_string('formBuilder_confirmDelete', 'block_cmanager'),
                get_string('formBuilder_deleteForm', 'block_cmanager'));

        // Button click handler.
        $js = '<script>
            // Delete request ok  button click handler.
            $("#okdelete_modal").click(function(){
                window.location = window.location = "form_builder.php?del="+formId;
            });
        </script>';

        $formsitemshtml = $pop . $js;
        foreach ($formrecords as $rec) {
            $formsitemshtml .= '<div class="row">';
            $formsitemshtml .= '<div class="col-6 col-sm-5 col-md-4 col-xl-3">' . $rec->value . '</div>';
            $formsitemshtml .= '<div class="col-6 col-sm-4 col-md-3 col-xl-2">';
            $formsitemshtml .= '<a title="' . get_string('formBuilder_editForm', 'block_cmanager')
                    . '" aria-label="' . get_string('formBuilder_editForm', 'block_cmanager')
                    . '" href="page2.php?id=' . $rec->id . '&name=' . urlencode($rec->value)
                    . '"><i class="icon fa fa-cog fa-fw" aria-hidden="true"></i></a>';
            $formsitemshtml .= ' <a title="' . get_string('formBuilder_previewForm', 'block_cmanager') . '" aria-label="'
                    . get_string('formBuilder_previewForm', 'block_cmanager') . '" href="preview.php?id='
                    . $rec->id . '"><i class="icon fa fa-search-plus fa-fw" aria-hidden="true"></i></a>';
            // Do not offer option to delete active form.
            if ($currentselectedform != $rec->id) {
                $formsitemshtml .= ' <a title="' . get_string('formBuilder_deleteForm', 'block_cmanager')
                        . '" aria-label="' . get_string('formBuilder_deleteForm', 'block_cmanager')
                        . '" href="#" onclick="javascript:deleteSelectedForm(\''
                        . get_string('formBuilder_confirmDelete', 'block_cmanager') . '\','
                        . $rec->id . ');"><i class="icon fa fa-trash fa-fw" aria-hidden="true"></i></a>';
            }
            $formsitemshtml .= '</div>';
            $formsitemshtml .= '</div>';
        }

        $mform->addElement('html', '<h2 class="mt-3">' . get_string('formBuilder_manageFormsText', 'block_cmanager') . '</h2>');

        $mform->addElement('html', '<p>' . get_string('formBuilder_selectAny', 'block_cmanager') . '</p>' . $formsitemshtml . '
            <input type="text" id = "newformname" size="20"> <input class="btn btn-default" type="button" value = "'
                    . get_string('formBuilder_createNewText', 'block_cmanager') . '" onclick="addNewField()">');
    }
}

$mform = new block_cmanager_builder_form(); // Name of the form you defined in file above.

$mform->focus();
$mform->display();
echo $OUTPUT->footer();
