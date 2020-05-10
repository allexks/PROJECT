<?php

require_once "classes/models/User.class.php";

/**
 * A single feedback response.
 */
class Feedback {

    const DB_TABLENAME = "feedback";

    public $id;
    public $question_id;
    public $user_id;
    public $text;

    public $username;

    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function fetch() {
        $table = self::DB_TABLENAME;
        $userstable = User::DB_TABLENAME;

        $query = "SELECT f.*, u.username
                  FROM $table f
                  JOIN $userstable u
                  ON u.id = f.user_id
                  WHERE f.id = :id
                  LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(":id", $this->id);

        if (!$stmt->execute()) {
            error_log("[!!] CRITICAL: SQL query unsucessful: "
                . $stmt->errorInfo()[2]);
            return false;
        }

        $rows_count = $stmt->rowCount();

        if ($rows_count <= 0) {
            return false;
        }

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->id = (int)$row["id"];
        $this->question_id = (int)$row["question_id"];
        $this->user_id = (int)$row["user_id"];
        $this->text = $row["text"];
        $this->username = $row["username"];

        return true;
    }
}
