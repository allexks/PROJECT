<?php

require_once "classes/models/Test.class.php";
require_once "classes/util/Database.class.php";
require_once "classes/util/View.class.php";

session_start();

// Establish database connection

$database = new Database();
$db = $database->getNewConnection();

if (!$db) {
    $view = new View("database_error");
    $view->send();
}

// Get test ID

$test_id = $_GET["id"] ?? "0";
$test_id = (int)$test_id;

if (!$test_id) {
    $view = new View("not_found");
    $view->send();
}

// Get test info

$test = new Test($db);
$test->id = $test_id;

if (!$test->fetch()) {
    $view = new View("not_found");
    $view->send();
}

if (!$test->fetchQuestions()) {
    $view = new View("database_error");
    $view->send();
}

$question_classes = [];
$sum = 0;
$max = 0;
$is_viewing_results = false;

// Perform results check if the form is submitted

if (isset($_POST["submit"])) {

    $is_viewing_results = true;

    // Determine right and wrongly answered questions

    foreach ($_POST as $key => $value) {

        // Answers to the questions are in arrays named "qXXX" where "XXX" is the id of the question

        if (!preg_match("/^[qQ][0-9]+$/", $key)) {
            continue;
        }

        $question_id = substr($key, 1, strlen($key) - 1) ?? "0";
        $question_id = (int)$question_id;

        if (!$question_id) {
            continue;
        }

        // Find the question from the ones we have fetched with the test

        $question = null;
        foreach ($test->questions as $ind_q => $q) {
            if ($q->id == $question_id) {
                $question = $q;
                break;
            }
        }

        if (!$question) {
            continue;
        }

        $correct_count = 0;

        foreach ($value as $ind_ag => $answer_given) {

            // Given answers are in the "aXXX" format where "XXX" is the id of the answer

            if (!preg_match("/^[aA][0-9]+$/", $answer_given)) {
                continue;
            }

            $answer_id = substr($answer_given, 1, strlen($answer_given) - 1) ?? "0";
            $answer_id = (int)$answer_id;

            if (!$answer_id) {
                continue;
            }

            // Find the answer among the real ones

            foreach ($question->answers as $ind_a => $answer) {
                if ($answer->id != $answer_id) {
                    continue;
                }

                if ($answer->is_correct) {
                    $correct_count++;
                } else {
                    // Even one wrong answer is enough to mark the question as wrongly answered.
                    $correct_count = 0;
                    break;
                }
            }
        }

        $all_possible_count = 0;

        // Calculate all possible right answers for this question

        foreach ($question->answers as $ind_a => $answer) {
            if ($answer->is_correct) {
                $all_possible_count++;
            }
        }

        // Determine the question status

        $ratio = $correct_count / $all_possible_count;

        $question_class = "partial";

        if ($ratio == 1) {
            $question_class = "correct";
        } elseif ($ratio == 0) {
            $question_class = "wrong";
        }

        $question_classes[(string)$question_id] = $question_class;
        $sum += $correct_count;
        $max += $all_possible_count;
    }
}

$params = [
    "title" => $test->title ?? "",
    "questions" => $test->questions ?? [],
    "question_classes" => $question_classes,
    "sum" => $sum,
    "max" => $max,
    "is_viewing_results" => $is_viewing_results,
];

foreach ($params["questions"] as $ind_q => $question) {
    $qkey = (string)$question->id;
    if (!isset($params["question_classes"][$qkey])) {
        $params["question_classes"][$qkey] = "";
    }
}

$view = new View("view_test");
$view->send($params);
