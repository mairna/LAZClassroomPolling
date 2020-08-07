<?php
namespace LAZ\objects\classroom\api;

use LAZ\objects\classroom\api\Assignments\AssignmentsRouter;
use LAZ\objects\classroom\api\Classrooms\ClassroomsRouter;
use LAZ\objects\classroom\api\CustomAssignment\CustomAssignmentRouter;
use LAZ\objects\classroom\api\CustomStandards\CustomStandardsRouter;
use LAZ\objects\classroom\api\DashboardCharts\DashboardChartsRouter;
use LAZ\objects\classroom\api\DownloadReports\DownloadReportsRouter;
use LAZ\objects\classroom\api\Email\EmailServiceApiRouter;
use LAZ\objects\classroom\api\GoogleClassroom\GoogleClassroomRouter;
use LAZ\objects\classroom\api\GradedVocabularyGame\GradedVocabularyGameRouter;
use LAZ\objects\classroom\api\Notifications\NotificationsRouter;
use LAZ\objects\classroom\api\RazPlusAdventure\RazPlusAdventureApiRouter;
use LAZ\objects\classroom\api\ReadingLevelPlacementPreview\ReadingLevelPlacementPreviewRouter;
use LAZ\objects\classroom\api\Reports\ReportsRouter;
use LAZ\objects\classroom\api\Reports\VocabularyGames\VocabularyGamesRouter;
use LAZ\objects\classroom\api\ScienceResources\ScienceResourcesRouter;
use LAZ\objects\classroom\api\GradedQuiz\GradedQuizRouter;
use LAZ\objects\classroom\api\SiteLicenses\SiteLicensesApiRouter;
use LAZ\objects\classroom\api\StateStandards\StateStandardsRouter;
use LAZ\objects\kidsaz\api\RewardCard\RewardCardTeacherCsiRouter;
use LAZ\objects\kidsaz\api\QrStudentLogin\QrLoginApiRouter;
use LAZ\objects\kidsaz\routes\LtiResourceSelectionApiRouter;
use LAZ\objects\library\KidsModuleAccessCheck;
use LAZ\objects\api\KidsRoleCheckMiddleware;
use LAZ\objects\api\BatchApiController;
use LAZ\objects\library\HttpMethod;
use LAZ\objects\library\Router\Router;
use LAZ\objects\library\Router\Endpoint;
use LAZ\objects\classroom\api\SharedStudents\SharedStudentsRouter;
use LAZ\objects\classroom\api\Groups\GroupsRouter;
use LAZ\objects\classroom\api\StudentGroups\StudentGroupsRouter;
use LAZ\objects\classroom\api\Students\StudentsRouter;
use LAZ\objects\classroom\api\ClassChart\ClassChartRouter;
use LAZ\objects\classroom\api\ClassroomConfigs\ClassroomConfigsRouter;
use LAZ\objects\classroom\api\FileCabinet\FileCabinetRouter;
use LAZ\objects\classroom\api\InBasket\InBasketRouter;
use LAZ\objects\classroom\api\PaperRunningRecords\PaperRunningRecordsRouter;
use LAZ\objects\classroom\api\AssignResources\AssignResourcesRouter;
use LAZ\objects\classroom\api\Messages\MessagesRouter;
use LAZ\objects\classroom\api\StudentsWriting\StudentsWritingRouter;
use LAZ\objects\classroom\api\StudentsScience\StudentsScienceRouter;
use LAZ\objects\classroom\api\StudentsReadyTest\StudentsReadyTestRouter;
use LAZ\objects\classroom\api\StudentsPhonics\StudentsPhonicsRouter;
use LAZ\objects\classroom\api\Bookroom\BookroomRouter;
use LAZ\objects\classroom\api\TeacherUtility\TeacherUtilityRouter;
use LAZ\objects\classroom\api\TargetedResources\TargetedResourcesRouter;
use LAZ\objects\classroom\api\Projectable\ProjectableRouter;
use LAZ\objects\shared\api\NotificationApiRouter;
use LAZ\objects\resource\api\ResourceRouter;
use LAZ\objects\classroom\api\Polls\PollsRouter;
use LAZ\objects\tools\FeatureCheck;

class TeacherApiRouter extends Router {

    public function __construct() {
        $middleware = [
            new KidsRoleCheckMiddleware([ KidsModuleAccessCheck::ROLE_TEACHER ])
        ];

        parent::__construct($middleware);
    }

    protected function registerRoutes() {
        $this->addRoute( new AssignmentsRouter() );
        $this->addRoute( new AssignResourcesRouter() );
        $this->addRoute( new BookroomRouter() );
        $this->addRoute( new ClassChartRouter() );
        $this->addRoute( new ClassroomConfigsRouter() );
        $this->addRoute( new DashboardChartsRouter() );
        $this->addRoute( new DownloadReportsRouter());
        $this->addRoute( new FileCabinetRouter() );
        $this->addRoute( new GroupsRouter() );
        $this->addRoute( new InBasketRouter() );
        $this->addRoute( new MessagesRouter() );
        $this->addRoute( new NotificationsRouter() );
        $this->addRoute( new PaperRunningRecordsRouter() );
        $this->addRoute( new ProjectableRouter() );
        $this->addRoute( new ResourceRouter() );
        $this->addRoute( new ScienceResourcesRouter() );
        $this->addRoute( new GradedQuizRouter() );
        $this->addRoute( new GradedVocabularyGameRouter() );
        $this->addRoute( new SharedStudentsRouter() );
        $this->addRoute( new StudentGroupsRouter() );
        $this->addRoute( new StudentsPhonicsRouter() );
        $this->addRoute( new StudentsReadyTestRouter() );
        $this->addRoute( new StudentsRouter() );
        $this->addRoute( new StudentsScienceRouter() );
        $this->addRoute( new StudentsWritingRouter() );
        $this->addRoute( new TargetedResourcesRouter() );
        $this->addRoute( new TeacherUtilityRouter() );
        $this->addRoute( new VocabularyGamesRouter());
        $this->addRoute( new RazPlusAdventureApiRouter() );
        $this->addRoute( new ReadingLevelPlacementPreviewRouter() );
        $this->addRoute( new LtiResourceSelectionApiRouter() );
        $this->addRoute( new EmailServiceApiRouter());
        $this->addRoute( new ReportsRouter());
        $this->addRoute( new StateStandardsRouter() );
        $this->addRoute( new CustomStandardsRouter() );
        $this->addRoute( new CustomAssignmentRouter() );
        $this->addRoute( new ClassroomsRouter() );
        $this->addRoute( new SiteLicensesApiRouter() );
        $this->addRoute( new QrLoginApiRouter());
        $this->addRoute( new NotificationApiRouter() );
        $this->addRoute( new GoogleClassroomRouter() );
        if(FeatureCheck::hasFeatureEnabled("CLASSROOM_POLLING")) {
            $this->addRoute( new PollsRouter() );
        }
        $this->addRoute( new Endpoint('/batch', HttpMethod::POST, new BatchApiController()) );

        $this->addRoute ( new RewardCardTeacherCsiRouter () );
    }
}
