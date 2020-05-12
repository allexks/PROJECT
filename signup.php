<?php

require_once "classes/util/View.class.php";
require_once "classes/util/Database.class.php";
require_once "classes/models/User.class.php";

session_start();

// Establish database connection

$database = new Database();
$db = $database->getNewConnection();

if (!$db) {
    $view = new View("database_error");
    $view->send();
}

if (isset($_POST["submitbutton"]))
{
	$name=$_POST["uname"];	
	$email=$_POST["email"];
	$password=$_POST["psw"];
	$user = new User($db);
	$user->username = $name;
	$user->email = $email;
	$user->password = $password;
	if($user->usernameExists() || $user->emailExists()) {
		$params["msg"] = "Username or Email already used";
		$view = new View("signup");
		$view->send($params);   
	}
	else {
		$user->create();
		$_SESSION["user_id"] = $user->id;
		header("Location: index.php");
	}
}

$view = new View("signup");
$view->send();
