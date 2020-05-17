<?php
	require_once "classes/util/View.class.php";
	require_once "classes/models/Import.class.php";
	require_once "classes/util/Database.class.php";

	session_start();

	$database = new Database();
	$db = $database->getNewConnection();

	if (!$db) {
	    $view = new View("database_error");
	    $view->send();
	}

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

			$test_id = $import->importTest($_SESSION["user_id"], $line_array[0]);

			$question_id = $import->importQuestion($test_id, $line_array[3]);

			$array_size = count($line_array);
			for ($i = 4; $i < $array_size; ++$i) {
				if ($i % 2 == 0) {
					$percent = $line_array[$i];
				}
				else {
					$answer = $line_array[$i];
					$res = $import->importAnswer($question_id, $answer, $percent);
					print_r($res);
				}
			}
		} 

		fclose($file_csv);
		$view = new View("import-response-success");
		$view->send();
	}
	catch (RuntimeException $e) {
		$params = [
			"message" => $e->getMessage(),
		];

		$view = new View("import-response-error");

		$view->send($params);
	}

?>