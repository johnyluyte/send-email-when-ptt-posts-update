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
      "check_total_days":
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
        // data
        "<br />article_title: " + data["article_title"] +
        "<br />article_author: " + data["article_author"] +
        "<br />article_board: " + data["article_board"] +
        "<br />article_date: " + data["article_date"] +
        "<br />check_ptt_url: " + data["check_ptt_url"] +
        "<br />check_hour_period: " + data["check_hour_period"] +
        "<br />check_total_days: " + data["check_total_days"] +
        "<br />email: " + data["email"] +
        "<br />article_last_push: " + data["article_last_push"]
      );

      // alert("Form submitted successfully.\nReturned json: " + data["json"]);
    }
  });
}


$("document").ready(function(){
  $("#myForm").submit(function(event){

    if(isValidPttURL($("#check_ptt_url").val())==false)
      return;

    if(isEmailNotEmpty($("#email").val())==false)
      return;

    sendAjax(createJsonData($(this)));

    // https://api.jquery.com/submit/
    // return false;  would does the same as event.preventDefault();
    event.preventDefault();
  });
});

