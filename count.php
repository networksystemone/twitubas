<?php
error_reporting(E_ERROR | E_PARSE);
$dataFile = "onlineusers.txt";
if (user_is_authenticated()) {
    $user = user_current_username();
}
// this is the time in **minutes** to consider someone online before removing them from our file
// berapa menit tenggang waktu yg dibutuhkan untuk tahu user masih online atau tidak.
$sessionTime = 5;
if(!file_exists($dataFile)) {
    $fp = fopen($dataFile, "w+");
    fclose($fp);
}
$users = array();
$onusers = array();
// check up
$fp = fopen($dataFile, "r");
flock($fp, LOCK_SH);
while(!feof($fp)) {
    $users[] = rtrim(fgets($fp, 32));
}
flock($fp, LOCK_UN);
fclose($fp);
// clean up
$x = 0;
$alreadyIn = FALSE;
foreach($users as $key => $data) {
    list(,$lastvisit) = explode("|", $data);
    if(time() - $lastvisit >= $sessionTime * 60) {
        $users[$x] = "";
    } else {
        if(strpos($data, $user) !== FALSE) {
            $alreadyIn = TRUE;
            $users[$x] = "$user|" . time(); //updating
        }
    }
    $x++;
}
if($alreadyIn == FALSE) {
    $users[] = "$user|" . time();
}
// write up
$fp = fopen($dataFile, "w+");
flock($fp, LOCK_EX);
$i = 0;
foreach($users as $single) {
    if($single != "") {
        fwrite($fp, $single . "\r\n");
        $i++;
    }
}
flock($fp, LOCK_UN);
fclose($fp);
?>