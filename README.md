Remote courses block
===========================

[![Build Status](https://travis-ci.org/LafColITS/moodle-block_import_course.svg?branch=master)](https://travis-ci.org/LafColITS/moodle-block_import_course)

This block prints a list of courses from another Moodle instance. It is designed for use with the [Remote course web service](https://github.com/LafColITS/moodle-local_import_course).

Configuration
-------------
To use the block you'll need to configure the local web service on the remote installation. The block is hard-coded to query the `local_import_course_get_courses_by_username` function over REST.

Requirements
------------
- Moodle 3.4 (build 2017111300 or later)

Installation
------------
Copy the import_course folder into your /local directory and visit your Admin Notification page to complete the installation.

Author
------
Charles Fulton (fultonc@lafayette.edu)
