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


if(!empty($programlist_ep_name)) {
	$epname = " - ".$programlist_ep_name;
}
else
{
	$epname = '';
}
$youtubes = explode(',',$programlist_youtube);

?>

<div style="text-align:center">
<br/>
<div>

<?php
if($programlist_src_type == 0)
{
	foreach ($youtubes as $key => $value) {
		if ($key < 4) {
			echo img('http://i.ytimg.com/vi/'.$value.'/1.jpg');
		}
	}
	// echo img('http://i.ytimg.com/vi/'.$youtubes[0].'/1.jpg');
	// echo '<img src="http://i.ytimg.com/vi/'.$youtubes[0].'/1.jpg" height="100" alt="" />';
}
elseif($programlist_src_type == 1)
{
	echo '<img src="http://www.dailymotion.com/thumbnail/160x120/video/'.$youtubes[0].'" height="100" alt="" />';
}
?>
</div>
<?php	
		$count_youtube = count($youtubes);
		for($i=0;$i<$count_youtube;$i++){
		
		echo "<br />ตอนที่ ".$programlist_ep.$epname." - ".($i+1).'/'.$count_youtube;
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
elseif($programlist_src_type == 14 || $programlist_src_type == 15)
{
	echo '<br /><a target="_blank" href="http://video.mthai.com/cool/player/'.$youtubes[$i].'.html">Link</a>';
	echo '<br />Password : '.$programlist_password;
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
