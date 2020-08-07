<?php

namespace LAZ\objects\kidsaz\dataAccess;

use LAZ\objects\data\DataManager;
use LAZ\objects\library\FormatQuery;
use LAZ\objects\library\SessionShardRetriever;
use Exception;

class PollsDbGateway {
    private $formatQuery;
    private $rkActivityDm;
    private $shardConfigurationId;

    public function __construct($shardConfigurationId = null) {
        $this->rkActivityDm = new DataManager(DataManager::DB_RK_ACTIVITY, DataManager::LOC_MASTER, $this->getShardConfigurationId());
        $this->formatQuery = new FormatQuery();
        $this->shardConfigurationId = $shardConfigurationId;
    }

    public function getShardConfigurationId() {
        return isset($this->shardConfigurationId) ? $this->shardConfigurationId : SessionShardRetriever::getSessionShardConfigurationId();
    }

    public function insertPoll($memberId) {
        $pollTableInsertValues = array(
            'sent_by_member_id' => $memberId,
            'created_at' => date('Y-m-d H:i:s')
        );
        $pollSQL = $this->formatQuery->generateInsertSQL('poll', $pollTableInsertValues);
        if ($this->rkActivityDm->query($pollSQL)) {
            return $this->rkActivityDm->lastId();
        }
        else {
            throw new Exception('Invalid poll entry');
        }
    }

    public function insertPollQuestion($pollId, $pollQuestion) {
        $pollQuestionTableInsertValues = array(
            'poll_id' => $pollId,
            'poll_question_text' => $pollQuestion
        );

        $pollQuestionSQL = $this->formatQuery->generateInsertSQL('poll_question', $pollQuestionTableInsertValues);

        if ($this->rkActivityDm->query($pollQuestionSQL)) {
            return $this->rkActivityDm->lastId();
        }
        else {
            throw new Exception('Invalid poll question entry');
        }
    }

    public function insertPollOptions($pollOptions, $pollQuestionId) {
        $pollOptionIds = array();
        foreach($pollOptions as $pollOption) {
            $pollQuestionOptionTableInsertValues = array(
                'poll_question_id' => $pollQuestionId,
                'poll_question_option_text' => $pollOption[text]
            );

            $pollQuestionOptionSQL = $this->formatQuery->generateInsertSQL('poll_question_option', $pollQuestionOptionTableInsertValues);

            if ($this->rkActivityDm->query($pollQuestionOptionSQL)) {
                array_push($pollOptionIds, $this->rkActivityDm->lastId());
            }
            else {
                throw new Exception('Invalid poll option entry');
            }
        }
        return $pollOptionIds;
    }

    public function insertPollStudentAnswers($pollQuestionId, $studentIds) {
        $pollStudentAnswerIds = array();
        foreach($studentIds as $studentId) {
            $pollQuestionStudentAnswerTableInsertValues = array(
                'poll_question_id' => $pollQuestionId,
                'student_id' => $studentId
            );

            $pollQuestionOptionAnswerSQL = $this->formatQuery->generateInsertSQL('poll_question_student_answer', $pollQuestionStudentAnswerTableInsertValues);

            if ($this->rkActivityDm->query($pollQuestionOptionAnswerSQL)) {
                array_push($pollStudentAnswerIds, $this->rkActivityDm->lastId());
            }
            else {
                throw new Exception('Invalid poll student answer entry');
            }
        }
        return $pollStudentAnswerIds;
    }

    public function deletePollData($tablename, $column, $value) {
        $sql = "DELETE FROM {$tablename} WHERE {$column} = {$value}";
        $this->rkActivityDm->query($sql);
    }
}