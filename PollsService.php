<?php
namespace LAZ\objects\kidsaz\services;

use Exception;
use LAZ\objects\kidsaz\dataAccess\PollsDbGateway;
use LAZ\objects\kidsaz\businessObjects\mobile\PushNotification;
use LAZ\objects\kidsaz\services\mobile\PushNotificationService;


class PollsService {
    private $pollsDBGateway;
    private $shardConfigId;

    public function __construct($shardConfigId) {
        $this->pollsDBGateway = new PollsDbGateway();
        $this->shardConfigId = $shardConfigId;
    }

    public function insertPollDataToDb($memberId, $pollQuestion, $pollOptions, $studentIds) {
        try {
            $pollId = $this->pollsDBGateway->insertPoll($memberId); //add validation testing
            $pollQuestionId = $this->pollsDBGateway->insertPollQuestion($pollId, $pollQuestion);
            $pollOptionIds = $this->pollsDBGateway->insertPollOptions($pollOptions, $pollQuestionId);
            $pollStudentAnswerIds =  $this->pollsDBGateway->insertPollStudentAnswers($pollQuestionId, $studentIds);
            return true;
        }
        catch(Exception $exception) { //this has not been tested yet
            if ($pollId) { $this->pollsDBGateway->deletePollData("poll", "poll_id", $pollId); }
            if ($pollQuestionId) { $this->pollsDBGateway->deletePollData("poll_question", "poll_question_id", $pollId); }
            if ($pollOptionIds) { $this->pollsDBGateway->deletePollData("poll_question_option", "poll_question_id", $pollQuestionId); }
            if ($pollStudentAnswerIds) { $this->pollsDBGateway->deletePollData("poll_question_student_answer", "poll_question_id", $pollQuestionId); }
            return false;
        }
    }


    public function sendNotification($pollQuestionText, $memberId, $title, $studentIds) {
        $pushNotification = new PushNotification();
        $pushNotification->setTitle($title);
        $pushNotification->setMessage($pollQuestionText);
        $pushNotification->includeRecipientId();
        $isTest = $_ENV['LP_INSTANCE'] != 'prod';
        $pushNotificationService = new PushNotificationService($isTest, $this->shardConfigId);
        $pushNotificationService->sendNotificationToStudents($pushNotification, $studentIds);
        return true;
    }
}