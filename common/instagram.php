<?php
 
 
/*
        Instagram Uploader plugin for @dabr
 
        HOW TO INSTALL?
        - Go to your dabr installed directory
        - Place instagram.php into 'common/' folder
        - Open your index.php with text editor, and
        - Insert this line, below the "require 'common/settings.php';" :
 
          require 'common/instagram.php';
 
        - Login and browse your dabr installation and see new 'Instagram' button on menu navigation
 
        please enjoy, and take your own risk!
*/
 
 
 
 
/*
        here we re-register the menu callback handler
        and insert new `instagram` button on logged user menu navigation
*/
 
menu_register(array(
    'instagram' => array(
        'callback' => 'instagram_page',
        'security' => true,
    ),
));
 
 
function instagram_page($query) {
        $cmd = (isset($_REQUEST["cmd"])) ? $_REQUEST["cmd"] : null;
        
        if ($cmd == "register") instagram_register();
        else instagram_upload();
}

function instagram_upload() {
        $content  = "<p style='padding:5px'>";
        $content .= "Instagram is a popular photograph apps on iPhone / iPad users<br/>";
        $content .= "Now, without any iOS device you can register an Instagram account or even upload photos to Instagram and share to your timeline :)";
        $content .= "<br/>";

        if (isset($_FILES['foto'])) {
                $instagramapi = new Instagramapi();
                $username = $_POST["username"];
                $password = $_POST["password"];
                $caption = stripslashes(" " . $_POST["caption"]);

                $temp = $_FILES['foto']['tmp_name'];
                $foto = "@".$temp;

                if ($username && $password && $foto) {
                        $res = $instagramapi->login($username, $password);
                        if ($res["status"] == "ok") {
                                $upl = $instagramapi->upload($foto, $caption);
                                if ($upl["status"] == "ok") {
                                        $code = $upl["media"]["code"];
                                        $instaurl = "http://instagr.am/p/$code";
                                        $content = trim($upl["media"]["comments"][0]["text"]) . " " . $instaurl;
                                        //Send the user's message to twitter
                                        $request = API_URL.'statuses/update.json';
                                        $post_data = array('source' => 'dabr', 'status' => $content);
                                        $status = twitter_process($request, $post_data);
                                        header("Location: " . BASE_URL); exit;

                                } else { $msg = "Instagram Error : " . $upl["message"];}
                        } else { $msg = "Instagram Error : " . $res["message"];}
                } else { $msg = "Please fill username and password"; }
                
        } else  if ($_POST) $msg = "<b>ERROR :</b> Please select a photo!";


        if (isset($msg)) $_REQUEST["msg"] = $msg;

        if (isset($_REQUEST["msg"])) {
                $content .= "<pre style='padding:5px'><b>ERROR:</b> " . $_REQUEST["msg"] . "</pre><br/>";
        }

        $content .= "<form method='post' enctype='multipart/form-data'>";
        $content .= "<input type='hidden' name='cmd' id='cmd' value='upload'/>";
        $content .= "<p>Use your instagr.am account details to login</p>";
        $content .= '<p>Username:<br /><input type="text" name="username" id="username" /></p>';
        $content .= '<p>Password:<br /><input type="password" name="password" id="password" /></p>';
        $content .= '<p>Select photo to upload:<br/><input type=file name="foto" id="foto"></p>';
        $content .= '<p>Image caption:<br/><textarea type="text" id="caption" name="caption"></textarea></p>';
        $content .= '<p><input type=submit value="Upload!" /></p>';
        
        $content .= "</form>";

        $content .= "<p><a href='" . BASE_URL . "instagram?cmd=register'>Doesnt have an account? Register Now!</a></p>";
        $content .= "</p>"; 
        theme('page', 'Instagram', $content);
}

function instagram_register() {
        $content  = "<p style='padding:5px'>";
        $content .= "Instagram is a popular photograph apps on iPhone / iPad users<br/>";
        $content .= "Now, without any iOS device you can register an Instagram account or even upload photos to Instagram and share to your timeline :)";
        $content .= "<br/>";

        if (isset($_POST["username"])) {
                $instagramapi = new Instagramapi();

                $username = $_POST["username"];
                $password = $_POST["password"];
                $repassword = $_POST["repassword"];
                $email = $_POST["email"];

                if (!$username) $msg = "Please fill username!";
                if (!$password && !$msg) $msg = "Please fill password!";
                if (!$repassword && !$msg) $msg = "Please fill re-password!";
                if (!$email && !$msg) $msg = "Please fill email!";

                if ($password != $repassword && !$msg) $msg = "Your password doesnt match with re-password!";

                if (!$msg) {
                        $err = $instagramapi->register($username, $password, $email);
                        $info = array();
                        if ($err["status"] == "ok" && $err["account_created"] == 1) {
                                $msg = "Congratulation " . $_POST["username"] . ". You are registered with Instagram now!<br/>";
                                $msg .= "Please remember to always login with username : " . $_POST["username"];
                        } else { $msg = $err["message"];}
                }
                
        }

        if (isset($msg)) $_REQUEST["msg"] = $msg;
        if (isset($_REQUEST["msg"])) {
                $content .= "<pre style='padding:5px'><b>ERROR:</b> " . $_REQUEST["msg"] . "</pre><br/>";
        }

        $content .= "<form method='post' enctype='multipart/form-data'>";

        $content .= "<input type='hidden' name='cmd' id='cmd' value='register'/>";
        $content .= "<p>Create an Instagram Account</p>";
        $content .= '<p>Username:<br /><input type="text" name="username" id="username" /></p>';
        $content .= '<p>Password:<br /><input type="password" name="password" id="password" /></p>';
        $content .= '<p>Retype Password:<br /><input type="password" name="repassword" id="repassword" /></p>';
        $content .= '<p>Email:<br /><input type="text" name="email" id="email" /></p>';
        $content .= '<p><input type=submit value="Register!" /></p>';
        $content .= "</form>";

        $content .= "<p><a href='" . BASE_URL . "instagram'>Already have an account? Login Now!</a></p>";
        $content .= "</p>"; 
        theme('page', 'Instagram', $content);
}
?>
<?
  class Instagramapi {
    var $user_agent = "Instagram";
    var $status = 0;
    var $error = "";
    var $timeout = 50;
    var $cookies = "";
    var $last_url = "";
    var $fix_cookies = false;

    function __construct($cookies=null) {
        if (!isset($cookies)) $cookies = tempnam("/tmp/", "CURLCOOKIE");
        $this->cookies = $cookies;
    }

    public function open($url,$post="") {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        if ($post != "") {
                curl_setopt($ch, CURLOPT_POST,1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        }

        curl_setopt($ch, CURLOPT_USERAGENT, $this->user_agent);
        curl_setopt($ch,CURLOPT_TIMEOUT, $this->timeout); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch,CURLOPT_REFERER, $this->last_url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

        curl_setopt ($ch, CURLOPT_COOKIEJAR, $this->cookies); 
        curl_setopt ($ch, CURLOPT_COOKIEFILE, $this->cookies);

        $data = curl_exec($ch);
        $this->status = curl_getinfo($ch,CURLINFO_HTTP_CODE);
        $this->error = curl_error($ch);
        $this->last_url = $url;
        curl_close($ch);
        return $data;
    }

  public function register($username, $password, $email) {
          if (!$username) return array("status" => "fail", "message" => "Please fill username");
          if (!$password) return array("status" => "fail", "message" => "Please fill password");
          if (!$email) return array("status" => "fail", "message" => "Please fill email address");

          $post = $this->_render_form(array("username" => $username, "password" => $password, "device_id" => "0714", "email"=>$email));
          $isi = $this->open("https://instagr.am/api/v1/accounts/create/", $post);
          return json_decode($isi,1);
  }

  public function login($username, $password) {
          if (!$username) return array("status" => "fail", "message" => "Please fill username");
          if (!$password) return array("status" => "fail", "message" => "Please fill password");
          $post = $this->_render_form(array("username" => $username, "password" => $password, "device_id" => "0714"));
          $isi = $this->open("https://instagr.am/api/v1/accounts/login/", $post);
          return json_decode($isi, 1);
  }

  public function upload($foto, $caption="Just uploaded a photo ...") {
          if (!$foto) return array("status" => "fail", "message" => "Please select a photos");
          $a = time();
          $post = array("device_timestamp" => $a, "lat" => 0, "lng" => 0, "photo" => $foto);
          $isi = $this->open("http://instagr.am/api/v1/media/upload/", $post);
          $ret = json_decode($isi,1);
          if ($ret["status"] === "ok") {
                  $pst = array("device_timestamp" => $a, "caption"=> $caption, "filter_type" => "15", "source_type" => "1");
                  $isi = $this->open("https://instagr.am/api/v1/media/configure/", $pst);
                  return json_decode($isi,1);
          } else { return $ret; }
  }

  function _render_form($data) {
        $t = "";
        foreach ($data as $k=>$v) { $t .= "&$k=$v";}
        return str_replace("###&","","###".$t);
  }

  function _stringBetween($start, $end, $var) {
    return preg_match('{' . preg_quote($start) . '(.*?)' . preg_quote($end) . '}s', $var, $m)
        ? $m[1]
        : '';
  }
}
?>