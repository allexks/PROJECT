<?php

require_once "classes/models/User.class.php";
require_once "classes/util/View.class.php";

// Determine currect logged user

if (!isset($_SESSION["user_id"])) {
    $view = new View("login_error", "Login error!");
    $view->send();
}

$user_id = (int)$_SESSION["user_id"] ?? 0;

// Fetch user info

$user = new User($db);
$user->id = $user_id;

if (!$user->idExists()){
    $view = new View("login_error", "Login error!");
    $view->send();
}
