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

// Determine currect logged user

if (!isset($_SESSION["user_id"])) {
    $view = new View("login_error");
    $view->send();
}

$user_id = (int)$_SESSION["user_id"] ?? 0;

// Fetch user info

$user = new User($db);
$user->id = $user_id;

if (!$user->idExists()){
    $view = new View("login_error");
    $view->send();
}

$view = new View("browse");

$params = [
    "username" => "",
    "tests" => [],
];

$params["username"] = $user->username;

// Fetch user tests

if (!$user->fetchTests()) {
    $view->send($params);
}

$params["tests"] = $user->tests;
$view->send($params);
