<?php

require_once "classes/util/Import.class.php";

session_start();

require "includes/db.php";
require "includes/user_id.php";

$import = new Import($db);

try {
    if (!isset($_FILES['file-upload']['error']) || is_array($_FILES['file-upload']['error'])) {
        throw new RuntimeException('Invalid parameters.');
    }

    switch ($_FILES['file-upload']['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_NO_FILE:
            throw new RuntimeException('No file sent.');
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            throw new RuntimeException('Exceeded filesize limit.');
        default:
            throw new RuntimeException('Unknown errors.');
    }

    if ($_FILES['file-upload']['size'] > 1000000) {
        throw new RuntimeException('Exceeded filesize limit.');
    }

    # Check file MIME type
    # Array of valid MIME types
    $csvMimes = array('text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel', 'text/plain');

    $file = new finfo(FILEINFO_MIME_TYPE);
    $check = array_search($file->file($_FILES['file-upload']['tmp_name']), $csvMimes, true);

    if ($check === false) {
        throw new RuntimeException('Invalid file format.');
    }

    $file_csv = fopen($_FILES['file-upload']['tmp_name'], 'r');
    // Get delimiter
    if (empty($_POST["csv-delimiter"])) {
      $delimiter = ',';
    }
    else {
      $delimiter = htmlspecialchars($_POST["csv-delimiter"]);
    }

    if ( !$file_csv ) {
        throw new RuntimeException("Could not open the file.");
    }

    while (($line_array = fgetcsv($file_csv, 0, $delimiter)) !== false) {
        $array_size = count($line_array);

        if ($array_size < 6) {
            throw new RuntimeException("Invalid parameters count.");
        }
        $check_if_uploaded = $import->imported($_SESSION["user_id"], $line_array[0]);

        if ($check_if_uploaded === true) {
          throw new RuntimeException("The test \"$line_array[0]\" is already uploaded.");
        }
      }
      rewind($file_csv);

    $questionsCount = 0;
    $testName = "";
    while (($line_array = fgetcsv($file_csv, 0, $delimiter)) !== false) {
        $array_size = count($line_array);

        if ($array_size < 6) {
            throw new RuntimeException("Invalid parameters count.");
        }

        $testName = $line_array[0];
        $test_id = $import->importTest($_SESSION["user_id"], $testName);

        if ($test_id === false) {
            throw new RuntimeException("Error occurred during test import. The test \"$testName\" was not imported.");
        }

        $q_types = ["multichoice", "truefalse", "shortanswer", "numerical", "essay"];

        $type = strtolower($line_array[1]);

        // If the type is not exactly one of the allowed types for example it is " multichoice" not "multichoice" the question import crashes
        if (!in_array($type, $q_types)) {
            continue; // question type not supported => skip it for now
        }

        // The questions must have different names it is not allowed to have the same question twice even tho it is different type
        $question_id = $import->importQuestion($test_id, $line_array[3], $type);
        ++$questionsCount;

        if ($question_id === false) {
          $result = $delete->deleteTest($_SESSION["user_id"], $testName);
          if ($result === false) {
            throw new RuntimeException("Error occurred during question import. The error could not be handled. Please delete the test \"$line_array[0]\" manually.");
          }
          else {
            throw new RuntimeException("Error occurred during question import. Please try to reupload the test \"$testName\".");
          }
        }

        if ($type === "multichoice" || $type === "truefalse") {
            for ($i = 4; $i < $array_size; ++$i) {
                if ($i % 2 == 0) {
                    $percent = $line_array[$i];
                }
                else {
                    $answer = $line_array[$i];
                    $res = $import->importAnswer($question_id, $answer, $percent);

                    if ($res === false) {
                      $result = $delete->deleteTest($_SESSION["user_id"], $testName);
                      if ($result === false) {
                        throw new RuntimeException("Error occurred during answer import. The error could not be handled. Please delete the test \"$line_array[0]\" manually.");
                      }
                      else {
                        throw new RuntimeException("Error occurred during answer import. Please try to reupload the test \"$testName\".");
                      }
                    }
                }
            }
        }
    }

    fclose($file_csv);

    $params = [
      "message" => "The test \"$testName\" was successfully uploaded.",
      "upload-info" => "$questionsCount questions successfully uploaded."
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
