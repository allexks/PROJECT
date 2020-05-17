<?php

require_once "classes/util/View.class.php";
require_once "classes/util/Database.class.php";
require_once "classes/models/User.class.php";

session_start();

$database = new Database();
$db = $database->getNewConnection();

if (!$db) {
    $view = new View("database_error");
    $view->send();
}

# TO DO
unset($_SESSION["user_id"]);
header("Location: index.php");

$view = new View("exit");
$view->send();