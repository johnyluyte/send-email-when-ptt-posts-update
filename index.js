function alert_message(msg){
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
      "action": "action_add"
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

function addSendAjax(data){
  $.ajax({
    type: "POST",
    dataType: "json",
    url: "response.php", //Relative or absolute path to response.php file
    data: data,
    success: function(data) {
      alert_message(
        "\n" + data["url"] +
        "\n" + data["extra_message"]
      );
      var tmp = '<div class="panel panel-default"><div class="panel-heading">';
      tmp += '<button class="btn btn-danger btn-xs">刪除這筆資料</button>\&nbsp;\&nbsp;\&nbsp;<a target="_blank" href="';
      tmp += data["url"] + '">' + data["url"] + '</a>';
      tmp += '&nbsp;&nbsp;&nbsp;(推文數：' + data["push_count"] + ')';
      tmp += '</div><div class="panel-body"><table class="table table-bordered table-hover">';
      tmp += '<tr><td class="col-md-1">標題</td><td class="col-md-4">' + data["title"] + '</td>';
      tmp += '<td class="col-md-1">加入日期</td><td class="col-md-6">' + data["add_date"] + '</td></tr>';
      tmp += '<tr><td>作者</td><td>' + data["author"] + '</td>';
      tmp += '<td>檢查截止</td><td>剩下 ' + data["remaining"] + ' 天</td></tr>';
      tmp += '<tr><td>看板</td><td>' + data["board"] + '</td>';
      tmp += '<td>將通知寄至</td><td>' + data["email"] + '</td></tr>';
      tmp += '<tr><td>發文日期</td><td>' + data["published_date"] + '</td>';
      tmp += '<td>上次檢查</td><td>' + data["last_check"] + '</td></tr>';
      tmp += '</table></div></div>';
      $("#div_show_rows").prepend(tmp);
    }
  });
}


$("document").ready(function(){
  $("#myForm").submit(function(event){

    if(isValidPttURL($("#url").val())==false)
      return;

    if(isEmailNotEmpty($("#email").val())==false)
      return;

    addSendAjax(createJsonData($(this)));

    // https://api.jquery.com/submit/
    // return false;  would does the same as event.preventDefault();
    event.preventDefault();
  });

  $(".btn.btn-danger.btn-xs").click(function(event){
    if(confirm("確定要刪除這筆資料嗎？")){
      deleteSendAjax($(this));
      // $(this).parent().parent().remove();
    }
  });

  $("#btn_check_now").click(function(event){
    $.ajax({
      type: "POST",
      dataType: "json",
      url: "cron.php",
      complete: function(data) {
        alert_message("檢查完成。");
      }
    });
  });
});


function deleteSendAjax(thisDeleteButton){
  var data = {
    "action": "action_delete",
    "url"   : thisDeleteButton.next().attr("href")
  };

  $.ajax({
    type: "POST",
    dataType: "json",
    url: "response.php",
    data: data,
    success: function(data) {
      alert_message(
        "\n" + data["url"] +
        "\n" + data["extra_message"]
      );
      thisDeleteButton.parent().parent().remove();
    }
  });
}
