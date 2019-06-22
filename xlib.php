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
 * Web service library functions
 *
 * @package    block_import_course
 * @credits    2015 Lafayette College ITS
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

function block_import_course_import($remotesite, $token, $category, $courseid, $editoroptions) {

    global $CFG, $USER, $DB;

    $restorcategory = $DB->get_record('course_categories', array('name' => $category), '*', MUST_EXIST);
    debug_trace($restorcategory->id,'import_course/extlib.php 37');
    $newcourse = new stdClass();

    $newcourse->category = $restorcategory->id;
    $newcourse->fullname = "full long name";
    $newcourse->shortname = 99999 + rand(0, 10000);
    debug_trace($newcourse,'import_course/extlib.php 43');
    $course = create_course($newcourse, $editoroptions);
    debug_trace($course,'import_course/extlib.php 45');
    // Generate the backup file.

    $url = $remotesite . '/webservice/rest/server.php';
    $params = array(
        'wstoken' =>  $token,
        'wsfunction' => 'block_import_course_get_course_backup_by_id',
        'moodlewsrestformat'=> 'json',
        'id' => $courseid,
        'username' => $USER->username
    );
    debug_trace($url, 'import_course/extlib.php 51');

    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params, '', '&'));

    $resp = json_decode(curl_exec($ch));
    debug_trace($resp, 'import_course/extlib.php 54');

    $uniq = 9999999 + rand(0, 100000);
    $tempdir = $CFG->tempdir."/backup/$uniq/";

    // Import the backup file.
    $timestamp = time();
    $filerecord = array(
       'contextid' => $course->id,
       'component' => 'block_import_course',
       'filearea'  => 'backup',
       'itemid'    => $uniq,
       'filepath'  => $tempdir,
       'filename'  => 'foo',
       'timecreated' => $timestamp,
       'timemodified' => $timestamp
    );

    debug_trace($filerecord, 'import_course/extlib.php 72');
    $fs = get_file_storage();
    $storedfile = $fs->create_file_from_url($filerecord, $resp->url . '?token=' . $token, null, true);

    debug_trace($storedfile, 'import_course/extlib.php 76');
    if (!is_dir($tempdir)) {
            mkdir($tempdir, 0777, true);
    }

    include_once("$CFG->libdir/filestorage/tgz_packer.php");

    if (!$storedfile->extract_to_pathname(new tgz_packer(), $tempdir)) {}

    // Restore backup into course.

    include_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');
    $controller = new restore_controller($uniq, $course->id,
        backup::INTERACTIVE_NO, backup::MODE_GENERAL, $USER->id,
        backup::TARGET_NEW_COURSE );
    $controller->execute_precheck();
    $controller->execute_plan();
    debug_trace($course, 'import_course/extlib.php 97');

    return $course;
}