(function () {
    "use strict";
    angular.module('kids.messages')
        .component('messagesContent', {
            templateUrl: '/js/angular/messages/messages-content.html',
            controller: 'MessageContentCtrl'
        })
        .controller('MessageContentCtrl', ['FeatureCheck', 'messagesList',
            function MessageContentCtrl(FeatureCheck, messagesList) {
                var ctrl = this;
                ctrl.isPoll = true;

                ctrl.showPolls = function () {
                    return FeatureCheck.isFeatureEnabled("CLASSROOM_POLLING");
                }

                ctrl.$onInit = function () {
                    ctrl.messagesList = messagesList;
                };

                ctrl.hasNoMessages = function () {
                    return ctrl.messagesList.length === 0;
                };

                ctrl.isPoll = function (message) {
                    return message.isPoll === true;
                };

            }]);
})();