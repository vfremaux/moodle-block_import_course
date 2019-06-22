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
 * @copyright  2015 Lafayette College ITS
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/lib/externallib.php');
require_once($CFG->dirroot.'/backup/util/includes/backup_includes.php');

/**
 * Web service API definition.
 *
 * @package local_remote_backup_provider
 * @copyright 2015 Lafayette College ITS
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_import_course_external extends external_api {

     /**
      * Parameter description for find_catalog().
      *
      * @return external_function_parameters
      */
    public static function find_catalog_parameters() {
        return new external_function_parameters(
            array(
                'topcategory' => new external_value(PARAM_CLEAN, 'topcategory', VALUE_DEFAULT, ''),
                'search'  => new external_value(PARAM_CLEAN, 'search',VALUE_DEFAULT, ''),
                'tags'    => new external_multiple_structure (
                    new external_single_structure (
                      array (
                        'name'  => new external_value(PARAM_CLEAN, 'motcle', VALUE_DEFAULT, ''),
                        'value' => new external_value(PARAM_CLEAN, 'valeur', VALUE_DEFAULT, ''),
                      )
                   ,'item', VALUE_DEFAULT, [])
               ,'tag',VALUE_DEFAULT, [['name' =>'', 'value' => '']])
            )
        );
    }

    /**
     * Find courses by text search.
     *
     * This function searches the course short name, full name, and idnumber.
     *
     * @param string $search The text to search on
     * @return array All courses found
     */
    public static function find_catalog($topcategory, $search, $tags) {
        global $DB;

        // Validate parameters passed from web service.

        $params = self::validate_parameters(self::find_catalog_parameters(), array(
           'topcategory' => $topcategory,
           'search' => $search,
           'tags' => $tags));

        // Capability check.
        if (!has_capability('moodle/course:viewhiddencourses', context_system::instance())) {
            return false;
        }

        // search id top category Catalog
        if ( !empty($params['topcategory'])) {
           $topcategory = $DB->get_record('course_categories', array('name' => $params['topcategory']),'*', MUST_EXIST);
        }

        function wherelike ($column, $str) {
          $arr = explode(',', $str);
          $i = 0;

          foreach ($arr as $key => $value) {
            if ($i == 0) {
               $where = "(LOWER(".$column.") LIKE '%".strtolower($value)."%'";
            } else {
               $where = $where . " OR ".$column." LIKE '%".strtolower($value)."%'";
            }
            $i++;
          }
          return  $where.')';
        }
        // Build query string.
        $searchsql = '(c.id != 1)';
        $from = 'FROM {course} c ';

        if (!empty($params['topcategory'])) {
           $from .= 'INNER JOIN {course_categories} cat ON c.category = cat.id ';
           $searchsql .= " AND (cat.path LIKE '".$topcategory->path."%')";
        }

        if (! empty($params['search'])) {
            $like = wherelike('c.shortname', strtolower($params['search']));
            $searchsql = $searchsql. " AND ".$like;
        }

        foreach ($tags as $k => $v) {
            $mcl = $v['name'];
            $mclval = $v['value'];
            if (! empty($v['value'])) {
               $like = wherelike("ldta_${k}.data", $mclval);
               $from = $from . "INNER JOIN {local_metadata_field} lfld_${k} ON (lfld_${k}.shortname = '".$mcl."' AND lfld_${k}.contextlevel = 50) ";
               $from = $from . "INNER JOIN {local_metadata} ldta_${k} ON (ldta_${k}.instanceid = c.id)           AND (ldta_${k}.fieldid = lfld_${k}.id) AND ".$like;
            }
        }

        // Run query.
        $fields = 'c.id,c.idnumber,c.shortname,c.fullname';

        $sql = "
            SELECT
                $fields
                $from
            WHERE
                $searchsql
            ORDER BY
                c.shortname ASC
        ";
        debug_trace($sql ,'remote_backup/externallib.php 123');
        $courses = $DB->get_records_sql($sql, array(), 0);
        debug_trace($courses, 'remote_backup/externallib.php 125');
        return $courses;
    }


    /**
     * Parameter description for find_catalog().
     *
     * @return external_description
     */
    public static function find_catalog_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'id'        => new external_value(PARAM_INT, 'id of course'),
                    'idnumber'  => new external_value(PARAM_RAW, 'idnumber of course'),
                    'shortname' => new external_value(PARAM_RAW, 'short name of course'),
                    'fullname'  => new external_value(PARAM_RAW, 'long name of course'),
                )
            )
        );
    }

    /**
     * Parameter description for get_course_backup_by_id().
     *
     * @return external_function_parameters
     */
    public static function get_course_backup_by_id_parameters() {
        return new external_function_parameters(
            array(
                'id' => new external_value(PARAM_INT, 'id'),
                'username' => new external_value(PARAM_USERNAME, 'username'),
            )
        );
    }

    /**
     * Create and retrieve a course backup by course id.
     *
     * The user is looked up by username as it is not a given that user ids match
     * across platforms.
     *
     * @param int $id the course id
     * @param string $username The username
     * @return array|bool An array containing the url or false on failure
     */
    public static function get_course_backup_by_id($id, $username) {
        global $CFG, $DB;

        // Validate parameters passed from web service.
        $params = self::validate_parameters(
            self::get_course_backup_by_id_parameters(), array('id' => $id, 'username' => $username)
        );

        // Extract the userid from the username.
        $userid = $DB->get_field('user', 'id', array('username' => $username));

        // Instantiate controller.
        $bc = new backup_controller(
            \backup::TYPE_1COURSE, $id, backup::FORMAT_MOODLE, backup::INTERACTIVE_NO, backup::MODE_GENERAL, $userid);

        // Run the backup.
        // patch set
        $settings = array(
                'role_assignments' => 0,
                'users' => 0,   // ajout CB
                'user_files' => 0,
                'activities' => 1,
                'blocks' => 1,
                'filters' => 1,
                'comments' => 0,
                'completion_information' => 0,
                'logs' => 0,
                'histories' => 0
            );
            foreach ($settings as $setting => $configsetting) {
                if ($bc->get_plan()->setting_exists($setting)) {
                    //debug_trace($setting .' '.$configsetting,'setting externallib;php 269');
                    $bc->get_plan()->get_setting($setting)->set_value($configsetting);
                }
            }

        // end patch
        $bc->set_status(backup::STATUS_AWAITING);
        $bc->execute_plan();
        $result = $bc->get_results();
        debug_trace($result,'remote/courses/externallib.php 212');
        if (isset($result['backup_destination']) && $result['backup_destination']) {
            $file = $result['backup_destination'];
            $context = context_course::instance($id);

            $timestamp = time();

            $filerecord = array(
                'contextid' => $context->id,
                'component' => 'block_import_course',
                'filearea' => 'backup',
                'itemid' => $timestamp,
                'filepath' => '/',
                'filename' => 'foo',
                'timecreated' => $timestamp,
                'timemodified' => $timestamp
            );
            debug_trace($file, 'remote/courses/externallib.php 229');
            debug_trace($filerecord, 'remote/courses/externallib.php 230');
            $fs = get_file_storage();
            $storedfile = $fs->create_file_from_storedfile($filerecord, $file);
            $file->delete();
            debug_trace($storedfile, 'remote/courses/externallib.php 231');
            // Make the link.
            $filepath = $storedfile->get_filepath() . $storedfile->get_filename();
            $fileurl = moodle_url::make_webservice_pluginfile_url(
                $storedfile->get_contextid(),
                $storedfile->get_component(),
                $storedfile->get_filearea(),
                $storedfile->get_itemid(),
                $storedfile->get_filepath(),
                $storedfile->get_filename()
            );
            return array('url' => $fileurl->out(true));
        } else {
            return false;
        }
    }

    /**
     * Parameter description for get_course_backup_by_id().
     *
     * @return external_description
     */
    public static function get_course_backup_by_id_returns() {
        return new external_single_structure(
            array(
                'url' => new external_value(PARAM_RAW, 'url of the backup file'),
            )
        );
    }


}