<?php

require_once "classes/models/Import.class.php";

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

    if ( !$file_csv ) {
        throw new RuntimeException("Could not open the file.");
    }

    while (($line = fgets($file_csv)) !== false) {
        $line_array = explode(',', $line);
        $array_size = count($line_array);

        if ($array_size < 6) {
            throw new RuntimeException("Invalid parameters count.");
        }

        $test_id = $import->importTest($_SESSION["user_id"], $line_array[0]);

        if ($test_id === false) {
            throw new RuntimeException("Error occurred during test import.");
        }

        $question_id = $import->importQuestion($test_id, $line_array[3]);

        if ($question_id === false) {
            throw new RuntimeException("Error occurred during test import.");
        }

        for ($i = 4; $i < $array_size; ++$i) {
            if ($i % 2 == 0) {
                $percent = $line_array[$i];
            }
            else {
                $answer = $line_array[$i];
                $res = $import->importAnswer($question_id, $answer, $percent);
            }
        }
    }

    fclose($file_csv);
    $view = new View("import-response-success", "Import success");
    $view->send();
}
catch (RuntimeException $e) {
    $params = [
        "message" => $e->getMessage(),
    ];

    $view = new View("import-response-error", "Import failure");
    $view->send($params);
}

?>
