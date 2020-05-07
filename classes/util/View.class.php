<?php

require_once "config/config.php";

/**
 * Class used for managing and using HTML temlates.
 */
class View {

    private $module;

    const TEMPLATE_DIR = SETTINGS["TEMPLATES_DIR"] . "/" . SETTINGS["TEMPLATE_NAME"];

    public function __construct($module_name) {
        $this->module = $module_name;
    }

    public function send() {
        include self::TEMPLATE_DIR . "/{$this->module}.html";
        exit(0);
    }
}
