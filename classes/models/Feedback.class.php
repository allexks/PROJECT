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

    public function fetchGivenUserAndQuestionIfAvailable() {
        $table = self::DB_TABLENAME;

        $query = "SELECT *
                  FROM $table
                  WHERE question_id = :questionid
                  AND user_id = :userid
                  LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $this->question_id = htmlspecialchars(strip_tags($this->question_id));
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $stmt->bindParam(":questionid", $this->question_id);
        $stmt->bindParam(":userid", $this->user_id);

        if (!$stmt->execute()) {
            error_log("[!!] CRITICAL: SQL query unsucessful: "
                . $stmt->errorInfo()[2]);
            return false;
        }

        $rows_count = $stmt->rowCount();

        if ($rows_count <= 0) {
            return true;
        }

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->id = (int)$row["id"];
        $this->question_id = (int)$row["question_id"];
        $this->user_id = (int)$row["user_id"];
        $this->text = $row["text"];

        return true;
    }

    public function create() {
        $table = self::DB_TABLENAME;

        $query = "INSERT INTO $table
                  (
                    `question_id`,
                    `user_id`,
                    `text`
                  ) VALUES (
                    :questionid,
                    :userid,
                    :txt
                  )";

        $stmt = $this->conn->prepare($query);
        $this->question_id = htmlspecialchars(strip_tags($this->question_id));
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->text = htmlspecialchars(strip_tags($this->text));
        $stmt->bindParam(":questionid", $this->question_id);
        $stmt->bindParam(":userid", $this->user_id);
        $stmt->bindParam(":txt", $this->text);

        if (!$stmt->execute()) {
            error_log("[!!] CRITICAL: SQL query unsucessful: "
                . $stmt->errorInfo()[2]);
            return false;
        }

        return true;
    }

    public function update() {
        $table = self::DB_TABLENAME;

        $query = "UPDATE $table
                  SET `text` = :txt
                  WHERE `id` = :id";

        $stmt = $this->conn->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id));
        $this->text = htmlspecialchars(strip_tags($this->text));
        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":txt", $this->text);

        if (!$stmt->execute()) {
            error_log("[!!] CRITICAL: SQL query unsucessful: "
                . $stmt->errorInfo()[2]);
            return false;
        }

        return true;
    }

    public function delete() {
        $table = self::DB_TABLENAME;

        $query = "DELETE FROM $table WHERE `id` = :id";

        $stmt = $this->conn->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(":id", $this->id);

        if (!$stmt->execute()) {
            error_log("[!!] CRITICAL: SQL query unsucessful: "
                . $stmt->errorInfo()[2]);
            return false;
        }

        return true;
    }
}
