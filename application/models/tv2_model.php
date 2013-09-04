<?php

class Tv2_model extends CI_Model
{
	public $thumbnail_path = "http://thumbnail.instardara.com/tv/";
	private $tv_thumbnail_path = 'http://thumbnail.instardara.com/tv/';
	private $poster_thumbnail_path = 'http://thumbnail.instardara.com/poster/';
	private $category_thumbnail_path = 'http://thumbnail.instardara.com/category/';
	private $channel_thumbnail_path = 'http://thumbnail.instardara.com/channel/';
	
	private $deviceSupport = array('ios', 'android', 'wp', 's40');

	private $isTH = FALSE;
	private $device = ''; 
	private $limit = 20;

	function __construct()
	{
		parent::__construct();
	}

	function setDevice($device) {
		$this->device = $device;
		if($this->device == 's40') {
			$this->limit = 10;
		}
	}

	function setIsTH($isTH) {
		$this->isTH = $isTH;
	}

	function isDeviceSupport() {
		return in_array($this->device, $this->deviceSupport);
	}
	
	function createButton($label ='', $url='') {
		// type = button , cancel
		$obj = new stdClass();
		$obj->label = $label;
		$obj->url = $url;
		return $obj;
	}
	
	function getMessage() {

		if ($this->isDeviceSupport()) {
			$sql = "SELECT create_date id, title, message FROM tv_message WHERE active = 1 AND device = '$this->device' ORDER BY create_date LIMIT 0,1";
			$query = $this->db->query($sql);
			if ($query->num_rows() > 0) {
				$row = $query->first_row();
				return $row;
			}
		}
		
		$obj = new stdClass();
		$obj->id = '1';
		$obj->message = 'Hi,';
		$obj->message = 'Welcome to TV Thailand';

		return $obj;
	}

	function getAdvertise() {
/* 		$this->db->select("ad_name name, CONCAT( ad_url, CONCAT(  '?ref=tvthailand&time=', UNIX_TIMESTAMP() ) ) url, ad_time 'time'"); */
		$this->db->select("ad_name name, ad_url url, ad_time 'time', interval");
		$this->db->from('ads');
		$this->db->where('active',1);
		return $this->db->get()->result();
	}
	
	function getCategory() {
		// $this->db->select('id, title, description, thumbnail');
		// $this->db->from('category');
		// $this->db->where('online',1);
		// $this->db->order_by('order');
		// return $this->db->get()->result();

		$sql = "SELECT id, title, description, CASE thumbnail WHEN '' THEN '' ELSE CONCAT('$this->category_thumbnail_path', thumbnail) END AS thumbnail
		FROM tv_category 
		WHERE online = 1 
		ORDER BY `order`";

		return $this->db->query($sql)->result();
	}

	function getChannel() {
		// $this->db->select('id, title, description, thumbnail');
		// $this->db->from('channel');
		// $this->db->where('online',1);
		// $this->db->order_by('order');
		// return $this->db->get()->result();

/*
		$sql = "SELECT id, title, description, CONCAT('$this->channel_thumbnail_path', thumbnail) thumbnail 
		FROM tv_channel
		WHERE online = 1 
		ORDER BY `order`";

		return $this->db->query($sql)->result();
		
*/
		
		
		if ($this->isDeviceSupport())
		{
			$sql = "SELECT id, title, description, CASE thumbnail WHEN '' THEN '' ELSE CONCAT('$this->channel_thumbnail_path', thumbnail) END AS thumbnail, url_$this->device url 
			FROM tv_channel
			WHERE online = 1 
			ORDER BY `order`";
			return $this->db->query($sql)->result();
		}
		else
		{
			$sql = "SELECT id, title, description, CASE thumbnail WHEN '' THEN '' ELSE CONCAT('$this->channel_thumbnail_path', thumbnail) END AS thumbnail, url
			FROM tv_channel
			WHERE online = 1 
			ORDER BY `order`";
			return $this->db->query($sql)->result();
		}
	}

	function getAllProgram() {
		$sql = "SELECT program_id id, 
		program_title title, 
		CASE program_thumbnail WHEN '' THEN '' ELSE CONCAT('$this->tv_thumbnail_path', program_thumbnail) END AS thumbnail, 
		program_time description, 
		rating 
		FROM tv_program 
		WHERE online = 1";

		if ($this->isDeviceSupport()) {
			$sql .= " AND `$this->device` = 1";
		}

		if(!$this->isTH) {
			$sql .= " AND th_restrict = 0";
		}

		$sql .= " ORDER BY `title` ASC";

		return $this->db->query($sql)->result();
	}

	function getWhatsNewProgram($start = 0) {
		$sql = "SELECT program_id id, 
		program_title title, 
		CASE program_thumbnail WHEN '' THEN '' ELSE CONCAT('$this->tv_thumbnail_path', program_thumbnail) END AS thumbnail,  
		program_time description,  last_epname, rating
		FROM tv_program 
		WHERE online = 1";

		if ($this->isDeviceSupport()) {
			$sql .= " AND `$this->device` = 1";
		}

		if(!$this->isTH) {
			$sql .= " AND th_restrict = 0";
		}

		$sql .= " ORDER BY `update_date` DESC";
		$sql .= " LIMIT ".intval($start)." , $this->limit";

		return $this->db->query($sql)->result();
	}

	function getProgramByTopHits($start = 0) {
		$sql = "SELECT program_id id, 
		program_title title, 
		CASE program_thumbnail WHEN '' THEN '' ELSE CONCAT('$this->tv_thumbnail_path', program_thumbnail) END AS thumbnail, 
		program_time description, 
		rating
		FROM tv_program 
		WHERE online = 1";

		if ($this->isDeviceSupport()) {
			$sql .= " AND `$this->device` = 1";
		}

		if(!$this->isTH) {
			$sql .= " AND th_restrict = 0";
		}

		$sql .= " ORDER BY `view_count` DESC";
		$sql .= " LIMIT ".intval($start)." , $this->limit";

		return $this->db->query($sql)->result();
	}
	
	function getProgramByCategory($id, $start = 0) {
		$id = intval($id);

		$sql = "SELECT program_id id, 
		program_title title, 
		CASE program_thumbnail WHEN '' THEN '' ELSE CONCAT('$this->tv_thumbnail_path', program_thumbnail) END AS thumbnail, 
		program_time description, 
		rating 
		FROM tv_program 
		WHERE online = 1 AND category_id = $id";

		if ($this->isDeviceSupport()) {
			$sql .= " AND `$this->device` = 1";
		}

		if(!$this->isTH) {
			$sql .= " AND th_restrict = 0";
		}

		$sql .= " ORDER BY `update_date` DESC";
		$sql .= " LIMIT ".intval($start)." , $this->limit";

		return $this->db->query($sql)->result();
	}

	function getProgramByChannel($id, $start = 0) {
		$id = intval($id);

		$sql = "SELECT program_id id, 
		program_title title, 
		CASE program_thumbnail WHEN '' THEN '' ELSE CONCAT('$this->tv_thumbnail_path', program_thumbnail) END AS thumbnail, 
		program_time description, 
		rating 
		FROM tv_program 
		WHERE online = 1 AND channel_id = $id";

		if ($this->isDeviceSupport()) {
			$sql .= " AND `$this->device` = 1";
		}

		if(!$this->isTH) {
			$sql .= " AND th_restrict = 0";
		}

		$sql .= " ORDER BY `update_date` DESC";
		$sql .= " LIMIT ".intval($start)." , $this->limit";

		return $this->db->query($sql)->result();
	}
	
	function getProgramSearch($keyword = '',$start = 0)
	{
		$limit = "LIMIT ".intval($start)." , $this->limit";
		if($this->isTH)
		{
			$sql = "SELECT tv_program.program_id id, tv_program.program_title title, CASE tv_program.program_thumbnail WHEN '' THEN '' ELSE CONCAT('$this->tv_thumbnail_path', tv_program.program_thumbnail) END AS thumbnail, tv_program.program_time description,
		CASE SUBSTRING(CONVERT(tv_program.program_title USING utf8), 1, 1)  WHEN SUBSTRING(CONVERT('$keyword' USING utf8), 1, 1) THEN 1 ELSE 0 END AS occur 
		FROM tv_program WHERE tv_program.program_title LIKE '%$keyword%' 
		ORDER BY occur DESC, tv_program.program_title ASC $limit";
		}
		else
		{
			$sql = "SELECT tv_program.program_id id, tv_program.program_title title, CASE tv_program.program_thumbnail WHEN '' THEN '' ELSE CONCAT('$this->tv_thumbnail_path', tv_program.program_thumbnail) END AS thumbnail, tv_program.program_time time,
		CASE SUBSTRING(CONVERT(tv_program.program_title USING utf8), 1, 1)  WHEN SUBSTRING(CONVERT('$keyword' USING utf8), 1, 1) THEN 1 ELSE 0 END AS occur 
		FROM tv_program WHERE tv_program.program_title LIKE '%$keyword%' AND tv_program.th_restrict = 0
		ORDER BY occur DESC, tv_program.program_title ASC $limit";
		}

		
		return $this->db->query($sql)->result();
	}
	
	function getEpisode($program_id, $start = 0)
	{
		$id = intval($program_id);
		$this->db->select('programlist_id id, programlist_ep ep, programlist_epname title,  programlist_youtube_encrypt video_encrypt, programlist_src_type src_type, programlist_date date, programlist_count view_count, parts, programlist_password pwd');
		$this->db->from('programlist');
		$this->db->where('programlist_banned',0);
		$this->db->where('program_id', $id);
		if($this->device == 's40') {
			$this->db->where('programlist_src_type', 0);
		}
		$this->db->order_by('ep', 'desc');
		$this->db->order_by('id', 'desc');
		$this->db->limit($this->limit, intval($start));
		return $this->db->get()->result();
	}
	
	function getProgramInfo($id)
	{
		$id = intval($id);
		$sql = "SELECT tv_program.program_id id, 
		tv_program.program_title title, 
		CASE tv_program.program_thumbnail WHEN '' THEN '' ELSE CONCAT('$this->tv_thumbnail_path', tv_program.program_thumbnail) END AS thumbnail, 
		CASE tv_program.poster WHEN '' THEN '' ELSE CONCAT('$this->poster_thumbnail_path', tv_program.poster) END AS poster, 
		tv_program.program_time description, tv_program.program_detail detail, 
		last_epname, 
		SUM( tv_programlist.programlist_count ) view_count, 
		rating,
		5000 as vote_count 
		FROM tv_program
		INNER JOIN tv_programlist ON ( tv_program.program_id = tv_programlist.program_id ) 
		WHERE tv_program.program_id = '$id'
		GROUP BY tv_programlist.program_id";
		return $this->db->query($sql)->row_array();
	}
	
	function getEPDetail($programlist_id)
	{
		$programlist_id = intval($programlist_id);
		
		$sql = "SELECT tv_program.program_title title, tv_program.program_thumbnail thumbnail, tv_programlist.programlist_ep ep, tv_programlist.programlist_epname, tv_programlist.programlist_youtube, tv_programlist.programlist_date, tv_programlist.programlist_count, tv_programlist.programlist_src_type
		FROM  `tv_programlist` 
		INNER JOIN  `tv_program` ON  `tv_program`.program_id =  `tv_programlist`.program_id
		WHERE programlist_id =  '$programlist_id'";
		
		return $this->db->query($sql)->row_array();
	}
	
	function viewEP($id)
	{
		if(!empty($id))
		{
			$sql = "UPDATE `tv_programlist` 
			SET programlist_count = programlist_count + 1 
			WHERE programlist_id = '$id'";
			$this->db->query($sql);
		}
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