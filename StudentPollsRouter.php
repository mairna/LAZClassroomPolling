<?php
namespace LAZ\objects\classroom\api\StudentPolls;

use LAZ\objects\library\Router\ControllerRouter;
use LAZ\objects\classroom\api\Polls\PollsApiController;

class StudentPollsRouter extends ControllerRouter {

    public function __construct() {
        parent::__construct(PollsApiController::class, '/StudentPolls');
    }

    protected function registerRoutes() {
        $this->post('/sendStudentResponseFromPoll', 'sendStudentResponseFromPoll');
    }

}
