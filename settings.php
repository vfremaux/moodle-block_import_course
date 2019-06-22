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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>..

    defined('MOODLE_INTERNAL') || die();

$key = 'block_import_course/archivesource';
$label = get_string('configarchivesource', 'block_import_course');
$desc = get_string('configarchivesource_desc', 'block_import_course');
$settings->add(new admin_setting_configtext($key, $label, $desc, ''));

$key = 'block_import_course/archiveurl';
$label = get_string('configarchiveurl', 'block_import_course');
$desc = get_string('configarchiveurl_desc', 'block_import_course');
$settings->add(new admin_setting_configtext($key, $label, $desc, ''));

$key = 'block_import_course/archivetoken';
$label = get_string('configarchivetoken', 'block_import_course');
$desc = get_string('configarchivetoken_desc', 'block_import_course');
$settings->add(new admin_setting_configtext($key, $label, $desc, ''));

$key = 'block_import_course/catalogsource';
$label = get_string('configcatalogsource', 'block_import_course');
$desc = get_string('configcatalogsource_desc', 'block_import_course');
$settings->add(new admin_setting_configtext($key, $label, $desc, ''));

$key = 'block_import_course/catalogurl';
$label = get_string('configcatalogurl', 'block_import_course');
$desc = get_string('configcatalogurl_desc', 'block_import_course');
$settings->add(new admin_setting_configtext($key, $label, $desc, ''));

$key = 'block_import_course/catalogtoken';
$label = get_string('configcatalogtoken', 'block_import_course');
$desc = get_string('configcatalogtoken_desc', 'block_import_course');
$settings->add(new admin_setting_configtext($key, $label, $desc, ''));

$key = 'block_import_course/teachingsource';
$label = get_string('configteachingsource', 'block_import_course');
$desc = get_string('configteachingsource_desc', 'block_import_course');
$settings->add(new admin_setting_configtext($key, $label, $desc, ''));

$key = 'block_import_course/teachingurl';
$label = get_string('configteachingurl', 'block_import_course');
$desc = get_string('configteachingurl_desc', 'block_import_course');
$settings->add(new admin_setting_configtext($key, $label, $desc, ''));

$key = 'block_import_course/teachingtoken';
$label = get_string('configteachingtoken', 'block_import_course');
$desc = get_string('configteachingtoken_desc', 'block_import_course');
$settings->add(new admin_setting_configtext($key, $label, $desc, ''));

$key = 'block_import_course/directory';
$label = get_string('configdirectory', 'block_import_course');
$desc = get_string('configdirectory_desc', 'block_import_course');
$settings->add(new admin_setting_configtext($key, $label, $desc, ''));