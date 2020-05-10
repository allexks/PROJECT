<?php

require_once "classes/models/Answer.class.php";
require_once "classes/models/Feedback.class.php";

/**
 * A question.
 */
class Question {

    const DB_TABLENAME = "questions";

    public $id;
    public $test_id;
    public $text;
    public $order_number;

    public $answers;
    public $feedback;

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
        $this->test_id = (int)$row["test_id"];
        $this->order_number = (int)$row["order_number"];
        $this->text = $row["text"];

        return true;
    }

    public function fetchAnswers() {
        $questionstable = self::DB_TABLENAME;
        $answerstable = Answer::DB_TABLENAME;

        $query = "SELECT a.*
                  FROM $answerstable a
                  JOIN $questionstable q
                  ON a.question_id = q.id
                  WHERE q.id = :id";

        $stmt = $this->conn->prepare($query);
        $questionid = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(":id", $questionid);

        if (!$stmt->execute()) {
            error_log("[!!] CRITICAL: SQL query unsucessful: "
                . $stmt->errorInfo()[2]);
            $this->answers = [];
            return false;
        }

        $rows_count = $stmt->rowCount();

        if ($rows_count <= 0) {
            $this->answers = [];
            return true;
        }

        $result = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $ans = new Answer($this->conn);
            $ans->id = (int)$row["id"];
            $ans->question_id = (int)$row["question_id"];
            $ans->is_correct = (bool)$row["is_correct"];
            $ans->text = $row["text"];
            $result[] = $ans;
        }

        $this->answers = $result;
        return true;
    }

    public function fetchFeedback($with_user_id = null) {
        $userstmt = isset($with_user_id) && !empty($with_user_id) ? "AND f.user_id = :userid" : "";

        $questionstable = self::DB_TABLENAME;
        $fbtable = Feedback::DB_TABLENAME;

        $query = "SELECT f.*
                  FROM $fbtable f
                  JOIN $questionstable q
                  ON f.question_id = q.id
                  WHERE q.id = :id
                  $userstmt";

        $stmt = $this->conn->prepare($query);
        $questionid = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(":id", $questionid);
        if (isset($with_user_id) && !empty($with_user_id)) {
            $stmt->bindParam(":userid", $with_user_id);
        }

        if (!$stmt->execute()) {
            error_log("[!!] CRITICAL: SQL query unsucessful: "
                . $stmt->errorInfo()[2]);
            $this->feedback = [];
            return false;
        }

        $rows_count = $stmt->rowCount();

        if ($rows_count <= 0) {
            $this->feedback = [];
            return true;
        }

        $result = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $fb = new Feedback($this->conn);
            $fb->id = (int)$row["id"];
            $fb->fetch();
            $result[] = $fb;
        }

        $this->feedback = $result;
        return true;
    }
}
