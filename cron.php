<?php
  $mail_receiver = null;
  $mail_message = null;
  $row_tmp = array();

  $db_configs_json = file_get_contents("db_config.json");
  $db_configs      = json_decode($db_configs_json, true);
  $servername      = $db_configs['servername'];
  $dbname          = $db_configs['dbname'];
  $username        = $db_configs['username'];
  $password        = $db_configs['password'];

  try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->exec("SET NAMES 'utf8';");

    // 從資料庫抓出資料，檢查有沒有新推文
    $sql = "SELECT * FROM main_table";
    foreach ($conn->query($sql) as $row) {
      handle_row($row, $conn);
    }

    // 將剛剛更新的暫存資料存回資料庫
    $sql = "UPDATE `main_table` SET `last_check`=:last_check, `remaining`=:remaining, `push_count`=:push_count WHERE `url`=:url";
    $stmt = $conn->prepare($sql);
    foreach($row_tmp as $key_url => $value_array) {
      $stmt->bindParam(':url', $key_url);
      $stmt->bindParam(':last_check', $value_array['last_check']);
      $stmt->bindParam(':remaining', $value_array['remaining']);
      $stmt->bindParam(':push_count', $value_array['push_count']);
      $stmt->execute();
      echo $stmt->rowCount() . " records UPDATED successfully <br>";
    }
  }
  catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
  }

  // 關閉資料庫連線
  $conn = null;

  // 檢查是否要寄信
  if($mail_message != null){
    send_notify_email($mail_receiver, $mail_message);
  }else{
    echo "您追蹤的文章都沒有新推文。";
  }


function handle_row($row, $conn){
  global $row_tmp;

  $url = $row['url'];
  $content = get_url_content($url);

  if($content==null){
    return;
  }else{
    $row_tmp[$url] = array();

    // 更新最後檢查的時間，先存入暫時的陣列
    date_default_timezone_set('Asia/Taipei');
    $row_tmp[$url]['last_check'] = date('Y/m/d l H:i:s', time());

    // 日期減一，先存入暫時的陣列
    $row_tmp[$url]['remaining'] = $row['remaining'] - 1;

    // 檢查推文數量是否有變化，先存入暫時的陣列
    $row_tmp[$url]['push_count'] = substr_count($content, "push-content");

    // 偵測到這篇文章有新增推文的話，就設定信件內容，等等再寄出
    if($row_tmp[$url]['push_count'] != $row['push_count']){
      set_message($row);
    }
  }
}

function set_message($row){
  global $mail_receiver, $mail_message;
  // TODO: 這邊應該針對不同的 email 收件者做設定，但目前只給我自己測試用，故假設收件者都是同一個人
  $mail_receiver = $row['email'];
  $mail_message .= "您在 PTT 追蹤的文章：<a href='" . $row['url'] . "'>" . $row['title'] ."</a> 有新的推文。 <br><br>";
}


function send_notify_email($mail_receiver, $mail_message){
  echo $mail_message;
  echo $mail_receiver;

  $To = $mail_receiver;
  $Subject = '您在 PTT 追蹤的文章有新的推文';
  $Message = $mail_message;
  // http://stackoverflow.com/questions/7266935/how-to-send-utf-8-email
  $from_name = "自動檢查PTT推文";
  $Headers = "From: =?utf-8?B?" . base64_encode($from_name) . "?=  \r\n" .
  "Reply-To: no-reply@chunnorris.cc \r\n" .
  "Content-type: text/html; charset=UTF-8 \r\n";

  mail($To, $Subject, $Message, $Headers);
}

function get_url_content($url){
  $handle = curl_init($url);
  curl_setopt($handle,  CURLOPT_RETURNTRANSFER, TRUE);
  curl_setopt($handle, CURLINFO_HEADER_OUT, true);
  curl_setopt($handle, CURLOPT_FOLLOWLOCATION, true);
  curl_setopt($handle, CURLOPT_COOKIE, 'over18=1');
  $response = curl_exec($handle);

  $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
  if($httpCode == 404) {
      return null;
  }

  curl_close($handle);
  return $response;
}

?>
