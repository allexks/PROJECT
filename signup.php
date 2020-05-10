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
	$user->name = $name;
	$user->email = $email;
	$user->password = $password;
	echo $user->name;
	$user->password = htmlspecialchars(strip_tags($user->password));
    $password_hash = password_hash($user->password, PASSWORD_BCRYPT);
    echo $password_hash;
}

$view = new View("signup");
$view->send();
