<?php

require_once "classes/util/Import.class.php";
require_once "classes/util/Delete.class.php";

session_start();

require "includes/db.php";
require "includes/user_id.php";

$import = new Import($db);
$delete = new Delete($db);

try {
  if ("" == trim($_POST["json-content"])) {
    throw new RuntimeException("Empty field.");
  }

  $test = json_decode($_POST["json-content"], true);

  if (is_null($test) || (json_last_error() !== JSON_ERROR_NONE)) {
    throw new RuntimeException("Incorrect JSON format.");
  }

  // Test import
  $testName = $test["title"];

  // Check if the test was already imported
  $check_if_uploaded = $import->imported($_SESSION["user_id"], $testName);

  if ($check_if_uploaded === true) {
    throw new RuntimeException("The test \"$testName\" is already uploaded.");
  }

  $test_id = $import->importTest($_SESSION["user_id"], $testName);

  if ($test_id === false) {
      throw new RuntimeException("Error occurred during test import. The test \"$testName\" was not imported.");
  }

  $keys = array_keys($test);
  $keysCnt = count($keys);
  if ($keysCnt < 7) {
    throw new RuntimeException("Invalid parameters count. Test cannot be empty.");
  }

  $questionsCount = 0;
  $skippedQuestions = 0;
  foreach ($keys as &$key) {
    if ($key == "title" || $key == "content" || $key == "media_file" || $key == "file_type" || $key == "lang" || $key == "created_at") {
      continue;
    }

    $q_types = ["multichoice", "truefalse", "shortanswer", "numerical", "essay"];
    // Get question type
    $type = strtolower($test[$key][0]);

    // If the type is not exactly one of the allowed types for example it is " multichoice" not "multichoice" the question import crashes
    if (!in_array($type, $q_types)) {
      continue; // question type not supported => skip it for now
    }

    $count = count($test[$key]);
    if ($type === "shortanswer" || $type === "numerical" || $type === "essay") {
      if ($count < 4) {
        throw new RuntimeException("Invalid parameters count.");
      }
    }
    else {
        if ($count < 6) {
          throw new RuntimeException("Invalid parameters count.");
        }
    }

    // The questions must have different names it is not allowed to have the same question twice even tho it is different type
    $question = $test[$key][1];
    $question_id = $import->importQuestion($test_id, $question, $type);

    if ($question_id === true) {
      ++$skippedQuestions;
    }
    else if ($question_id === false) {
      $result = $delete->deleteTest($_SESSION["user_id"], $testName);
      if ($result === false) {
        throw new RuntimeException("Error occurred during question import. The error could not be handled. Please delete the test \"$testName\" manually.");
      }
      else {
        throw new RuntimeException("Error occurred during question import. Please try to reupload the test \"$testName\".");
      }
    }
    else {
      ++$questionsCount;

      $data = count($test[$key]);
      if ($type === "multichoice" || $type === "truefalse") {
          for ($i = 4; $i < $data; ++$i) {
              if ($i % 2 == 0) {
                  $percent = $test[$key][$i];
              }
              else {
                  $answer = $test[$key][$i];
                  $res = $import->importAnswer($question_id, $answer, $percent);

                  if ($res === false) {
                      $result = $delete->deleteTest($_SESSION["user_id"], $testName);
                      if ($result === false) {
                        throw new RuntimeException("Error occurred during answer import. The error could not be handled. Please delete the test \"$testName\" manually.");
                      }
                      else {
                        throw new RuntimeException("Error occurred during answer import. Please try to reupload the test \"$testName\".");
                      }
                  }
              }
          }
        }
      }
    }

    $totalQuestions = $questionsCount + $skippedQuestions;
    $params = [
      "message" => "The test \"$testName\" was successfully uploaded.",
      "upload-info" => "$questionsCount/$totalQuestions questions successfully uploaded."
    ];

    $view = new View("import-response-success", "Import success");
    $view->send($params);
}
catch (RuntimeException $e) {
  $params = [
      "message" => $e->getMessage(),
  ];

  $view = new View("import-response-error", "Import failure");
  $view->send($params);
}

?>
