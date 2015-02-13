<?php
/* Part of the codes are modified from https://github.com/jonsuh/jQuery-Ajax-Call-to-PHP-Script-with-JSON-Return */

if (is_ajax()) {
  if (isset($_POST["action"]) && !empty($_POST["action"])) { //Checks if action value exists
    $action = $_POST["action"];
    switch($action) { //Switch case for value of action
      case "get_article_title": get_article_title(); break;
    }
  }
}




//Function to check if the request is an AJAX request
function is_ajax() {
  return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}


function get_article_title(){
  $url = $_POST["ptt_url"];

  if(url_is_valid($url)=="false"){
    // TODO echo alert when 404
    $post_data = array('ptt_title' => 'error not valid!!!',
        'K_A' => 'asadasdas'
    );
    echo json_encode($post_data);
    return;
  }

  $return = $_POST;
  $return["json"] = json_encode($return);
  echo json_encode($return);

}


/*
  Check if the URL is valid
  e.g. https://www.ptt.cc/bbs/not_exist_board/not_exist_page.html is not valid
*/
function url_is_valid($url){
  $handle = curl_init($url);
  curl_setopt($handle,  CURLOPT_RETURNTRANSFER, TRUE);
  curl_setopt($handle, CURLINFO_HEADER_OUT, true);

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


?>