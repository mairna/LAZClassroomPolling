(function() {
    "use strict";
    angular.module('kids')
        .component("classroomPolling", {
            templateUrl: "/js/angular/students/classroom-polling/classroom-polling.html",
            controller: "ClassroomPolling",
            bindings: {
                student: "<?"
            }
        })

        .controller("ClassroomPolling", ["FeatureCheck", "LazModalService", "messageHandler", function (FeatureCheck, LazModalService, messageHandler) {
                var ctrl = this;
                ctrl.showComponent = FeatureCheck.isFeatureEnabled("CLASSROOM_POLLING");
                ctrl.openModal = function($event) {
                    $event.stopPropagation();
                    LazModalService.showModal({
                        controller: 'PollingStudentsModal',
                        controllerAs: '$ctrl',
                        hideCloseButton: 'true',
                        overrideClass: 'js-none',
                        template: '<polling-students close="$ctrl.close" student="$ctrl.student"></polling-students>',
                        inputs: {
                            student: ctrl.student
                        }
                    })
                        .catch(function () {
                            messageHandler.error("There was a problem contacting the server. Please try again.");
                        });
                };
            }])
        .controller('PollingStudentsModal', ['close', function (close) {
            var ctrl = this;

            ctrl.close = function() {
                var html5Elem = angular.element('#html5-recorder');
                html5Elem.hide();
                angular.element('#html5-recorder-wrapper').append(html5Elem);

                close();
            };
        }])
        .component('pollingStudents', {
            bindings: {
                close: "<"
            },
            controller: 'PollingStudents',
            templateUrl: '/js/angular/students/classroom-polling/poll-modal.html'
        })
        .controller('PollingStudents', ["$http", "messageHandler", "students", function($http, messageHandler, students) {
                var ctrl = this;
                ctrl.students = students.get();
                ctrl.minimumPollOptions = 2;
                ctrl.maximumPollOptions = 6;
                ctrl.pollQuestion = "";

                ctrl.studentIds = ctrl.students.map(function(student) {
                    return student.student_id
                });

                ctrl.close = function () {
                    close();
                };

                ctrl.options = [{text: ''}, {text: ''}];

                ctrl.addNewOption = function() {
                    ctrl.options.push({text: ''});
                };

                ctrl.removeOption = function(optionName) {
                    if ( (ctrl.options.length) !== ctrl.minimumPollOptions) {
                        ctrl.options.splice(optionName, 1);
                    }
                };

                ctrl.disableAddOption = function() {
                    return ctrl.options.length >= ctrl.maximumPollOptions;
                };

                ctrl.pollInputsAreValid = function() {
                    if(ctrl.pollQuestion == "") {
                        messageHandler.error('There must be a question in the poll. Please try again.');
                        return false;
                    }
                    var count = 0;
                    //var options_filled = false;
                    for (var i = 0; i < ctrl.options.length; i++) {
                        if (ctrl.options[i].text == "") {
                            messageHandler.error('One or more options are blank. Please remove them or fill them in.');
                            return false;
                        }
                        else {
                            count++;
                        }
                    }
                    if (count < ctrl.minimumPollOptions) {
                        messageHandler.error('There must be at least two options filled. Please try again.');
                        return false;
                    }
                    return true;
                };


                ctrl.closePopover = function () {
                    if (self.popoverCtrl) {
                        self.popoverCtrl.close();
                    }
                };



                ctrl.sendPoll = function() {
                    if (ctrl.pollInputsAreValid()) {
                        var data = {
                            poll_question: ctrl.pollQuestion,
                            poll_options: ctrl.options,
                            student_ids: ctrl.studentIds
                        };
                        $http.post('/api/polls/sendStudentPoll', data)
                            .then(function(response){
                                ctrl.close();
                                messageHandler.error("Message has been sent");
                            })
                            .catch(function() {
                                messageHandler.error('There was a problem sending the message. Try again later.');
                            })
                    }
                };
            }]);
})();