<?php

require(dirname(__FILE__).'/../../config.php');
require $CFG->dirroot . '/theme/howcollege/extras/autoload.php';
global $PAGE, $OUTPUT;

$fileId = optional_param('fileid', '', PARAM_TEXT);
$fileName = optional_param('name', '', PARAM_TEXT);

$errorthrown = false;

if($fileId) {

    global $CFG;
    putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $CFG->dirroot . '/theme/howcollege/extras/test.json');
    $client = new Google_Client();
    $client->setApplicationName('moodle scorm');
    $client->addScope(\Google_Service_Drive::DRIVE);
    $client->useApplicationDefaultCredentials();
    $service = new Google_Service_Drive($client);

    try {
        $getScorm = $service->files->get($fileId, array('alt' => 'media'));
        $testcontent = $getScorm->getBody()->getContents();
        $length = $getScorm->getBody()->getSize();

        ob_end_clean();
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . $length);
        flush();
        echo $testcontent;
        exit();

    } catch (\Throwable $e) {
        print_r($e->getMessage());
        print_r($e->getCode());
        print "The Google Drive API threw an error, but dont worry, we'll come back for this.";
        return "error";
    }
}


$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('course');
$PAGE->set_title("Scorm Title");
$PAGE->set_heading("Blank page");
$PAGE->set_url($CFG->wwwroot.'/local/scorm_downloads/filedownload.php');

echo $OUTPUT->header();
echo "Hello World";
echo $OUTPUT->footer();


