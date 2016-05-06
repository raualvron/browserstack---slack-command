<?php

//Getting autoload from composer
include 'vendor/autoload.php';

//Declaring classes to use in the script
use Alexschwarz89\Browserstack\Screenshots\Api;

// Default values for BrowserSlack
const BROWSERSTACK_ACCOUNT   = '';
const BROWSERSTACK_PASSWORD  = '';

//Creating new API object
$api  = new Api(BROWSERSTACK_ACCOUNT, BROWSERSTACK_PASSWORD);

?>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>BrowserStack @Waracle.net</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <link rel="stylesheet" href="assert/bootstrap.css" media="screen">
</head>
<body>
  <div class="navbar navbar-default navbar-fixed-top">
    <div class="container">
      <div class="navbar-header">
        <a href="../" class="navbar-brand">BrowserStack @Waracle.net</a>
      </div>
    </div>
  </div>


  <div class="container" ng-app="browserApp" ng-controller="mainController">
    <div class="file-date alert alert-success">

      <?php 

      // JSON file
      $fileJ = 'browsers.json';

       // Open the file to get existing content
      $openF = fopen($fileJ,"w");

        //JSON variable with all devices
      $browserList    = $api->getBrowsers();

        //Returns the JSON representation of a value
      $browserList = json_encode($browserList);

        // Write the contents back to the file
      fwrite($openF, $browserList);

      fclose($openF);

      
      
      if (file_exists($fileJ)) {
        echo "<center>Browser.json was updated : <strong>" . date ("d F Y H:i", filemtime($fileJ)) . "</strong></center>";
      }

      ?>

    </div>
    <div class="page-header" id="banner">
      <div class="form-browser">
        <form>
          <div class="form-group">
            <div class="input-group">
              <div class="input-group-addon"><i class="fa fa-search"></i></div>
              <input type="text" class="form-control" placeholder="Type your search" ng-model="searchBrowser">
            </div>      
          </div>
        </form>
      </div>



      <table class="browser-table table table-bordered table-striped">

        <thead>
          <tr>
            <td>
              <a href="#" ng-click="sortType = 'os'; sortReverse = !sortReverse">
                Operating system 
                <span ng-show="sortType == 'os' && !sortReverse" class="fa fa-caret-down"></span>
                <span ng-show="sortType == 'os' && sortReverse" class="fa fa-caret-up"></span>
              </a>
            </td>
            <td>
              <a href="#" ng-click="sortType = 'os_version'; sortReverse = !sortReverse">
                Operating system version
                <span ng-show="sortType == 'os_version' && !sortReverse" class="fa fa-caret-down"></span>
                <span ng-show="sortType == 'os_version' && sortReverse" class="fa fa-caret-up"></span>
              </a>
            </td>
            <td>
              <a href="#" ng-click="sortType = 'device'; sortReverse = !sortReverse">
                Device
                <span ng-show="sortType == 'device' && !sortReverse" class="fa fa-caret-down"></span>
                <span ng-show="sortType == 'device' && sortReverse" class="fa fa-caret-up"></span>
              </a>
            </td>
            <td>
              <a href="#" ng-click="sortType = 'browser'; sortReverse = !sortReverse">
                Browser
                <span ng-show="sortType == 'browser' && !sortReverse" class="fa fa-caret-down"></span>
                <span ng-show="sortType == 'browser' && sortReverse" class="fa fa-caret-up"></span>
              </a>
            </td>
            <td>
              <a href="#" ng-click="sortType = 'browser_version'; sortReverse = !sortReverse">
                Browser version 
                <span ng-show="sortType == 'browser_version' && !sortReverse" class="fa fa-caret-down"></span>
                <span ng-show="sortType == 'browser_version' && sortReverse" class="fa fa-caret-up"></span>
              </a>
            </td>
          </tr>
        </thead>

        <tbody>
          <tr class="{{item.os}}" ng-repeat="item in browsers | orderBy:sortType:sortReverse | filter:searchBrowser">
            <td>{{ item.os }}</td>
            <td>{{ item.os_version }}</td>
            <td>{{ item.device }}</td>
            <td>{{ item.browser }}</td>
            <td>{{ item.browser_version }}</td>
          </tr>
        </tbody>

      </table>

    </div>



  </div>
  <footer>
   <div class="footer-bottom">
    BrowserStack JSON with AngularJS (Waracle.net)
  </div>
</footer>
</body>
<script src="https://code.jquery.com/jquery-1.10.2.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.5.5/angular.min.js"></script>
</html>

<script type="text/javascript">

angular.module('browserApp', [])

.controller('mainController', function($scope, $http) {

  $scope.sortType     = 'name'; // set the default sort type
  $scope.sortReverse  = false;  // set the default sort order
  $scope.searchBrowser   = '';     // set the default search/filter term

  //Getting the browser JSON file
  $http.get('browsers.json', {cache: true}).success(function(data) {

    //Converting from string to integer.
    for (var i = data.length - 1; i >= 0; i--) {
      if (data[i].browser_version != null){
        var pos = data[i];
        pos["browser_version"] = parseInt(data[i].browser_version);
      }
    };

    $scope.browsers = data;

  });

});
</script>
<script type="text/javascript">

</script>