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
	$password=$_POST["psw"];
	$user = new User($db);
	$user->name = $name;
	$user->password = $password;
	echo $user->name;
	echo $user->password;
}

$view = new View("login");
$view->send();
