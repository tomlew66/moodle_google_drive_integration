<?php

function UR_exists($url1){
    $headers1=get_headers($url1);
    return stripos($headers1[0],"200 OK")?true:false;
}

function GDriveCourses()
{
    global $COURSE, $CFG, $OUTPUT;
    require $CFG->dirroot . '/theme/howcollege/extras/autoload.php';
    putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $CFG->dirroot . '/theme/howcollege/extras/dfe-vrb-779a6e4972d5.json');
    $client1 = new Google_Client();
    $client1->setApplicationName('moodle scorm1');
    $client1->addScope(\Google_Service_Drive::DRIVE);
    $client1->useApplicationDefaultCredentials();

    $service1 = new Google_Service_Drive($client1);

    $courseId = $COURSE->id;
    $courseName = $COURSE->fullname;
    $folderId = $COURSE->idnumber;
    
    $optParams = array(
        'fields' => "nextPageToken, files(id,name, size, webViewLink, fileExtension)",
        'q' => "'" . $folderId . "' in parents",
    );
    
    $results1 = $service1->files->listFiles($optParams);
    $resultlist = [];
    $resultlistmp4 = [];
    $comingsoon = $OUTPUT->image_url('comingsoon', 'theme');
    foreach ($results1 as $result1 => $value1) {
        if ($value1->fileExtension == 'docx') {
            if (strpos($value1->name, '(Accessibility Version)')) {
                $docxav1 = str_ireplace(' (Accessibility Version).docx', '', $value1->name);
                $docxav = strtolower($docxav1);
            $resultlist[$docxav] = ["docxid" => $value1->id, "docxname" => $value1->name];
            } elseif (strpos($value1->name, '(Accessibility)')) {
                $docxa1 = str_ireplace(' (Accessibility).docx', '', $value1->name);
                $docxa = strtolower($docxa1);
            $resultlist[$docxa ] = ["docxid" => $value1->id, "docxname" => $value1->name];
            }
        } elseif ($value1->fileExtension == 'mp4') {
            $mp4title1 = str_ireplace(' (Accessibility).docx', '', $value1->name);
            $mp4title = strtolower($mp4title1);
            $resultlistmp4[$mp4title] = ["mp4id" => $value1->id, "mp4name" => $value1->name];
        } elseif ($value1->fileExtension == 'str') {
            $strtitle1= str_ireplace(' (Accessibility).docx', '', $value1->name);
            $strtitle = strtolower($strtitle1);
            $resultlistmp4[$strtitle ] = ["strid" => $value1->id, "strname" => $value1->name];
        }
    }


// ----------------------------------------------------------------------------------------------------------//
//------------------------ Generate initial list of files, categorise and then sort -------------------------//
// ----------------------------------------------------------------------------------------------------------//


            foreach ($results1 as $result1 => $value1) {
                if ($value1->fileExtension == 'pdf' && strpos($value1->name, 'Worksheet')) {
                    $extraworksheetnamelist[$value1->name] = $value1->id;
                    //$finalkeywksht = substr($value1->name, 0, strpos($value1->name, ' - '));
                    } elseif ($value1->fileExtension == 'pdf' && strpos($value1->name, 'Case Study')) {
                    $extracasestudynamelist[$value1->name] = $value1->id;
                } elseif ($value1->fileExtension == 'pdf' && strpos($value1->name, 'Task')) {
                    $extratasknamelist[$value1->name] = $value1->id;
                } if ($value1->fileExtension == 'pdf' && strpos($value1->name, 'Task') == false && strpos($value1->name, 'Case Study') == false && strpos($value1->name, 'Worksheet') == false) {
                    $extragenericnamelist[$value1->name] = $value1->id;
                }
                }   ksort($extraworksheetnamelist);
                    ksort($extracasestudynamelist);
                    ksort($extratasknamelist);
                    ksort($extragenericnamelist);

// ----------------------------------------------------------------------------------------------------------//
//---------------------------- Generate worksheet arrays and loop through them ------------------------------//
// ----------------------------------------------------------------------------------------------------------//

    $worksheettestlist = [];
    $pdfworksheetlist = [];

                foreach ($extraworksheetnamelist as $keywksht => $valuewksht) {
                    $finalkeywksht1 = substr($keywksht, 0, strpos($keywksht, ' - '));
                    $finalkeywksht = strtolower($finalkeywksht1);
                    if ($pdfworksheetlist[$finalkeywksht] == false) {
                        $pdfwkshtid = 'pdfworksheetid1';
                        $pdfwkshtname = 'pdfworksheetname1';
                        $pdfworksheetlist[$finalkeywksht] = [$pdfwkshtid++ => $valuewksht, $pdfwkshtname++ => $keywksht];
                    } elseif ($pdfworksheetlist[$finalkeywksht] == true) {
                        $worksheettestlist[$finalkeywksht] = [$pdfwkshtid++ => $valuewksht, $pdfwkshtname++ => $keywksht];
                        $pdfworksheetlist = array_merge_recursive($pdfworksheetlist, $worksheettestlist);
                        $worksheettestlist = [];
                    }
                }

// ----------------------------------------------------------------------------------------------------------//
//---------------------------- Generate case study arrays and loop through them -----------------------------//
// ----------------------------------------------------------------------------------------------------------//

                $casestudytestlist = [];
                $pdfcasestudylist = [];
                foreach ($extracasestudynamelist as $keycs => $valuecs) {
                    $finalkeycs1 = substr($keycs, 0, strpos($keycs, ' - '));
                    $finalkeycs = strtolower($finalkeycs1);
                    if ($pdfcasestudylist[$finalkeycs] == false) {
                        $pdfcsid = 'pdfcasestudyid1';
                        $pdfcsname = 'pdfcasestudyname1';
                        $pdfcasestudylist[$finalkeycs] = [$pdfcsid++ => $valuecs, $pdfcsname++ => $keycs];
                    } elseif ($pdfcasestudylist[$finalkeycs] == true) {
                        $casestudytestlist[$finalkeycs] = [$pdfcsid++ => $valuecs, $pdfcsname++ => $keycs];
                        $pdfcasestudylist = array_merge_recursive($pdfcasestudylist, $casestudytestlist);
                        $casestudytestlist = [];
                    }
                }

// ----------------------------------------------------------------------------------------------------------//
//------------------------------- Generate task arrays and loop through them --------------------------------//
// ----------------------------------------------------------------------------------------------------------//

        $tasktestlist = [];
        $pdftasklist = [];
        foreach ($extratasknamelist as $keytsk => $valuetsk) {
            $finalkeytsk1 = substr($keytsk, 0, strpos($keytsk, ' - '));
            $finalkeytsk = strtolower($finalkeytsk1);
            if ($pdftasklist[$finalkeytsk] == false) {
                $pdftskid = 'pdftaskid1';
                $pdftskname = 'pdftaskname1';
                $pdftasklist[$finalkeytsk] = [$pdftskid++ => $valuetsk, $pdftskname++ => $keytsk];
            } elseif ($pdftasklist[$finalkeytsk] == true) {
                $tasktestlist[$finalkeytsk] = [$pdftskid++ => $valuetsk, $pdftskname++ => $keytsk];
                $pdftasklist = array_merge_recursive($pdftasklist, $tasktestlist);
                $tasktestlist = [];
            }
        }

// ----------------------------------------------------------------------------------------------------------//
//--------------------------- Generate generic pdf arrays and loop through them -----------------------------//
// ----------------------------------------------------------------------------------------------------------//

        $generictestlist = [];
        $pdfgenericlist = [];
        foreach ($extragenericnamelist as $keygeneric => $valuegeneric) {
            $finalkeygeneric1 = substr($keygeneric, 0, strpos($keygeneric, ' - '));
            $finalkeygeneric = strtolower($finalkeygeneric1);
            if ($pdfgenericlist[$finalkeygeneric] == false) {
                $pdfgenericid = 'pdfgenericid1';
                $pdfgenericname = 'pdfgenericname1';
                $pdfgenericlist[$finalkeygeneric] = [$pdfgenericid++ => $valuegeneric, $pdfgenericname++ => $keygeneric];
            } elseif ($pdfgenericlist[$finalkeygeneric] == true) {
                $generictestlist[$finalkeygeneric] = [$pdfgenericid++ => $valuegeneric, $pdfgenericname++ => $keygeneric];
                $pdfgenericlist = array_merge_recursive($pdfgenericlist, $generictestlist);
                $generictestlist = [];
            }
        }

// ----------------------------------------------------------------------------------------------------------//
//---------------------------------- Generate zip array and loop through ------------------------------------//
// ----------------------------------------------------------------------------------------------------------//

        $results2 = $service1->files->listFiles($optParams);
        $resultziplist = [];
        foreach ($results2 as $result2 => $value2) {
            if ($value2->fileExtension == 'zip') {
                $scormtitle1 = str_ireplace('.zip', '', $value2->name);
                $scormtitlefinal = strtolower($scormtitle1);
                $resultziplist[$scormtitlefinal] = ["zipid" => $value2->id, "zipname" => $value2->name];
            }
        }

// ----------------------------------------------------------------------------------------------------------//
//---------------- Merge all the generated arrays based on their key (basic file title) ---------------------//
// ----------------------------------------------------------------------------------------------------------//

    $finalarray = array_merge_recursive($resultlist, $resultziplist, $resultlistmp4, $pdfworksheetlist, $pdfcasestudylist, $pdftasklist, $pdfgenericlist);


// ----------------------------------------------------------------------------------------------------------//
//-------------------------------------------- Generate HTML ------------------------------------------------//
// ----------------------------------------------------------------------------------------------------------//


        $filecontents = '';
        $previewurl1 = '';
        if (empty($finalarray)) {
            $filecontents .= '<div class="container">
                                <img class="imagecomingsoon" src="' . $comingsoon . '" alt="Coming Soon">
                            </div>';
        } else {
            foreach ($finalarray as $finalarrayvalue) {

                if ($finalarrayvalue['mp4name']) {
                    $filecontents .= '<div class="card mb-3 col-sm-4 course-mp4-card" id="course-mp4-card" style="max-width: 18rem;">
                                  <div class="card-header mp4-card-header course-results-title"><h5>' . str_replace('.mp4', '', $finalarrayvalue['mp4name']) . '</h5></div>
                                  <div class="card-body text-dark">
                                  <video class="videopreview" controls="controls">
                                    <source src="https://drive.google.com/uc?export=download&id=' . $finalarrayvalue['mp4id'] . '" type="video/mp4"/>
                                   </video>
                                    <hr class="hr-file-spacer">
                                    ' . getdriveids($finalarrayvalue['mp4name'], $finalarrayvalue['mp4id'], 'mp4') . '
                                    <hr class="hr-file-spacer">
                                    ' . getdriveids($finalarrayvalue['strname'], $finalarrayvalue['strid'], 'str') . '
                                </div>
                                </div>';
                } else {

                    $filenametest = str_replace('.zip', '', $finalarrayvalue['zipname']);
                    $filenameurl = str_replace(' ', '%20', $filenametest);
                    $coursenamefinal = str_replace(' ', '%20', $courseName);
                    $finalurl1 = "https://content.howcollege.ac.uk/DFE/" . $coursenamefinal . "/" . $filenameurl . "/story.html";
                    $finalurl2 = "https://content.howcollege.ac.uk/DFE/" . $coursenamefinal . "/" . $filenameurl . "/scormcontent/index.html";
                    $previewurl = "https://content.howcollege.ac.uk/DFE/" . $coursenamefinal . "/" . $filenameurl . "/story.html";
                    if (UR_exists($previewurl)) {
                        $previewurl1 = $finalurl1;
                    } else {
                        $previewurl1 = $finalurl2;
                    }

                    if ($finalarrayvalue['zipname']) {
                        $filetitle = str_replace('.zip', '', ($finalarrayvalue['zipname']));
                    } else if ($finalarrayvalue['docxname']) {
                        $filetitle = str_replace('.docx', '', ($finalarrayvalue['docxname']));
                    } else if ($finalarrayvalue['pdfname']) {
                        $filetitle = str_replace('.pdf', '', ($finalarrayvalue['pdfname']));
                    } else {
                        $filetitle = 'Unknown Title: Assistance Required';
                    }
                    $filecontents .= '<div class="card mb-3 course-zip-card" id="course-zip-card">
                                  <div class="card-header zip-card-header course-results-title"><h5>' . $filetitle . '</h5></div>
                                  <div class="card-body text-dark" id="card-body-files">
                                    <a class="preview-scorm" href="' . $previewurl1 . '" target="_blank"><div class="previewsvgholder"><svg class="previewsvg svg-inline--fa fa-eye fa-w-18" aria-hidden="true" focusable="false" data-prefix="far" data-icon="eye" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><path d="M288 144a110.94 110.94 0 0 0-31.24 5 55.4 55.4 0 0 1 7.24 27 56 56 0 0 1-56 56 55.4 55.4 0 0 1-27-7.24A111.71 111.71 0 1 0 288 144zm284.52 97.4C518.29 135.59 410.93 64 288 64S57.68 135.64 3.48 241.41a32.35 32.35 0 0 0 0 29.19C57.71 376.41 165.07 448 288 448s230.32-71.64 284.52-177.41a32.35 32.35 0 0 0 0-29.19zM288 400c-98.65 0-189.09-55-237.93-144C98.91 167 189.34 112 288 112s189.09 55 237.93 144C477.1 345 386.66 400 288 400z"></path></svg></div><p class="preview-scorm-text">Preview Resource</p></a>';
                    if ($finalarrayvalue['zipname']) {
                        $filecontents .= '<hr class="hr-file-spacer">' . getdriveids($finalarrayvalue['zipname'], $finalarrayvalue['zipid'], 'zip') . '';
                    } elseif ($finalarrayvalue['zipname'] == false) {
                        $filecontents .= '<hr class="hr-file-spacer">' . getdriveids($finalarrayvalue['zipname'], $finalarrayvalue['zipid'], 'zip') . '';
                    }
                    if ($finalarrayvalue['docxname']) {
                        $filecontents .= '<hr class="hr-file-spacer">' . getdriveids($finalarrayvalue['docxname'], $finalarrayvalue['docxid'], 'docx') . '';
                    }


                    // Generate the Task HTML //

                    $pdftasknamefinal = 'pdftaskname1';
                    $pdftaskidfinal = 'pdftaskid1';
                    if ($finalarrayvalue[$pdftasknamefinal]) {
                        while ($finalarrayvalue[$pdftasknamefinal] == true) {
                            $filecontents .= '<hr class="hr-file-spacer">' . getdriveids($finalarrayvalue[$pdftasknamefinal], $finalarrayvalue[$pdftaskidfinal], 'pdftask') . '';
                            $pdftasknamefinal++;
                            $pdftaskidfinal++;
                        }
                    }

                    // Generate the Worksheet HTML //

                    $pdfworksheet1name = 'pdfworksheetname1';
                    $pdfworksheet1id = 'pdfworksheetid1';
                    if ($finalarrayvalue[$pdfworksheet1name]) {
                        while ($finalarrayvalue[$pdfworksheet1name] == true) {
                            $filecontents .= '<hr class="hr-file-spacer">' . getdriveids($finalarrayvalue[$pdfworksheet1name], $finalarrayvalue[$pdfworksheet1id], 'pdfworksheet') . '';
                            $pdfworksheet1name++;
                            $pdfworksheet1id++;
                        }
                    }


                    // Generate the Case Study HTML //
                    $pdfcasestudyname = 'pdfcasestudyname1';
                    $pdfcasestudyid = 'pdfcasestudyid1';
                    if ($finalarrayvalue[$pdfcasestudyname]) {
                        while ($finalarrayvalue[$pdfcasestudyname] == true) {
                            $filecontents .= '<hr class="hr-file-spacer">' . getdriveids($finalarrayvalue[$pdfcasestudyname], $finalarrayvalue[$pdfcasestudyid], 'pdfcasestudy') . '';
                            $pdfcasestudyname++;
                            $pdfcasestudyid++;
                        }
                    }

                    // Generate the Generic HTML //
                    $pdfgenericnamefinal = 'pdfgenericname1';
                    $pdfgenericidfinal = 'pdfgenericid1';
                    if ($finalarrayvalue[$pdfgenericnamefinal]) {
                        while ($finalarrayvalue[$pdfgenericnamefinal] == true) {
                            $filecontents .= '<hr class="hr-file-spacer">' . getdriveids($finalarrayvalue[$pdfgenericnamefinal], $finalarrayvalue[$pdfgenericidfinal], 'pdfgeneric') . '';
                            $pdfgenericnamefinal++;
                            $pdfgenericidfinal++;
                        }
                    }
                    $filecontents .= '</div>
                                            </div>';
                }
            }

        }
    return $filecontents;
    return $previewurl1;

    }
    function getdriveids($filename66, $fileId66, $filetype)
    {
        $output = "";
        $zipsvg = '<div class="zipsvgholder"><svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="file-archive" class="svg-inline--fa fa-file-archive fa-w-12 zipsvg" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><path d="M377 105L279.1 7c-4.5-4.5-10.6-7-17-7H256v128h128v-6.1c0-6.3-2.5-12.4-7-16.9zM128.4 336c-17.9 0-32.4 12.1-32.4 27 0 15 14.6 27 32.5 27s32.4-12.1 32.4-27-14.6-27-32.5-27zM224 136V0h-63.6v32h-32V0H24C10.7 0 0 10.7 0 24v464c0 13.3 10.7 24 24 24h336c13.3 0 24-10.7 24-24V160H248c-13.2 0-24-10.8-24-24zM95.9 32h32v32h-32zm32.3 384c-33.2 0-58-30.4-51.4-62.9L96.4 256v-32h32v-32h-32v-32h32v-32h-32V96h32V64h32v32h-32v32h32v32h-32v32h32v32h-32v32h22.1c5.7 0 10.7 4.1 11.8 9.7l17.3 87.7c6.4 32.4-18.4 62.6-51.4 62.6z"></path></svg></div>';
        $docxsvg = '<div class="docxsvgholder"><svg aria-hidden="true" focusable="false" data-prefix="far" data-icon="file-word" class="svg-inline--fa fa-file-word fa-w-12 docxsvg" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><path d="M369.9 97.9L286 14C277 5 264.8-.1 252.1-.1H48C21.5 0 0 21.5 0 48v416c0 26.5 21.5 48 48 48h288c26.5 0 48-21.5 48-48V131.9c0-12.7-5.1-25-14.1-34zM332.1 128H256V51.9l76.1 76.1zM48 464V48h160v104c0 13.3 10.7 24 24 24h104v288H48zm220.1-208c-5.7 0-10.6 4-11.7 9.5-20.6 97.7-20.4 95.4-21 103.5-.2-1.2-.4-2.6-.7-4.3-.8-5.1.3.2-23.6-99.5-1.3-5.4-6.1-9.2-11.7-9.2h-13.3c-5.5 0-10.3 3.8-11.7 9.1-24.4 99-24 96.2-24.8 103.7-.1-1.1-.2-2.5-.5-4.2-.7-5.2-14.1-73.3-19.1-99-1.1-5.6-6-9.7-11.8-9.7h-16.8c-7.8 0-13.5 7.3-11.7 14.8 8 32.6 26.7 109.5 33.2 136 1.3 5.4 6.1 9.1 11.7 9.1h25.2c5.5 0 10.3-3.7 11.6-9.1l17.9-71.4c1.5-6.2 2.5-12 3-17.3l2.9 17.3c.1.4 12.6 50.5 17.9 71.4 1.3 5.3 6.1 9.1 11.6 9.1h24.7c5.5 0 10.3-3.7 11.6-9.1 20.8-81.9 30.2-119 34.5-136 1.9-7.6-3.8-14.9-11.6-14.9h-15.8z"></path></svg></div>';
        $mp4svg = '<div class="mp4svgholder"><svg  aria-hidden="true" focusable="false" data-prefix="far" data-icon="file-video" class="svg-inline--fa fa-file-video fa-w-12 mp4svg" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><path d="M369.941 97.941l-83.882-83.882A48 48 0 0 0 252.118 0H48C21.49 0 0 21.49 0 48v416c0 26.51 21.49 48 48 48h288c26.51 0 48-21.49 48-48V131.882a48 48 0 0 0-14.059-33.941zM332.118 128H256V51.882L332.118 128zM48 464V48h160v104c0 13.255 10.745 24 24 24h104v288H48zm228.687-211.303L224 305.374V268c0-11.046-8.954-20-20-20H100c-11.046 0-20 8.954-20 20v104c0 11.046 8.954 20 20 20h104c11.046 0 20-8.954 20-20v-37.374l52.687 52.674C286.704 397.318 304 390.28 304 375.986V264.011c0-14.311-17.309-21.319-27.313-11.314z"></path></svg></div>';
        $strsvg = '<div class="strsvgholder"><svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="closed-captioning" class="svg-inline--fa fa-closed-captioning fa-w-16 strsvg" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M464 64H48C21.5 64 0 85.5 0 112v288c0 26.5 21.5 48 48 48h416c26.5 0 48-21.5 48-48V112c0-26.5-21.5-48-48-48zM218.1 287.7c2.8-2.5 7.1-2.1 9.2.9l19.5 27.7c1.7 2.4 1.5 5.6-.5 7.7-53.6 56.8-172.8 32.1-172.8-67.9 0-97.3 121.7-119.5 172.5-70.1 2.1 2 2.5 3.2 1 5.7l-17.5 30.5c-1.9 3.1-6.2 4-9.1 1.7-40.8-32-94.6-14.9-94.6 31.2.1 48 51.1 70.5 92.3 32.6zm190.4 0c2.8-2.5 7.1-2.1 9.2.9l19.5 27.7c1.7 2.4 1.5 5.6-.5 7.7-53.5 56.9-172.7 32.1-172.7-67.9 0-97.3 121.7-119.5 172.5-70.1 2.1 2 2.5 3.2 1 5.7L420 222.2c-1.9 3.1-6.2 4-9.1 1.7-40.8-32-94.6-14.9-94.6 31.2 0 48 51 70.5 92.2 32.6z"></path></svg></div>';
        $pdfsvgtask = '<div class="pdf1svgholder-task"><svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="file-pdf" class="svg-inline--fa fa-file-pdf fa-w-12 pdfsvg" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><path d="M181.9 256.1c-5-16-4.9-46.9-2-46.9 8.4 0 7.6 36.9 2 46.9zm-1.7 47.2c-7.7 20.2-17.3 43.3-28.4 62.7 18.3-7 39-17.2 62.9-21.9-12.7-9.6-24.9-23.4-34.5-40.8zM86.1 428.1c0 .8 13.2-5.4 34.9-40.2-6.7 6.3-29.1 24.5-34.9 40.2zM248 160h136v328c0 13.3-10.7 24-24 24H24c-13.3 0-24-10.7-24-24V24C0 10.7 10.7 0 24 0h200v136c0 13.2 10.8 24 24 24zm-8 171.8c-20-12.2-33.3-29-42.7-53.8 4.5-18.5 11.6-46.6 6.2-64.2-4.7-29.4-42.4-26.5-47.8-6.8-5 18.3-.4 44.1 8.1 77-11.6 27.6-28.7 64.6-40.8 85.8-.1 0-.1.1-.2.1-27.1 13.9-73.6 44.5-54.5 68 5.6 6.9 16 10 21.5 10 17.9 0 35.7-18 61.1-61.8 25.8-8.5 54.1-19.1 79-23.2 21.7 11.8 47.1 19.5 64 19.5 29.2 0 31.2-32 19.7-43.4-13.9-13.6-54.3-9.7-73.6-7.2zM377 105L279 7c-4.5-4.5-10.6-7-17-7h-6v128h128v-6.1c0-6.3-2.5-12.4-7-16.9zm-74.1 255.3c4.1-2.7-2.5-11.9-42.8-9 37.1 15.8 42.8 9 42.8 9z"></path></svg></div>';
        $pdfsvgcasestudy = '<div class="pdf1svgholder-casestudy"><svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="file-pdf" class="svg-inline--fa fa-file-pdf fa-w-12 pdfsvg" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><path d="M181.9 256.1c-5-16-4.9-46.9-2-46.9 8.4 0 7.6 36.9 2 46.9zm-1.7 47.2c-7.7 20.2-17.3 43.3-28.4 62.7 18.3-7 39-17.2 62.9-21.9-12.7-9.6-24.9-23.4-34.5-40.8zM86.1 428.1c0 .8 13.2-5.4 34.9-40.2-6.7 6.3-29.1 24.5-34.9 40.2zM248 160h136v328c0 13.3-10.7 24-24 24H24c-13.3 0-24-10.7-24-24V24C0 10.7 10.7 0 24 0h200v136c0 13.2 10.8 24 24 24zm-8 171.8c-20-12.2-33.3-29-42.7-53.8 4.5-18.5 11.6-46.6 6.2-64.2-4.7-29.4-42.4-26.5-47.8-6.8-5 18.3-.4 44.1 8.1 77-11.6 27.6-28.7 64.6-40.8 85.8-.1 0-.1.1-.2.1-27.1 13.9-73.6 44.5-54.5 68 5.6 6.9 16 10 21.5 10 17.9 0 35.7-18 61.1-61.8 25.8-8.5 54.1-19.1 79-23.2 21.7 11.8 47.1 19.5 64 19.5 29.2 0 31.2-32 19.7-43.4-13.9-13.6-54.3-9.7-73.6-7.2zM377 105L279 7c-4.5-4.5-10.6-7-17-7h-6v128h128v-6.1c0-6.3-2.5-12.4-7-16.9zm-74.1 255.3c4.1-2.7-2.5-11.9-42.8-9 37.1 15.8 42.8 9 42.8 9z"></path></svg></div>';
        $pdfsvgworksheet = '<div class="pdf1svgholder-worksheet"><svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="file-pdf" class="svg-inline--fa fa-file-pdf fa-w-12 pdfsvg" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><path d="M181.9 256.1c-5-16-4.9-46.9-2-46.9 8.4 0 7.6 36.9 2 46.9zm-1.7 47.2c-7.7 20.2-17.3 43.3-28.4 62.7 18.3-7 39-17.2 62.9-21.9-12.7-9.6-24.9-23.4-34.5-40.8zM86.1 428.1c0 .8 13.2-5.4 34.9-40.2-6.7 6.3-29.1 24.5-34.9 40.2zM248 160h136v328c0 13.3-10.7 24-24 24H24c-13.3 0-24-10.7-24-24V24C0 10.7 10.7 0 24 0h200v136c0 13.2 10.8 24 24 24zm-8 171.8c-20-12.2-33.3-29-42.7-53.8 4.5-18.5 11.6-46.6 6.2-64.2-4.7-29.4-42.4-26.5-47.8-6.8-5 18.3-.4 44.1 8.1 77-11.6 27.6-28.7 64.6-40.8 85.8-.1 0-.1.1-.2.1-27.1 13.9-73.6 44.5-54.5 68 5.6 6.9 16 10 21.5 10 17.9 0 35.7-18 61.1-61.8 25.8-8.5 54.1-19.1 79-23.2 21.7 11.8 47.1 19.5 64 19.5 29.2 0 31.2-32 19.7-43.4-13.9-13.6-54.3-9.7-73.6-7.2zM377 105L279 7c-4.5-4.5-10.6-7-17-7h-6v128h128v-6.1c0-6.3-2.5-12.4-7-16.9zm-74.1 255.3c4.1-2.7-2.5-11.9-42.8-9 37.1 15.8 42.8 9 42.8 9z"></path></svg></div>';
        $pdfsvggeneric = '<div class="pdf1svgholder-generic"><svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="file-pdf" class="svg-inline--fa fa-file-pdf fa-w-12 pdfsvg" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><path d="M181.9 256.1c-5-16-4.9-46.9-2-46.9 8.4 0 7.6 36.9 2 46.9zm-1.7 47.2c-7.7 20.2-17.3 43.3-28.4 62.7 18.3-7 39-17.2 62.9-21.9-12.7-9.6-24.9-23.4-34.5-40.8zM86.1 428.1c0 .8 13.2-5.4 34.9-40.2-6.7 6.3-29.1 24.5-34.9 40.2zM248 160h136v328c0 13.3-10.7 24-24 24H24c-13.3 0-24-10.7-24-24V24C0 10.7 10.7 0 24 0h200v136c0 13.2 10.8 24 24 24zm-8 171.8c-20-12.2-33.3-29-42.7-53.8 4.5-18.5 11.6-46.6 6.2-64.2-4.7-29.4-42.4-26.5-47.8-6.8-5 18.3-.4 44.1 8.1 77-11.6 27.6-28.7 64.6-40.8 85.8-.1 0-.1.1-.2.1-27.1 13.9-73.6 44.5-54.5 68 5.6 6.9 16 10 21.5 10 17.9 0 35.7-18 61.1-61.8 25.8-8.5 54.1-19.1 79-23.2 21.7 11.8 47.1 19.5 64 19.5 29.2 0 31.2-32 19.7-43.4-13.9-13.6-54.3-9.7-73.6-7.2zM377 105L279 7c-4.5-4.5-10.6-7-17-7h-6v128h128v-6.1c0-6.3-2.5-12.4-7-16.9zm-74.1 255.3c4.1-2.7-2.5-11.9-42.8-9 37.1 15.8 42.8 9 42.8 9z"></path></svg></div>';
        $link = new moodle_url('/local/scorm_downloads/filedownload.php',
            [
                'fileid' => $fileId66,
                'name' => $filename66
            ]
        );

        if ($filetype == 'zip') {
            $output .= '<a class="download-scorm" target="_blank" href="' . $link . '">' . $zipsvg . '<p class="download-scorm-text">Download SCORM package</p></a>';
        } elseif ($filetype == 'docx') {
            $output .= '<a class="download-docx" target="_blank" href="' . $link . '">' . $docxsvg . '<p class="download-docx-text">Download Accessibility Document</p></a>';
        } elseif ($filetype == 'pdfworksheet') {
            $output .= '<a class="download-pdf" target="_blank" href="' . $link . '">' . $pdfsvgworksheet . '<p class="download-pdf-text">Download'. substr($filename66, strpos($filename66,' -'), -4) . '</p></a>';
        } elseif ($filetype == 'pdftask') {
            $output .= '<a class="download-pdf" target="_blank" href="' . $link . '">' . $pdfsvgtask . '<p class="download-pdf-text">Download'. substr($filename66, strpos($filename66,' -'), -4) . '</p></a>';
        } elseif ($filetype == 'pdfcasestudy') {
            $output .= '<a class="download-pdf" target="_blank" href="' . $link . '">' . $pdfsvgcasestudy . '<p class="download-pdf-text">Download'. substr($filename66, strpos($filename66,' -'), -4) . '</p></a>';
        } elseif ($filetype == 'pdfgeneric') {
            $output .= '<a class="download-pdf" target="_blank" href="' . $link . '">' . $pdfsvggeneric . '<p class="download-pdf-text">Download'. substr($filename66, strpos($filename66,' -'), -4) . '</p></a>';
        } elseif ($filetype == 'mp4') {
            $output .= '<a class="download-mp4" target="_blank" href="' . $link . '">' . $mp4svg . '<p class="download-mp4-text">Download Video</p></a>';
        } elseif ($filetype == 'str') {
            $output .= '<a class="download-str" target="_blank" href="' . $link . '">' . $strsvg . '<p class="download-str-text">Download Subtitles</p></a>';
        }
        return $output;
    }




