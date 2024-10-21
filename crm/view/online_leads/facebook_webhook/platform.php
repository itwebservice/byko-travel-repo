<?php
include '../../../model/model.php';
$data = mysqli_fetch_assoc(mysqlQuery("select `facebook_appid`, `facebook_appsecret`, `facebook_callback` FROM `app_settings`"));

?>
<legend>Facebook Platform</legend>

<!--<script src="https://code.jquery.com/jquery-3.6.3.min.js"></script>-->

<script>
  window.fbAsyncInit = function() {
    FB.init({
      appId: '<?= $data['facebook_appid'] ?>',
      xfbml: true,
      version: 'v15.0',
      cookie: true,
    });
  };

  (function(d, s, id) {
    var js, fjs = d.getElementsByTagName(s)[0];
    if (d.getElementById(id)) {
      return;
    }
    js = d.createElement(s);
    js.id = id;
    js.src = "https://connect.facebook.net/en_US/sdk.js";
    fjs.parentNode.insertBefore(js, fjs);
  }(document, 'script', 'facebook-jssdk'));

  function subscribeApp(page_id, page_access_token) {
    console.log('Subscribing page to app! ' + page_id);
    FB.api(
      '/' + page_id + '/subscribed_apps',
      'post', {
        access_token: page_access_token,
        subscribed_fields: ['leadgen', 'leads_retrieval', 'pages_manage_metadata', 'pages_manage_ads']
      },
      function(response) {
        console.log('Successfully subscribed page', response);
        alert('Successfully subscribed page');
      }
    );
  }


  // Only works after `FB.init` is called
  function myFacebookLogin() {
    var dropdown = "";
    FB.login(function(response) {
      console.log('Successfully logged in', response);
      alert('Successfully logged in');
      FB.api('/me/accounts', function(response) {
        console.log('Successfully retrieved pages', response);
        var pages = response.data;
        var ul = document.getElementById('list');
        for (var i = 0, len = pages.length; i < len; i++) {
          var page = pages[i];
          var li = document.createElement('li');
          var a = document.createElement('a');
          a.href = "#";
          a.onclick = subscribeApp.bind(this, page.id, page.access_token);
          a.innerHTML = page.name;
          dropdown += "<option value='" + page.id + "'>" + page.name + "</option>";
          li.appendChild(a);
          ul.appendChild(li);
        }
        $('#pageId').html(dropdown);
      });
    }, {
      scope: 'pages_show_list'
    });
  }
</script>

<script>
  function makeSubscription() {
    var callback_url = "<?= $data['facebook_callback'] ?>";
    var fields = "leadgen";
    var verify_token = "abc123";
    var object = "page";
    FB.api(
      '/<?= $data['facebook_appid'] ?>/subscriptions',
      'POST', {
        "object": object,
        "callback_url": callback_url,
        "fields": fields,
        "verify_token": verify_token,
        "access_token": "<?= $data['facebook_appid'] . '|' . $data['facebook_appsecret'] ?>"
      },
      function(response) {
        console.log(response);

      }
    );
  }


  function getAccountDetail() {
    FB.api(
      '/me/accounts',
      'GET', {},
      function(response) {
        console.log(response);
      }
    );
  }

  function getPageDetails2() {


    getPageAccessToken(pageId);
    var access_token = $('#page_access_token').val();
    FB.api(
      '/' + pageId + '/subscribed_apps',
      'GET', {
        access_token: access_token
      },
      function(response) {
        // Insert your code here
        console.log(response);
      }
    );
  }

  function getPageDetails() {
    var pageId = $('#pageId').val();
    var htmlDropdown = "";
    FB.api(
      '/' + pageId,
      'GET', {
        "fields": "access_token"
      },
      function(response) {
        // Insert your code here


        FB.api(
          '/' + pageId + '/subscribed_apps',
          'GET', {
            access_token: response.access_token
          },
          function(xyzdata) {
            // Insert your code here
            $('#page_access_token').val(response.access_token);

            FB.api(
              '/' + pageId + '/leadgen_forms',
              'GET', {
                access_token: response.access_token
              },
              function(leadgen_forms) {
                // Insert your code here
                console.log(leadgen_forms);
                var pages = leadgen_forms.data;
                for (var i = 0, len = pages.length; i < len; i++) {
                  var page = pages[i];
                  htmlDropdown += "<option value='" + page.id + "'>" + page.name + "</option>";
                }
                $('#formId').html(htmlDropdown);

              }
            );
          }

        );

      }
    );

  }

  function getformDetails() {
    var pageAccess = $('#page_access_token').val();
    var pageId = $('#formId').val();
    var htmlData = "";

    FB.api(
      '/' + pageId + '/leads',
      'GET', {
        access_token: pageAccess
      },
      function(leadgen_forms) {
        // Insert your code here
        console.log(leadgen_forms);
        var pages = leadgen_forms.data;
        console.log(pages);
        for (var i = 0, len = pages.length; i < len; i++) {
          var page = pages[i]['field_data'];

          htmlData += "Name:" + page[0].values[0] + "<br>";

        }
        $('#datas').html(htmlData);
      }
    );
  }

  function getSingleLead(leadId, unqId) {
    var url = '<?= BASE_URL ?>';
    FB.api(
      '/' + leadId,
      'GET', {},
      function(response) {
        console.log(response);

        if (response.field_data != "" || response.field_data != undefined) {
          $.post(url + 'controller/online_leads/facebook_set_data.php', {
            mainData: response.field_data,
            unqId: unqId
          }, function(data) {


          });
        }
        // Insert your code here
      }
    );
  }

  function setApp() {
    var appId = $('#formappId').val();
    var appSecret = $('#formappSecret').val();
    var appCallback = $('#formcallbackUrl').val();
    var url = '<?= BASE_URL ?>';
    $.post(url + 'controller/online_leads/facebook.php', {
      appId: appId,
      appSecret: appSecret,
      appCallback: appCallback,

    }, function(data) {
      alert(data);
    });
  }

  function fetchData() {
    checkLogin();
    var url = '<?= BASE_URL ?>';
    $.post(url + 'controller/online_leads/facebook_fetch_data.php', {}, function(data) {

      var decodeData = JSON.parse(data);
      for (var i = 0; i < decodeData.length; i++) {
        if (decodeData[i]['leadgen_id'] != "") {
          console.log(decodeData[i]);
          getSingleLead(decodeData[i]['leadgen_id'], decodeData[i]['unqId']);
        }
      }
      alert('completed');
    });
  }

  function checkLogin() {
    FB.getLoginStatus(function(response) {
      if (response.status === 'connected') {
        return true;
      } else {
        alert("Login With facebook First");
        return false;
      }
    });
  }
</script>
<div class="row mg_bt_10">
  <!--<div class="col-md-3 mg_bt_10">Pages:-->
  <!--  <select name="" id="pageId">-->
  <!--    <option value="">Select Page</option>-->
  <!--  </select>-->
  <!--</div>-->
  <!--<div class="col-md-3 mg_bt_10">-->
  <!--  Forms:-->
  <!--  <select name="" id="formId">-->
  <!--    <option value="">Select Form</option>-->
  <!--  </select>-->
  <!--</div>-->
  <div class="col-md-12 mg_bt_10">
    <h6>App Id:</h6>
    <input type="text" class="form-control" name="appId" id="formappId" value="<?= $data['facebook_appid'] ?>" placeholder="App Id">

  </div>
  <div class="col-md-12 mg_bt_10">

    <h6>App Secret:</h6>

    <input type="text" class="form-control" name="appSecret" id="formappSecret" value="<?= $data['facebook_appsecret'] ?>" placeholder="App Secret">

  </div>

  <div class="col-md-12 mg_bt_10">
    <h6>Callback Url:</h6>
    <input type="text" class="form-control" name="callbackUrl" id="formcallbackUrl" value="<?= $data['facebook_callback'] ?>" placeholder="Callback Url">

  </div>
</div>

<input type="hidden" name="" id="page_access_token">





<button class="btn btn-success" onclick="setApp()"><i class="fa fa-check"></i>Set App</button>


<button class="btn btn-primary" onclick="makeSubscription();btnDisableEnable(this.id)" id="makeSubscription"><i class="fa fa-facebook"></i>Subscribe with Facebook</button>
<button class="btn btn-primary" onclick="myFacebookLogin();btnDisableEnable(this.id)" id="myFacebookLogin"><i class="fa fa-facebook"></i>Login with Facebook</button>
<!--<button onclick="getAccountDetail();btnDisableEnable(this.id)">Account Details</button>-->
<!--<button onclick="getPageDetails();btnDisableEnable(this.id)">Page Details</button>-->
<!--<button onclick="getformDetails();btnDisableEnable(this.id)">form Details</button>-->
<!--<button onclick="getSingleLead(';btnDisableEnable(this.id)1235858640370001')">SIngle Lead</button>-->
<button class="btn btn-success" onclick="fetchData();btnDisableEnable(this.id)" id="fetchData"><i class="fa fa-arrow-down"></i>Fetch Data</button>

<ul id="list"></ul>
<div id="datas"></div>





<style>
  .btn-primary {
    transition: .5s ease-out;
    color: #fff;
    box-shadow: none;
    padding-left: 35px;
    margin-right: 10px;
    position: relative;
    border: 1px solid #58a653;
    border-radius: 25px !important;
    line-height: 22px;
  }

  .btn-primary:hover {
    color: #4285f4 !important;
    background-color: #fff !important;
    border-color: #4285f4;
  }

  .btn-primary i {
    position: absolute;
    left: 0;
    top: 0;
    padding: 10px;
    border-radius: 50%;
    background-color: rgba(255, 255, 255, 0.2);
    height: 32px;
    width: 32px;
  }

  .btn-success {
    margin-right: 10px;
  }
</style>