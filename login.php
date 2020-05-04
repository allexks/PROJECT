<?php

require_once "config/config.php";

// TODO

$template_dir = SETTINGS["TEMPLATES_DIR"] . "/" . SETTINGS["TEMPLATE_NAME"];
include "$template_dir/login.html";
