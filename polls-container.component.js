(function () {
    "use strict";
    angular.module('kids.messages')
        .component('pollsContainer', {
            templateUrl: '/js/angular/messages/polls-container.html',
            controller: 'PollsContainerCtrl',
            bindings: {
                poll: "<",
            }
        })
        .controller('PollsContainerCtrl', [ '$timeout', 'inDegradedMode', 'messageHandler', '$http',
            function PollsContainerCtrl($timeout, inDegradedMode, messageHandler, $http) {
                var ctrl = this;

                ctrl.$onInit = function () {
                    ctrl.inDegradedMode = inDegradedMode;
                    ctrl.created_at = ctrl.poll.created_at;
                    ctrl.profileSrc = ctrl.poll.profileSrc;
                    ctrl.sender = ctrl.poll.sender;
                    ctrl.text = ctrl.poll.message;
                    ctrl.options = ctrl.poll.options;
                    ctrl.questionId = ctrl.poll.poll_question_id;
                    ctrl.answered_status = ctrl.poll.answered_at;
                    ctrl.areOptionButtonsDisabled = (ctrl.poll.answered_at != null);
                };

                //ctrl.checkIfOptionButtonsAreDisabled = function () {
                //    ctrl.areOptionButtonsDisabled = ctrl.answered_status !== null;
                //}

                ctrl.isPollAnswered = function () {
                    return ctrl.answered_status !== null;
                }

                ctrl.pollIsAnswered = function() {
                    ctrl.areOptionButtonsDisabled = true;
                    ctrl.answered_status = "Has been answered";
                }

                ctrl.sendStudentResponse = function (option) {
                    ctrl.pollIsAnswered();
                    var studentResponseData = {
                        poll_question: ctrl.questionId,
                        student_selected_option: option
                    };
                    $http.post('/api/StudentPolls/sendStudentResponseFromPoll', studentResponseData)
                        .then(function(response){
                            ctrl.options[ctrl.options.indexOf(option)] = "You chose " + ctrl.options[ctrl.options.indexOf(option)] + ".";
                        })
                        .catch(function() {
                            messageHandler.error('There was a problem sending the message. Try again later.');
                        })
                };

                ctrl.disableOptionButtons = function () {
                    return ctrl.areOptionButtonsDisabled;
                };

            }]);
})();