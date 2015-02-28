var requests = angular.module('Requests', ['Utils']);

requests.factory('RequestFactory', function($http, UtilsFactory) {
    var factory = {};

    factory.registerUser = function (user) {
        var promise = $http({
            method: 'POST',
            url: '/ajax/register_user.php',
            data: UtilsFactory.prepareData(user),
            headers: {'Content-Type': 'application/x-www-form-urlencoded'}
        });
        return promise;
    };

    factory.addTask = function (task) {
        var promise = $http({
            method: 'POST',
            url: '/ajax/add_task.php',
            data: UtilsFactory.prepareData(task),
            headers: {'Content-Type': 'application/x-www-form-urlencoded'}
        });
        return promise;
    };

    factory.getTasks = function (offset) {
        var promise = $http({
            method: 'GET',
            url: '/ajax/get_tasks.php',
            data: function (offset) {
                return offset == undefined ? '' : 'offset=' + offset;
            },
            headers: {'Content-Type': 'application/x-www-form-urlencoded'}
        })
            .success(function (data) {
                console.log(data);
            });
        return promise;
    };

    factory.deleteTask = function(taskId) {
        var promise = $http({
           method: 'POST',
            url: '/ajax/delete_task.php',
            data: UtilsFactory.prepareData({
                taskId: taskId
            }),
            headers: {'Content-Type': 'application/x-www-form-urlencoded'}
        });
        return promise;
    };

    factory.completeTask = function(taskId) {
        var promise = $http({
            method: 'POST',
            url: '/ajax/complete_task.php',
            data: UtilsFactory.prepareData({
                taskId: taskId
            }),
            headers: {'Content-Type': 'application/x-www-form-urlencoded'}
        });
        return promise;
    };

    factory.getWallet = function () {
        var promise = $http({
            method: 'GET',
            url: '/ajax/get_wallet.php',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'}
        });
        return promise;
    };

    factory.addMoney = function (money) {
        var promise = $http({
            method: 'POST',
            url: '/ajax/add_money.php',
            data: UtilsFactory.prepareData({
                amount: money
            }),
            headers: {'Content-Type': 'application/x-www-form-urlencoded'}
        });
        return promise;
    };

    return factory;
});