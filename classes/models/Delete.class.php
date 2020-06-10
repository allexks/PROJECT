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

      return true;
    }
}
