<?php

require_once "classes/util/Database.class.php";
require_once "classes/models/User.class.php";
require_once "classes/util/View.class.php";

$view = new View("export");

session_start();

$database = new Database();
$db = $database->getNewConnection();

if (!$db) {
    $view = new View("database_error");
    $view->send();
}

if (!isset($_SESSION["user_id"])) {
    $view = new View("login_error");
    $view->send();
}

$user_id = (int)$_SESSION["user_id"] ?? 0;

$user = new User($db);
$user->id = $user_id;

if (!$user->idExists()){
    $view = new View("login_error");
    $view->send();
}

$view->send();