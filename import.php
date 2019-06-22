<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Version details.
 *
 * @package    import_courses
 * @copyright  2019 CB
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

//defined('MOODLE_INTERNAL') || die;

require_once("../../config.php");
require_once("$CFG->dirroot/local/advancedperfs/debugtools.php");
require_once("$CFG->dirroot/course/lib.php");
require_once($CFG->dirroot."/blocks/import_course/xlib.php");

$courseid = optional_param('id', 0, PARAM_INT);
$remotesite = optional_param('remotesite', 0, PARAM_URL);
$token = optional_param('token', 0, PARAM_RAW);
$editoroptions = array();

$config = get_config('block_import_course');
debug_trace($config, 'import.php 40');

require_login();
$systemcontext = context_system::instance();
require_capability('moodle/site:config', $systemcontext);

$courserestored = block_import_course_import($remotesite, $token, $config->directory, $courseid, $editoroptions);

debug_trace($courserestored->id, 'import.php 45');

$urlparams = array('id' => $courserestored->id);
$url = new moodle_url('/course/edit.php', $urlparams);

redirect($url);