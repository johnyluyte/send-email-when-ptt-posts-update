function alert_error(msg){
  console.log(msg);
  alert(msg);
}

function isValidPttURL(url){
    // Check if the URL is REGEX correct (http(s)://www.ptt.cc/bbs/*).
    ptt_regex = /http[s]?:\/\/www.ptt.cc\/bbs\/*/;
    // https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/String/match
    if(url.match(ptt_regex)==null){
      alert_error("Error: Invalid URL! \n批踢踢文章網址錯誤。");
      return false;
    }
}

function isEmailNotEmpty(email){
    // We uses input type="email" so the browser will check the syntax of the e-mail for us.
    // However, we still need to check if the e-mail is empty.
    if(email==''){
      alert_error("Error: Invalid E-mail! \n電子郵件欄位不可為空白");
      return false;
    }
}

function createJsonData(myForm){
    var data = {
      "action": "action_check_ptt"
    };
    /*
      $(this).serialize() 會將 $(this)，也就是 <form>，裡面的 <input> 欄位與值變成 name: value 的 json 格式
      在這個例子中，包含
      "check_ptt_url":
      "check_hour_period":
      "check_persist_days":
      "email":
    */
    data = myForm.serialize() + "&" + $.param(data);
    return data;
}

function sendAjax(data){
  $.ajax({
    type: "POST",
    dataType: "json",
    url: "response.php", //Relative or absolute path to response.php file
    data: data,
    success: function(data) {
      if(data["error"]!=null){
        alert_error(data["error"]);
        $("#the-return").html(data["error"]);
        return;
      }
      $("#the-return").html(
        "<br />" + data["url"] +
        "<br />" + data["title"] +
        "<br />" + data["author"] +
        "<br />" + data["board"] +
        "<br />" + data["published_date"] +
        "<br />" + data["period"] +
        "<br />" + data["email"] +
        "<br />" + data["remaining"] +
        "<br />" + data["push_count"] +
        "<br />" + data["extra_message"]
      );
      // alert("Form submitted successfully.\nReturned json: " + data["json"]);
    }
  });
}


$("document").ready(function(){
  $("#myForm").submit(function(event){

    if(isValidPttURL($("#url").val())==false)
      return;

    if(isEmailNotEmpty($("#email").val())==false)
      return;

    sendAjax(createJsonData($(this)));

    // https://api.jquery.com/submit/
    // return false;  would does the same as event.preventDefault();
    event.preventDefault();
  });

  $(".btn.btn-danger.btn-xs").click(function(event){
    if(confirm("確定要刪除這筆資料嗎？")){
      $(this).parent().parent().remove();
    }
  });
});

