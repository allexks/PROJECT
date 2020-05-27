<?php

require_once "classes/util/Database.class.php";
require_once "classes/models/User.class.php";
require_once "classes/util/View.class.php";

session_start();

require "includes/db.php";
require "includes/user_id.php";

$view = new View("import", "Import");
$view->send();
