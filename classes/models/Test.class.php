<?php

require_once "classes/models/Question.class.php";
require_once "classes/models/Question.class.php";

/**
 * A test.
 */
class Test {

    const DB_TABLENAME = "tests";

    public $id;
    public $user_id;
    public $title;
    public $time_uploaded;

    public $questions;

    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function fetch() {
        $table = self::DB_TABLENAME;

        $query = "SELECT *
                  FROM $table
                  WHERE id = :id
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
        $this->user_id = (int)$row["user_id"];
        $this->time_uploaded = $row["time_uploaded"];
        $this->title = $row["title"];

        return true;
    }

    public static function fetchAll($dbconnection) {
        $table = self::DB_TABLENAME;
        $userstable = User::DB_TABLENAME;

        $query = "SELECT t.*, u.username
                  FROM $table t
                  JOIN $userstable u
                  ON u.id = t.user_id
                  ORDER BY t.id DESC";

        $stmt = $dbconnection->prepare($query);

        if(!$stmt->execute()) {
            error_log("[!!] CRITICAL: SQL query unsucessful: "
                . $stmt->errorInfo()[2]);
            return false;
        }

        $rows_count = $stmt->rowCount();

        if ($rows_count <= 0) {
            return [];
        }

        $result = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $test = new Test($dbconnection);
            $test->id = (int)$row["id"];
            $test->user_id = (int)$row["user_id"];
            $test->time_uploaded = $row["time_uploaded"];
            $test->title = $row["title"];
            $test->username = $row["username"];
            $test->fetchQuestions();
            $result[] = $test;
        }

        return $result;
    }

    public function fetchQuestions() {
        $teststable = self::DB_TABLENAME;
        $questionstable = Question::DB_TABLENAME;

        $query = "SELECT q.*
                  FROM $questionstable q
                  JOIN $teststable t
                  ON q.test_id = t.id
                  WHERE t.id = :id
                  ORDER BY q.order_number ASC";

        $stmt = $this->conn->prepare($query);
        $testid = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(":id", $testid);

        if (!$stmt->execute()) {
            error_log("[!!] CRITICAL: SQL query unsucessful: "
                . $stmt->errorInfo()[2]);
            $this->questions = [];
            return false;
        }

        $rows_count = $stmt->rowCount();

        if ($rows_count <= 0) {
            $this->questions = [];
            return true;
        }

        $result = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $test = new Question($this->conn);
            $test->id = (int)$row["id"];
            $test->test_id = (int)$row["test_id"];
            $test->order_number = (int)$row["order_number"];
            $test->text = $row["text"];
            $test->fetchAnswers();
            $result[] = $test;
        }

        $this->questions = $result;
        return true;
    }
}
