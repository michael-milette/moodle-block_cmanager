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

require_once("../../../config.php");
global $CFG, $DB;
require_once("$CFG->libdir/formslib.php");
require_login();
$PAGE->set_context(context_system::instance());

require_once('../lib/displayLists.php');

$context = context_system::instance();
if (!has_capability('block/cmanager:viewrecord', $context)) {
    throw new \moodle_exception('cannotviewrecord', 'block_cmanager');
}

$mid = required_param('id', PARAM_INT);

$rec = $DB->get_recordset_select('block_cmanager_records', 'id = ' . $mid);
$displaymodhtml = block_cmanager_display_admin_list($rec, false, false, false, '');
echo '<div style="font-family: Arial,Verdana,Helvetica,sans-serif">' . $displaymodhtml . '</div>';
