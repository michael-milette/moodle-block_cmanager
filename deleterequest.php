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

/*
 * Delete request.php
 *
 * This page is called through AJAX to delete a specific and all associated comments.
 */
require_once("../../config.php");
global $CFG, $DB;

// Check user has capability.
$context = context_system::instance();
if (!has_capability('block/cmanager:deleterecord', $context)) {
    throw new \moodle_exception('cannotdelete', 'block_cmanager');
}

$deleteid = required_param('id', PARAM_INT);
$type = optional_param('t', '', PARAM_TEXT);

// Delete the record.
$deletequery = "id = $deleteid";
$DB->delete_records('block_cmanager_records', array('id' => $deleteid));

// Delete associated comments.
$res = $DB->delete_records('block_cmanager_comments', array('instanceid' => $deleteid));

if ($res) {
    $event = \block_cmanager\event\course_deleted::create(array(
    'objectid' => '',
    'other' => get_string('courserecorddeleted', 'block_cmanager') . 'ID:' . $deleteid,
    'context' => $context,
    ));
    $event->trigger();
}

// Redirect the browser back when finished deleting.
if ($type == 'a') {
    echo "<script>window.location='cmanager_admin.php';</script>";
} else if ($type == 'adminarch') {
    echo "<script>window.location='cmanager_admin_arch.php';</script>";
} else {
    echo "<script>window.location='module_manager.php';</script>";
}
