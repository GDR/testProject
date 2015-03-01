var modals = angular.module('Modals', ['Utils', 'Authentication', 'Requests']);

modals.controller('ModalSignInController', function ($scope, $modalInstance, AuthFactory) {

    $scope.user = {
        username: '',
        password: ''
    };
    $scope.error = null;

    $scope.signIn = function () {
        AuthFactory.signIn($scope.user)
            .success(function () {
                $modalInstance.close($scope.user);
            })
            .error(function (data) {
                $scope.error = data.reason;
            });
    };

    $scope.close = function () {
        $modalInstance.dismiss();
    };
});


modals.controller('ModalSignUpController', function ($scope, $modalInstance, USER_TYPES, RequestFactory) {

    $scope.USER_TYPES_CONST = USER_TYPES;

    $scope.user = {
        username: '',
        password: '',
        userType: $scope.USER_TYPES_CONST.CUSTOMER
    };
    $scope.error = null;

    $scope.signUp = function () {
        RequestFactory.registerUser($scope.user)
            .success(function () {
                $modalInstance.close();
            })
            .error(function (data, status, headers, config) {
                $scope.error = data.reason;
            });
    };

    $scope.close = function () {
        $modalInstance.dismiss();
    };
});

modals.controller('ModalAddTaskController', function ($scope, $modalInstance, RequestFactory) {
    $scope.task = {
        title: '',
        price: 5.99
    };

    $scope.error = null;

    $scope.addTask = function () {
        RequestFactory.addTask($scope.task)
            .success(function (data) {
                $modalInstance.close(data);
            })
            .error(function (data, status) {
                $scope.error = data.reason;
            });
    };

    $scope.close = function () {
        $modalInstance.dismiss();
    }
});

modals.controller('ModalAddMoneyController', function ($scope, $modalInstance, RequestFactory) {
    $scope.money = 0;

    $scope.error = null;

    $scope.addMoney = function () {
        RequestFactory.addMoney($scope.money)
            .success(function (data) {
                $modalInstance.close(data);
            })

            .error(function (data, status) {
                $scope.error = data.reason;
            });
    };

    $scope.close = function() {
        $modalInstance.dismiss();
    }
});