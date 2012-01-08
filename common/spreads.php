<?php

function twitter_spreads_page($text) {
  $date_joined = date('jS M Y', $raw_date_joined);
  $tweets_per_day = twitter_tweets_per_day($user, 1);
 $content .= '<fieldset><legend><img src="http://si0.twimg.com/images/dev/cms/intents/bird/bird_blue/bird_16_blue.png" width="16" height="16" /> What\'s Happening?</legend><div class="ss"><div class="form"><form method="post" action="update">
  <div style="margin:0;padding:0;display:inline"><textarea id="status" name="status" cols="44" style="width:100%">I\'m using #twitUBAS. Wanna try mobile twitter with fast access & rich of features? Try #twitUBe now. http://twit.basko.ro</textarea><input name="in_reply_to_id" value="'.$in_reply_to_id.'" type="hidden" /><input type="submit" value="Tweet" class="buttons" /></form></span><span id="remaining">140</span> 
  <span id="geo" style="display: none;"><input onclick="goGeo()" type="checkbox" id="geoloc" name="location" /> <label for="geoloc" id="lblGeo"></label></span></div></div></div></fieldset>  <script type="text/javascript">
started = false;
chkbox = document.getElementById("geoloc");
if (navigator.geolocation) {
    geoStatus("Tweet my location");
    if ("'.$_COOKIE['geo'].'"=="Y") {
        chkbox.checked = true;
        goGeo();
    }
}
function goGeo(node) {
    if (started) return;
    started = true;
    geoStatus("Locating...");
    navigator.geolocation.getCurrentPosition(geoSuccess, geoStatus);
}
function geoStatus(msg) {
    document.getElementById("geo").style.display = "inline";
    document.getElementById("lblGeo").innerHTML = msg;
}
function geoSuccess(position) {
    geoStatus("Tweet my <a href=\'http://maps.google.co.uk/m?q=" + position.coords.latitude + "," + position.coords.longitude + "\' target=\'blank\'>location</a>");
    chkbox.value = position.coords.latitude + "," + position.coords.longitude;
}
  </script>'; 
    

    theme('page', "Spreads", $content);
} 

?>