<?php
namespace LAZ\objects\kidsaz\services;

use LAZ\objects\kidsaz\dataAccess\StudentPollsDbGateway;

class StudentPollsService {

    private $studentPollsDbGateway;
    private $studentMessagesService;

    public function __construct($shard) {
        $this->studentPollsDbGateway = new StudentPollsDbGateway($shard);
        $this->studentMessagesService = new StudentMessagesService($shard);
    }

    public function getPolls($studentId) {
        $pollList = $this->getPollsAsStructuredArray($studentId);
        $memberIds = array_unique(array_column($pollList, 'member_id'));
        $profilePics = $this->studentMessagesService->getProfilePics($memberIds);
        $this->addProfilePicsandDates($pollList, $profilePics);
        return $pollList;
    }

    private function getPollsAsStructuredArray($studentId) {
        $structuredPollsArray = array();
        $rawPolls = $this->studentPollsDbGateway->getPolls($studentId);
        foreach ($rawPolls as $rawPoll) {
            if(empty($structuredPollsArray[$rawPoll['poll_question_id']])) {
                $structuredPollsArray[$rawPoll['poll_question_id']] = array_diff_key($rawPoll, array_flip(['poll_question_option_id', 'poll_question_option_text']));
            }
            $structuredPollsArray[$rawPoll['poll_question_id']]['options'][] = $rawPoll['poll_question_option_text'];
        }
        return $structuredPollsArray;
    }
    
    public function setDBGateway($gateway) {
        $this->studentPollsDbGateway = $gateway;
    }

    public function addProfilePicsandDates(&$pollList, $profilePics) {
        foreach($pollList as &$poll) {
            $poll['profileSrc'] = $profilePics[$poll['member_id']];
            $poll['isPoll'] = true;
            $convertedDate = strtotime( $poll['created_at_datetime']);
            $poll['created_at'] = date( 'm/d/Y', $convertedDate );
        }
    }

    public function mergeMessagesAndPollsIntoMessages($messages, $polls) {
        $messages = array_merge($messages, $polls);
        $columns = array_column($messages, 'created_at_datetime');
        array_multisort($columns, SORT_DESC, $messages);
        return $messages;
    }

    public function updateStudentAnswerForPoll($pollQuestionId, $studentSelectedPollOption, $studentId) {
        if ($pollQuestionId && $studentSelectedPollOption && $studentId) {
            $this->studentPollsDbGateway->updateStudentAnswerForPoll($pollQuestionId, $studentSelectedPollOption, $studentId);
        }
    }
}
