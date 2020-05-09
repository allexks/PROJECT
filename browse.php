<?php

require_once "classes/util/Database.class.php";
require_once "classes/models/User.class.php";
require_once "classes/util/View.class.php";

$view = new View("browse");

$params = [
    "is_logged" => false,
    "database_error" => true,
    "username" => "",
    "tests" => [],
];

session_start();

// Establish database connection

$database = new Database();
$db = $database->getNewConnection();

if (!$db) {
    $view->send($params);
}

$params["database_error"] = false;

// Determine currect logged user

if (!isset($_SESSION["user_id"])) {
    $view->send($params);
}

$user_id = (int)$_SESSION["user_id"] ?? 0;

// == TEST: ==
// $user_id = 1;
// == ===== ==

// Fetch user info

$user = new User($db);
$user->id = $user_id;

if (!$user->idExists()){
    $view->send($params);
}

$params["is_logged"] = true;
$params["username"] = $user->username;

// Fetch user tests

if (!$user->fetchTests()) {
    $view->send($params);
}

$params["tests"] = $user->tests;
$view->send($params);
