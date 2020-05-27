<?php

require_once "config/config.php";

/**
 * Class used for managing and using HTML temlates.
 */
class View {

    private $module;
    private $title;

    const TEMPLATE_DIR = SETTINGS["TEMPLATES_DIR"] . "/" . SETTINGS["TEMPLATE_NAME"];

    public function __construct($module_name, $page_title = "") {
        $this->module = $module_name;
        $this->title = $page_title;
    }

    public function send($params = array()) {
        include self::TEMPLATE_DIR . "/main.html";
        exit(0);
    }
}
