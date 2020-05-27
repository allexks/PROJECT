<?php

// Establish database connection

$database = new Database();
$db = $database->getNewConnection();

if (!$db) {
    $view = new View("database_error", "Database error!");
    $view->send();
}
