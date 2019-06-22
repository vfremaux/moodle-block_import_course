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

require_once("$CFG->dirroot/blocks/import_course/locallib.php");

/**
 * Loads the block editing form.
 *
 * @package   block_import_course
 * @copyright 2015 Lafayette College ITS
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_import_course_edit_form extends block_edit_form {

    /**
     * Defines the block editing form.
     *
     * @param stdClass $mform
     */
    protected function specific_definition($mform) {
        // Section header title according to language file.
        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));

        // Configure the block title.
        $mform->addElement('text', 'config_title', get_string('blocktitle', 'block_import_course'));
        $mform->setDefault('config_title', get_string('import_course', 'block_import_course'));
        $mform->setType('config_title', PARAM_MULTILANG);

        // Intro text.
        $mform->addElement('editor', 'config_introtext', get_string('blockintrotext', 'block_import_course'));
        $mform->setType('config_introtext', PARAM_RAW);


        // Courses to show.
        $mform->addElement('text', 'config_numcourses',
            get_string('blocknumcourses', 'block_import_course'), array('size' => '2'));
        $mform->setDefault('config_numcourses', IMPORT_COURSE_DEFAULT_DISPLAY);
        $mform->setType('config_numcourses', PARAM_INT);
    }
}
