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

namespace block_cmanager\event;

class course_deleted extends \core\event\base {
    protected function init() {
        $this->data['crud'] = 'c'; // Possible values: c (create), r (read), u (update), d (delete).
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = '';
    }

    public static function get_name() {
        return get_string('deletecourserequest', 'block_cmanager');
    }

    public function get_description() {
        return get_string('courserecdeleted', 'block_cmanager') . ' ' . $this->other;
    }

    public function get_url() {
        return new \moodle_url('/test.php');
    }

    public function get_legacy_logdata() {

    }

    public static function get_legacy_eventname() {

    }

    protected function get_legacy_eventdata() {

    }
}
