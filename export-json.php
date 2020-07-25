<?php

require_once "classes/models/Test.class.php";

session_start();

require "includes/db.php";

function put_data_in_json(array &$array)
{
   if (count($array) == 0) {
     return null;
   }
   ob_start();
   $json_data = json_encode($array, JSON_PRETTY_PRINT);
   file_put_contents("php://output", $json_data);
   return ob_get_clean();
}

function download_send_headers($filename) {
    // disable caching
    $now = gmdate("D, d M Y H:i:s");
    header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
    header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
    header("Last-Modified: {$now} GMT");

    // force download
    header("Content-Type: application/force-download");
    header("Content-Type: application/octet-stream");
    header("Content-Type: application/download");

    // disposition / encoding on response body
    header("Content-Disposition: attachment;filename={$filename}");
    header("Content-Transfer-Encoding: binary");
}

// Get test ID

$test_id = $_GET["id"] ?? "0";
$test_id = (int)$test_id;

if (!$test_id) {
    $view = new View("not_found", "Not Found");
    $view->send();
}

// Get test info

$test = new Test($db);
$test->id = $test_id;

if (!$test->fetch()) {
    $view = new View("not_found", "Not Found");
    $view->send();
}

if (!$test->fetchQuestions()) {
    $view = new View("database_error", "Database error!");
    $view->send();
}

/*

Example JSON test 1:
{
   "test": "test12",
   "question1": ["multichoice", "questionName", "100", "asd", "0", "asdsff"],
   "question2": ["truefalse", "questionName2", "0", "dsa", "100", "dss"],
   "question3": ["shortanswer", "questionName3"],
   "question4": ["numerical", "questionName4"],
   "question5": ["essay", "questionName5"]
}

Example JSON test 2:
{
   "test": "test2",
   "question1": ["multichoice", "questionName", "100", "asd", "0", "asdsff"],
   "question2": ["truefalse", "questionName2", "0", "dsa", "100", "dss"],
   "question3": ["shortanswer", "questionName3"],
   "question4": ["numerical", "questionName4"],
   "question5": ["essay", "questionName5"],
   "question6": ["multichoice", "questionName", "100", "asd", "0", "asdsff"],
   "question7": ["multichoice", "questionName", "100", "asd", "0", "asdsff"],
   "question8": ["multichoice", "questionName", "100", "asd", "0", "asdsff"],
   "question9": ["multichoice", "questionName", "100", "asd", "0", "asdsff"]
}


*/

$response = array();
$response['test'] = $test->title;

foreach ($test->questions as $ind_q => $question) {
    $current_question = array();

    $current_question[] = $question->type;
    $current_question[] = $question->text;

    if (isset($question->answers) && !empty($question->answers)) {
      foreach ($question->answers as $ind_a => $answer) {
        $current_question[] = (string)(((int)$answer->is_correct) * 100);
        $current_question[] = $answer->text;
      }
    }

    $response['question' . ($ind_q + 1)] = $current_question;
}

download_send_headers($test->title . "_exported.json");
echo put_data_in_json($response);
