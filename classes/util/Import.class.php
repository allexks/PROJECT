<?php

require_once "classes/models/User.class.php";
require_once "classes/util/Database.class.php";
require_once "classes/models/Test.class.php";
require_once "classes/models/Question.class.php";
require_once "classes/models/Answer.class.php";

class Import {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function importTest($test_user_id, $testtitle) {
    	$testtable = Test::DB_TABLENAME;

    	$search_test_query = "SELECT t.*
    						FROM $testtable t
    						WHERE t.title = :testtitle AND t.user_id = :test_user_id";

		$stmt = $this->conn->prepare($search_test_query);
        $prep_testtitle = htmlspecialchars(strip_tags($testtitle));
        $prep_test_user_id = htmlspecialchars(strip_tags($test_user_id));
        $stmt->bindParam(":testtitle", $prep_testtitle);
        $stmt->bindParam(":test_user_id", $prep_test_user_id);

        if (!$stmt->execute()) {
            error_log("[!!] CRITICAL: SQL query unsucessful: "
                . $stmt->errorInfo()[2]);
            return false;
        }

		$rows_count = $stmt->rowCount();

		if ($rows_count <= 0) {
	        $insert_test_query = "INSERT INTO $testtable
				                  (
				                      user_id,
				                      title
				                  )
				                  VALUES
				                  (
				                      :test_user_id,
				                      :testtitle
				                  )";

	  		$stmt_insert = $this->conn->prepare($insert_test_query);
	        $stmt_insert->bindParam(":test_user_id", $prep_test_user_id);
	        $stmt_insert->bindParam(":testtitle", $prep_testtitle);

	        if (!$stmt_insert->execute()) {
	            error_log("[!!] CRITICAL: SQL query unsucessful: "
	                . $stmt_insert->errorInfo()[2]);
	            return false;
	        }
        }

        $test_id = "SELECT t.id
						FROM $testtable t
						WHERE t.title = :testtitle AND t.user_id = :test_user_id";

			$stmt_get_id = $this->conn->prepare($test_id);
	        $stmt_get_id->bindParam(":testtitle", $prep_testtitle);
          $stmt_get_id->bindParam(":test_user_id", $prep_test_user_id);

			if (!$stmt_get_id->execute()) {
	            error_log("[!!] CRITICAL: SQL query unsucessful: "
	                . $stmt_get_id->errorInfo()[2]);
	            return false;
	        }

	        $rows_count = $stmt_get_id->rowCount();

	        if ($rows_count > 0) {
	        	$result = $stmt_get_id->fetch(PDO::FETCH_ASSOC);

       			return (int)$result["id"];
       		}

       	return false;
    }

    public function importQuestion($test_id, $question, $q_type) {
    	$questiontable = Question::DB_TABLENAME;

    	$search_question_query = "SELECT q.*
    						FROM $questiontable q
    						WHERE q.test_id = :test_id AND q.text = :question";

		$stmt = $this->conn->prepare($search_question_query);
		$prep_test_id = htmlspecialchars(strip_tags($test_id));
        $prep_question = htmlspecialchars(strip_tags($question));
        $stmt->bindParam(":test_id", $prep_test_id);
        $stmt->bindParam(":question", $prep_question);

        if (!$stmt->execute()) {
            error_log("[!!] CRITICAL: SQL query unsucessful: "
                . $stmt->errorInfo()[2]);
            return false;
        }

        $search_questions_query = "SELECT q.*
    						FROM $questiontable q
    						WHERE q.test_id = :test_id";

		$stmt_count = $this->conn->prepare($search_questions_query);
		$stmt_count->bindParam(":test_id", $prep_test_id);

		if (!$stmt_count->execute()) {
			error_log("[!!] CRITICAL: SQL query unsucessful: "
			. $stmt_count->errorInfo()[2]);
			return false;
		}

		$questions_count = $stmt_count->rowCount() + 1;

		$rows_count = $stmt->rowCount();

		if ($rows_count <= 0) {
	        $insert_question_query = "INSERT INTO $questiontable
				                  (
				                      test_id,
				                      type,
				                      text,
				                      order_number
				                  )
				                  VALUES
				                  (
				                      :test_id,
                              :type,
				                      :question,
				                      :order_number
				                  )";

	  		  $stmt_insert = $this->conn->prepare($insert_question_query);
	        $prep_order_num = htmlspecialchars(strip_tags($questions_count));
	        $prep_q_type = strtoupper(htmlspecialchars(strip_tags($q_type)));
	        $stmt_insert->bindParam(":test_id", $prep_test_id);
	        $stmt_insert->bindParam(":type", $prep_q_type);
	        $stmt_insert->bindParam(":question", $prep_question);
	        $stmt_insert->bindParam(":order_number", $prep_order_num);

	        if (!$stmt_insert->execute()) {
	            error_log("[!!] CRITICAL: SQL query unsucessful: "
	                . $stmt_insert->errorInfo()[2]);
	            return false;
	        }
        }
        else {
          return true;
        }

        $question_id = "SELECT q.*
    						FROM $questiontable q
    						WHERE q.test_id = :test_id AND q.text = :question";

		    $stmt_get_id = $this->conn->prepare($question_id);
        $stmt_get_id->bindParam(":test_id", $prep_test_id);
        $stmt_get_id->bindParam(":question", $prep_question);

		if (!$stmt_get_id->execute()) {
            error_log("[!!] CRITICAL: SQL query unsucessful: "
                . $stmt_get_id->errorInfo()[2]);
            return false;
        }

        $rows_count = $stmt_get_id->rowCount();

        if ($rows_count > 0) {
        	$result = $stmt_get_id->fetch(PDO::FETCH_ASSOC);

   			return (int)$result["id"];
   		}
   		else {
   			return false;
   		}
    }

    public function importAnswer($question_id, $answer, $correct) {
    	$answertable = Answer::DB_TABLENAME;

    	$search_answer_query = "SELECT a.*
    						FROM $answertable a
    						WHERE a.question_id = :question_id AND a.text = :answer";

		$stmt = $this->conn->prepare($search_answer_query);
		$prep_question_id = htmlspecialchars(strip_tags($question_id));
        $prep_answer = htmlspecialchars(strip_tags($answer));
        $stmt->bindParam(":question_id", $prep_question_id);
        $stmt->bindParam(":answer", $prep_answer);

        if (!$stmt->execute()) {
            error_log("[!!] CRITICAL: SQL query unsucessful: "
                . $stmt->errorInfo()[2]);
            return false;
        }

		$rows_count = $stmt->rowCount();

		if ($rows_count <= 0) {
	        $insert_answer_query = "INSERT INTO $answertable
				                  (
				                      question_id,
				                      text,
				                      is_correct
				                  )
				                  VALUES
				                  (
				                      :question_id,
				                      :answer,
				                      :is_correct
				                  )";

	  		$stmt_insert = $this->conn->prepare($insert_answer_query);

	        if ($correct == 100) {
	        	$prep_is_correct = 1;
	        }
	        else {
	        	$prep_is_correct = 0;
	        }

	        $stmt_insert->bindParam(":question_id", $prep_question_id);
	        $stmt_insert->bindParam(":answer", $prep_answer);
	        $stmt_insert->bindParam(":is_correct", $prep_is_correct);

	        if (!$stmt_insert->execute()) {
	            error_log("[!!] CRITICAL: SQL query unsucessful: "
	                . $stmt_insert->errorInfo()[2]);
	            return false;
	        }

	        return true;
        }
    }

    public function imported($test_user_id, $testtitle) {
      $testtable = Test::DB_TABLENAME;

      $search_test_query = "SELECT t.*
                FROM $testtable t
                WHERE t.title = :testtitle AND t.user_id = :test_user_id";

        $stmt = $this->conn->prepare($search_test_query);
        $prep_testtitle = htmlspecialchars(strip_tags($testtitle));
        $prep_test_user_id = htmlspecialchars(strip_tags($test_user_id));
        $stmt->bindParam(":testtitle", $prep_testtitle);
        $stmt->bindParam(":test_user_id", $prep_test_user_id);

        if (!$stmt->execute()) {
            error_log("[!!] CRITICAL: SQL query unsucessful: "
                . $stmt->errorInfo()[2]);
            return false;
        }

        $rows_count = $stmt->rowCount();
        if ($rows_count <= 0) {
          return false;
        }

        return true;
    }
}
