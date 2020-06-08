<?php

require_once "classes/models/User.class.php";
require_once "classes/util/Database.class.php";
require_once "classes/models/Test.class.php";
require_once "classes/models/Question.class.php";
require_once "classes/models/Answer.class.php";

class Delete {
  private $conn;

  public function __construct($db) {
      $this->conn = $db;
  }

  public function deleteTest($test_user_id, $testtitle) {
  	$testtable = Test::DB_TABLENAME;

  	$search_test_query = "SELECT t.*
  						FROM $testtable t
  						WHERE t.title = :testtitle AND t.user_id = :test_user_id";

      $stmt = $this->conn->prepare($search_test_query);
      $prep_testtitle = htmlspecialchars(strip_tags($testtitle));
      $prep_userid = htmlspecialchars(strip_tags($test_user_id));
      $stmt->bindParam(":testtitle", $prep_testtitle);
      $stmt->bindParam(":test_user_id", $prep_userid);

      if (!$stmt->execute()) {
          error_log("[!!] CRITICAL: SQL query unsucessful: "
              . $stmt->errorInfo()[2]);
          return false;
      }

  	$rows_count = $stmt->rowCount();

  	if ($rows_count > 0) {
      $test_id = $stmt->fetch(PDO::FETCH_ASSOC);

      $result = $this->deleteQuestion((int)$test_id["id"]);

      if ($result === true) {
        $delete_test_query = "DELETE t.*
      						FROM $testtable t
      						WHERE t.title = :testtitle AND t.user_id = :test_user_id";

        $delete_stmt = $this->conn->prepare($delete_test_query);
        $delete_stmt->bindParam(":testtitle", $prep_testtitle);
        $delete_stmt->bindParam(":test_user_id", $prep_userid);

        if (!$delete_stmt->execute()) {
            error_log("[!!] CRITICAL: SQL query unsucessful: "
                . $delete_stmt->errorInfo()[2]);
            return false;
        }

        return true;
      }
      else {
        return false;
      }
    }

    return false;
  }

  public function deleteQuestion($test_id) {
    $questiontable = Question::DB_TABLENAME;

    $search_question_query = "SELECT q.*
              FROM $questiontable q
              WHERE q.test_id = :test_id;";

    $stmt = $this->conn->prepare($search_question_query);
    $prep_test_id = htmlspecialchars(strip_tags($test_id));
    $stmt->bindParam(":test_id", $prep_test_id);

    if (!$stmt->execute()) {
        error_log("[!!] CRITICAL: SQL query unsucessful: "
            . $stmt->errorInfo()[2]);
        return false;
    }

    $rows_count = $stmt->rowCount();

    if ($rows_count <= 0) {
      // No questions for the current test
      return true;
    }

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $question_id = (int)$row["id"];

        $result = $this->deleteAnswer($question_id);

        if ($result === true) {
          // Delete question after deleting its answers
          $delete_question_query = "DELETE q.*
                    FROM $questiontable q
                    WHERE q.id = :question_id;";

          $delete_stmt = $this->conn->prepare($delete_question_query);
          $prep_question_id = htmlspecialchars(strip_tags($question_id));
          $delete_stmt->bindParam(":question_id", $prep_question_id);

          if (!$delete_stmt->execute()) {
              error_log("[!!] CRITICAL: SQL query unsucessful: "
                  . $delete_stmt->errorInfo()[2]);
              return false;
          }
        }
        else {
          return false;
        }
    }

    return true;
  }

  public function deleteAnswer($question_id) {
    $answertable = Answer::DB_TABLENAME;

    $search_answer_query = "SELECT a.*
              FROM $answertable a
              WHERE a.question_id = :question_id";

    $stmt = $this->conn->prepare($search_answer_query);
  	$prep_question_id = htmlspecialchars(strip_tags($question_id));
    $stmt->bindParam(":question_id", $prep_question_id);

    if (!$stmt->execute()) {
        error_log("[!!] CRITICAL: SQL query unsucessful: "
            . $stmt->errorInfo()[2]);
        return false;
    }

    $rows_count = $stmt->rowCount();

    if ($rows_count <= 0) {
      // No answers for the current question
      return true;
    }

    $delete_answer_query = "DELETE a.*
              FROM $answertable a
              WHERE a.question_id = :question_id";

    $delete_stmt = $this->conn->prepare($delete_answer_query);
    $delete_stmt->bindParam(":question_id", $prep_question_id);

    if (!$delete_stmt->execute()) {
        error_log("[!!] CRITICAL: SQL query unsucessful: "
            . $delete_stmt->errorInfo()[2]);
        return false;
    }

    return true;
  }
}
