<?php
namespace LAZ\objects\classroom\api\Polls;

use LAZ\objects\library\Router\ControllerRouter;

class PollsRouter extends ControllerRouter {

    public function __construct() {
        parent::__construct(PollsApiController::class, '/polls');
    }

    protected function registerRoutes() {
        $this->post('/sendStudentPoll', 'sendStudentPoll');
    }

}