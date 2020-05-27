<?php

require_once "classes/util/View.class.php";
require_once "classes/util/Database.class.php";
require_once "classes/models/User.class.php";

session_start();

require "db.php";

unset($_SESSION["user_id"]);
$_SESSION["logout_msg"] = "Successful log out.";
header("Location: index.php");
