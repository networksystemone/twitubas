<?php
$myFile = "onlineusers.txt";
$fsc = file($myFile);
$lines = count(file($myFile));
$content = "<div>".$lines." Online Users:<br />";
foreach($fsc as $line) {
    $array = explode("|", $line);
    $content .= $array[0] ."</a><br />";
}
$content .= "</div>";

?>