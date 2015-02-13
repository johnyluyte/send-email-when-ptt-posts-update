function alert_error(msg){
  console.log(msg);
  alert(msg);
}

$("document").ready(function(){
  $("#myForm").submit(function(){
    // Check if the URL is REGEX correct (http(s)://www.ptt.cc/bbs/*).
    var url = $("#ptt_url").val();
    ptt_regex = /http[s]?:\/\/www.ptt.cc\/bbs\/*/;
    // https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/String/match
    if(url.match(ptt_regex)==null){
      alert_error("Error: Invalid URL! \n批踢踢文章網址錯誤。");
      return false;
    }

    // We uses input type="email" so the browser will check the syntax of the e-mail for us.
    // However, we still need to check if the e-mail is empty.
    if($("#email").val()==''){
      alert_error("Error: Invalid E-mail! \n電子郵件欄位不可為空白");
      return false;
    }

    var data = {
      "action": "get_article_title"
    };
    data = $(this).serialize() + "&" + $.param(data);
    $.ajax({
      type: "POST",
      dataType: "json",
      url: "response.php", //Relative or absolute path to response.php file
      data: data,
      success: function(data) {
        if(data["error"]!=null){
          alert_error(data["error"]);
          return;
        }
        $(".the-return").html(
          // data
          "ptt_url: " + data["ptt_url"] +
          "<br />ptt_title: " + data["ptt_title"] +
          "<br />http_code: " + data["http_code"] +
          "<br />errr: " + data["errr"] +
          "<br />check_hour_period: " + data["check_hour_period"] +
          "<br />check_total_days: " + data["check_total_days"] +
          "<br />email: " + data["email"] +
          "<br />JSON: " + data["json"]
        );

        // alert("Form submitted successfully.\nReturned json: " + data["json"]);
      }
    });
    return false;
  }); // .submit()
});

