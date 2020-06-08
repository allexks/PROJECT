<?php
  require_once "classes/models/Delete.class.php";

  session_start();

  require "includes/db.php";
  require "includes/user_id.php";

  $delete = new Delete($db);

  $result = $delete->deleteTest($_SESSION["user_id"], $_POST["test-title"]);

  if ($result === false) {
    $view = new View("delete-response-error", "Delete test");
  }
  else {
    $view = new View("delete-response-success", "Delete test");
  }

  $view->send();
?>
