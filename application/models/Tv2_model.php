<?php

class Tv2_model extends CI_Model
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
			$sql = "SELECT create_date id, title, message FROM messages WHERE is_active = 1 AND device = '$this->device' ORDER BY create_date LIMIT 0,1";
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
			$this->db->select("name, url_$this->device url, time_$this->device 'time', `interval`");
			$this->db->where("time_$this->device >", 0);
			$this->db->where("url_$this->device !=", "");
		}
		else {
			$this->db->select("name, url, `time`, `interval`");
			$this->db->where("time >", 0);
			$this->db->where("url !=", "");
		}
		$this->db->where("v2", 1);
		$this->db->where("build_min <=", $this->build);
		$this->db->where("build_max >=", $this->build);
		$this->db->from('advertises');
		$this->db->where('is_active', 1);
		return $this->db->get()->result();
	}
	
	function getPrerollAdvertise(){
		$this->db->select("name, url, skip_time");
		$this->db->from('preroll_advertises');
		$this->db->where('is_active', 1);
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
			$obj->thumbnail = $this->category_thumbnail_pat.'s40_00_recently.png';
		}
		else
		{
			$obj->thumbnail = $this->category_thumbnail_path.'00_recently.png';
		}

		array_push($catList, $obj);
		
		// Add Top Hits
		$obj = new stdClass();
		$obj->id = 'tophits';
		$obj->title = 'Top Hits';
		$obj->description = 'Top Hits';
		if ($this->device == "s40")
		{
			$obj->thumbnail = $this->category_thumbnail_path.'s40_00_cate_tophits.png';
		}
		else
		{
			$obj->thumbnail = $this->category_thumbnail_path.'00_cate_tophits.png';
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

		$sql .= " FROM categories WHERE is_online = 1";
		
		if(!$this->isTH) {
			$sql .= " AND th_restrict = 0";
		}
		
		$sql .= " ORDER BY `order_display`, title";
				
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
			
			$sql .= " FROM channels
				WHERE is_online = 1 ";

			if ($this->device == "s40")
			{
				$sql .= " AND has_show = 1 ";
			}
			$sql .= " ORDER BY `order_display`, title";

			return $this->db->query($sql)->result();
		}
		else
		{
			$sql = "SELECT id, title, description, CASE thumbnail WHEN '' THEN '' ELSE CONCAT('$this->channel_thumbnail_path', thumbnail) END AS thumbnail, url, has_show
			FROM channels
			WHERE is_online = 1 
			ORDER BY `order_display`, title";
			return $this->db->query($sql)->result();
		}
	}
	
	function getRadio() {
		if ($this->isDeviceSupport())
		{
			$sql = "SELECT id, title, description, 
			CASE thumbnail WHEN '' THEN '' ELSE CONCAT('$this->radio_thumbnail_path', thumbnail) END AS thumbnail, url_$this->device url, category  
			FROM radios 
			WHERE is_online = 1 AND url_$this->device != '' ORDER BY `order_display`, category";
			return $this->db->query($sql)->result();
		}
		else
		{
			$sql = "SELECT id, title, description, 
			CASE thumbnail WHEN '' THEN '' ELSE CONCAT('$this->radio_thumbnail_path', thumbnail) END AS thumbnail, url, category 
			FROM radios
			WHERE is_online = 1 AND url != '' 
			ORDER BY `order_display`, category";
			return $this->db->query($sql)->result();
		}
	}

	function getAllProgram() {
		$sql = "SELECT id, 
		title, 
		CASE thumbnail WHEN '' THEN '' ELSE CONCAT('$this->tv_thumbnail_path', thumbnail) END AS thumbnail, 
		description, 
		is_otv, otv_id, otv_api_name,
		CASE otv_logo WHEN '' THEN '' ELSE CONCAT('$this->otv_logo_path', otv_logo) END AS otv_logo   
		FROM shows 
		WHERE is_online = 1";

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
		$sql = "SELECT id, 
		title, 
		CASE thumbnail WHEN '' THEN '' ELSE CONCAT('$this->tv_thumbnail_path', thumbnail) END AS thumbnail,  
		description,  last_epname, rating,
		is_otv, otv_id, otv_api_name,
		CASE otv_logo WHEN '' THEN '' ELSE CONCAT('$this->otv_logo_path', otv_logo) END AS otv_logo   
		FROM shows
		WHERE is_online = 1";

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
		$sql = "SELECT id, 
		title, 
		CASE thumbnail WHEN '' THEN '' ELSE CONCAT('$this->tv_thumbnail_path', thumbnail) END AS thumbnail, 
		description, 
		rating,
		is_otv, otv_id, otv_api_name,
		CASE otv_logo WHEN '' THEN '' ELSE CONCAT('$this->otv_logo_path', otv_logo) END AS otv_logo  
		FROM shows 
		WHERE is_online = 1";

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

		$sql = "SELECT id, 
		title, 
		CASE thumbnail WHEN '' THEN '' ELSE CONCAT('$this->tv_thumbnail_path', thumbnail) END AS thumbnail, 
		description, 
		rating,
		is_otv, otv_id, otv_api_name,
		CASE otv_logo WHEN '' THEN '' ELSE CONCAT('$this->otv_logo_path', otv_logo) END AS otv_logo  
		FROM shows 
		WHERE is_online = 1 AND category_id = $id";

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

		$sql = "SELECT id, 
		title, 
		CASE thumbnail WHEN '' THEN '' ELSE CONCAT('$this->tv_thumbnail_path', thumbnail) END AS thumbnail, 
		description, 
		rating,
		is_otv, otv_id, otv_api_name,
		CASE otv_logo WHEN '' THEN '' ELSE CONCAT('$this->otv_logo_path', otv_logo) END AS otv_logo  
		FROM shows 
		WHERE is_online = 1 AND channel_id = $id";

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
		
		$special = 'shows.is_online = 1 AND';
		if(strpos($keyword, '@') === 0) {
			$special = '';
			$keyword = substr($keyword, 1);
		}
		
		if($this->isTH)
		{
			$sql = "SELECT shows.id, shows.title, CASE shows.thumbnail WHEN '' THEN '' ELSE CONCAT('$this->tv_thumbnail_path', shows.thumbnail) END AS thumbnail, shows.description,
		CASE SUBSTRING(CONVERT(shows.title USING utf8), 1, 1)  WHEN SUBSTRING(CONVERT('$keyword' USING utf8), 1, 1) THEN 1 ELSE 0 END AS occur, 
		is_otv, otv_id, otv_api_name, CASE otv_logo WHEN '' THEN '' ELSE CONCAT('$this->otv_logo_path', otv_logo) END AS otv_logo  
		FROM shows WHERE $special shows.title LIKE '%$keyword%' 
		ORDER BY occur DESC, shows.title ASC $limit";
		}
		else
		{
			$sql = "SELECT shows.id, shows.title, CASE shows.thumbnail WHEN '' THEN '' ELSE CONCAT('$this->tv_thumbnail_path', shows.thumbnail) END AS thumbnail, shows.description,
		CASE SUBSTRING(CONVERT(shows.title USING utf8), 1, 1)  WHEN SUBSTRING(CONVERT('$keyword' USING utf8), 1, 1) THEN 1 ELSE 0 END AS occur, 
		is_otv, otv_id, otv_api_name, CASE otv_logo WHEN '' THEN '' ELSE CONCAT('$this->otv_logo_path', otv_logo) END AS otv_logo  
		FROM shows WHERE $special shows.title LIKE '%$keyword%'  AND shows.th_restrict = 0 
		ORDER BY occur DESC, shows.title ASC $limit";
		}
		
		return $this->db->query($sql)->result();
	}
	
	function getEpisode($showId, $start = 0)
	{
		$id = intval($showId);
		$this->db->select('id, ep, title, video_encrypt, src_type, date, view_count, parts, password pwd');
		$this->db->from('episodes');
		$this->db->where('banned',0);
		$this->db->where('show_id', $id);
		if($this->device == 's40') {
			$this->db->where('src_type', 0);
		}
		$this->db->order_by('ep', 'desc');
		$this->db->order_by('id', 'desc');
		$this->db->limit($this->limit, intval($start));
		return $this->db->get()->result();
	}
	
	function getEpisodeRaw($showId, $start = 0)
	{
		$id = intval($showId);
		$this->db->select('id, ep, title, video_encrypt, src_type, date, view_count, parts, password pwd');
		$this->db->from('episodes');
		$this->db->where('banned',0);
		$this->db->where('show_id', $id);
		if($this->device == 's40') {
			$this->db->where('src_type', 0);
		}
		$this->db->order_by('ep', 'desc');
		$this->db->order_by('id', 'desc');
		$this->db->limit($this->limit, intval($start));
		return $this->db->get()->result();
	}
	
	function getProgramInfo($id)
	{
		$id = intval($id);
		$sql = "SELECT id, 
		title, 
		CASE thumbnail WHEN '' THEN '' ELSE CONCAT('$this->tv_thumbnail_path', thumbnail) END AS thumbnail, 
		CASE poster WHEN '' THEN '' ELSE CONCAT('$this->poster_thumbnail_path', poster) END AS poster, 
		description, detail, 
		last_epname, 
		view_count, 
		rating,
		5000 as vote_count,
		is_otv, otv_id, otv_api_name,
		CASE otv_logo WHEN '' THEN '' ELSE CONCAT('$this->otv_logo_path', otv_logo) END AS otv_logo  
		FROM shows
		WHERE id = '$id'";
		return $this->db->query($sql)->row_array();
	}
	
	function getProgramInfoOtv($id)
	{
		$id = intval($id);
		$sql = "SELECT id, 
		title, 
		CASE thumbnail WHEN '' THEN '' ELSE CONCAT('$this->tv_thumbnail_path', thumbnail) END AS thumbnail, 
		CASE poster WHEN '' THEN '' ELSE CONCAT('$this->poster_thumbnail_path', poster) END AS poster, 
		description, detail, 
		last_epname, 
		view_count, 
		rating,
		5000 as vote_count,
		is_otv, otv_id, otv_api_name,
		CASE otv_logo WHEN '' THEN '' ELSE CONCAT('$this->otv_logo_path', otv_logo) END AS otv_logo  
		FROM shows
		WHERE otv_id = '$id'";
		return $this->db->query($sql)->row_array();
	}
	
	function viewEP($id)
	{
		if(!empty($id))
		{
			$sql = "UPDATE `episodes` 
			SET view_count = view_count + 1 
			WHERE id = '$id'";
			$this->db->query($sql);
		}
	}

	function encryptData()
	{
		$this->db->select('id, video');
		$this->db->from('episodes');
		$result = $this->db->get()->result();
		$enFrom = array('+','/','=','a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s');
		$enTo = array('-','_',',','!','@','#','$','%','^','&','*','(',')','{','}','[',']',':',';','<','>','?');
		foreach ($result as $row) {
			$encrypt = base64_encode($row->video);
			$encrypt = str_replace($enFrom, $enTo, $encrypt);
			$data = array('video_encrypt' => $encrypt);
			$this->db->where('id', $row->id);
			$this->db->update('episodes', $data);
		}
		return 'Encrypt Data Successfully';
	}
}
?>
