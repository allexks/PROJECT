<?php

require_once "classes/models/User.class.php";

session_start();

require "includes/db.php";

if (isset($_POST["submitbutton"]))
{
    $name = $_POST["uname"];
    $email = $_POST["email"];
    $password = $_POST["psw"];
    $user = new User($db);
    $user->username = $name;
    $user->email = $email;
    $user->password = $password;
    if ($user->usernameExists() || $user->emailExists()) {
        $params["msg"] = "Username or Email already used";
        $view = new View("signup", "Sign up");
        $view->send($params);
    }
    else {
        if ($user->create()) {
            $_SESSION["user_id"] = $user->id;
            header("Location: index.php");
        } else {
            $params["msg"] = "There was a problem! Please try again later.";
            $view = new View("signup", "Sign up");
            $view->send($params);
        }
    }
}

$view = new View("signup", "Sign up");
$view->send();
