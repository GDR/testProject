var app = angular.module('app', ['ngAnimate', 'toaster', 'ui.bootstrap', 'ui.router']);

app.constant('USER_TYPES', {
    'CUSTOMER': 0,
    "PERFORMER": 1
});

app.run(function (AuthFactory) {
    AuthFactory.check_access_request();
});

app.config(function ($stateProvider, $urlRouterProvider) {
    $urlRouterProvider.otherwise('/');
    $stateProvider
        .state('about', {
            url: '/',
            templateUrl: 'templates/about_content.html',
            controller: 'AboutController'
        })
        .state('tasks',{
            url: '/tasks',
            templateUrl: 'templates/tasks.html',
            controller: 'TasksController'
        });
});

app.controller('AboutController', function ($scope) {
    $scope.title = 'Labor Exchange test project';
    $scope.subTitle = 'Service which helps you to find a performer for your tasks';
});

app.controller('TasksController', function($scope, $modal) {
    $scope.title = 'Labor Exchange test project';
    $scope.subTitle = 'Service which helps you to find a performer for your tasks';

    $scope.addTask = function() {
        var modalInstance = $modal.open({
            templateUrl: 'templates/modal/add_task.html',
            controller: 'ModalAddTaskController',
            size: 'sm'
        });
    }
});

app.factory('AuthFactory', function ($http, USER_TYPES) {
    var factory = {};

    var user = {
        logged_in: false,
        username: '',
        userType: null,
        isSigned: function () {
            return this.logged_in;
        },
        getUserTypeName: function () {
            if (this.userType == USER_TYPES.CUSTOMER) {
                return 'customer';
            }
            if (this.userType == USER_TYPES.PERFORMER) {
                return 'performer';
            }
            return '';
        }
    };

    factory.signInRequest = function (username, password) {
        var promise = $http({
            method: 'POST',
            url: '/ajax/login_user.php',
            data: 'username=' + username + '&password=' + password,
            headers: {'Content-Type': 'application/x-www-form-urlencoded'}
        });
        promise.success(function (data) {
            user.logged_in = true;
            user.username = data.username;
            user.userType = data.user_type;
        });
        return promise;
    };

    factory.sign_out_request = function () {
        return $http({
            method: 'POST',
            url: '/ajax/logout_user.php',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'}
        });
    };

    factory.check_access_request = function () {
        var promise = $http({
            method: 'GET',
            url: '/ajax/check_access.php',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'}
        });
        promise.success(function (data) {
            user.logged_in = true;
            user.username = data.username;
            user.userType = data.userType;
        });
    };

    factory.user = user;

    return factory;
});

app.factory('RequestFactory', function ($http) {
    var factory = {};

    factory.register_user = function (username, password, userType) {
        var promise = $http({
            method: 'POST',
            url: '/ajax/register_user.php',
            data: 'username=' + username +
            '&password=' + password +
            '&user_type=' + userType,
            headers: {'Content-Type': 'application/x-www-form-urlencoded'}
        });
        return promise;
    };

    factory.addTask = function(task) {
        console.log(task);
        var promise = $http({
            method: 'POST',
            url: '/ajax/add_task.php',
            data: 'title=' + task.title +
                '&price=' + task.price,
            headers: {'Content-Type': 'application/x-www-form-urlencoded'}
        });
        return promise;
    };

    return factory;
});

app.controller('NavbarController', function ($scope, toaster, $modal, AuthFactory) {

    $scope.user = AuthFactory.user;
    $scope.$watch(
        function () {
            return AuthFactory.user;
        },
        function (newVal) {
            $scope.user = newVal;
        }
    );

    $scope.sign_in = function () {
        var modalInstance = $modal.open({
            templateUrl: 'templates/modal/sign_in.html',
            controller: 'ModalSignInController',
            size: 'sm'
        });

        modalInstance.result.then(
            function (res) {
                toaster.success("Logged in", "As: " + res + " " + AuthFactory.user.isSigned());
            }
        );
    };

    $scope.sign_out = function () {
        $scope.user.logged_in = false;
        AuthFactory.sign_out_request();
        toaster.success("Logged out", "Good bye!");
    };

    $scope.sign_up = function () {
        var modalInstance = $modal.open({
            templateUrl: 'templates/modal/sign_up.html',
            controller: 'ModalSignUpController',
            size: 'sm'
        });

        modalInstance.result.then(
            function (res) {
                toaster.success("Registered", "As: " + res.username +
                " pass: " + res.password +
                " userType: " + res.userType);
            }
        );
    };
});

app.directive('navbar', function () {
    return {
        restrict: 'E',
        templateUrl: 'templates/navbar.html',
        controller: 'NavbarController'
    }
});

app.controller('ModalSignInController', function ($scope, $modalInstance, AuthFactory) {

    $scope.username = '';
    $scope.password = '';
    $scope.hasError = false;

    $scope.sign_in = function () {
        AuthFactory.signInRequest($scope.username, $scope.password)
            .success(function () {
                $modalInstance.close($scope.username);
            })
            .error(function () {
                $scope.hasError = true;
            });
    };

    $scope.close = function () {
        $modalInstance.dismiss();
    };
});


app.controller('ModalSignUpController', function ($scope, $modalInstance, USER_TYPES, RequestFactory) {
    $scope.username = '';
    $scope.password = '';
    $scope.hasError = false;
    $scope.errorMsg = '';
    $scope.USER_TYPES_CONST = USER_TYPES;
    $scope.userType = $scope.USER_TYPES_CONST.CUSTOMER;

    $scope.sign_up = function () {
        RequestFactory.register_user($scope.username, $scope.password, $scope.userType)
            .success(function () {
                $modalInstance.close({
                    username: $scope.username,
                    password: $scope.password,
                    userType: $scope.userType
                });
            })
            .error(function (data, status, headers, config) {
                $scope.hasError = true;
                $scope.errorMsg = data.reason;
                // called asynchronously if an error occurs
                // or server returns response with an error status.
            });
    };

    $scope.close = function () {
        $modalInstance.dismiss();
    };
});

app.controller('ModalAddTaskController', function ($scope, $modalInstance, RequestFactory) {
    $scope.task = {
        title: '',
        price: 5.99
    };

    $scope.error = null;

    $scope.addTask = function() {
        RequestFactory.addTask($scope.task)
            .success(function() {
                $modalInstance.close();
            })
            .error(function(data, status) {
                $scope.error = data.reason;
            });
    }

    $scope.close = function () {
        $modalInstance.dismiss();
    }
});
