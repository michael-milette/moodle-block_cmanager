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

require_once("../../config.php");
global $CFG, $DB;

$type = required_param('type', PARAM_TEXT);

if ($type == 'del') {
    $values = $_POST['values'];
    foreach ($values as $id) {
        if ($id != 'null') {
            $DB->delete_records('block_cmanager_records', array('id' => $id));
            // Delete associated comments.
            $DB->delete_records('block_cmanager_comments', array('instanceid' => $id));
        }
    }
}

// Update the values for emails.
if ($type == 'updatefield') {

    $postvalue = required_param('value', PARAM_TEXT);
    $postid = required_param('id', PARAM_TEXT);

    $selectquery = "varname = '$postid'";
    $recordexists = $DB->record_exists_select('block_cmanager_config', $selectquery);

    if ($recordexists) {
        // If the record exists.
        $currentrecord = $DB->get_record('block_cmanager_config', array('varname' => $postid));
        $newrec = new stdClass();
        $newrec->id = $currentrecord->id;
        $newrec->varname = $postid;
        $newrec->value = $postvalue;
        $DB->update_record('block_cmanager_config', $newrec);
        echo "updated";
    } else {
        $newrec = new stdClass();
        $newrec->varname = $postid;
        $newrec->value = $postvalue;
        $DB->insert_record('block_cmanager_config', $newrec);
        echo "inserted";
    }
}

if ($type == 'updatecategory') {
    $newrec = new stdClass();
    $newrec->id = required_param('recId', PARAM_TEXT);
    $newrec->cate = required_param('value', PARAM_TEXT);
    $DB->update_record('block_cmanager_records', $newrec);
}
