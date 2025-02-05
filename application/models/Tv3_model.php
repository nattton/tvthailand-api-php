<?php

class Tv3_model extends CI_Model
{
	public $thumbnail_path = "http://thumbnail.instardara.com/tv/";
	private $tv_thumbnail_path = 'http://thumbnail.instardara.com/tv/';
	private $poster_thumbnail_path = 'http://thumbnail.instardara.com/poster/';
	private $category_thumbnail_path = 'http://thumbnail.instardara.com/category/';
	private $channel_thumbnail_path = 'http://thumbnail.instardara.com/channel/';
	private $radio_thumbnail_path = 'http://thumbnail.instardara.com/radio/';
	private $otv_logo_path = 'http://thumbnail.instardara.com/otv_logo/';
	
	private $deviceSupport = array('ios', 'android', 'wp', 's40', 'windows');

	private $isTH = FALSE;
	private $device = '';
	private $appId = '';
	private $build = 0;
	private $version = '';
	private $limit = 20;

	function __construct()
	{
		parent::__construct();
	}

	function setClientInfo($countryCode, $device, $appId, $build, $version) {
		$this->isTH = ($countryCode == 'TH');
		$this->device = $device;
		$this->appId = $appId;
		$this->build = intval($build);
		$this->version = $version;

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
		if ($this->isDeviceSupport()) {
			$this->db->select("ad_name name, ad_url_$this->device url, ad_time_$this->device 'time', `interval`");
			$this->db->where("ad_time_$this->device >", 0);
			$this->db->where("ad_url_$this->device !=", "");
		}
		else {
			$this->db->select("ad_name name, ad_url url, ad_time 'time', `interval`");
			$this->db->where("ad_time >", 0);
			$this->db->where("ad_url !=", "");
		}
		$this->db->where("v3", 1);
		$this->db->where("build_min <=", $this->build);
		$this->db->where("build_max >=", $this->build);
		$this->db->from('ads');
		$this->db->where('active', 1);
		return $this->db->get()->result();
	}
	
	function getPrerollAdvertise(){
		$this->db->select("name, url, skip_time");
		$this->db->from('preroll_advertise');
		$this->db->where('active', 1);
		if ($this->isDeviceSupport()) {
			$this->db->where("platform", $this->device);
		}
		return $this->db->get()->result();
	}
	
	function getCategory() {
		$catList = array();
		
		// Add Recents
		$obj = new stdClass();
		$obj->id = 'recents';
		$obj->title = 'รายการล่าสุด';
		$obj->description = 'Recents';
		if ($this->device == "s40")
		{
			$obj->thumbnail = 'http://thumbnail.instardara.com/category/s40_00_recently.png';
		}
		else
		{
			$obj->thumbnail = 'http://thumbnail.instardara.com/category/00_recently.png';
		}

		array_push($catList, $obj);
		
		// Add Top Hits
		$obj = new stdClass();
		$obj->id = 'tophits';
		$obj->title = 'Top Hits';
		$obj->description = 'Top Hits';
		if ($this->device == "s40")
		{
			$obj->thumbnail = 'http://thumbnail.instardara.com/category/s40_00_cate_tophits.png';
		}
		else
		{
			$obj->thumbnail = 'http://thumbnail.instardara.com/category/00_cate_tophits.png';
		}

		array_push($catList, $obj);
		
		
		if ($this->device == "s40")
		{
			$sql = "SELECT id, title, description, CASE thumbnail_s40 WHEN '' THEN '' ELSE CONCAT('$this->category_thumbnail_path', thumbnail_s40) END AS thumbnail ";
		}
		else
		{
			$sql = "SELECT id, title, description, CASE thumbnail WHEN '' THEN '' ELSE CONCAT('$this->category_thumbnail_path', thumbnail) END AS thumbnail ";
		}

		$sql .= " FROM tv_category WHERE online = 1 AND v3 = 1";
		
		if(!$this->isTH) {
			$sql .= " AND th_restrict = 0";
		}
		
		$sql .= " ORDER BY `order`, title";
				
		$cats = $this->db->query($sql)->result();
		foreach($cats as $cat) {
			array_push($catList, $cat);			
		}

		return $catList;
	}

	function getChannel() {
		
		if ($this->isDeviceSupport())
		{
			if ($this->device == "s40")
			{
				$sql = "SELECT id, title, description, CASE thumbnail_s40 WHEN '' THEN '' ELSE CONCAT('$this->channel_thumbnail_path', thumbnail_s40) END AS thumbnail, url_$this->device url, has_show ";
			}
			else
			{
				$sql = "SELECT id, title, description, CASE thumbnail WHEN '' THEN '' ELSE CONCAT('$this->channel_thumbnail_path', thumbnail) END AS thumbnail, url_$this->device url, has_show ";
			}
			
			$sql .= " FROM tv_channel
				WHERE online = 1 ";

			if ($this->device == "s40")
			{
				$sql .= " AND has_show = 1 ";
			}
			$sql .= " ORDER BY `order`, title";

			return $this->db->query($sql)->result();
		}
		else
		{
			$sql = "SELECT id, title, description, CASE thumbnail WHEN '' THEN '' ELSE CONCAT('$this->channel_thumbnail_path', thumbnail) END AS thumbnail, url, has_show
			FROM tv_channel
			WHERE online = 1 
			ORDER BY `order`, title";
			return $this->db->query($sql)->result();
		}
	}
	
	function getRadio() {
		if ($this->isDeviceSupport())
		{
			$sql = "SELECT id, title, description, 
			CASE thumbnail WHEN '' THEN '' ELSE CONCAT('$this->radio_thumbnail_path', thumbnail) END AS thumbnail, url_$this->device url, category  
			FROM tv_radio 
			WHERE online = 1 AND url_$this->device != '' ORDER BY `order`, category";
			return $this->db->query($sql)->result();
		}
		else
		{
			$sql = "SELECT id, title, description, 
			CASE thumbnail WHEN '' THEN '' ELSE CONCAT('$this->radio_thumbnail_path', thumbnail) END AS thumbnail, url, category 
			FROM tv_radio
			WHERE online = 1 AND url != '' 
			ORDER BY `order`, category";
			return $this->db->query($sql)->result();
		}
	}

	function getAllProgram() {
		$sql = "SELECT program_id id, 
		program_title title, 
		CASE program_thumbnail WHEN '' THEN '' ELSE CONCAT('$this->tv_thumbnail_path', program_thumbnail) END AS thumbnail, 
		program_time description, 
		rating,
		is_otv, otv_id, otv_api_name, 
		CASE otv_logo WHEN '' THEN '' ELSE CONCAT('$this->otv_logo_path', otv_logo) END AS otv_logo 
		FROM tv_program 
		WHERE online = 1 AND v3 = 1";

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
		program_time description,  last_epname, rating,
		is_otv, otv_id, otv_api_name, 
		CASE otv_logo WHEN '' THEN '' ELSE CONCAT('$this->otv_logo_path', otv_logo) END AS otv_logo   
		FROM tv_program
		WHERE online = 1 AND v3 = 1";

		if ($this->isDeviceSupport()) {
			$sql .= " AND `$this->device` = 1";
		}

		if(!$this->isTH) {
			$sql .= " AND th_restrict = 0";
		}
		$sql .= " AND `build_min` <= $this->build AND `build_max` >= $this->build";
		$sql .= " ORDER BY `update_date` DESC";
		$sql .= " LIMIT ".intval($start)." , $this->limit";

		return $this->db->query($sql)->result();
	}

	function getProgramByTopHits($start = 0) {
		$sql = "SELECT program_id id, 
		program_title title, 
		CASE program_thumbnail WHEN '' THEN '' ELSE CONCAT('$this->tv_thumbnail_path', program_thumbnail) END AS thumbnail, 
		program_time description, 
		rating,
		is_otv, otv_id, otv_api_name, 
		CASE otv_logo WHEN '' THEN '' ELSE CONCAT('$this->otv_logo_path', otv_logo) END AS otv_logo  
		FROM tv_program 
		WHERE online = 1 AND v3 = 1";

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
		rating,
		is_otv, otv_id, otv_api_name, 
		CASE otv_logo WHEN '' THEN '' ELSE CONCAT('$this->otv_logo_path', otv_logo) END AS otv_logo  
		FROM tv_program 
		WHERE online = 1 AND v3 = 1 AND category_id = $id";

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
		rating,
		is_otv, otv_id, otv_api_name, 
		CASE otv_logo WHEN '' THEN '' ELSE CONCAT('$this->otv_logo_path', otv_logo) END AS otv_logo  
		FROM tv_program 
		WHERE online = 1 AND v3 = 1 AND channel_id = $id";

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
		$limit = "LIMIT ".intval($start)." , 40";
		
		$special = 'tv_program.online = 1 AND tv_program.v3 = 1 AND';
		if(strpos($keyword, '@') === 0) {
			$special = '';
			$keyword = substr($keyword, 1);
		}
		
		if($this->isTH)
		{
			$sql = "SELECT tv_program.program_id id, tv_program.program_title title, CASE tv_program.program_thumbnail WHEN '' THEN '' ELSE CONCAT('$this->tv_thumbnail_path', tv_program.program_thumbnail) END AS thumbnail, tv_program.program_time description,
		CASE SUBSTRING(CONVERT(tv_program.program_title USING utf8), 1, 1)  WHEN SUBSTRING(CONVERT('$keyword' USING utf8), 1, 1) THEN 1 ELSE 0 END AS occur, 
		is_otv, otv_id, otv_api_name, 
		CASE otv_logo WHEN '' THEN '' ELSE CONCAT('$this->otv_logo_path', otv_logo) END AS otv_logo  
		FROM tv_program WHERE $special tv_program.program_title LIKE '%$keyword%' 
		ORDER BY occur DESC, tv_program.program_title ASC $limit";
		}
		else
		{
			$sql = "SELECT tv_program.program_id id, tv_program.program_title title, CASE tv_program.program_thumbnail WHEN '' THEN '' ELSE CONCAT('$this->tv_thumbnail_path', tv_program.program_thumbnail) END AS thumbnail, tv_program.program_time time,
		CASE SUBSTRING(CONVERT(tv_program.program_title USING utf8), 1, 1)  WHEN SUBSTRING(CONVERT('$keyword' USING utf8), 1, 1) THEN 1 ELSE 0 END AS occur,
		is_otv, otv_id, otv_api_name, 
		CASE otv_logo WHEN '' THEN '' ELSE CONCAT('$this->otv_logo_path', otv_logo) END AS otv_logo   
		FROM tv_program WHERE $special tv_program.program_title LIKE '%$keyword%' AND tv_program.th_restrict = 0
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
	
	function getEpisodeRaw($program_id, $start = 0)
	{
		$id = intval($program_id);
		$this->db->select('programlist_id id, programlist_ep ep, programlist_epname title,  programlist_youtube video_encrypt, programlist_src_type src_type, programlist_date date, programlist_count view_count, parts, programlist_password pwd');
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
		$sql = "SELECT program_id id, 
		program_title title, 
		CASE program_thumbnail WHEN '' THEN '' ELSE CONCAT('$this->tv_thumbnail_path', program_thumbnail) END AS thumbnail, 
		CASE poster WHEN '' THEN '' ELSE CONCAT('$this->poster_thumbnail_path', poster) END AS poster, 
		program_time description, program_detail detail, 
		last_epname, 
		view_count, 
		rating,
		5000 as vote_count,
		is_otv, otv_id, otv_api_name, 
		CASE otv_logo WHEN '' THEN '' ELSE CONCAT('$this->otv_logo_path', otv_logo) END AS otv_logo  
		FROM tv_program
		WHERE program_id = '$id'";
		return $this->db->query($sql)->row_array();
	}
	
	function getProgramInfoOtv($id)
	{
		$id = intval($id);
		$sql = "SELECT program_id id, 
		program_title title, 
		CASE program_thumbnail WHEN '' THEN '' ELSE CONCAT('$this->tv_thumbnail_path', program_thumbnail) END AS thumbnail, 
		CASE poster WHEN '' THEN '' ELSE CONCAT('$this->poster_thumbnail_path', poster) END AS poster, 
		program_time description, program_detail detail, 
		last_epname, 
		view_count, 
		rating,
		5000 as vote_count,
		is_otv, otv_id, otv_api_name, 
		CASE otv_logo WHEN '' THEN '' ELSE CONCAT('$this->otv_logo_path', otv_logo) END AS otv_logo  
		FROM tv_program
		WHERE otv_id = '$id'";
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
