<?php
$this->load->helper('html');

$ua = $_SERVER['HTTP_USER_AGENT'];
//echo $ua;
if(stristr($ua, "Android") || stristr($ua, "iPhone") || stristr($ua, "Windows CE") || stristr($ua, "AvantGo") ||
 stristr($ua,"Mazingo") || stristr($ua, "Mobile") ||
 stristr($ua, "T68") || stristr($ua,"Syncalot") ||
 stristr($ua, "Blazer") ) {
 	$isMobile = TRUE;
 	$width = 320;
 	$height = 240;
 } else {
	$isMobile = FALSE;
 	$width = 640;
 	$height = 390;
 }
?><!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="viewport" content="width=<?php echo $width; ?>, user-scalable=yes,
initial-scale=1, maximum-scale=1, minimum-scale=1"">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black">
<?php
if(!empty($programlist_ep_name)) {
	$epname = " - ".$programlist_ep_name;
}
else
{
	$epname = '';
}
$youtubes = explode(',',$programlist_youtube);


$title = $program_title." วันที่ ".$programlist_date;


echo "<title>TV Thailand on Web : ".$title."&nbsp;".$programlist_epname."</title>";

?>


<script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-22403997-3']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>

</head>
<body style="overflow:scroll;margin:0px">
<div style="text-align:center">
<br/>
<div><?php echo $title."&nbsp;".$programlist_epname."&nbsp;&nbsp;&nbsp;View : ".$programlist_count;?></div>
<div>

<?php
if($programlist_src_type == 0)
{
	echo img('http://i.ytimg.com/vi/'.$youtubes[0].'/1.jpg');
	// echo '<img src="http://i.ytimg.com/vi/'.$youtubes[0].'/1.jpg" height="100" alt="" />';
}
elseif($programlist_src_type == 1)
{
	echo '<img src="http://www.dailymotion.com/thumbnail/160x120/video/'.$youtubes[0].'" height="100" alt="" />';
}
?>
</div>
<?php

if ($isMobile) {
?>
<script type="text/javascript"><!--
google_ad_client = "ca-pub-6286183933093345";
/* Mobile Banner */
google_ad_slot = "7927269492";
google_ad_width = 320;
google_ad_height = 50;
//-->
</script>
<script type="text/javascript"
src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
</script>
<?php
} else {

?>

<script type="text/javascript"><!--
google_ad_client = "ca-pub-6286183933093345";
/* Leaderboard */
google_ad_slot = "6419882255";
google_ad_width = 728;
google_ad_height = 90;
//-->
</script>
<script type="text/javascript"
src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
</script>

<?php
 } // End AdSense

		
		$count_youtube = count($youtubes);
		for($i=0;$i<$count_youtube;$i++){
		
		echo "<br />ตอนที่ ".$programlist_ep.$epname." - ".($i+1);
if($programlist_src_type == 0)
{
		echo '<br /><object style="width: '.$width.'px;height: '.$height.'px;">
<param name="movie" value="http://www.youtube.com/v/'.$youtubes[$i].'?version=3&autoplay=0&feature=player_embedded">
<param name="allowFullScreen" value="true">
<param name="allowScriptAccess" value="always">
<embed src="http://www.youtube.com/v/'.$youtubes[$i].'?version=3&autoplay=0&feature=player_embedded" 
type="application/x-shockwave-flash" 
allowfullscreen="true" allowScriptAccess="always" width="'.$width.'" height="'.$height.'"></object></center><br />';
}
elseif($programlist_src_type == 1)
{
	echo '<br /><iframe frameborder="0" width="'.$width.'" height="'.$height.'" src="http://www.dailymotion.com/embed/video/'.$youtubes[$i].'"></iframe>';
	
}
		}
?>
</div>
<?php
if(isset($programlist_id)){
		$sql = "UPDATE `tv_programlist` SET programlist_count = programlist_count + 1 WHERE tv_programlist.programlist_id = '$programlist_id'";
		$db->query($sql);
}


?>
<br/>
<br/>
<br/>
</body>
</html>
