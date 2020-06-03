<?php

session_start();

require "includes/db.php";
require "includes/user_id.php";

$view = new View("import", "Import");
$view->send();
