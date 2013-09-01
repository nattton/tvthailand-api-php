<?php

class Tv_model extends CI_Model
{
	public $thumbnail_path = 'http://thumbnail.instardara.com/tv/';
	private $deviceSupport = array('ios', 'android', 'wp');

	private $isTH = FALSE;
	private $device = '';

	function __construct()
	{
		parent::__construct();
	}
	
	function setDevice($device) {
		$this->device = $device;
	}

	function setIsTH($isTH) {
		$this->isTH = $isTH;
	}

	function isDeviceSupport() {
		return in_array($this->device, $this->deviceSupport);
	}
	
	function createButton($label ='', $url='')
	{
		// type = button , cancel
		$obj = new stdClass();
		$obj->label = $label;
		$obj->url = $url;
		return $obj;
	}

	function getCategory()
	{
		$this->db->select('category_id, category_name');
		$this->db->from('category_v1');
		$this->db->where('online',1);
		$this->db->order_by('category_order');
		return $this->db->get()->result();
	}
	
	function getCategoryLao()
	{
		$this->db->select('category_id, category_name_lao category_name');
		$this->db->from('category_v1');
		$this->db->where('online',1);
		$this->db->order_by('category_order');
		return $this->db->get()->result();
	}

	function getCategoryV2()
	{
		$this->db->select('id, title, description, thumbnail');
		$this->db->from('category');
		$this->db->where('online',1);
		$this->db->order_by('order');
		return $this->db->get()->result();
	}

	function getChannel()
	{
		$this->db->select('id, title, description, thumbnail');
		$this->db->from('channel');
		$this->db->where('online',1);
		$this->db->order_by('order');
		return $this->db->get()->result();
	}

	
	function getLiveChannel($device='')
	{
		$deviceSupport = array('ios', 'android', 'wp');
		if (in_array($device, $deviceSupport))
		{
			$this->db->select('id, title, message, image, url_'.$device.' url');
			$this->db->where('url_'.$device.' !=', '');
		}
		else
		{
			$this->db->select('id, title, message, image, url');
		}
		
		$this->db->from('live_channel');
		$this->db->where('online',1);
		$this->db->order_by('order');
		return $this->db->get()->result();
	}
	
	function getAds()
	{
		// $this->db->select("ad_name name, CONCAT( ad_url, CONCAT(  '?ref=tvthailand&time=', UNIX_TIMESTAMP() ) ) url, ad_time 'time'");
		if ($this->isDeviceSupport())
		{
			$this->db->select("ad_name name, CONCAT( ad_url, CONCAT(  '?ref=tvthailand&time=', UNIX_TIMESTAMP() ) ) url, ad_time_".$this->device." 'time'");
		}
		else
		{
			$this->db->select("ad_name name, CONCAT( ad_url, CONCAT(  '?ref=tvthailand&time=', UNIX_TIMESTAMP() ) ) url, ad_time 'time'");
		}

		$this->db->from('ads');
		$this->db->where('active',1);
		return $this->db->get()->result();
	}
	
	function getProgramSearch($keyword = '',$start = 0)
	{
		$limit = "LIMIT ".intval($start)." , 20";
/*
		$pos = strpos($keyword, '@');
		if ($pos !== false) {
			$keyword = str_replace('@', '', $keyword);
			$sql = "SELECT tv_program.program_id, tv_program.program_title title, tv_program.program_thumbnail thumbnail, tv_program.program_time time,
		CASE SUBSTRING(CONVERT(tv_program.program_title USING utf8), 1, 1)  WHEN SUBSTRING(CONVERT('$keyword' USING utf8), 1, 1) THEN 1 ELSE 0 END AS occur 
		FROM tv_program WHERE tv_program.program_title LIKE '%$keyword%' 
		ORDER BY occur DESC, tv_program.program_title ASC $limit";
		}
		else {
			$sql = "SELECT tv_program.program_id, tv_program.program_title title, tv_program.program_thumbnail thumbnail, tv_program.program_time time,
		CASE SUBSTRING(CONVERT(tv_program.program_title USING utf8), 1, 1)  WHEN SUBSTRING(CONVERT('$keyword' USING utf8), 1, 1) THEN 1 ELSE 0 END AS occur 
		FROM tv_program WHERE tv_program.program_title LIKE '%$keyword%' AND tv_program.online = 1
		ORDER BY occur DESC, tv_program.program_title ASC $limit";
		}
*/

		$where = "";
		if ($this->isDeviceSupport()) {
			$where .= " AND tv_program.$this->device = 1";
		}
		if(!$this->isTH)
		{
			$where .= " AND tv_program.th_restrict = 0";
		}
		
		$sql = "SELECT tv_program.program_id, tv_program.program_title title, tv_program.program_thumbnail thumbnail, tv_program.program_time time,
		CASE SUBSTRING(CONVERT(tv_program.program_title USING utf8), 1, 1)  WHEN SUBSTRING(CONVERT('$keyword' USING utf8), 1, 1) THEN 1 ELSE 0 END AS occur 
		FROM tv_program WHERE tv_program.program_title LIKE '%$keyword%' AND tv_program.online = 1 $where
		ORDER BY occur DESC, tv_program.program_title ASC $limit";
		
		return $this->db->query($sql)->result();
	}
	
	function getProgram($cat_id, $start = 0)
	{
		$numLimit = 30;
		$cat_id = intval($cat_id);
		$limit = "LIMIT ".intval($start)." , $numLimit";
		
		if(0 == $cat_id)
		{
			$this->db->select('program_id, program_title title, program_thumbnail thumbnail,  last_epname time');
			$this->db->from('program');
			$this->db->where('online', 1);
			
			if ($this->isDeviceSupport()) {
				$this->db->where($this->device, 1);
			}
			if(!$this->isTH)
			{
				$this->db->where('th_restrict', 0);
			}

			$this->db->order_by('update_date', 'desc');
			$this->db->limit($numLimit, intval($start));
			return $this->db->get()->result();
			
/* 			return $this->methodProgramLastest(" WHERE tv_program.online = 1",$limit); */
		}
		elseif(99 == $cat_id)
		{
			$where = "";
			if ($this->isDeviceSupport()) {
				$where .= " AND tv_program.$this->device = 1";
			}
			if(!$this->isTH)
			{
				$where .= " AND tv_program.th_restrict = 0";
			}

			$sql = "SELECT tv_program.program_id, tv_program.program_title title, tv_program.program_thumbnail thumbnail, tv_program.program_time time FROM tv_program
INNER JOIN tv_programlist ON ( tv_program.program_id = tv_programlist.program_id ) 
WHERE tv_programlist.programlist_banned = 0 AND tv_program.online = 1  $where GROUP BY tv_programlist.program_id ORDER BY SUM( tv_programlist.programlist_count ) DESC $limit";

// 			if($this->isTH)
// 			{
// 				$sql = "SELECT tv_program.program_id, tv_program.program_title title, tv_program.program_thumbnail thumbnail, tv_program.program_time time FROM tv_program
// INNER JOIN tv_programlist ON ( tv_program.program_id = tv_programlist.program_id ) 
// WHERE tv_programlist.programlist_banned = 0 AND tv_program.online = 1 GROUP BY tv_programlist.program_id ORDER BY SUM( tv_programlist.programlist_count ) DESC $limit";
// 			}

			return $this->db->query($sql)->result();
		}
		elseif(100 < $cat_id )
		{
			$channel_id = $cat_id - 100;
			return $this->getProgramChannel($channel_id, $start, $numLimit);
		}
		elseif(100 == $cat_id)
		{
			$this->db->select('program_id, program_title title, program_thumbnail thumbnail,  program_time time');
			$this->db->from('program');
			$this->db->where('online', 1);

			if ($this->isDeviceSupport()) {
				$this->db->where($this->device, 1);
			}
			if(!$this->isTH)
			{
				$this->db->where('th_restrict', 0);
			}

			$this->db->order_by('title', 'asc');
			$this->db->limit($numLimit, intval($start));
			return $this->db->get()->result();
			/*

			$sql = "SELECT tv_program.program_id, tv_program.program_title title, tv_program.program_thumbnail thumbnail, tv_program.program_time time FROM tv_program
INNER JOIN tv_programlist ON ( tv_program.program_id = tv_programlist.program_id ) GROUP BY tv_programlist.program_id ORDER BY tv_program.program_title ASC $limit";
				return $this->db->query($sql)->result();
*/
				
		}
		else
		{
			$this->db->select('program_id, program_title title, program_thumbnail thumbnail,  program_time time');
			$this->db->from('program');
			$this->db->where('online', 1);

			if ($this->isDeviceSupport()) {
				$this->db->where($this->device, 1);
			}
			if(!$this->isTH)
			{
				$this->db->where('th_restrict', 0);
			}

			$this->db->where('category_id', $cat_id);
			$this->db->order_by('update_date', 'desc');
			$this->db->limit($numLimit, intval($start));
			return $this->db->get()->result();
		}
	}

	function getProgramLao($cat_id, $start = 0)
	{
		$numLimit = 30;
		$cat_id = intval($cat_id);
		$limit = "LIMIT ".intval($start)." , $numLimit";
		
		if(0 == $cat_id)
		{
			$this->db->select('program_id, program_title_lao title, program_thumbnail thumbnail,  last_epname time');
			$this->db->from('program');
			$this->db->where('online', 1);
			
			if ($this->isDeviceSupport()) {
				$this->db->where($this->device, 1);
			}
			if(!$this->isTH)
			{
				$this->db->where('th_restrict', 0);
			}

			$this->db->order_by('update_date', 'desc');
			$this->db->limit($numLimit, intval($start));
			return $this->db->get()->result();
			
/* 			return $this->methodProgramLastest(" WHERE tv_program.online = 1",$limit); */
		}
		elseif(99 == $cat_id)
		{
			$where = "";
			if ($this->isDeviceSupport()) {
				$where .= " AND tv_program.$this->device = 1";
			}
			if(!$this->isTH)
			{
				$where .= " AND tv_program.th_restrict = 0";
			}

			$sql = "SELECT tv_program.program_id, tv_program.program_title_lao title, tv_program.program_thumbnail thumbnail, tv_program.program_time time FROM tv_program
INNER JOIN tv_programlist ON ( tv_program.program_id = tv_programlist.program_id ) 
WHERE tv_programlist.programlist_banned = 0 AND tv_program.online = 1  $where GROUP BY tv_programlist.program_id ORDER BY SUM( tv_programlist.programlist_count ) DESC $limit";

// 			if($this->isTH)
// 			{
// 				$sql = "SELECT tv_program.program_id, tv_program.program_title title, tv_program.program_thumbnail thumbnail, tv_program.program_time time FROM tv_program
// INNER JOIN tv_programlist ON ( tv_program.program_id = tv_programlist.program_id ) 
// WHERE tv_programlist.programlist_banned = 0 AND tv_program.online = 1 GROUP BY tv_programlist.program_id ORDER BY SUM( tv_programlist.programlist_count ) DESC $limit";
// 			}

			return $this->db->query($sql)->result();
		}
		elseif(100 < $cat_id )
		{
			$channel_id = $cat_id - 100;
			return $this->getProgramChannel($channel_id, $start, $numLimit);
		}
		elseif(100 == $cat_id)
		{
			$this->db->select('program_id, program_title_lao title, program_thumbnail thumbnail,  program_time time');
			$this->db->from('program');
			$this->db->where('online', 1);

			if ($this->isDeviceSupport()) {
				$this->db->where($this->device, 1);
			}
			if(!$this->isTH)
			{
				$this->db->where('th_restrict', 0);
			}

			$this->db->order_by('title', 'asc');
			$this->db->limit($numLimit, intval($start));
			return $this->db->get()->result();
			/*

			$sql = "SELECT tv_program.program_id, tv_program.program_title title, tv_program.program_thumbnail thumbnail, tv_program.program_time time FROM tv_program
INNER JOIN tv_programlist ON ( tv_program.program_id = tv_programlist.program_id ) GROUP BY tv_programlist.program_id ORDER BY tv_program.program_title ASC $limit";
				return $this->db->query($sql)->result();
*/
				
		}
		else
		{
			$this->db->select('program_id, program_title_lao title, program_thumbnail thumbnail,  program_time time');
			$this->db->from('program');
			$this->db->where('online', 1);

			if ($this->isDeviceSupport()) {
				$this->db->where($this->device, 1);
			}
			if(!$this->isTH)
			{
				$this->db->where('th_restrict', 0);
			}

			$this->db->where('category_id', $cat_id);
			$this->db->order_by('update_date', 'desc');
			$this->db->limit($numLimit, intval($start));
			return $this->db->get()->result();
		}
	}

	function getProgramChannel($channel_id, $start = 0, $numLimit = 20)
	{
		$this->db->select('program_id, program_title title, program_thumbnail thumbnail,  program_time time');
		$this->db->from('program');
		$this->db->where('online', 1);

		if ($this->isDeviceSupport()) {
				$this->db->where($this->device, 1);
		}
		if(!$this->isTH)
		{
			$this->db->where('th_restrict', 0);
		}

		$this->db->where('channel_id', $channel_id);
		$this->db->order_by('update_date', 'desc');
		$this->db->limit($numLimit, intval($start));
		return $this->db->get()->result();
	}

	function getProgramNewRelease($start = 0, $numLimit = 20, $isTH = FALSE)
	{
		$this->db->select('program_id, program_title title, program_thumbnail thumbnail,  program_time time');
		$this->db->from('program');
		$this->db->where('online', 1);

		if ($this->isDeviceSupport()) {
				$this->db->where($this->device, 1);
		}
		if(!$this->isTH)
		{
			$this->db->where('th_restrict', 0);
		}

		$this->db->order_by('program_id', 'desc');
		$this->db->limit($numLimit, intval($start));
		return $this->db->get()->result();
	}
	
	function getProgramlist($program_id,$start = 0)
	{
		$program_id = intval($program_id);
		$this->db->select('programlist_id, programlist_ep ep, programlist_epname epname,  programlist_youtube_encrypt youtube_encrypt, programlist_src_type src_type, programlist_date date, programlist_count count, programlist_password pwd');
		$this->db->from('programlist');
		$this->db->where('programlist_banned',0);
		$this->db->where('program_id', $program_id);
		$this->db->order_by('programlist_ep', 'desc');
		$this->db->order_by('programlist_id', 'desc');
		$this->db->limit(40, intval($start));
		return $this->db->get()->result();
	}
	
/*
	function getProgramlistPlain($program_id,$start = 0)
	{
		$program_id = intval($program_id);
		$this->db->select('programlist_id, programlist_ep ep, programlist_epname epname,  programlist_youtube youtube_encrypt, programlist_src_type src_type, programlist_date date, programlist_count count');
		$this->db->from('programlist');
		$this->db->where('program_id', $program_id);
		$this->db->order_by('programlist_ep', 'desc');
		$this->db->order_by('programlist_id', 'desc');
		$this->db->limit(20, intval($start));
		return $this->db->get()->result();
	}
*/
	
	
	function getProgramDetail($program_id)
	{
		$program_id = intval($program_id);
		
		$sql = "SELECT tv_program.program_id, tv_program.program_title title, CONCAT('$this->thumbnail_path',tv_program.program_thumbnail) thumbnail, tv_program.program_detail detail, tv_program.program_time time, SUM( tv_programlist.programlist_count ) count
			FROM tv_program
			INNER JOIN tv_programlist ON ( tv_program.program_id = tv_programlist.program_id ) 
			WHERE tv_program.program_id = '$program_id'
			GROUP BY tv_programlist.program_id";
		
		return $this->db->query($sql)->row_array();
	}
	
	function getProgramInfo($program_id)
	{
		$program_id = intval($program_id);
		$query = $this->db->query("SELECT * FROM tv_program WHERE program_id = '$program_id'");
		if ($query->num_rows() > 0)
		{
			return $query->row();
		}
		else
		{
			return FALSE;
		}
	}
	
	function getProgramlistDetail($programlist_id)
	{
		$programlist_id = intval($programlist_id);
		
		$sql = "SELECT tv_program.program_title, tv_program.program_thumbnail, tv_programlist.programlist_ep, tv_programlist.programlist_epname, tv_programlist.programlist_youtube, tv_programlist.programlist_date, tv_programlist.programlist_count, tv_programlist.programlist_src_type
		FROM  `tv_programlist` 
		INNER JOIN  `tv_program` ON  `tv_program`.program_id =  `tv_programlist`.program_id
		WHERE programlist_id =  '$programlist_id'";
		
		return $this->db->query($sql)->row_array();
	}
	
	function viewProgramlist($programlist_id)
	{
		if(!empty($programlist_id))
		{
			$sql = "UPDATE `tv_programlist` 
			SET programlist_count = programlist_count + 1 
			WHERE programlist_id = '$programlist_id'";
			$this->db->query($sql);
		}
	}
	
	function updateStatViewToServer($greater = 50)
	{
		$this->db->where('programlist_count >',$greater);
		$this->db->order_by('programlist_count','desc');
		$query = $this->db->get('programlist_stat');
		
		$countProcess = 0;
		foreach($query->result() as $row)
		{
			/* 		http://cms.makathon.com/api/updateViewProgramlistStat/15586/30 */
			$url = 'http://cms.makathon.com/api/updateViewProgramlistStat/'.$row->programlist_id.'/'.$row->programlist_count;
			$jsonText = file_get_contents($url);
			$jObj = json_decode($jsonText);
			if($jObj->code == 200)
			{
				$data = array('programlist_count' => 'programlist_count - '.strval($row->programlist_count));
				$this->db->where('programlist_id', $row->programlist_id);
				$this->db->update('programlist_stat', $data);
				$countProcess++;
			}
		}
		echo $countProcess.' Processes : Finish';
	}
	
	function iosToken($deviceid,$devicetoken)
	{
		$arrayReplace = array('<',' ','>');
		$devicetoken = str_replace($arrayReplace, '', $devicetoken);

		$sql = "REPLACE INTO tv_device (device_id, devicetoken, device, timeupdate ) 
		VALUES ('$deviceid', '$devicetoken', 'ios', NOW())";
		$this->db->query($sql);
	}
	
	function methodProgramLastest($where ='',$limit='')
	{
		$sql = "SELECT tv_program.program_id,
		tv_program.program_title title,
		tv_program.program_thumbnail thumbnail,
		tv_program.program_time time
		FROM tv_program JOIN (SELECT tv_programlist.program_id,
		MAX(tv_programlist.programlist_id) as programlist_id,
		MAX(tv_programlist.programlist_date) as pl_date 
		FROM tv_programlist 
		GROUP BY program_id 
		ORDER BY MAX(programlist_date) DESC) as prlist 
		ON tv_program.program_id = prlist.program_id 
		$where
		ORDER BY prlist.programlist_id DESC
		$limit ";
		
		return $this->db->query($sql)->result();
	}
	
	function encryptData()
	{
		$this->db->select('programlist_id, programlist_youtube');
		$this->db->from('programlist');
		$result = $this->db->get()->result();
		$enFrom = array('+','/','=','a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s');
		$enTo = array('-','_',',','!','@','#','$','%','^','&','*','(',')','{','}','[',']',':',';','<','>','?');
		foreach ($result as $row) {
			$encrypt = base64_encode($row->programlist_youtube);
			$encrypt = str_replace($enFrom, $enTo, $encrypt);
			$data = array('programlist_youtube_encrypt' => $encrypt);
			$this->db->where('programlist_id', $row->programlist_id);
			$this->db->update('programlist', $data);
		}
		return 'Encrypt Data Successfully';
	}
}
?>