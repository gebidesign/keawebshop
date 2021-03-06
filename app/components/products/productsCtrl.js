'use strict';

shopApp.controller('productsController', ['$scope', '$http','$routeParams',
    function($scope, $http, $routeParams) {

        var productId = $routeParams.productId;
        $scope.formData = {};
        $scope.addOrderData = {};

        // add product
        $scope.processForm = function () {

            $scope.formData.product_id = productId;

            $http({
                method: 'POST',
                url: 'api/products/save_product.php',
                data: $.param($scope.formData),  // pass in data as strings
                headers: {'Content-Type': 'application/x-www-form-urlencoded'}  // set the headers so angular passing info as form data (not request payload)

            }).success(function (data) {

              $scope.message = data.message;
            });
        };

        // add product
        $scope.processFormAdd = function () {

          $http({
            method: 'POST',
            url: 'api/products/add_product.php',
            data: $.param($scope.formData),  // pass in data as strings
            headers: {'Content-Type': 'application/x-www-form-urlencoded'}  // set the headers so angular passing info as form data (not request payload)

          }).success(function (data) {
            $scope.message = data.message;
            $scope.formData = {};
          });
        };

        $scope.processFormAddOrder = function () {

          $http({
            method: 'POST',
            url: 'api/orders/add_order.php',
            data: $.param($scope.addOrderData),  // pass in data as strings
            headers: {'Content-Type': 'application/x-www-form-urlencoded'}  // set the headers so angular passing info as form data (not request payload)

          }).success(function (data) {
            $scope.message = data.message;
            $scope.addOrderData = {};
          });
        };

        $http
            .get('api/products/get_edit_product.php?id=' + productId)
            .success(function (response) {

                $scope.formData = response[0];
            });
}]);
