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
 * @copyright  2023 TNG Consulting Inc.
 * @author     Kyle Goslin, Daniel McSweeney
 * @author     Michael Milette
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Generate a generic bootstrap pop-up window.
 *
 * @param string $title the text in the title bar
 * @param string title to be shown
 * @param string $text
 * @param string $btntext text on the single button.
 * @return string HTML
 */
function generategenericpop($id, $title, $text, $btntext) {

    $html = '

<!-- Modal for quick approve -->
<div class="modal" style="top:100px" id="' . $id . '" tabindex="-1" role="dialog"
        aria-labelledby="exampleModalLabel" aria-hidden="true" data-backdrop="false">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="exampleModalLabel">' . $title . '</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">

        '.$text.'



      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">' . $btntext . '</button>

      </div>
    </div>
  </div>
</div>
';

    return $html;
}

/**
 * Generate a generic bootstrap pop-up with a conf option.
 *
 * @param integer $id
 * @param string $title the text in the title bar
 * @param string $text to be shown
 * @param string $btntext text on the single button.
 * @return string HTML
 */
function generategenericconfirm($id, $title, $text, $btntext) {

    $html = '

<!-- e -->
<div class="modal" id="' . $id . '" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true"
        data-backdrop="false">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="exampleModalLabel">' . $title . '</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
            ' . $text . '



      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">' . get_string('cancel', 'block_cmanager') . '</button>
        <button type="button" class="btn btn-primary" id="ok' . $id . '">' . $btntext . '</button>
      </div>
    </div>
  </div>
</div>
    ';
    return $html;
}
