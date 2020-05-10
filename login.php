<?php

require_once "classes/util/View.class.php";
require_once "classes/util/Database.class.php";

session_start();

$view = new View("login");

// Establish database connection

$database = new Database();
$db = $database->getNewConnection();

if (!$db) {
    $view = new View("database_error");
    $view->send();
}

$view->send();
