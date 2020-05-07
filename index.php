<?php

require_once "classes/util/Database.class.php";
require_once "classes/util/View.class.php";
$view = new View("index");

session_start();


$database = new Database();
$db = $database->getNewConnection();

$is_logged = false;
$user_id = $_SESSION["user_id"] ?? "0";

if ($db && $user_id) {
    $user = new User($db);
    if ($user->idExists()){
        $is_logged = true;
    }
    // TODO
}

$view->send();
