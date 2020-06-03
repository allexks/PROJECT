<?php

session_start();

require "includes/db.php";
require "includes/user_id.php";

$view = new View("browse", "Browse your tests");

$params = [
    "all_system_tests" => false,
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
