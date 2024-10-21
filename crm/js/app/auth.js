$(function(){
    $('#frm_login').validate({
      submitHandler:function(){
        var username = $('#txt_username').val();
        var password = $('#txt_password').val();
        var token = $('#token').val();
       
        var financial_year_id = $('#financial_year_id').val();
        $("#site_alert").empty();
        if(username==""){
          $('#site_alert').vialert({ type:'error', title:'Error', message:'Username is required' });
          return false;
        }
        if(password==""){
          $('#site_alert').vialert({ type:'error', title:'Error', message:'Password is required' });
          return false;
        }
        if(financial_year_id==""){
          $('#site_alert').vialert({ type:'error', title:'Error', message:'Financial year is required' });
          return false;
        }
        if(token==""){
          $('#site_alert').vialert({ type:'error', title:'Error', message:'Token is required' });
          return false;
        }

        $('#sign_in').button('loading');

        $.post('controller/login/login_verify.php', {token:token, username : username, password : password, financial_year_id : financial_year_id }, function(data,status){
         
          if(data=="valid")
            {        
              localStorage.setItem("reminder", true);
              window.location.href = "view/dashboard/dashboard_main.php";
            } 
            else
            {    
              $('.app_btn').button('reset');  
              $('#site_alert').vialert({ type:'error', title:'Error', message:data });
            }
        }
        
        ) .fail(function(xhr, status, error) {
          $('.app_btn').button('reset');$('#site_alert').vialert({ type:'error', title:'Error', message:error });
          // window.location.reload();
          setTimeout(window.location.reload.bind(window.location), 1000);

  });
      }
    });
});