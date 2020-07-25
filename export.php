<?php

require_once "classes/models/Test.class.php";

session_start();

require "includes/db.php";

// Get test ID

$test_id = $_GET["id"] ?? "0";
$test_id = (int)$test_id;

if (!$test_id) {
    $view = new View("not_found", "Not Found");
    $view->send();
}
else {
	$view = new View("export");
	$params = [
    "id" => $test_id,
];
    $view->send($params);
}
