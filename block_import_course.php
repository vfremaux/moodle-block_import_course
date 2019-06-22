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
 * Prints a list of courses from another Moodle instance.
 *
 * @package   block_import_course
 * @copyright 2015 Lafayette College ITS
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot.'/blocks/import_course/locallib.php');

/**
 * Block definition.
 *
 * @package   block_import_course
 * @copyright 2015 Lafayette College ITS
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_import_course extends block_base {

    /**
     * Sets the block title.
     */
    public function init() {
        $this->title = get_string('import_course', 'block_import_course');
    }

    /**
     * Returns supported formats.
     * @return array
     */
    public function applicable_formats() {
        return array(
            'all' => true
        );
    }

    public function has_config() {
        return true;
    }

    /**
     * Returns the block content.
     * @return string
     */
    public function get_content() {
        global $USER, $FULLME, $OUTPUT;

        debug_trace($this->content, 'block_import_course.php 66');
        if ($this->content !== null) {
            return $this->content;
        }

        $this->content         = new stdClass();
        $this->content->text   = '';
        $this->content->footer = '';

        // Default content.
        debug_trace($this->config, 'block_import_course.php 76');
        if (!empty($this->config->introtext)) {
            $this->content->text .= $this->config->introtext['text'];
        }
/*
        // Quit if remote URL and token aren't set.
        if (empty($this->config->wstoken) || empty($this->config->remotesite)) {
            $this->content->text = get_string('unconfigured', 'block_import_course');
            return $this->content;
        }
*/
        $source = optional_param('source', '', PARAM_TEXT);
        $searchstring = optional_param('remote_search', '', PARAM_TEXT);
        $sources = block_import_course_get_sources();

        $template = new StdClass;
        $template->formurl = $FULLME;
        $template->sourceselect = html_writer::select($sources, 'source', $source, array());
        $template->searchstring = $searchstring;
        $this->content->text .= $OUTPUT->render_from_template('block_import_course/search_form', $template);
        debug_trace($this->content->text,'import_course/block_import_course.php 96');

        if (!empty($source)) {
            debug_trace($source, 'block_import_course 117');
            debug_trace($searchstring, 'block_import_course 118');
            $config = get_config('block_import_course');
            debug_trace($config, 'block_import_course 119');
            $siteurlkey = $source.'url';
            $tokenkey = $source.'token';

            $url = $config->$siteurlkey
                . '/webservice/rest/server.php?wstoken='
                . $config->$tokenkey . '&wsfunction=block_import_course_find_catalog';
            $format = 'json';
            // debug_trace($url,'bloc_import_course.php 86');

            $params = Array (
                'topcategory' => 'Catalogue',
                'search'  => $searchstring,
                'tags'    => Array( ),

            );

            // Params: we use the username for consistency.
            // $params = array('username' => $USER->username);

            // Retrieve data.
            $ch = curl_init($url.'&moodlewsrestformat='.$format.'&'.http_build_query($params, '', '&'));

            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params, '', '&'));

            $resp = json_decode(curl_exec($ch));

            // $curl = new curl;
            //debug_trace(buildQuery($params),'bloc_import_course.php 119');

            // $resp = json_decode($curl->post($url. '&moodlewsrestformat='.$format.'&'.http_build_query($params, '', '&')));

            debug_trace($resp,'bloc_import_course.php 157');

            if (!is_null($resp) && is_array($resp) && count($resp) > 0) {
                $this->content->text .= '<ul class="list">';
                $coursesprinted = 0;
                foreach ($resp as $course) {
                    $link = html_writer::tag('a', $course->fullname,
                            array('href' => new moodle_url('/blocks/import_course/import.php', ['id' => $course->id,
                                'remotesite' => $config->$siteurlkey,
                                'token'     =>  $config->$tokenkey])));
                    $this->content->text .= html_writer::tag('li', $link,
                            array('class' => 'import_course'));
                    $coursesprinted++;
                    if ($coursesprinted == $this->config->numcourses) {
                        break;
                    }
                }
                $this->content->text .= '</ul>';
            }
        }
        debug_trace($this->content,'import_course/block_import_course.php 176');
        return $this->content;
    }

    /**
     * Multiple instances are not supported.
     * @return boolean
     */
    public function instance_allow_multiple() {
        return false;
    }

    /**
     * Returns the block title.
     * @return string
     */
    public function specialization() {
        if (!empty($this->config->title)) {
            $this->title = $this->config->title;
        } else {
            $this->title = get_string('import_course', 'block_import_course');
        }
    }
}
