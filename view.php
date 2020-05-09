<?php

require_once "classes/models/Test.class.php";
require_once "classes/util/Database.class.php";
require_once "classes/util/View.class.php";

session_start();

// Establish database connection

$database = new Database();
$db = $database->getNewConnection();

if (!$db) {
    $view = new View("database_error");
    $view->send();
}

// Determine currect logged user

$user_id = $_SESSION["user_id"] ?? "0";
$user_id = (int)$user_id;

if (!$user_id) {
    $view = new View("login_error");
    $view->send();
}

// == TEST: ==
// $user_id = 1;
// == ===== ==

// Get test ID

$test_id = $_GET["id"] ?? "0";
$test_id = (int)$test_id;

if (!$test_id) {
    $view = new View("not_found");
    $view->send();
}

// Get test info

$test = new Test($db);
$test->id = $test_id;

if (!$test->fetch()) {
    $view = new View("not_found");
    $view->send();
}

if (!$test->fetchQuestions()) {
    $view = new View("database_error");
    $view->send();
}

// Check if the user has access to the test

if ($test->user_id != $user_id) {
    $view = new View("not_found");
    $view->send();
}

// Perform results check if the form is submitted

if (isset($_POST["submit"])) {

    // TODO
}

$params = [
    "title" => $test->title ?? "",
    "questions" => $test->questions ?? [],
];

$view = new View("view_test");
$view->send($params);
