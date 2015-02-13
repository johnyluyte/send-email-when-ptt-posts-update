$("document").ready(function(){
  $("#myForm").submit(function(){
    // Check if the URL is REGEX correct (http(s)://www.ptt.cc/bbs/*).
    var url = $("#ptt_url").val();
    ptt_regex = /http[s]?:\/\/www.ptt.cc\/bbs\/*/;

    // https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/String/match
    if(url.match(ptt_regex)==null){
      var error_invalid_url = "Error: Invalid URL! \n批踢踢文章網址錯誤。 ";
      console.log(error_invalid_url);
      alert(error_invalid_url);
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

