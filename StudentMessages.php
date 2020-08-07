<?php
namespace LAZ\objects\modules\razkids\StudentMessages;


use LAZ\objects\base\Controller;
use LAZ\objects\kidsaz\dataAccess\StudentPollsDbGateway;
use LAZ\objects\kidsaz\services\MessagesService;
use LAZ\objects\kidsaz\services\StudentMessagesService;
use LAZ\objects\kidsaz\services\StudentPollsService;
use LAZ\objects\razkids\StudentInfoCache;
use LAZ\objects\tools\FeatureCheck;
use LAZ\objects\library\RKTeacherHelpers;
use LAZ\objects\tools\BadSessionContextLogger;
use LAZ\objects\library\RKStudentHelpers;
class StudentMessages extends Controller
{
    /**
     * @var StudentMessagesModel
     */
    public $model;
    private $teacherAccountID; //set from SESSION
    private $memberId;

    function __construct(){
	     
		parent::__construct();
	}

	function control(){
		$studentId = null;
		
        $featureCheck = new FeatureCheck();
        $teacherHelpers = new RKTeacherHelpers();
        $shardConfigId = $this->getShardConfigurationId();

		// If user is not logged in, redirect to login form
		if (!isset($_SESSION['authorized']) && !StudentInfoCache::isAuthenticated() && $_SESSION['parentInfo']['authenticated'] != 'y') {
			$_SESSION['redirectUri'] = $_SERVER['REQUEST_URI'];
			$this->redirect("/main/Login");
		}

       	if ($_SESSION['subscriptionUsageViewCurrentTeacherId']) {
       		$this->teacherAccountID = $_SESSION['subscriptionUsageViewCurrentTeacherId'];
       		$this->view->assign("teacherID", $this->teacherAccountID);
            $this->memberId = RKTeacherHelpers::getMemberIdFromAccountId($this->teacherAccountID);
       		$this->view->assign("teacher_name", $teacherHelpers->getTeacherName($this->memberId));
       	} else {
       		$this->teacherAccountID = $_SESSION['account_id'];
            $this->memberId = RKTeacherHelpers::getMemberIdFromAccountId($this->teacherAccountID);
       	}
       	
	    if ($_GET['action'] === 'getTeacherAudioMessage') {
	       	$this->model->getTeacherAudioMessage($_GET['subdirectory'], $_GET['filename'], $_GET['type'], $shardConfigId);
	    } else if ($_GET['action'] === 'markAudioMessageAsRead') {
	        $messageRecordingId = $_POST['messageRecordingId'];
	        if (is_numeric($messageRecordingId)) {
    		    $this->model->markAudioMessageAsRead($messageRecordingId, $shardConfigId);
	        } else {
	            BadSessionContextLogger::logBadSessionContext("markAudioMessageAsRead handler did not find numeric messageRecordingId in POST: [$messageRecordingId].");
	        }
	    }

		if ($_GET['view'] != null){
		    //page request
			$view = $_GET['view'];

			switch($view){
				//student retrieving new messages
				case "getMessages":
                default:
				    $studentHelpers = new RKStudentHelpers();

				    $studentId = StudentInfoCache::getStudentId();
				    if (is_numeric($studentId)) {
                        $studentMessagesService = new StudentMessagesService($shardConfigId);
                        $studentPollsService = new StudentPollsService($shardConfigId);
                        $messages = $studentMessagesService->getMessages($studentId);
                        if ($this->isFeatureEnabled("CLASSROOM_POLLING")) {
                            $polls = $studentPollsService->getPolls($studentId);
                            $messages = $studentPollsService->mergeMessagesAndPollsIntoMessages($messages, $polls);
                        }
                        $isIntermediateInterfaceEnabled = StudentInfoCache::isIntermediateInterfaceEnabled();
                        $this->model->markAllMessagesRead($studentId, $shardConfigId);
                        MessagesService::invalidateStudentMessagesInfoCache();

                        $headerData = $studentHelpers->getHeaderData($studentId);
                    } else {
				        $messages = array();
				        $headerData = null;
                        $isIntermediateInterfaceEnabled = false;
                    }

                    $this->view->assign('isIntermediateInterface', $isIntermediateInterfaceEnabled);
                    $this->view->assign('messages', $messages);
                    $this->view->assign("tab_context", "messages");
                    $this->view->assign("useNewRecorder", false);
                    $this->view->assign('returnUrl', '/main/StudentPortal');
                    $this->view->assign('returnButtonText', 'Home');
					$this->view->assign("content", "StudentMessages - view");
					$this->view->assign("headerData", $headerData);
					$this->view->assign("title", "Your Messages");//Fb #13177
				break;
			}
		}
	}

    private function getShardConfigurationId() {
        return $_SESSION['teacherAccountInfo']['shardConfigurationId'];
    }
}


