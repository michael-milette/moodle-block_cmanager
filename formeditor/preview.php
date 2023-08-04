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

$context = context_system::instance();
if (!has_capability('block/cmanager:viewconfig', $context)) {
    throw new \moodle_exception('cannotviewrecords', 'block_cmanager');
}

require_once('../validate_admin.php');

require_once("$CFG->libdir/formslib.php");

$PAGE->navbar->ignore_active();
$PAGE->navbar->add(get_string('cmanagerDisplay', 'block_cmanager'), new moodle_url('/blocks/cmanager/cmanager_admin.php'));
$PAGE->navbar->add(get_string('configurecoursemanagersettings', 'block_cmanager'),
        new moodle_url('/blocks/cmanager/cmanager_confighome.php'));
$PAGE->navbar->add(get_string('formpage2builder', 'block_cmanager'),
        new moodle_url('/blocks/cmanager/formeditor/form_builder.php'));
$PAGE->navbar->add(get_string('previewform', 'block_cmanager'));
$mid = optional_param('id', '', PARAM_INT);
$PAGE->set_url('/blocks/cmanager/formeditor/preview.php', ['id' => $mid]);
$PAGE->set_context(context_system::instance());
$PAGE->set_heading(get_string('formBuilder_previewHeader', 'block_cmanager'));
$PAGE->set_title(get_string('formBuilder_previewHeader', 'block_cmanager'));
echo $OUTPUT->header();

if (!empty($mid)) {
    $formid = $mid;
} else {
    echo 'Error: No ID specified.';
    die;
}

?>
<script>
function goBack(){
    window.location ="form_builder.php";
}
</script>
<?php
/**
 * cmanager new course form.
 *
 * Preview form
 * @package    block_cmanager
 * @copyright  2018 Kyle Goslin, Daniel McSweeney
 * @copyright  2023 TNG Consulting Inc.
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_cmanager_preview_form extends moodleform {

    public function definition() {
        global $CFG;
        global $USER, $DB;
        global $formid;

        $mform =& $this->_form; // Don't forget the underscore!

        $fieldnamecounter = 1; // This counter is used to increment the naming conventions of each field.

        // Back Button.
        $mform->addElement('html', '<p><a class="btn btn-default" href="form_builder.php"><img src="../icons/back.png"/> '
                . get_string('back', 'block_cmanager').'</a></p>');

        // Page description text.
        $mform->addElement('html', '<p>'.get_string('formBuilder_previewInstructions1', 'block_cmanager').'</p><p>'
                . get_string('formBuilder_previewInstructions2', 'block_cmanager').'</p>');

        $mform->addElement('html', '<h2>' . get_string('formBuilder_step2', 'block_cmanager') . '</h2>');

        $selectquery = "";
        $formfields = $DB->get_records('block_cmanager_formfields', array('formid' => $formid), 'position ASC');

        foreach ($formfields as $field) {

            $fieldname = 'f' . $fieldnamecounter; // Give each field an incremented fieldname.

            switch ($field->type) {
                case 'textfield':
                    block_cmanager_create_textfield(format_string($field->lefttext), $mform, $fieldname, $field->reqfield);
                    break;
                case 'textarea':
                    block_cmanager_create_textarea(format_string($field->lefttext), $mform, $fieldname, $field->reqfield);
                    break;
                case 'dropdown':
                    block_cmanager_create_dropdown(format_string($field->lefttext), $field->id, $mform, $fieldname,
                            $field->reqfield);
                    break;
                case 'radio':
                    block_cmanager_create_radio(format_string($field->lefttext), $field->id, $mform, $fieldname, $field->reqfield);
                    break;
            }
            $fieldnamecounter++;
        }
    }
}

/**
 * Create a text field.
 *
 * @return void
 */
function block_cmanager_create_textfield($lefttext, $form, $fieldname, $reqfield) {

    $form->addElement('text', $fieldname, $lefttext, '');
    $form->setType($fieldname, PARAM_TEXT);
    if ($reqfield == 1) {
        $form->addRule($fieldname, '', 'required', null, 'server', false, false);
    }
}

/**
 * Create text area.
 *
 * @return void
 */
function block_cmanager_create_textarea($lefttext, $form, $fieldname, $reqfield) {

    $form->addElement('textarea', $fieldname, $lefttext, 'wrap="virtual" rows="5" cols="60"');
    $form->setType($fieldname, PARAM_TEXT);
    if ($reqfield == 1) {
        $form->addRule($fieldname, '', 'required', null, 'server', false, false);
    }
}

/**
 * Create a radio button.
 *
 * @return void
 */
function block_cmanager_create_radio($lefttext, $id, $form, $fieldname, $reqfield) {
    global $DB;

    $form->setType($fieldname, PARAM_TEXT);
    $selectquery = "fieldid = '$id'";
    $field3items = $DB->get_recordset_select('block_cmanager_form_data', $select = $selectquery);

    $attributes = '';
    $radioarray = [];
    foreach ($field3items as $item) {
        $radioarray[] = $form->createElement('radio', $fieldname, '', $item->value,  $item->value, $attributes);
    }
    $form->addGroup($radioarray, $fieldname, $lefttext, [(count($radioarray) > 1 ? '<br>' : '')], false);
    if ($reqfield == 1) {
        $form->addRule($fieldname, '', 'required', null, 'server', false, false);
    }
}

/**
 * Create a Moodle form dropdown menu.
 *
 * @return void
 */
function block_cmanager_create_dropdown($lefttext, $id, $form, $fieldname, $reqfield) {

    global $DB;

    $options = array();
    $form->setType($fieldname, PARAM_TEXT);
    $selectquery = "fieldid = '$id'";

    $field3items = $DB->get_recordset_select('block_cmanager_form_data', $select = $selectquery);

    foreach ($field3items as $item) {
        $value = $item->value;
        if ($value != '') {
            $options[$value] = format_string($value);
        }
    }

    $form->addElement('select', $fieldname, $lefttext , $options);
    if ($reqfield == 1) {
        $form->addRule($fieldname, get_string('preview_modmode', 'block_cmanager'), 'required', null, 'server', false, false);
    }

}

$mform = new block_cmanager_preview_form();

$mform->focus();
$mform->display();
echo $OUTPUT->footer();
