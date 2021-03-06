<!doctype html>
<html>

<head>
  <meta charset="utf-8">
  <title>send-email-when-ptt-posts-update</title>
  <link rel="stylesheet" type="text/css" href="lib/bootstrap/darkly.min.css">
  <link rel="stylesheet" type="text/css" href="index.css">
</head>

<body>
  <div class="container">

    <div id="div_show_rows">
    <!-- 目前現有的資料(從資料庫讀取) -->
    <?php
      $db_configs_json = file_get_contents("db_config.json");
      $db_configs      = json_decode($db_configs_json, true);
      $servername      = $db_configs['servername'];
      $dbname          = $db_configs['dbname'];
      $username        = $db_configs['username'];
      $password        = $db_configs['password'];

      try {
        // Open a Connection to MySQL
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        // set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->exec("SET NAMES 'utf8';");

        $sql = "SELECT * FROM main_table";
        foreach ($conn->query($sql) as $row) {
          create_panel($row);
        }
      }
      catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
      }
      $conn = null;


      function create_panel($row){
        echo '<div class="panel panel-default"><div class="panel-heading">';
        echo '<button class="btn btn-danger btn-xs">刪除這筆資料</button>';
        echo '&nbsp;&nbsp;&nbsp;';
        echo '<a target="_blank" href="' . $row['url'] . '">' . $row['url'] . '</a>';
        echo '&nbsp;&nbsp;&nbsp;';
        echo '(推文數：'. $row['push_count'] .')';
        echo '</div><div class="panel-body"><table class="table table-bordered table-hover">';
        echo '<tr><td class="col-md-1">標題</td><td class="col-md-4">' . $row['title'];
        echo '</td><td class="col-md-1">加入日期</td><td class="col-md-6">' . $row['add_date'];
        echo '</td></tr><tr><td>作者</td><td>' . $row['author'];
        echo '</td><td>檢查截止</td><td> 剩下 ' . $row['remaining'] . ' 天';
        echo '</td></tr><tr><td>看板</td><td>' . $row['board'];
        echo '</td><td>將通知寄至</td><td>' . $row['email'];
        echo '</td></tr><tr><td>發文日期</td><td>' . $row['published_date'];
        echo '</td><td>上次檢查</td><td>' . $row['last_check'];
        echo '</td></tr></table></div></div>';
      }
    ?>

    <!-- 範例版面
    <div class="panel panel-default">
        <div class="panel-heading">
          <button class="btn btn-danger btn-xs">刪除這筆資料</button>
          &nbsp;&nbsp;&nbsp;
          <a target="_blank" href="https://www.ptt.cc/bbs/Boy-Girl/M.1421932242.A.CAF.html">https://www.ptt.cc/bbs/Boy-Girl/M.1421932242.A.CAF.html</a>
          &nbsp;&nbsp;&nbsp;
          (推文數：128)
        </div>
        <div class="panel-body">
          <table class="table table-bordered table-hover">
            <tr>
              <td class="col-md-1">標題</td>
              <td class="col-md-4">[範例] 文章標題</td>
              <td class="col-md-1">加入日期</td>
              <td class="col-md-6">2015-09-25 22:38:22</td>
            </tr>
            <tr>
              <td>作者</td>
              <td>sunshine5566 (陽光56)</td>
              <td>檢查截止</td>
              <td>剩下 7 天</td>
            </tr>
            <tr>
              <td>看板</td>
              <td>Boy-Girl</td>
              <td>將通知寄至</td>
              <td>johnyluyte@gmail.com</td>
            </tr>
            <tr>
              <td>發文日期</td>
              <td>Thu Jan 22</td>
              <td>上次檢查</td>
              <td>Thu Jan 24</td>
            </tr>
          </table>
        </div>
    </div>
    -->

    </div>
    <hr>

    <!-- 新增新的一筆資料 -->
    <div class="panel panel-primary">
        <div class="panel-heading">
          新增一筆新的資料
        </div>
        <div class="panel-body">
          <form id="myForm" method="post" accept-charset="utf-8">
            <label class="control-label" for="inputDefault">新增批踢踢文章網址：</label>
            <!-- <input type="text" class="form-control" id="inputDefault" placeholder="例如：https://www.ptt.cc/bbs/Boy-Girl/M.1421932242.A.CAF.html"> -->
            <input type="text" id="url" name="url" size="70" placeholder=" https://www.ptt.cc/bbs/Boy-Girl/M.1421932242.A.CAF.html">
            <br>
            <label class="control-label" for="inputDefault">每隔</label>
            <select style="color:black" id="select" name="period">
              <option>24</option>
            </select>
            小時檢查一次該文章是否有新留言，
            <label class="control-label" for="inputDefault">持續：</label>
            <select style="color:black" id="select" name="remaining">
              <option>31</option>
            </select>
            天。
            <br> 若有新留言，請寄電子郵件至：
            <input type="email" id="email" name="email" size="70" placeholder=" abc@example.com">
            <br>
            <!-- <input type="submit"> -->
            <!-- <button type="button" id="btn-addNewEvent" class="btn btn-primary">Add new Event</button> -->
            <input type="submit" id="btn-addNewEvent" class="btn btn-primary" value="Add new Event">
          </form>
        </div>
    </div>

    <div id="div_info">
      <button id="btn_check_now" class="btn btn-info btn-lg">立即檢查是否有新留言</button>
    </div>

    <footer class="footer">
      <div class="container">
        <p class="text-muted">
          訂閱並檢查 PTT 是否有新推文. Built with
          <a href='http://getbootstrap.com/' target='_blank'>Bootstrap</a> /
          <a href='https://jquery.com/' target='_blank'>jQuery</a>. By <a href='mailto:johnyluyte@gmail.com'>Chun Chien</a>, licensed under the <a href='http://opensource.org/licenses/MIT' target='_blank'>MIT License</a>. Fork me on <a href='https://github.com/johnyluyte/send-email-when-ptt-posts-update' target='_blank'>Github</a>.
          <br/>
        </p>
      </div>
    </footer>

  </div>
  <!-- /container -->

  <!-- Insert External Sctips Here -->
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
  <script src="lib/bootstrap/bootstrap.min.js"></script>
  <script src='index.js'></script>
</body>

</html>
