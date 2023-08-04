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
 * @copyright  2012-2014 Kyle Goslin, Daniel McSweeney (Institute of Technology Blanchardstown)
 * @copyright  2021-2023 TNG Consulting Inc.
 * @author     Kyle Goslin, Daniel McSweeney
 * @author     Michael Milette
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function xmldb_block_cmanager_upgrade($oldversion) {
    global $DB;

    $result = true;

    if ($oldversion < 2013112539) {

        $newrec = new stdClass();
        $newrec->varname = 'denytext1';
        $newrec->value = 'You may enter a denial reason here.';
        $DB->insert_record('block_cmanager_config', $newrec, false);

        $newrec = new stdClass();
        $newrec->varname = 'denytext2';
        $newrec->value = 'You may enter a denial reason here.';
        $DB->insert_record('block_cmanager_config', $newrec, false);

        $newrec = new stdClass();
        $newrec->varname = 'denytext3';
        $newrec->value = 'You may enter a denial reason here.';
        $DB->insert_record('block_cmanager_config', $newrec, false);

        $newrec = new stdClass();
        $newrec->varname = 'denytext4';
        $newrec->value = 'You may enter a denial reason here.';
        $DB->insert_record('block_cmanager_config', $newrec, false);

        $newrec = new stdClass();
        $newrec->varname = 'denytext5';
        $newrec->value = 'You may enter a denial reason here.';
        $DB->insert_record('block_cmanager_config', $newrec, false);

    }

    return $result;
}
