<?php

class test_client {

    protected $t; // Target.

    public function __construct() {

        $this->t = new StdClass;

        // Setup this settings for tests:
        $this->t->baseurl = 'https://igs35-catalogue-qualif.activeprolearn.com'; // The remote Moodle url to push in.
        $this->t->wstoken = 'd6deb0d97f34584974d5bc67def8fcf0'; // The service token for access.
        $this->t->cataloguecategory  = 'Catalogue';
        $this->t->restorecategory = 'Arrivées';

    }
    public function test_find_files ($searchstring) {

        $url = $this->t->baseurl
            . '/webservice/rest/server.php?wstoken='
            . $this->t->wstoken . '&wsfunction=block_import_course_find_catalog';

        $params = Array (
            'topcategory' => $this->t->cataloguecategory,
            'search'  => $searchstring,
            'tags'    => Array( ),

        );

        return $this->send($url,$params);
    }

    public function test_import_course ($courseid) {

       $url = $this->t->baseurl . '/webservice/rest/server.php?wstoken='
       . $this->t->wstoken . '&wsfunction=block_import_course_get_course_backup_by_id&moodlewsrestformat=json';

       $editoroptions = array();
       echo basename(__DIR__).PHP_EOL;

       require "xlib.php";
       echo 'Appel import_course'.PHP_EOL;
       $courserestored = import_course ($this->t->baseurl, $this->t->wstoken, $this->t->restorecategory, $courseid, $editoroptions);

       echo 'Retour import_course'.PHP_EOl;

       return  $courserestored->id;


    }
    protected function send($serviceurl, $params) {
        $serviceurl .=  '&moodlewsrestformat=json';
        $ch = curl_init($serviceurl);

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params, '', '&'));

        echo "Firing CUrl $serviceurl ...".PHP_EOL;
        if (!$result = curl_exec($ch)) {
            echo "CURL Error : ".curl_errno($ch).' '.curl_error($ch)."\n";
            return;
        }

        if (preg_match('/EXCEPTION/', $result)) {
            echo $result;
            return;
        }

        $result = json_decode($result);
        return $result;
    }


}

// Effective test scenario

$client = new test_client();

echo "Test find files".PHP_EOL;
print_r ($client->test_find_files(''));

echo "Test find files with search pattern ".PHP_EOL;
print_r ($client->test_find_files('COU'));
