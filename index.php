<?php

require_once "classes/util/Database.class.php";
require_once "classes/util/View.class.php";
$view = new View("index");

$params = [
    "is_logged" => false,
    "database_error" => true,
    "username" => "",
];

session_start();

$database = new Database();
$db = $database->getNewConnection();

if (!$db) {
    $view->send($params);
}

$params["database_error"] = false;

if (!isset($_SESSION["user_id"])) {
    $view->send($params);
}

$user_id = (int)$_SESSION["user_id"] ?? 0;

$user = new User($db);
$user->id = $user_id;

if (!$user->idExists()){
    $view->send($params);
}

$params["is_logged"] = true;

// TODO

$view->send($params);
