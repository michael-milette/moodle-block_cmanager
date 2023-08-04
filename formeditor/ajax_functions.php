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

$context = context_system::instance();
if (!has_capability('block/cmanager:approverecord', $context)) {
    throw new \moodle_exception('cannotviewconfig', 'block_cmanager');
}

// Check the type of ajax call that has been made to this page and redirect to that function.
$type = required_param('type', PARAM_TEXT);
switch ($type) {
    case 'add':
        block_cmanager_add_new_item();
        break;
    case 'save':
        block_cmanager_save_changes();
        break;
    case 'page2addfield':
        block_cmanager_add_field();
        break;
    case 'updatefield':
        block_cmanager_update_field();
        break;
    case 'addvaluetodropdown':
        block_cmanager_add_value_to_dropdown();
        break;
    case 'getdropdownvalues':
        block_cmanager_get_dropdown_values();
        break;
    case 'addnewform':
        block_cmanager_add_new_form();
        break;
    case 'saveselectedform':
        block_cmanager_save_selected_form();
        break;
    case 'saveoptionalvalue':
        block_cmanager_save_optional_value();
        break;
}

/**
 * Save a selected form
 */
function block_cmanager_save_selected_form() {
    global $DB;

    $value = required_param('value', PARAM_TEXT);
    $rowid = $DB->get_field_select('block_cmanager_config', 'id', "varname = 'current_active_form_id'");

    $dataobject = new stdClass();
    $dataobject->id = $rowid;
    $dataobject->value = addslashes($value);
    $DB->update_record('block_cmanager_config', $dataobject);
}

/**
 * Add a new form
 */
function block_cmanager_add_new_form() {

    global $DB;

    $formname = required_param('value', PARAM_TEXT);

    $object = new stdClass();
    $object->id = '';
    $object->varname = 'page2form';
    $object->value = $formname;

    $id = $DB->insert_record('block_cmanager_config', $object, true);
}

/**
 * Add a value to a dropdown menu
 */
function block_cmanager_add_value_to_dropdown() {

    global $DB;

    $id = required_param('id', PARAM_INT);
    $value = required_param('value', PARAM_TEXT);

    $object = new stdClass();
    $object->id = '';
    $object->fieldid = $id;
    $object->value = $value;

    $id = $DB->insert_record('block_cmanager_form_data', $object, true);

}

/**
 * Update a field
 */
function block_cmanager_update_field() {

    global $DB;
    echo $elementid = required_param('id', PARAM_INT);
    echo $value = required_param('value', PARAM_TEXT);

    $dataobject = new stdClass();
    $dataobject->id = $elementid;
    $dataobject->lefttext = addslashes($value);
    $DB->update_record('block_cmanager_formfields', $dataobject);

}

/**
 * Add a new field.
 */
function block_cmanager_add_field() {

    global $CFG, $DB;

    $fieldtype = required_param('fieldtype', PARAM_TEXT);
    $formid = required_param('formid', PARAM_TEXT);

    $query = "SELECT * FROM " . $CFG->prefix . "block_cmanager_formfields where formid = $formid ORDER BY position DESC";
    $record = $DB->get_record_sql($query, null, IGNORE_MISSING);

    // If no record exists, just start of with 1000 and then add one on to the numbering.
    $pos = 1000;
    if ($record) {
        $pos = $record->position;
    }

    $pos++;

    $object = new stdClass();
    $object->id = '';
    $object->position = $pos;
    $object->formid = $formid;
    $object->reqfield = '1';
    switch ($fieldtype) {
        case 'textfield':
            $object->type = 'textfield';
            $id = $DB->insert_record('block_cmanager_formfields', $object, true);
            echo $id;
            break;
        case 'textarea':
            $object->type = 'textarea';
            $id = $DB->insert_record('block_cmanager_formfields', $object, true);
            echo $id;
            break;
        case 'dropdown':
            $object->type = 'dropdown';
            $id = $DB->insert_record('block_cmanager_formfields', $object, true);
            echo $id;
            break;
        case 'radio':
            $object->type = 'radio';
            $id = $DB->insert_record('block_cmanager_formfields', $object, true);
            echo $id;
            break;
    }

}

/**
 * Get a collection of dropdown menu values.
 */
function block_cmanager_get_dropdown_values() {

    $id = required_param('id', PARAM_INT);
    global $DB;
    $field3itemhtml = '';
    $selectquery = "fieldid = '$id'";
    $formid = $_SESSION['formid'];
    $field3items = $DB->get_recordset_select('block_cmanager_form_data', $select = $selectquery);

    if ($field3items->valid()) {
        foreach ($field3items as $item) {
            $field3itemhtml .= '<div class="row">';
            $field3itemhtml .= '<div class="col-sm-2">' . format_string($item->value, true,
                    ['context' => context_system::instance()]) . '</div>';
            $field3itemhtml .= '<div class="col-sm-1"><a href="page2.php?id=' . $formid . '&t=dropitem&fid=' . $id
                    . '&del=' . $item->id . '"><i class="icon fa fa-trash fa-fw " title="' . get_string('delete')
                    . '" aria-label="' . get_string('delete') . '"></i></a></div>';
            $field3itemhtml .= '</div>';
        }
    }
    echo $field3itemhtml;
}

/**
 * Save changes that have been made.
 */
function block_cmanager_save_changes() {
    global $DB;

    $f1t = required_param('f1t', PARAM_TEXT);
    $f1d = required_param('f1d', PARAM_TEXT);
    $f2t = required_param('f2t', PARAM_TEXT);
    $f2d = required_param('f2d', PARAM_TEXT);
    $f3d = required_param('f3d', PARAM_TEXT);
    $dstat = required_param('dstat', PARAM_TEXT);

    // Field 1 title id.
    $dataobject->id = $DB->get_field_select('block_cmanager_config', 'id', "varname = 'page1_fieldname1'");
    $dataobject->varname['page1_fieldname1'];
    $dataobject->value = $f1t;
    $DB->update_record('block_cmanager_config', $dataobject);

    // Field 1 desc id.
    $dataobject->id = $$DB->get_field_select('block_cmanager_config', 'id', "varname = 'page1_fielddesc1'");
    $dataobject->varname['page1_fielddesc1'];
    $dataobject->value = $f1d;
    $DB->update_record('block_cmanager_config', $dataobject);

    // Field 2 title id.
    $dataobject->id = $$DB->get_field_select('block_cmanager_config', 'id', "varname = 'page1_fieldname2'");
    $dataobject->varname['page1_fieldname2'];
    $dataobject->value = $f2t;
    $DB->update_record('block_cmanager_config', $dataobject);

    // Field 2 desc id.
    $dataobject->id = $DB->get_field_select('block_cmanager_config', 'id', "varname = 'page1_fielddesc2'");
    $dataobject->varname['page1_fielddesc2'];
    $dataobject->value = $f2d;
    $DB->update_record('block_cmanager_config', $dataobject);

    // Field 3 desc id.
    $dataobject->id = $DB->get_field_select('block_cmanager_config', 'id', "varname = 'page1_fielddesc3'");
    $dataobject->varname['page1_fielddesc3'];
    $dataobject->value = $f3d;
    $DB->update_record('block_cmanager_config', $dataobject);

    // Status field id.
    $dataobject->id = $DB->get_field_select('block_cmanager_config', 'id', "varname = 'page1_field3status'");
    $dataobject->varname['page1_field3status'];
    $dataobject->value = $dstat;
    $DB->update_record('block_cmanager_config', $dataobject);

}
/**
 * Add a new item.
 */
function block_cmanager_add_new_item() {
    global $DB;

    $newvalue = required_param('valuetoadd', PARAM_TEXT);

    $object = new stdClass();
    $object->varname = 'page1_field3value';
    $object->value = addslashes($newvalue);
    $DB->insert_record('block_cmanager_config', $object, false, $primarykey = 'id');

}
/**
 * Save an optional value.
 */
function block_cmanager_save_optional_value() {

    global $DB;

    $id = required_param('id', PARAM_INT);
    $value = required_param('value', PARAM_TEXT);

    $dataobject = new stdClass();
    $dataobject->id = $id;
    $dataobject->reqfield = addslashes($value);

    $DB->update_record('block_cmanager_formfields', $dataobject);

}
