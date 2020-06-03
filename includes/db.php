<?php

require_once "classes/util/Database.class.php";
require_once "classes/util/View.class.php";

// Establish database connection

$database = new Database();
$db = $database->getNewConnection();

if (!$db) {
    $view = new View("database_error", "Database error!");
    $view->send();
}
