var app = angular.module('app', ['ui.bootstrap'], function($httpProvider) {
    /*
    ====================================
     Хак для отправки информации на php
    ====================================
     */

    // Используем x-www-form-urlencoded Content-Type
    $httpProvider.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';

    // Переопределяем дефолтный transformRequest в $http-сервисе
    $httpProvider.defaults.transformRequest = [function(data)
    {
        /**
         * рабочая лошадка; преобразует объект в x-www-form-urlencoded строку.
         * @param {Object} obj
         * @return {String}
         */
        var param = function(obj)
        {
            var query = '';
            var name, value, fullSubName, subValue, innerObj, i;

            for(name in obj)
            {
                value = obj[name];

                if(value instanceof Array)
                {
                    for(i=0; i<value.length; ++i)
                    {
                        subValue = value[i];
                        fullSubName = name + '[' + i + ']';
                        innerObj = {};
                        innerObj[fullSubName] = subValue;
                        query += param(innerObj) + '&';
                    }
                }
                else if(value instanceof Object)
                {
                    for(subName in value)
                    {
                        subValue = value[subName];
                        fullSubName = name + '[' + subName + ']';
                        innerObj = {};
                        innerObj[fullSubName] = subValue;
                        query += param(innerObj) + '&';
                    }
                }
                else if(value !== undefined && value !== null)
                {
                    query += encodeURIComponent(name) + '=' + encodeURIComponent(value) + '&';
                }
            }

            return query.length ? query.substr(0, query.length - 1) : query;
        };

        return angular.isObject(data) && String(data) !== '[object File]' ? param(data) : data;
    }];

    /*
     ====================================
     Хак для отправки информации на php
     ====================================
     */
});

app.controller('headerController', ['$scope', function($scope) {
    $scope.title = "Labor Exchange test project";
    $scope.subTitle = "Service which helps to find executors for your tasks";
}]);

app.controller('ModalController', function ($scope, $modal) {
    $scope.openModal = function() {
        var modalInstance = $modal.open({
            templateUrl: 'templates/modal/sign_in.html',
            controller: 'ModalInstanceController',
            size:'sm'
        });
    }
});

app.controller('ModalInstanceController', function($scope, $http, $document, $modalInstance) {
    $('#username').focus();
    $scope.title = "Sign in";
    $scope.ok = function() {
        $http.post('/ajax/login_user.php', {username: $scope.username, password: $scope.password})
            .success(function(data, status, headers, config) {
                alert("Logged in");
            })
            .error(function(data, status, header, config){
                alert("Wrong password");
            });
        //alert($scope.username + "\n" + $scope.password);
    }

    $scope.close = function() {
        alert("Close");
    }

    function onKeydown(evt) {
        if (evt.which === 13) { // enter key
            evt.preventDefault();
            $scope.ok();
        }
    }

    $document.on('keydown', onKeydown);
    $scope.$on('$destroy', function () {
        $document.off('keydown', onKeydown);
    });
});