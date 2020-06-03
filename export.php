<?php

session_start();

require "includes/db.php";
require "includes/user_id.php";

$view = new View("export", "Export");
$view->send();
