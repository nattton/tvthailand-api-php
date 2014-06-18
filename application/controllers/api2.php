<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Api2 extends CI_Controller {
	private $namespace_prefix = 'API2';
	private $cache_time = 21600;
	private $country_code = '';
	private $country_cache = '';
	private $isTH = FALSE;
	private $device = '';
	private $lr = "0";

	function __construct()
	{
		parent::__construct();

 		$this->load->library('MemcacheSASL','','memcached');
		if(ENVIRONMENT == 'production') {
	 		$this->memcached->addServer('tvthailand.gntesa.cfg.use1.cache.amazonaws.com', '11211');			
		} else {
			$this->country_cache = 'TH';
			$this->isTH = TRUE;
		}
		
		// Set Device

		($this->device = $this->input->get('device')) or ($this->device = '');
		
		
		if($this->input->get('lr') == 1) {
			$this->lr = "1";
		}

		// Location

		if (array_key_exists('GEOIP_COUNTRY_CODE', $_SERVER)) {
			$this->country_code = $_SERVER['GEOIP_COUNTRY_CODE'];
			if($this->country_code == 'TH') {
				$this->country_cache = 'TH';
				$this->isTH = TRUE;
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

	private function getChannelKey($id) {
		return "API:CHANNEL:$id";
	}
	
	private function getRadioKey($id) {
		return "API:RADIO:$id";
	}
	
	private function getWhatsNewKey() {
		return "API:WHATSNEW";
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

	public function showWhatsNewKey($id) {
		$key =  $this->getWhatsNewKey($id);
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
	
	public function message() {
		$cache_key = "$this->namespace_prefix:message:$this->device";
		$memData = $this->memcached->get($cache_key);
		if(FALSE != $memData)
		{
			$this->output->set_content_type('application/json')->set_output($memData);
		}
		else
		{
			$this->load->model('Tv2_model','', TRUE);
			$this->Tv2_model->setDevice($this->device);
			$result = $this->Tv2_model->getMessage();
			
			$data['json']->id = $result->id;
			$data['json']->title = $result->title;
			$data['json']->message = $result->message;
	
			$buttons = array();
			if($this->device == 'android') {
				array_push($buttons, $this->Tv2_model->createButton('Rating & Review','http://goo.gl/1BJYa'));
				array_push($buttons, $this->Tv2_model->createButton('Fan Page','https://www.facebook.com/TV.Thailand'));
			}
			elseif($this->device == 'ios') {
				array_push($buttons, $this->Tv2_model->createButton('Rating & Review','https://itunes.apple.com/us/app/tv-thailand/id458429827?ls=1&mt=8'));
				array_push($buttons, $this->Tv2_model->createButton('Fan Page','https://www.facebook.com/TV.Thailand'));
			}
			elseif($this->device == 'wp') {
				array_push($buttons, $this->Tv2_model->createButton('Fan Page','https://www.facebook.com/TV.Thailand'));		
			}
			
			$data['json']->buttons = $buttons;
			$json = $this->load->view('json', $data, TRUE);
			$this->memcached->add($cache_key, $json, $this->cache_time);
			$this->output->set_content_type('application/json')->set_output($json);
		}
		
	}
	
	public function advertise()
	{
		$cache_key = "$this->namespace_prefix:advertise:$this->device";
		$memData = $this->memcached->get($cache_key);
		if(FALSE != $memData)
		{
			$this->output->set_content_type('application/json')->set_output($memData);
		}
		else
		{
			$this->load->model('Tv2_model','', TRUE);
			$this->Tv2_model->setDevice($this->device);
			$data['json']->delay_start = 1000;
			$data['json']->ads = $this->Tv2_model->getAdvertise();

			$json = $this->load->view('json', $data, TRUE);
			$this->memcached->add($cache_key, $json, $this->cache_time);
			$this->output->set_content_type('application/json')->set_output($json);
		}
	}

	public function section() {
		$cache_key = sprintf("%s:%s:%s:%s:%s", $this->namespace_prefix, "section", $this->device, $this->country_cache, $this->lr);
		$memData = $this->memcached->get($cache_key);
		if(FALSE != $memData)
		{
			$this->output->set_content_type('application/json')->set_output($memData);
		}
		else
		{
			$this->load->model('Tv2_model','', TRUE);
			$this->Tv2_model->setDevice($this->device);
			$this->Tv2_model->setIsTH($this->isTH);
			$this->Tv2_model->setLegalRights($this->lr);

			$data['json']->categories = $this->Tv2_model->getCategory();
			$data['json']->channels = $this->Tv2_model->getChannel();
			$data['json']->radios = $this->Tv2_model->getRadio();

			$memData = $this->load->view('json', $data, TRUE);

			$this->memcached->add($cache_key, $memData, $this->cache_time);
			$this->output->set_content_type('application/json')->set_output($memData);
		}
	}

	public function category($id = -1, $start = 0)
	{
		$cache_key = sprintf("%s:%s:%s:%s:%s:%s:%s", $this->namespace_prefix, "category", $id, $start, $this->device, $this->country_cache, $this->lr);
		$memData = $this->memcached->get($cache_key);
		if(FALSE != $memData)
		{
			$this->output->set_content_type('application/json')->set_output($memData);
		}
		else
		{
			if (-1 == $id) {
				$memData = $this->_getCategory();
			}
			else if ('tophits' == $id) {
				$memData = $this->_getProgramTopHits($start);
			}
			else if ('recents' == $id) {
				$memData = $this->_getProgramRecents($start);
				$this->storeKey($this->getWhatsNewKey(), $cache_key);
			}
			else {
				$memData = $this->_getProgramByCategory($id, $start);
			}
			$this->memcached->add($cache_key, $memData, $this->cache_time);
			$this->output->set_content_type('application/json')->set_output($memData);
		}
	}

	public function channel($id = -1, $start = 0)
	{	
		$cache_key = sprintf("%s:%s:%s:%s:%s:%s:%s", $this->namespace_prefix, "channel", $id, $start, $this->device, $this->country_cache, $this->lr);
		$memData = $this->memcached->get($cache_key);
		if(FALSE != $memData)
		{
			$this->output->set_content_type('application/json')->set_output($memData);
		}
		else
		{
			if (-1 == $id) {
				$memData = $this->_getChannel();
			}
			else {
				$memData = $this->_getProgramByChannel($id, $start);
			}
			$this->memcached->add($cache_key, $memData, $this->cache_time);
			$this->output->set_content_type('application/json')->set_output($memData);
		}
	}
	
	public function radio($id = -1, $start = 0)
	{	
		$cache_key = sprintf("%s:%s:%s:%s:%s:%s:%s", $this->namespace_prefix, "radio", $id, $start, $this->device, $this->country_cache, $this->lr);
		$memData = $this->memcached->get($cache_key);
		if(FALSE != $memData)
		{
			$this->output->set_content_type('application/json')->set_output($memData);
		}
		else
		{
			$memData = $this->_getRadio();
			$this->memcached->add($cache_key, $memData, $this->cache_time);
			$this->output->set_content_type('application/json')->set_output($memData);
		}
	}
	
	public function search($start = 0)
	{	
		$keyword = $this->input->get('keyword');
		$cache_key = sprintf("%s:%s:%s:%s:%s:%s:%s", $this->namespace_prefix, "search", $keyword, $start, $this->device, $this->country_cache, $this->lr);
		$memData = $this->memcached->get($cache_key);
		if(FALSE != $memData) {
			$this->output->set_content_type('application/json')->set_output($memData);
		}
		else {
			$memData = $this->_getProgramBySearch($keyword, $start);
			$this->memcached->add($cache_key, $memData, $this->cache_time);
			$this->output->set_content_type('application/json')->set_output($memData);
		}
	}

	public function all_program()
	{
		$cache_key = "$this->namespace_prefix:all_program:$this->device:$this->country_cache";
		$cache_key = sprintf("%s:%s:%s:%s:%s", $this->namespace_prefix, "all_program", $this->device, $this->country_cache, $this->lr);
		$memData = $this->memcached->get($cache_key);
		if(FALSE != $memData)
		{
			$this->output->set_content_type('application/json')->set_output($memData);
		}
		else
		{
			$this->load->model('Tv2_model','', TRUE);
			$this->Tv2_model->setDevice($this->device);
			$this->Tv2_model->setIsTH($this->isTH);
			$this->Tv2_model->setLegalRights($this->lr);

			$data['json']->programs = $this->Tv2_model->getAllProgram();

			$memData = $this->load->view('json', $data, TRUE);

			$this->memcached->add($cache_key, $memData, $this->cache_time);

/* 			$this->storeKey($this->getWhatsNewKey(), $cache_key); */

			$this->output->set_content_type('application/json')->set_output($memData);
		}
	}

	public function whatsnew($start = 0)
	{
		$cache_key = sprintf("%s:%s:%s:%s:%s:%s:%s", $this->namespace_prefix, "whatsnew", $start, $this->device, $this->country_cache, $this->lr);
		$memData = $this->memcached->get($cache_key);
		if(FALSE != $memData)
		{
			$this->output->set_content_type('application/json')->set_output($memData);
		}
		else
		{
			$memData = $this->_getProgramRecents($start);
			$this->memcached->add($cache_key, $memData, $this->cache_time);
			$this->storeKey($this->getWhatsNewKey(), $cache_key);
			$this->output->set_content_type('application/json')->set_output($memData);
		}
	}

	public function tophits($start = 0)
	{
		$cache_key = sprintf("%s:%s:%s:%s:%s:%s:%s", $this->namespace_prefix, "tophits", $start, $this->device, $this->country_cache, $this->lr);
		$memData = $this->memcached->get($cache_key);
		if(FALSE != $memData)
		{
			$this->output->set_content_type('application/json')->set_output($memData);
		}
		else
		{
			$memData = $this->_getProgramTopHits($start);
			$this->memcached->add($cache_key, $memData, $this->cache_time);
			$this->output->set_content_type('application/json')->set_output($memData);
		}
	}

	private function _getCategory()
	{
		$this->load->model('Tv2_model','', TRUE);
		$this->Tv2_model->setDevice($this->device);
		$this->Tv2_model->setIsTH($this->isTH);
		$this->Tv2_model->setLegalRights($this->lr);
		
		$data['json']->categories = $this->Tv2_model->getCategory();
		return $this->load->view('json', $data, TRUE);
	}

	private function _getChannel() {
		
		$this->load->model('Tv2_model','', TRUE);
		$this->Tv2_model->setDevice($this->device);
		$this->Tv2_model->setIsTH($this->isTH);
		$this->Tv2_model->setLegalRights($this->lr);
		
		$data['json']->channels = $this->Tv2_model->getChannel();
		$data['json']->categories = $data['json']->channels;
		return $this->load->view('json', $data, TRUE);
	}
	
	private function _getRadio() {
		
		$this->load->model('Tv2_model','', TRUE);
		$this->Tv2_model->setDevice($this->device);
		
		$data['json']->radios = $this->Tv2_model->getRadio();
		return $this->load->view('json', $data, TRUE);
	}
	
	
	private function _getProgramRecents($start = 0) {
		$this->load->model('Tv2_model','', TRUE);
		$this->Tv2_model->setDevice($this->device);
		$this->Tv2_model->setIsTH($this->isTH);
		$this->Tv2_model->setLegalRights($this->lr);

		$data['json']->programs = $this->Tv2_model->getWhatsNewProgram($start);

		return $this->load->view('json', $data, TRUE);
	}
	
	private function _getProgramTopHits($start = 0) {
		$this->load->model('Tv2_model','', TRUE);
		$this->Tv2_model->setDevice($this->device);
		$this->Tv2_model->setIsTH($this->isTH);
		$this->Tv2_model->setLegalRights($this->lr);

		$data['json']->programs = $this->Tv2_model->getProgramByTopHits($start);

		return $this->load->view('json', $data, TRUE);
	}

	private function _getProgramByCategory($id, $start = 0) {
		$this->load->model('Tv2_model','', TRUE);
		$this->Tv2_model->setDevice($this->device);
		$this->Tv2_model->setIsTH($this->isTH);
		$this->Tv2_model->setLegalRights($this->lr);

		$data['json']->programs = $this->Tv2_model->getProgramByCategory($id, $start);
		return $this->load->view('json', $data, TRUE);
	}

	private function _getProgramByChannel($id, $start = 0) {
		$this->load->model('Tv2_model','', TRUE);
		$this->Tv2_model->setDevice($this->device);
		$this->Tv2_model->setIsTH($this->isTH);
		$this->Tv2_model->setLegalRights($this->lr);

		$data['json']->programs = $this->Tv2_model->getProgramByChannel($id, $start);
		return $this->load->view('json', $data, TRUE);
	}
	
	private function _getProgramBySearch($keyword, $start = 0) {
		$keyword = $this->input->get('keyword');
	
		$this->load->model('Tv2_model','', TRUE);
		$this->Tv2_model->setDevice($this->device);
		$this->Tv2_model->setIsTH($this->isTH);
		$this->Tv2_model->setLegalRights($this->lr);

		$data['json']->programs = $this->Tv2_model->getProgramSearch($keyword, $start);
		return $this->load->view('json', $data, TRUE);
	}

	public function episode($id, $start = 0) {
		$cache_key = sprintf("%s:%s:%s:%s:%s:%s:%s", $this->namespace_prefix, "episode", $id, $start, $this->device, $this->country_cache, $this->lr);
		$memData = $this->memcached->get($cache_key);
		if(FALSE != $memData)
		{
			$this->output->set_content_type('application/json')->set_output($memData);
		}
		else
		{
			$this->load->model('Tv2_model','', TRUE);
			$this->Tv2_model->setDevice($this->device);
			$this->Tv2_model->setIsTH($this->isTH);
			$this->Tv2_model->setLegalRights($this->lr);
			
			$data['json']->code = 200;
			if($start == 0) {
				$data['json']->info = $this->Tv2_model->getProgramInfo($id);
			}
			$data['json']->episodes = $this->Tv2_model->getEpisode($id, $start);
			$json = $this->load->view('json',$data,TRUE);
			$this->memcached->add($cache_key, $json, $this->cache_time);
			$this->storeKey($this->getProgramKey($id), $cache_key);
			$this->output->set_content_type('application/json')->set_output($json);
		}
	}

	public function program_info($id)
	{
		$cache_key = "$this->namespace_prefix:program_info:$id";
		$memData = $this->memcached->get($cache_key);
		if(FALSE != $memData)
		{
			$this->output->set_content_type('application/json')->set_output($memData);
		}
		else
		{
			$this->load->model('Tv2_model','', TRUE);
			$data['json'] = $this->Tv2_model->getProgramInfo($id);
			$json = $this->load->view('json', $data, TRUE);
			$this->memcached->add($cache_key, $json, $this->cache_time);
			$this->output->set_content_type('application/json')->set_output($json);
		}
	}
	
	public function program_info_otv($id)
	{
		$cache_key = "$this->namespace_prefix:program_info_otv:$id";
		$memData = $this->memcached->get($cache_key);
		if(FALSE != $memData)
		{
			$this->output->set_content_type('application/json')->set_output($memData);
		}
		else
		{
			$this->load->model('Tv2_model','', TRUE);
			$data['json'] = $this->Tv2_model->getProgramInfoOtv($id);
			$json = $this->load->view('json', $data, TRUE);
			$this->memcached->add($cache_key, $json, $this->cache_time);
			$this->output->set_content_type('application/json')->set_output($json);
		}
	}


	public function clearCache($program_id = -1)
	{
		if($program_id == -1)
		{
			$this->memcached->flush();
			echo 'Flush MemCached';
		}
		else {

			// Get Key of Category 0 (Lastest)
			$whatsnewKeyArray = $this->showWhatsNewKey();
			
			// Delete Store Key of Category 0
			$this->memcached->delete($this->getWhatsNewKey());

			// Delete Key of Category 0
			foreach ($whatsnewKeyArray as $value) {
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
		}
		
	}

	public function view_episode($id)
	{
		$this->load->model('Tv2_model','', TRUE);
		$this->Tv2_model->viewEP($id);
		
		## TEST ##

/*
		$this->load->helper('url');
		$url = 'http://27.131.144.6:8088/tv/index.html';
		redirect($url, 'location', 301);
*/

	}
	
	############### private function ##############
	
	public function encryptData()
	{
		$this->load->model('Tv2_model','', TRUE);
		$this->Tv2_model->encryptData();
	}
}