<?php

require_once "config/config.php";

/**
 * Class used for obtaining and storing an SQL connection.
 */
class Database {

    private $conn;

    public function getNewConnection() {
        $this->conn = null;

        $host = SETTINGS["DATABASE_HOST"];
        $db_name = SETTINGS["DATABASE_NAME"];
        $db_username = SETTINGS["DATABASE_USER"];
        $db_password = SETTINGS["DATABASE_PASSWORD"];

        try {
            $this->conn = new PDO(
                "mysql:host=$host;dbname=$db_name;charset=utf8",
                $db_username,
                $db_password
            );
        } catch (PDOException $exception) {
            error_log("[!!] FATAL: Database connection unsucessful: "
                . $exception->getMessage());
        }

        return $this->conn;
    }
}
