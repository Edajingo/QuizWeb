<?php
/**
 * Date: 11/3/17
 * Time: 8:19 AM
 */
include_once "Constf.php";

class db
{
    /**
     * Queury's a specific question in a specific quiz
     *
     * @param $quizId the quiz id that the question is in
     * @param $questionId the question that is being queured in the quiz
     * @return array if there is data in the qeury or
     *         void if there is no data in the query
     */
    public static function querQuest($quizId, $questionId) {
        $conn = new mysqli(Constf::host, Constf::username, Constf::password, Constf::dbName);
        if ($conn->connect_error)
            die("Connection failed: " . $conn->connect_error);
        $sql = "SELECT Question_S FROM Question WHERE Question.Quiz_id=$quizId AND Question_id=$questionId";
        $questionS = $conn->query($sql);
        $sql = "SELECT Option_S FROM Options WHERE Question_id=$questionId";
        $optionsS = $conn->query($sql);
        $conn->close();
        if ($questionS->num_rows > 0 && $optionsS->num_rows > 0) {
            $final = array('questS' => $questionS, 'opts' => $optionsS);
            return $final;
        } else {
            echo "No Rows were Selected";
            return;
        }
    }

    /**
     * This method querys the questions for a given quiz
     *
     * @param $quizId int the quiz that has the questions
     * @return mysqli_result the questions for the quiz otherwise nothing
     */
    public static function querQuests($quizId) {
        $conn = new mysqli(Constf::host, Constf::username, Constf::password, Constf::dbName);
        if ($conn->connect_error)
            die("Connection failed: " . $conn->connect_error);
        $sql = "SELECT Question_id, Question_S FROM Question WHERE Quiz_id=$quizId";
        $result = $conn->query($sql);
        $conn->close();
        if ($result->num_rows > 0) {
            return $result;
        } else {
            echo "No Rows were Selected";
            return;
        }
    }

    /**
     * Querys the specific optinons for a given question
     *
     * @param $questionId int the question that holds all the options
     * @return mysqli_result the options for the quiz
     */
    public static function querOpts($questionId) {
        $conn = new mysqli(Constf::host, Constf::username, Constf::password, Constf::dbName);
        if ($conn->connect_error)
            die("Connection failed: " . $conn->connect_error);
        $sql = "SELECT Option_id, Option_S FROM Options WHERE Question_id=$questionId";
        $result = $conn->query($sql);
        $conn->close();
        if ($result->num_rows > 0) {
            return $result;
        } else {
            echo "No Rows were Selected";
            return;
        }
    }

    /**
     * basic query to get the Answer to the given question
     *
     * @param $questionId the question id for the specific answer
     * @return the result of the query if there is one
     */
    public static function querAnsw($questionId) {
        $conn = new mysqli(Constf::host, Constf::username, Constf::password, Constf::dbName);
        if ($conn->connect_error)
            die("Connection failed: " . $conn->connect_error);
        $sql = "SELECT Option_id, Reason FROM Answer WHERE Answer.Question_id=$questionId";
        $result = $conn->query($sql);
        $conn->close();
        if ($result->num_rows > 0)
            return $result;
        else {
            echo "No Rows were Selected";
            return;
        }
    }

    /**
     * Querys all data relevant to the specified quiz
     *
     * @param $quizId the quiz that is being queryed
     * @return the quiz information
     */
    public static function querQuiz($quizId) {
        $conn = new mysqli(Constf::host, Constf::username, Constf::password, Constf::dbName);
        if ($conn->connect_error)
            die("Connection failed: " . $conn->connect_error);
        $sql = "SELECT Quiz_name  FROM Quiz WHERE Quiz_id=$quizId";
        $result = $conn->query($sql);
        $conn->close();
        if ($result->num_rows > 0)
            return $result;
        else {
            echo "No Rows were Selected";
            return;
        }
    }

    /**
     * this method will insert a quiz into the database based
     * on the quiz object that was passed in
     *
     * @param $quiz the quiz object that will be inserted
     */
    public static function insertQuiz($quiz) {
        $questions = array();
        $options = array();
        $date = "'" . date("Y-m-d") . "'";
        $datetime = "'" . date("Y-m-d h:i:s") . "'";
        $name = "'" . $quiz->getQuizName() . "'";

        //unpack
        for ($i = 0; $i < $quiz->getAmntQuests(); $i++) {
            $questions[$i] = $quiz->getQuestions()[$i];
            for ($j = 0; $j < $quiz->getQuestions()[$i]->getAmntOpts(); $j++) {
                $options[$j] = $quiz->getQuestions()[$i]->getOptions()[$j];
            }
        }

        //connection
        $conn = new mysqli(Constf::host, Constf::username, Constf::password, Constf::dbName, 8889);
        if ($conn->connect_error) {
            echo "Connection Failed";
            die("Connection failed: " . $conn->connect_error);
        }

        //insert the quiz
        $stm = $conn->prepare("INSERT INTO quizweb.Quiz (Quiz_name, Url, Date_created, Date_updated)  VALUES (?, ?, ?, ?)");
        $quizUrl = "tesfda";
        $stm->bind_param("ssss", $name, $quizUrl, $date, $datetime);
        if ($stm-> execute() === true)
            $quizId = $stm->insert_id;
        $stm->close();

        //Inserting questions and options together
        $stmq = $conn->prepare("INSERT INTO quizweb.Question (Question_id, Quiz_id, Question_S) VALUES (?, ?, ?)");
        $stmo = $conn->prepare("INSERT INTO quizweb.Options (Option_id, Question_id, Option_S) VALUES (?, ?, ?)");
        $stma = $conn->prepare("INSERT INTO quizweb.answer (Option_id, Question_id, Reason) VALUES (?, ?, ?)");
        foreach ($questions as $q){
            $qId = $q->getQuestionId();
            $qStr = $q->getQuestionS();
            $stmq->bind_param("iis", $qId, $quizId, $qStr);
            $stmq->execute();
            foreach ($options as $o) {
                $oId = $o->getOptionId();
                $oStr = $o->getOptionS();
                $stmo->bind_param("iis", $oId, $qId, $oStr);
                $stmo->execute();
                if ($o->isAnswer()) {
                    $oReason = $o->getReason();
                    $stma->bind_param("iis", $oId, $qId, $oReason);
                    $stma->execute();
                }
            }
        }
        $stmq->close();
        $stmo->close();
        $stma->close();
    }
}























