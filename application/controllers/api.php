<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Api extends CI_Controller {
	private $namespace_prefix = 'API';
	private $cache_time = 21600;
	private $country_code = '';
	private $ex_cache = '';
	private $isTH = TRUE;
	private $device = '';

	function __construct()
	{
		parent::__construct();

 		$this->load->library('MemcacheSASL','','memcached');
 		$this->memcached->addServer('tvthailand.gntesa.cfg.use1.cache.amazonaws.com', '11211');

		// Set Device

		($this->device = $this->input->get('device')) or ($this->device = '');

		// Location

		if (array_key_exists('GEOIP_COUNTRY_CODE', $_SERVER)) {
			$this->country_code = $_SERVER['GEOIP_COUNTRY_CODE'];
			if($this->country_code == 'US') {
				$this->country_cache = ':US';
				$this->isTH = FALSE;
			}
		}
	}

	public function index()
	{
		$this->load->view('welcome_message');
	}


	private function getCategoryKey($id) {
		return "API:CATEGORY:$id";
	}

	private function getProgramKey($id) {
		return "API:PROGRAM:$id";
	}

	private function storeKey($cache_key, $value) {
		$memData = $this->memcached->get($cache_key);

		if(FALSE != $memData) {
			$key_array = json_decode($memData);

			if (!in_array($value, $key_array)) {
				array_push($key_array, $value);
				$memData = json_encode($key_array);
				$this->memcached->set($cache_key, $memData, $this->cache_time);
			}
			
		}
		 else {
		 	$key_array = array($value,);
		 	$memData = json_encode($key_array);
		 	$this->memcached->add($cache_key, $memData, $this->cache_time);
		 }
		 // echo $memData;
	}

	public function showKey($cache_key) {
		$memData = $this->memcached->get($cache_key);
		echo $memData;

		if(FALSE != $memData) {
			return json_decode($memData);
		}
		else {
			return array();
		}
	}

	public function showCategoryKey($id) {
		$key =  $this->getCategoryKey($id);
		echo $key;
		echo "<br/>";
		$memData = $this->memcached->get($key);

		echo $memData;
		if(FALSE != $memData) {
			return json_decode($memData);
		}
		else {
			return array();
		}
	}

	public function showProgramKey($id) {
		$key =  $this->getProgramKey($id);
		$memData = $this->memcached->get($key);

		echo $memData;
		if(FALSE != $memData) {
			return json_decode($memData);
		}
		else {
			return array();
		}
	}
	
	public function getMessageiOS()
	{
		$cache_key = $this->namespace_prefix.'getMessageiOS';
		$memData = $this->memcached->get($cache_key);
		if(FALSE != $memData)
		{
			$this->output->set_content_type('application/json')->set_output($memData);
		}
		else
		{	
			$data['json']->id = '201302200200';
			$data['json']->title = '*Notice*';
			$data['json']->message = 'Welcome to TV Thailand for iOS';
			
			$this->load->model('Tv_model','', FALSE);
			$buttons = array();
/* 			array_push($buttons, $this->Tv_model->createButton('Read Review','http://goo.gl/Vdoba')); */
			array_push($buttons, $this->Tv_model->createButton('Rating & Review','https://itunes.apple.com/us/app/tv-thailand/id458429827?ls=1&mt=8'));
			array_push($buttons, $this->Tv_model->createButton('Fan Page','https://www.facebook.com/TV.Thailand'));
			
			$data['json']->buttons = $buttons;
			$json = $this->load->view('json', $data, TRUE);
			$this->memcached->add($cache_key, $json, $this->cache_time);
			$this->output->set_content_type('application/json')->set_output($json);
		}
	}
	
	public function getMessageAndroid()
	{
		$cache_key = $this->namespace_prefix.'getMessageAndroid';
		$memData = $this->memcached->get($cache_key);
		if(FALSE != $memData)
		{
			$this->output->set_content_type('application/json')->set_output($memData);
		}
		else
		{
			$data['json']->id = '201302800200';
			$data['json']->title = '* Notice *';
			$data['json']->message = 'Welcome to TV Thailand for Android';
			$obj = new stdClass();
			$obj->versionCode = 62;
			$obj->apk = 'http://goo.gl/8tfx1';
			$data['json']->app_version = $obj;
			
			$this->load->model('Tv_model','', FALSE);
			$buttons = array();
 			array_push($buttons, $this->Tv_model->createButton('Rating & Review','http://goo.gl/1BJYa')); 
/* 			array_push($buttons, $this->Tv_model->createButton('Read','http://goo.gl/NeDgB')); */
			array_push($buttons, $this->Tv_model->createButton('Fan Page','https://www.facebook.com/TV.Thailand'));
			
			$data['json']->buttons = $buttons;

			$json = $this->load->view('json', $data, TRUE);
			$this->memcached->add($cache_key, $json, $this->cache_time);
			$this->output->set_content_type('application/json')->set_output($json);
		}
	}
	
	public function getMessageWP()
	{
		$cache_key = $this->namespace_prefix.'getMessageWP';
		$memData = $this->memcached->get($cache_key);
		if(FALSE != $memData)
		{
			$this->output->set_content_type('application/json')->set_output($memData);
		}
		else
		{
			$data['json']->id = '201302200200';
			$data['json']->title = '* Notice *';
			$data['json']->message = 'Welcome to TV Thailand for Windows Phone';;
			
			$this->load->model('Tv_model','', FALSE);
			$buttons = array();
/*  			array_push($buttons, $this->Tv_model->createButton('Rating & Review','http://goo.gl/1BJYa'));  */
/* 			array_push($buttons, $this->Tv_model->createButton('Read','http://goo.gl/NeDgB')); */
			array_push($buttons, $this->Tv_model->createButton('Fan Page','https://www.facebook.com/TV.Thailand'));
			
			$data['json']->buttons = $buttons;

			$json = $this->load->view('json', $data, TRUE);
			$this->memcached->add($cache_key, $json, $this->cache_time);
			$this->output->set_content_type('application/json')->set_output($json);
		}
	}
	
	public function getInHouseAd()
	{
		($device = $this->input->get('device')) or ($device = '');

		$cache_key = "$this->namespace_prefix:getInHouseAd:$this->device";

		$memData = $this->memcached->get($cache_key);
		if(FALSE != $memData)
		{
			$this->output->set_content_type('application/json')->set_output($memData);
		}
		else
		{
			$this->load->model('Tv_model','', TRUE);
			$this->Tv_model->setDevice($this->device);
			$data['json']->delayStart = 5000;
			$data['json']->ads = $this->Tv_model->getAds();

			$pageAds = new stdClass();
			if($this->isTH) {
				$pageAds->program = "admob";
				$pageAds->programlist = "admob";
				$pageAds->ep = "admob";
				$pageAds->player = "admob";
			}
			else {
				$pageAds->program = "inmobi";
				$pageAds->programlist = "inmobi";
				$pageAds->ep = "inmobi";
				$pageAds->player = "inmobi";
			}
			
			$data['json']->pageAds = $pageAds;

			$json = $this->load->view('json', $data, TRUE);
			$this->memcached->add($cache_key, $json, $this->cache_time);
			$this->output->set_content_type('application/json')->set_output($json);
		}
	}

	public function getCategory()
	{	
		// $cache_key = "$this->namespace_prefix:getCategory";
		$cache_key = sprintf("%s:%s", $this->namespace_prefix, "getCategory");
		$memData = $this->memcached->get($cache_key);
		if(FALSE != $memData)
		{
			$this->output->set_content_type('application/json')->set_output($memData);
		}
		else
		{
			$this->load->model('Tv_model','', TRUE);
			$data['json']->ads = $this->Tv_model->getAds();
			$data['json']->categories = $this->Tv_model->getCategory();
			$json = $this->load->view('json', $data, TRUE);
			$this->memcached->add($cache_key, $json, $this->cache_time);
			$this->output->set_content_type('application/json')->set_output($json);
		}

		/* $this->load->view('json',$data,TRUE) */;
	}

	public function getCategoryLao()
	{	
		// $cache_key = "$this->namespace_prefix:getCategoryLao";
		$cache_key = sprintf("%s:%s", $this->namespace_prefix, "getCategoryLao");
		$memData = $this->memcached->get($cache_key);
		if(FALSE != $memData)
		{
			$this->output->set_content_type('application/json')->set_output($memData);
		}
		else
		{
			$this->load->model('Tv_model','', TRUE);
			$data['json']->ads = $this->Tv_model->getAds();
			$data['json']->categories = $this->Tv_model->getCategoryLao();
			$json = $this->load->view('json', $data, TRUE);
			$this->memcached->add($cache_key, $json, $this->cache_time);
			$this->output->set_content_type('application/json')->set_output($json);
		}

		/* $this->load->view('json',$data,TRUE) */;
	}

	public function getCategoryV2()
	{	
		$cache_key = $this->namespace_prefix.'getCategoryV2';
		$memData = $this->memcached->get($cache_key);
		if(FALSE != $memData)
		{
			$this->output->set_content_type('application/json')->set_output($memData);
		}
		else
		{
			$this->load->model('Tv_model','', TRUE);
			$data['json']->image_path = $this->Tv_model->thumbnail_path;
			$data['json']->categories = $this->Tv_model->getCategoryV2();
			$data['json']->channels = $this->Tv_model->getChannel();
			$json = $this->load->view('json', $data, TRUE);
			$this->memcached->add($cache_key, $json, $this->cache_time);
			$this->output->set_content_type('application/json')->set_output($json);
		}

		// $this->load->view('json',$data,TRUE)
	}

	public function getChannel()
	{	
		$cache_key = $this->namespace_prefix.'getChannel';
		$memData = $this->memcached->get($cache_key);
		if(FALSE != $memData)
		{
			$this->output->set_content_type('application/json')->set_output($memData);
		}
		else
		{
			$this->load->model('Tv_model','', TRUE);
			$data['json']->image_path = $this->Tv_model->thumbnail_path;
			$data['json']->channels = $this->Tv_model->getChannel();
			$json = $this->load->view('json', $data, TRUE);
			$this->memcached->add($cache_key, $json, $this->cache_time);
			$this->output->set_content_type('application/json')->set_output($json);
		}

		/* $this->load->view('json',$data,TRUE) */;
	}

	public function getLiveChannel()
	{	
		($device = $this->input->get('device')) or ($device = '');
		$cache_key = $this->namespace_prefix.'getLiveChannel_'.$device;
		$memData = $this->memcached->get($cache_key);
		if(FALSE != $memData)
		{
			$this->output->set_content_type('application/json')->set_output($memData);
		}
		else
		{
			$this->load->model('Tv_model','', TRUE);
			$data['json']->image_path = $this->Tv_model->thumbnail_path;
			$data['json']->channels = $this->Tv_model->getLiveChannel($device);
			$json = $this->load->view('json', $data, TRUE);
			$this->memcached->add($cache_key, $json, $this->cache_time);
			$this->output->set_content_type('application/json')->set_output($json);
		}

		/* $this->load->view('json',$data,TRUE) */;
	}
	
	public function getProgram($cat_id = 0, $start = 0)
	{
		$cache_key = sprintf("%s:%s:%s:%s:%s:%s", $this->namespace_prefix, "getProgram", $cat_id, $start, $this->device, $this->ex_cache);
		$memData = $this->memcached->get($cache_key);
		if(FALSE != $memData)
		{
			$this->output->set_content_type('application/json')->set_output($memData);
		}
		else
		{
			$this->load->model('Tv_model','', TRUE);
			$this->Tv_model->setDevice($this->device);
			$this->Tv_model->setIsTH($this->isTH);

			$data['json']->thumbnail_path = $this->Tv_model->thumbnail_path;
			$data['json']->programs = $this->Tv_model->getProgram($cat_id, $start);
			$json = $this->load->view('json',$data,TRUE);

			$this->memcached->add($cache_key, $json, $this->cache_time);

			$this->storeKey($this->getCategoryKey($cat_id), $cache_key);

			$this->output->set_content_type('application/json')->set_output($json);
		}
		
/* 		$this->load->view('json',$data); */
	}

		public function getProgramLao($cat_id = 0, $start = 0)
	{
		$cache_key = sprintf("%s:%s:%s:%s:%s:%s", $this->namespace_prefix, "getProgramLao", $cat_id, $start, $this->device, $this->ex_cache);
		$memData = $this->memcached->get($cache_key);
		if(FALSE != $memData)
		{
			$this->output->set_content_type('application/json')->set_output($memData);
		}
		else
		{
			$this->load->model('Tv_model','', TRUE);
			$this->Tv_model->setDevice($this->device);
			$this->Tv_model->setIsTH($this->isTH);

			$data['json']->thumbnail_path = $this->Tv_model->thumbnail_path;
			$data['json']->programs = $this->Tv_model->getProgramLao($cat_id, $start);
			$json = $this->load->view('json',$data,TRUE);

			$this->memcached->add($cache_key, $json, $this->cache_time);

			$this->storeKey($this->getCategoryKey($cat_id), $cache_key);

			$this->output->set_content_type('application/json')->set_output($json);
		}
		
/* 		$this->load->view('json',$data); */
	}
	
	public function getWhatsNew($start = 0)
	{
		// $cache_key = "$this->namespace_prefix:getWhatsNew:$start:$this->device:$this->ex_cache";
		$cache_key = sprintf("%s:%s:%s:%s:%s", $this->namespace_prefix, "getWhatsNew", $start, $this->device, $this->ex_cache);

		$memData = $this->memcached->get($cache_key);
		if(FALSE != $memData)
		{
			$this->output->set_content_type('application/json')->set_output($memData);
		}
		else
		{
			$this->load->model('Tv_model','', TRUE);
			$this->Tv_model->setDevice($this->device);
			$this->Tv_model->setIsTH($this->isTH);

			$data['json']->thumbnail_path = $this->Tv_model->thumbnail_path;
			$data['json']->programs = $this->Tv_model->getProgram(0, $start);
			$json = $this->load->view('json',$data,TRUE);
			$this->memcached->add($cache_key, $json, $this->cache_time);

			$this->storeKey($this->getCategoryKey(0), $cache_key);

			$this->output->set_content_type('application/json')->set_output($json);
		}
		
/* 		$this->load->view('json',$data); */
	}

	public function getNewRelease($start = 0)
	{
		$cache_key = $this->namespace_prefix.'getNewRelease_'.$start.$this->ex_cache;;
		$memData = $this->memcached->get($cache_key);
		if(FALSE != $memData)
		{
			$this->output->set_content_type('application/json')->set_output($memData);
		}
		else
		{
			$this->load->model('Tv_model','', TRUE);
			$data['json']->thumbnail_path = $this->Tv_model->thumbnail_path;
			$this->Tv_model->setDevice($this->device);
			$this->Tv_model->setIsTH($this->isTH);

			$data['json']->programs = $this->Tv_model->getProgramNewRelease($start, 30);
			$json = $this->load->view('json',$data,TRUE);
			$this->memcached->add($cache_key, $json, $this->cache_time);
			$this->output->set_content_type('application/json')->set_output($json);
		}
		
/* 		$this->load->view('json',$data); */
	}
	
	public function getProgramSearch($start = 0)
	{
		$keyword = $this->input->get('keyword');

		$cache_key = "$this->namespace_prefix:getProgramSearch:$keyword:$start:$this->device:$this->ex_cache";

		$memData = $this->memcached->get($cache_key);
		if(FALSE != $memData)
		{
			$this->output->set_content_type('application/json')->set_output($memData);
		}
		else
		{
			$this->load->model('Tv_model','', TRUE);
			$this->Tv_model->setDevice($this->device);
			$this->Tv_model->setIsTH($this->isTH);
			
			$data['json']->thumbnail_path = $this->Tv_model->thumbnail_path;
			$data['json']->programs = $this->Tv_model->getProgramSearch($keyword, $start);
			$json = $this->load->view('json',$data,TRUE);
			$this->memcached->add($cache_key, $json, $this->cache_time);
			$this->output->set_content_type('application/json')->set_output($json);
		}
	}

	public function getProgramlist($program_id, $start = 0)
	{
		// $cache_key = "$this->namespace_prefix:getProgramlist:$program_id:$start";
		$cache_key = sprintf("%s:%s:%s:%s", $this->namespace_prefix, "getProgramlist", $program_id, $start);

		$memData = $this->memcached->get($cache_key);
		if(FALSE != $memData)
		{
			$this->output->set_content_type('application/json')->set_output($memData);
		}
		else
		{
			$this->load->model('Tv_model','', TRUE);
			$data['json']->programlists = $this->Tv_model->getProgramlist($program_id,$start);
			$json = $this->load->view('json', $data, TRUE);
			$this->memcached->add($cache_key, $json, $this->cache_time);

			$this->storeKey($this->getProgramKey($program_id), $cache_key);

			$this->output->set_content_type('application/json')->set_output($json);
		}
		
/* 		$this->load->view('json',$data); */
	}
	
	public function getProgramlistDetail($programlist_id)
	{
		$this->load->model('Tv_model','', TRUE);
		$data['json']->programlist = $this->Tv_model->getProgramlistDetail($programlist_id);
		$this->load->view('json',$data);
	}
	
	public function getProgramDetail($program_id)
	{
		$cache_key = "$this->namespace_prefix:getProgramDetail:$$program_id";

		$memData = $this->memcached->get($cache_key);
		if(FALSE != $memData)
		{
			$this->output->set_content_type('application/json')->set_output($memData);
		}
		else
		{
			$this->load->model('Tv_model','', TRUE);
			$data['json'] = $this->Tv_model->getProgramDetail($program_id);
			$json = $this->load->view('json', $data, TRUE);
			$this->memcached->add($cache_key, $json, $this->cache_time);
			$this->output->set_content_type('application/json')->set_output($json);
		}
	}
	

	public function viewProgramlist($programlist_id)
	{
		$this->load->model('Tv_model','', TRUE);
		$this->Tv_model->viewProgramlist($programlist_id);
		
		## TEST ##

		$this->load->helper('url');
		$url = 'http://27.131.144.6:8088/tv/index.html';
		redirect($url, 'location', 301);

	}
	
	public function iosToken($deviceid,$devicetoken)
	{
		// $this->load->model('Tv_model','', TRUE);
		// $this->Tv_model->iosToken($deviceid,$devicetoken);
  		
		## TEST ##
/*
		$this->load->helper('url');
		$url = 'http://www.thetvthailand.com/api/register_ios_token/$deviceid/$devicetoken/';
		redirect($url, 'location', 301);
*/
		
	}
	
	public function apns()
	{
		$getdata = $this->input->get();
		
	}
	
	############### private function ##############

	public function clearCache($program_id = -1)
	{
		if($program_id == -1)
		{
			$this->memcached->flush();
			echo 'Flush MemCached';
		}
		else {

			// Get Key of Category 0 (Lastest)
			$categoryKeyArray = $this->showCategoryKey(0);
			
			// Delete Store Key of Category 0
			$this->memcached->delete($this->getCategoryKey(0));

			// Delete Key of Category 0
			foreach ($categoryKeyArray as $value) {
				$this->memcached->delete($value);
			}

			// Get Key of Program $program
			$programKeyArray = $this->showProgramKey($program_id);

			// Delete Store Key of Program $program
			$this->memcached->delete($this->getProgramKey($program_id));

			// Delete Key of Program $program
			foreach ($programKeyArray as $value) {
				$this->memcached->delete($value);
			}

			// $this->load->model('Tv_model','', TRUE);
			// $program = $this->Tv_model->getProgramInfo($program_id);
			// if($program != FALSE)
			// {

			// }

			// $this->load->helper('url');
			// redirect("/api2/clearCache/$program_id", 'refresh');
		}
		
	}
	
	public function encryptData()
	{
		$this->load->model('Tv_model','', TRUE);
		$this->Tv_model->encryptData();
	}
}