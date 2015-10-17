<?php
$this->load->helper('html');

$ua = $_SERVER['HTTP_USER_AGENT'];
if(stristr($ua, "Android") || stristr($ua, "iPhone") || stristr($ua, "Windows CE") || stristr($ua, "AvantGo") ||
 stristr($ua,"Mazingo") || stristr($ua, "Mobile") ||
 stristr($ua, "T68") || stristr($ua,"Syncalot") ||
 stristr($ua, "Blazer") ) {
 	$isMobile = TRUE;
 	$width = 280;
 	$height = 200;
 } else {
	$isMobile = FALSE;
 	$width = 640;
 	$height = 390;
 }


if(!empty($title)) {
	$title = " - ".$title;
}
else
{
	$title = '';
}
$videos = explode(',', $video);

?>

<div style="text-align:center">
<br/>
<div>

<?php
if($src_type == 0)
{
	foreach ($videos as $key => $value) {
		if ($key < 4) {
			echo img('http://i.ytimg.com/vi/'.$value.'/1.jpg');
		}
	}
}
elseif($src_type == 1)
{
	echo '<img src="http://www.dailymotion.com/thumbnail/160x120/video/'.$videos[0].'" height="100" alt="" />';
}
?>
</div>
<?php	
		$count_video = count($videos);
		for($i=0;$i<$count_video;$i++){
		
		echo "<br />ตอนที่ ".$ep.$title." - ".($i+1).'/'.$count_video;
if($src_type == 0)
{
		echo '<br /><object style="width: '.$width.'px;height: '.$height.'px;">
<param name="movie" value="http://www.youtube.com/v/'.$videos[$i].'?version=3&autoplay=0&feature=player_embedded">
<param name="allowFullScreen" value="true">
<param name="allowScriptAccess" value="always">
<embed src="http://www.youtube.com/v/'.$videos[$i].'?version=3&autoplay=0&feature=player_embedded" 
type="application/x-shockwave-flash" 
allowfullscreen="true" allowScriptAccess="always" width="'.$width.'" height="'.$height.'"></object></center><br />';
}
elseif($src_type == 1)
{
	echo '<br /><iframe frameborder="0" width="'.$width.'" height="'.$height.'" src="http://www.dailymotion.com/embed/video/'.$videos[$i].'"></iframe>';
	
}
elseif($src_type == 14 || $src_type == 15)
{
	echo '<br /><a target="_blank" href="http://video.mthai.com/cool/player/'.$videos[$i].'.html">Link</a>';
	echo '<br />Password : '.$password;
}
		}
?>
</div>
<?php
if(isset($id)){
		$sql = "UPDATE `episodes` SET view_count = view_count + 1 WHERE episodes.id = '$id'";
		$db->query($sql);
}


?>
<br/>
<br/>
<br/>
