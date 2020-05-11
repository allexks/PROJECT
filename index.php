<?php

require_once "classes/util/Database.class.php";
require_once "classes/models/User.class.php";
require_once "classes/util/View.class.php";

session_start();

// Establish database connection

$database = new Database();
$db = $database->getNewConnection();

if (!$db) {
    $view = new View("database_error");
    $view->send();
}

// Fetch all tests

$tests = Test::fetchAll($db);

if ($tests === false) {
    $view = new View("database_error");
    $view->send();
}

// Display them

$view = new View("browse");

$params = [
    "username" => "",
    "tests" => $tests,
];

$view->send($params);
