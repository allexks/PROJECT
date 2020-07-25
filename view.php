<?php

require_once "classes/models/Test.class.php";
require_once "classes/models/Feedback.class.php";

session_start();

require "includes/db.php";
$can_manage_test = false;

// Get test ID

$test_id = $_GET["id"] ?? "0";
$test_id = (int)$test_id;

if (!$test_id) {
    $view = new View("not_found", "Not Found");
    $view->send();
}

// Get test info

$test = new Test($db);
$test->id = $test_id;

if (!$test->fetch()) {
    $view = new View("not_found", "Not Found");
    $view->send();
}

if (!$test->fetchQuestions()) {
    $view = new View("database_error", "Database error!");
    $view->send();
}

$question_classes = [];
$sum = 0;
$max = 0;
$is_viewing_results = false;

$give_feedback_mode = false;
$view_feedback_mode = false;

// Get user ID

$user_id = $_SESSION["user_id"] ?? "0";
$user_id = (int)$user_id;

if ($user_id && $user_id == $test->user_id) {
    $give_feedback_mode = false;
    $view_feedback_mode = true;
    $can_manage_test = true;
} elseif ($user_id) {
    $give_feedback_mode = true;
    $view_feedback_mode = false;
    $can_manage_test = false;
}

// Perform results check if the form is submitted

if (isset($_POST["submit"]) || isset($_POST["feedback"])) {

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

            $wrong = false;
            foreach ($question->answers as $ind_a => $answer) {
                if ($answer->id != $answer_id) {
                    continue;
                }

                if ($answer->is_correct) {
                    $correct_count++;
                } else {
                    // Even one wrong answer is enough to mark the question as wrongly answered.
                    $correct_count = 0;
                    $wrong = true;
                    break;
                }
            }

            if ($wrong) {
                break;
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

        if ($question->isOpen()) {
            $question_class = "partial";
        }

        $question_classes[(string)$question_id] = $question_class;
        $sum += $correct_count;
        $max += $all_possible_count;
    }
}


if (isset($_POST["feedback"]) && $user_id) {
    foreach ($_POST as $key => $value) {

        // Feedback for the questions are in string named "fXXX" where "XXX" is the id of the question

        if (!preg_match("/^[fF][0-9]+$/", $key)) {
            continue;
        }

        $question_id = substr($key, 1, strlen($key) - 1) ?? "0";
        $question_id = (int)$question_id;

        if (!$question_id) {
            continue;
        }

        // Update feedback

        $feedback = new Feedback($db);
        $feedback->question_id = $question_id;
        $feedback->user_id = $user_id;

        if ($feedback->fetchGivenUserAndQuestionIfAvailable() === false) {
            // error with the query; skip it for now
            continue;
        }

        $feedback->text = $value;

        if (!$feedback->id && !empty($value)) {
            // No previous feedback here.
            // If there is such now, let's INSERT it into the database.
            $feedback->create();

        } elseif (empty($value)) {
            // Empty text field means no feedback => delete the entry.
            $feedback->delete();

        } else {
            $feedback->update();
        }
    }
}

// Leave feedback

$feedback_for_question = [];

if ($give_feedback_mode) {
    foreach ($test->questions as $ind_q => $question) {
        $question->fetchFeedback($user_id);

        if (!isset($question->feedback) || !isset($question->feedback[0])) {
            $feedback_for_question[(string)$question->id] = "";
            continue;
        }

        $feedback_for_question[(string)$question->id] = $question->feedback[0]->text;
    }
}

// View the feedback

if ($view_feedback_mode) {
    foreach ($test->questions as $ind_q => $question) {
        $question->fetchFeedback();

        if ($question->feedback === null) {
            $question->feedback = [];
        }
    }
}

// Prepare output

$params = [
    "title" => $test->title ?? "",
    "questions" => $test->questions ?? [],
    "question_classes" => $question_classes,
    "sum" => $sum,
    "max" => $max,
    "is_viewing_results" => $is_viewing_results,
    "give_feedback_mode" => $give_feedback_mode,
    "view_feedback_mode" => $view_feedback_mode,
    "feedback_for_question" => $feedback_for_question,
    "can_manage_test" => $can_manage_test,
];

foreach ($params["questions"] as $ind_q => $question) {
    $qkey = (string)$question->id;
    if (!isset($params["question_classes"][$qkey])) {
        $params["question_classes"][$qkey] = "";
    }
}

$view = new View("view_test", "View test");
$view->send($params);
