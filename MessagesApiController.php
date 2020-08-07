<?php
namespace LAZ\objects\classroom\api\Messages;

use LAZ\objects\library\Router\Exception\UnprocessableEntityException;
use LAZ\objects\library\Router\Resource;
use Psr\Http\Message\ServerRequestInterface;
use LAZ\objects\library\KidsModuleAccessCheck;
use LAZ\objects\library\AuthorizationException;
use LAZ\objects\kidsaz\services\MessagesService;

class MessagesApiController implements Resource {
    private $resource;
    private $messageService;
    private $kidsModuleAccessCheck;

    public function __construct() {
        $this->messageService = new MessagesService($this->getShardId());
        $this->kidsModuleAccessCheck = new KidsModuleAccessCheck();
    }

    public function setResource($resource) {
        $this->resource = $resource;
    }

    private function getShardId() {
        return (int)$_SESSION['teacherAccountInfo']['shardConfigurationId'];
    }

    private function checkIsValidStudentId($studentId) {
        if(!is_numeric($studentId)) {
            throw new UnprocessableEntityException('Invalid student id. Please try again.');
        }
    }

    private function checkStudentAccess($studentId, $access) {
        $this->checkIsValidStudentId($studentId);
        if (!$this->kidsModuleAccessCheck->haveStudentAccess($studentId, $access)) {
            throw new AuthorizationException();
        }
    }

    private function checkValidBonusStarsAward($stars) {
        if (!is_null($stars)
            && !MessagesService::isValidBonusStarsAward($stars)) {
            throw new \InvalidArgumentException('Invalid bonus star amount');
        }
    }

    public function sendStudentMessage(ServerRequestInterface $request) {
        $studentIds = $this->resource['student_ids'];
        $memberId = (int)$_SESSION['teacherAccountInfo']['member_id'];
        $this->checkValidBonusStarsAward($this->resource['bonus_stars']);
        foreach($studentIds as $studentId) {
            $this->checkIsValidStudentId($studentId);
            $this->checkStudentAccess($studentId, [KidsModuleAccessCheck::STUDENT_ACCESS_OWNER, KidsModuleAccessCheck::STUDENT_ACCESS_USER]);
        }
        return $this->messageService->sendStudentMessage($studentIds, $memberId, $this->resource);
    }

    public function getAllMessages(ServerRequestInterface $request) {
        $memberId = (int)$_SESSION['teacherAccountInfo']['member_id'];
        return $this->messageService->getAllMessages($memberId);
    }

    public function deleteClassMessage(ServerRequestInterface $request) {
        $memberId = (int)$_SESSION['teacherAccountInfo']['member_id'];
        $messageId = (int)$request->getAttribute('message_id');
        $this->messageService->deleteClassMessage($messageId, $memberId);
    }

    public function sendStudentPoll(ServerRequestInterface $request){
        // The /api/polls route should resolve here
    }
}
