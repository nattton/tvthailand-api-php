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
		$this->db->from('categories_v1');
		$this->db->where('online', 1);
		if(!$this->isTH) {
			$this->db->where('th_restrict', 0);
		}
		$this->db->order_by('category_order');
		return $this->db->get()->result();
	}

	function getCategoryV2()
	{
		$this->db->select('id, title, description, thumbnail');
		$this->db->from('categories');
		$this->db->where('is_online', 1);
		$this->db->order_by('order_display');
		return $this->db->get()->result();
	}

	function getChannel()
	{
		$this->db->select('id, title, description, thumbnail');
		$this->db->from('channels');
		$this->db->where('is_online', 1);
		$this->db->order_by('order_display');
		return $this->db->get()->result();
	}

	
	function getLiveChannel($device='')
	{
		$deviceSupport = array('ios', 'android', 'wp');
		if (in_array($device, $deviceSupport))
		{
			$this->db->select('id, title, description, thumbnail, url_'.$device.' url');
			$this->db->where('url_'.$device.' !=', '');
		}
		else
		{
			$this->db->select('id, title, description, thumbnail, url');
		}
		
		$this->db->from('live_channels');
		$this->db->where('is_online',1);
		$this->db->order_by('order_display');
		return $this->db->get()->result();
	}
	
	function getAds()
	{	
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

		$this->db->from('advertises');
		$this->db->where('is_active',1);
		return $this->db->get()->result();
	}
	
	function getProgramSearch($keyword = '',$start = 0)
	{
		$limit = "LIMIT ".intval($start)." , 20";
		$where = "";
		if ($this->isDeviceSupport()) {
			$where .= " AND shows.$this->device = 1";
		}
		
		$sql = "SELECT shows.id program_id, shows.title, shows.thumbnail, shows.description `time`,
		CASE SUBSTRING(CONVERT(shows.title USING utf8), 1, 1)  WHEN SUBSTRING(CONVERT('$keyword' USING utf8), 1, 1) THEN 1 ELSE 0 END AS occur 
		FROM shows WHERE shows.title LIKE '%$keyword%' AND shows.is_online = 1 $where
		ORDER BY occur DESC, shows.title ASC $limit";
		
		return $this->db->query($sql)->result();
	}
	
	function getProgram($cat_id, $start = 0)
	{
		$numLimit = 30;
		$cat_id = intval($cat_id);
		$limit = "LIMIT ".intval($start)." , $numLimit";
		
		if(0 == $cat_id)
		{
			$this->db->select('id program_id, title, thumbnail, last_epname time');
			$this->db->from('shows');
			$this->db->where('is_online', 1);
			
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
		}
		elseif(99 == $cat_id)
		{
			$where = "";
			if ($this->isDeviceSupport()) {
				$where .= " AND shows.$this->device = 1";
			}
			if(!$this->isTH)
			{
				$where .= " AND shows.th_restrict = 0";
			}

			$sql = "SELECT shows.id program_id, shows.title, shows.thumbnail, shows.description `time` FROM shows
INNER JOIN episodes ON ( shows.id = episodes.show_id ) 
WHERE episodes.banned = 0 AND shows.is_online = 1  $where GROUP BY episodes.show_id ORDER BY SUM( episodes.view_count ) DESC $limit";

			return $this->db->query($sql)->result();
		}
		elseif(100 < $cat_id )
		{
			$channel_id = $cat_id - 100;
			return $this->getProgramChannel($channel_id, $start, $numLimit);
		}
		elseif(100 == $cat_id)
		{
			$this->db->select('program_id, title, thumbnail, description `time`');
			$this->db->from('shows');
			$this->db->where('is_online', 1);

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
		}
		else
		{
			$this->db->select('id program_id, title, thumbnail, description `time`');
			$this->db->from('shows');
			$this->db->where('is_online', 1);

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
		$this->db->select('id program_id, title, thumbnail, description `time`');
		$this->db->from('shows');
		$this->db->where('is_online', 1);

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
		$this->db->select('id program_id, title, thumbnail, description `time`');
		$this->db->from('shows');
		$this->db->where('is_online', 1);

		if ($this->isDeviceSupport()) {
				$this->db->where($this->device, 1);
		}
		if(!$this->isTH)
		{
			$this->db->where('th_restrict', 0);
		}

		$this->db->order_by('id', 'desc');
		$this->db->limit($numLimit, intval($start));
		return $this->db->get()->result();
	}
	
	function getProgramlist($program_id,$start = 0)
	{
		$program_id = intval($program_id);
		$this->db->select('id programlist_id, ep, title epname, video_encrypt youtube_encrypt, src_type, `date`, view_count count, password pwd');
		$this->db->from('episodes');
		$this->db->where('banned',0);
		$this->db->where('show_id', $program_id);
		$this->db->order_by('ep', 'desc');
		$this->db->order_by('id', 'desc');
		$this->db->limit(40, intval($start));
		return $this->db->get()->result();
	}
	
	function getProgramDetail($program_id)
	{
		$program_id = intval($program_id);
		
		$sql = "SELECT shows.id program_id, shows.title, CONCAT('$this->thumbnail_path',shows.thumbnail) thumbnail, shows.detail, shows.description `time`, SUM( episodes.view_count ) count
			FROM shows
			INNER JOIN episodes ON ( shows.id = episodes.show_id ) 
			WHERE shows.id = '$program_id'
			GROUP BY episodes.show_id";
		
		return $this->db->query($sql)->row_array();
	}
	
	function getProgramInfo($program_id)
	{
		$program_id = intval($program_id);
		$query = $this->db->query("SELECT * FROM shows WHERE id = '$program_id'");
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
		
		$sql = "SELECT shows.title, shows.thumbnail, episodes.ep, episodes.title, episodes.video, episodes.date, episodes.view_count, episodes.src_type
		FROM  `episodes` 
		INNER JOIN  `shows` ON  `shows`.id =  `episodes`.show_id
		WHERE `episodes`.id =  '$programlist_id'";
		
		return $this->db->query($sql)->row_array();
	}
	
	function viewProgramlist($programlist_id)
	{
		if(!empty($programlist_id))
		{
			$sql = "UPDATE `episodes` 
			SET view_count = view_count + 1 
			WHERE id = '$programlist_id'";
			$this->db->query($sql);
		}
	}
	
	function methodProgramLastest($where ='',$limit='')
	{
		$sql = "SELECT shows.id program_id,
		shows.title,
		shows.thumbnail,
		shows.time
		FROM shows JOIN (SELECT episodes.show_id,
		MAX(episodes.id) as programlist_id,
		MAX(episodes.date) as pl_date 
		FROM episodes 
		GROUP BY show_id 
		ORDER BY MAX(date) DESC) as prlist 
		ON shows.id = prlist.show_id 
		$where
		ORDER BY prlist.show_id DESC
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