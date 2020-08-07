<?php
namespace LAZ\objects\kidsaz\dataAccess;

use LAZ\objects\data\DataManager;

class StudentPollsDbGateway {

    private $rkActivityMasterDm;

    public function __construct($shard) {
        $this->rkActivityMasterDm = new DataManager(DataManager::DB_RK_ACTIVITY, DataManager::LOC_MASTER, $shard);
    }

    public function setDataManager($dataManager) {
        $this->rkActivityMasterDm = $dataManager;
    }

    public function getPolls($studentId) {
        $sql = "SELECT poll_question.poll_question_id,
                    poll_question.poll_question_text AS message, 
                    concat(member.first_name, ' ', member.last_name) as sender, 
                    poll.created_at AS created_at_datetime, 
                    poll.sent_by_member_id AS member_id,
                    poll_question_option.poll_question_option_id,
                    poll_question_option.poll_question_option_text,
                    poll_question_student_answer.answered_at
                FROM poll_question
                JOIN poll ON poll_question.poll_id = poll.poll_id 
                JOIN accounts.member ON member.member_id = poll.sent_by_member_id
                JOIN poll_question_option ON poll_question.poll_question_id = poll_question_option.poll_question_id
                JOIN poll_question_student_answer 
                    ON poll_question.poll_question_id = poll_question_student_answer.poll_question_id 
                    WHERE student_id = $studentId";
        $this->rkActivityMasterDm->query($sql);
        return $this->rkActivityMasterDm->fetchAll();
    }

    public function updateStudentAnswerForPoll($pollQuestionId, $studentSelectedPollOption, $studentId) {
        $selectedPollOptionId = "SELECT poll_question_option_id 
                                 FROM poll_question_option 
                                 WHERE poll_question_id = $pollQuestionId 
                                    AND poll_question_option_text = '". $studentSelectedPollOption. "' 
                                    LIMIT 1";
        $result = $this->rkActivityMasterDm->query($selectedPollOptionId);
        $optionId = 221;
        while($sub_row = $this->rkActivityMasterDm->fetch($result))
        {
            $optionId = $sub_row['poll_question_option_id'];
        }
        $sql = "UPDATE poll_question_student_answer
                SET answered_at = CURRENT_TIMESTAMP, poll_question_option_id = $optionId
                WHERE poll_question_id = $pollQuestionId AND student_id = $studentId";
        if ($this->rkActivityMasterDm->query($sql)) {
            return true;
        }

    }
}
