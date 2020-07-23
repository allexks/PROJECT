<?php

require_once "classes/models/Test.class.php";

session_start();

require "includes/db.php";


function put_data_in_csv(array &$array)
{
   if (count($array) == 0) {
     return null;
   }
   ob_start();
   $df = fopen("php://output", 'a');
   foreach ($array as $row) {
      fputcsv($df, $row);
   }
   fclose($df);
   return ob_get_clean();
}

function put_data_in_json(array &$array)
{
   if (count($array) == 0) {
     return null;
   }
   ob_start();
   // $df = fopen("php://output", 'a');
   $json_data = json_encode($array, JSON_PRETTY_PRINT);
   file_put_contents("php://output", $json_data); 
   // fclose($df);
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


$arr = array();
$response = array();
$response['id'] = $test->id;
$response['title'] = $test->title;
$response['created_at'] = $test->time_uploaded;
$response['questions'] = [];
// $posts = array();
// $result=mysql_query($sql);
// while($row=mysql_fetch_array($result)) { 
//   $title=$row['title']; 
//   $url=$row['url']; 

//   $posts[] = array('title'=> $title, 'url'=> $url);
// } 

// $response['posts'] = $posts;

// $fp = fopen('results.json', 'w');
// fwrite($fp, json_encode($response));
// fclose($fp);

foreach ($test->questions as $ind_q => $question) {
       $current_question = array();
       $current_question['id'] = $question->id;
       $current_question['question'] = $question->text;
       $current_question['answers'] = [];
//     $row = array();
//     $row[] = $test->title;
//     $row[] = $question->type;
//     $row[] = "";
//     $row[] = "";
//     $row[] = $question->text;

      if (!$question->answers || empty($question->answers)) {
          
          // $row[] = 0;  // satisfying the min column limit of 6
      } else {
          foreach ($question->answers as $ind_a => $answer) {
              $current_answer = array();
              // "id": "125",
              //       "question_id": "32",
              //       "answer": "Мездра",
              //       "is_correct": "0"
              $current_answer['id'] = $answer->id;
              $current_answer['question_id'] = $answer->question_id;
              $current_answer['answer'] = $answer->text;
              $current_answer['is_correct'] = $answer->is_correct;
              // if ($answer->is_correct) {
              //     $row[] = 100;
              // } else{
              //     $row[] = 0;
              // }
              // $row[] = $answer->text;
              $current_question['answers'][] = $current_answer;
          }
      }
      $response['questions'][] = $current_question;
//     $arr[] = $row;
}

download_send_headers($test->title . "_exported" . ".json");
echo put_data_in_json($response);
