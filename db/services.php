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
 * Web service definitions for local_remote_backup_provider
 *
 * @package    local_remote_backup_provider
 * @copyright  2015 La fayette College ITS
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = array(
    'block_import_course_find_catalog' => array(
         'classname' => 'block_import_course_external',
         'methodname' => 'find_catalog',
         'classpath' => 'blocks/import_course/externallib.php',
         'description' => 'Find catalog courses matching a given string.',
         'type' => 'read',
         'capabilities' => 'moodle/course:viewhiddencourses',
    ),

    'block_import_course_get_course_backup_by_id' => array(
         'classname' => 'block_import_course_external',
         'methodname' => 'get_course_backup_by_id',
         'classpath' => 'blocks/import_course/externallib.php',
         'description' => 'Generate a course backup file and return a link.',
         'type' => 'read',
         'capabilities' => 'moodle/backup:backupcourse',
    ),
);

// We define the services to install as pre-build services. A pre-build service is not editable by administrator.
$services = array(
    'ImportCourse' => array(
        'functions' => array (
            'block_import_course_find_catalog',
            'block_import_course_get_course_backup_by_id',
        ),
        'restrictedusers' => 0,
        'enabled' => 1,
        'downloadfiles' => 0,
    )
);