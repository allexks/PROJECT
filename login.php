<?php

require_once "classes/util/View.class.php";
require_once "classes/util/Database.class.php";
require_once "classes/models/User.class.php";

session_start();

// Establish database connection

require "includes/db.php";

if (isset($_POST["submitbutton"])) {
    $name = $_POST["uname"];
    $password = $_POST["psw"];
    $user = new User($db);
    $user->username = $name;
    $is_logged_in = $user->usernameExists() && password_verify($password, $user->password);

    if ($is_logged_in) {
        $_SESSION["user_id"] = $user->id;
        header("Location: index.php");
    }
    else {
        $params["msg"] = "Incorrect Username or Password";
        $view = new View("login", "Login");
        $view->send($params);
    }
}
else {
    if (isset($_POST["signup"])) {
        header("Location: signup.php");
    }
}

$view = new View("login", "Login");
$view->send();
