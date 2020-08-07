<?php
namespace LAZ\objects\classroom\api\Polls;

use GuzzleHttp\Psr7\ServerRequest;
use LAZ\objects\kidsaz\dataAccess\PollsDbGateway;
use LAZ\objects\library\PHPUtil;
use LAZ\objects\library\Router\Resource;
use Psr\Http\Message\ServerRequestInterface;
use LAZ\objects\kidsaz\services\PollsService;
use LAZ\objects\library\SessionShardRetriever;
use LAZ\objects\kidsaz\services\StudentPollsService;
use LAZ\objects\razkids\StudentInfoCache;

class PollsApiController implements Resource {

    private $resource;
    private $pollsDBGateway;
    private $pollsService;
    private $studentPollsService;
    private $sessionShard;

    public function __construct() {
        $this->pollsDBGateway = new PollsDbGateway();
        $this->sessionShard = new SessionShardRetriever();
        $this->pollsService = new PollsService($this->sessionShard->getSessionShardConfigurationId());
        $this->studentPollsService = new StudentPollsService($this->sessionShard->getSessionShardConfigurationId());
    }

    public function setResource($resource) {
        $this->resource = $resource;
    }

    public function sendStudentPoll(ServerRequestInterface $request) {
        $studentIds = $this->resource['student_ids'];
        $memberId = (int)$_SESSION['teacherAccountInfo']['member_id'];
        $title = $_SESSION['teacherAccountInfo']['first_name'] . ' ' . $_SESSION['teacherAccountInfo']['last_name'];
        $pollQuestion = PHPUtil::stripSpecialCharacters($this->resource['poll_question']);
        $pollOptions = $this->resource['poll_options'];
        if ($this->pollsService->insertPollDataToDb($memberId, $pollQuestion, $pollOptions, $studentIds)) {
            $this->pollsService->sendNotification($pollQuestion, $memberId, $title, $studentIds);
        }
    }

    public function sendStudentResponseFromPoll(ServerRequestInterface $request) {
        $pollQuestionId = $this->resource['poll_question'];
        $selectedPollOption = $this->resource['student_selected_option'];
        $studentId = StudentInfoCache::getStudentId();
        $this->studentPollsService->updateStudentAnswerForPoll($pollQuestionId, $selectedPollOption, $studentId);
    }

}