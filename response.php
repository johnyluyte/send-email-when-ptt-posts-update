<?php
/* Part of the codes are modified from https://github.com/jonsuh/jQuery-Ajax-Call-to-PHP-Script-with-JSON-Return */

/* 安全性，檢查是否為 AJAX、是否為 POST 方法，並檢查 $action 是否正確*/
if (is_ajax()) {
  if (isset($_POST["action"]) && !empty($_POST["action"])) { //Checks if action value exists
    $action = $_POST["action"];
    switch($action) { //Switch case for value of action
      case "action_check_ptt": main_job(); break;
    }
  }
}

//Function to check if the request is an AJAX request
function is_ajax() {
  return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}


/*
  Return the HTML content of the URL, return false if 404.
  e.g. https://www.ptt.cc/bbs/not_exist_board/not_exist_page.html is not valid
*/
function get_url_content($url){
  $handle = curl_init($url);
  curl_setopt($handle,  CURLOPT_RETURNTRANSFER, TRUE);
  curl_setopt($handle, CURLINFO_HEADER_OUT, true);

  // 即使遇到轉址也不會出錯
  curl_setopt($handle, CURLOPT_FOLLOWLOCATION, true);
  // 跳過 "PTT 本網站已依網站內容分級規定處理 確認已經滿 18 歲" 的畫面
  curl_setopt($handle, CURLOPT_COOKIE, 'over18=1');
  /* Get the HTML or whatever is linked in $url. */
  $response = curl_exec($handle);

  /* Check for 404 (file not found).
     https://stackoverflow.com/questions/408405/easy-way-to-test-a-url-for-404-in-php
     https://stackoverflow.com/questions/10227879/php-curl-http-code-return-0
     https://en.wikipedia.org/wiki/List_of_HTTP_status_codes
  */
  $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
  if($httpCode == 404) {
      /* Handle 404 here. */
      return "false";
  }

  /*switch($httpCode) {
      case 404:
        curl_close($handle);
        return false;
  }*/

  curl_close($handle);
  /* Handle $response here. */
  return $response;
}


function get_next_article_metadata($content, $start_from){
  /*
    我們要的資訊(作者、看板、標題、時間)在的：
      作者</span><span class="article-meta-value">uu26793 (Neural Damping)</span></div><div class="article-metaline-right"><span class="article-meta-tag">
      看板</span><span class="article-meta-value">C_Chat</span></div><div class="article-metaline"><span class="article-meta-tag">
      標題</span><span class="article-meta-value">Re: [閒聊] 台北市資訊局你....</span></div><div class="article-metaline"><span class="article-meta-tag">
      時間</span><span class="article-meta-value">Wed Aug 12 10:43:59 2015</span>
  */
  $posValueStart = strpos($content, "article-meta-value", $start_from) + 20;  // add 20 to reach the "value"
  $posValueEnd = strpos($content, "</span>", $posValueStart);
  // http://www.w3schools.com/php/func_string_strpos.asp
  // http://www.w3schools.com/php/func_string_substr.asp
  return substr($content, $posValueStart, ($posValueEnd - $posValueStart) );
}

function fetch_meta_data($content){
  // 抓出 PTT 文章的作者、看板、標題、時間
  $posMetaDataStart = strpos($content, "main-content");
  $content = substr($content, $posMetaDataStart);
  $author = get_next_article_metadata($content, 0);
  $board = get_next_article_metadata($content, strpos($content, $author));
  $title = get_next_article_metadata($content, strpos($content, $board));
  $published_date = get_next_article_metadata($content, strpos($content, $title));

  // 抓出最後一個推文
  // $postLastPushStart = strrpos($content, "push-content") + 16;
  // $postLastPushEnd = strrpos($content, "</span><span", $postLastPushStart);
  // $last_push = substr($content, $postLastPushStart, ($postLastPushEnd-$postLastPushStart) );

  $return = $_POST;
  $return["title"] = $title;
  $return["author"] = $author;
  $return["board"] = $board;
  $return["published_date"] = $published_date;
  // 改抓推文數，能達到相同效果，且不會受到相同推文的影響
  $return["push_count"] = substr_count($content, "push-content");
  return $return;
}

function main_job(){
  /*
    如果沒有 404 的話，$content 的內容會是「整個 HTML」
    e.g.
    <html>
      <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width">
        <meta name="robots" content="all" />
        ...
  */
  $content = get_url_content($_POST["url"]);

  if($content=="false"){
    $return_json["extra_message"] = "An error occurred when parsing the url. (404 page not found)";
  }else{
    $return_json = fetch_meta_data($content);
    $return_json["extra_message"] = save_to_database($return_json);
  }

  // 以 AJAX 回傳結果給 client 端
  echo json_encode($return_json);
}


function save_to_database($data){
  $db_configs_json = file_get_contents("db_config.json");
  $db_configs      = json_decode($db_configs_json, true);
  $servername      = $db_configs['servername'];
  $dbname          = $db_configs['dbname'];
  $username        = $db_configs['username'];
  $password        = $db_configs['password'];

  $debug_message = "";
  try {
    // Open a Connection to MySQL
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->exec("SET NAMES 'utf8';");
    $debug_message = "Connected successfully";

    // http://www.w3schools.com/php/php_mysql_prepared_statements.asp
    // $stmt = $conn->prepare("INSERT INTO main_table (url, title, author, board, published_date, period, email, last_check, remaining, last_push) VALUES (:url, :title, :author, :board, :published_date, :period, :email, :last_check, :remaining, :last_push)");

    $sql =
    "INSERT INTO main_table (url, title, author, board, published_date, period, email, last_check, remaining, push_count)
      VALUES ('" .
        $data['url'] ."', '" .
        $data['title'] . "',  '" .
        $data['author'] . "',  '" .
        $data['board'] . "',  '" .
        $data['published_date'] . "',  '" .
        $data['period'] . "',  '" .
        $data['email'] . "',  '" .
        'None' . "',  '" .
        $data['remaining'] . "',  '" .
        $data['push_count'] . "')";

    // use exec() because no results are returned
    $conn->exec($sql);
    $debug_message = "New record created successfully";

    }
  catch(PDOException $e)
    {
    $debug_message = "Connection failed: " . $e->getMessage();
    }

  // Close the Connection
  $conn = null;

  return $debug_message;
}

?>
