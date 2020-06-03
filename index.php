<?php

require_once "classes/models/User.class.php";

session_start();

require "includes/db.php";

// Fetch all tests

$tests = Test::fetchAll($db);

if ($tests === false) {
    $view = new View("database_error", "Database error!");
    $view->send();
}

$params = [
    "all_system_tests" => true,
    "username" => "",
    "tests" => $tests,
    "logout_msg" => "",
];

// To display correct message, check if the user is logged in
if (isset($_SESSION["user_id"])) {
	$user_id = (int)$_SESSION["user_id"] ?? 0;

	$user = new User($db);
	$user->id = $user_id;

	if ($user->idExists()) {
		$params["username"] = $user->username;
	}
}

if (isset($_SESSION["logout_msg"])) {
	$params["logout_msg"] = $_SESSION["logout_msg"];
	unset($_SESSION["logout_msg"]);
}

$view = new View("browse", "Welcome");
$view->send($params);
