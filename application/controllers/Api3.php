<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Api3 extends CI_Controller {
	private $namespace_prefix = 'API3';
	private $cache_time = 1800;
	private $country_code = '';
	private $country_cache = '';
	private $isTH = FALSE;
	private $device = '';

	function __construct()
	{
		parent::__construct();
 		$this->load->driver('cache');

		($this->device = $this->input->get('device')) or ($this->device = '');

		// Location
		if (ENVIRONMENT == 'development') {
			$this->country_cache = 'TH';
			$this->isTH = TRUE;			
		}
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
		return $this->cache->redis->rPush($cache_key, $value);
	}

	public function showKey($cache_key) {
		$memData = $this->cache->redis->lAll($cache_key);
		return $memData;
	}

	public function showCategoryKey($id) {
		$key =  $this->getCategoryKey($id);
		echo $key;
		echo "<br/>";
		$memData = $this->cache->redis->lAll($key);
		echo var_dump($memData);
		return $memData;
	}

	public function showWhatsNewKey() {
		$key =  $this->getWhatsNewKey();
		echo $key;
		echo "<br/>";
		$memData = $this->cache->redis->lAll($key);
		echo var_dump($memData);
		return $memData;
	}

	public function showProgramKey($id) {
		$key =  $this->getProgramKey($id);
		echo $key;
		echo "<br/>";
		$memData = $this->cache->redis->lAll($key);
		echo var_dump($memData);
		return $memData;
	}

	public function message() {
		$cache_key = "$this->namespace_prefix:message:$this->device";
		$memData = $this->cache->redis->get($cache_key);
		if(FALSE == $memData)
		{
			$this->load->model('Tv3_model','', TRUE);
			$this->Tv3_model->setDevice($this->device);
			$result = $this->Tv3_model->getMessage();

			$data = new stdClass();
			$data->id = $result->id;
			$data->title = $result->title;
			$data->message = $result->message;

			$buttons = array();
			if($this->device == 'android') {
				array_push($buttons, $this->Tv3_model->createButton('Rating & Review','http://goo.gl/1BJYa'));
				array_push($buttons, $this->Tv3_model->createButton('Fan Page','https://www.facebook.com/TV.Thailand'));
			}
			elseif($this->device == 'ios') {
				array_push($buttons, $this->Tv3_model->createButton('Rating & Review','https://itunes.apple.com/us/app/tv-thailand/id458429827?ls=1&mt=8'));
				array_push($buttons, $this->Tv3_model->createButton('Fan Page','https://www.facebook.com/TV.Thailand'));
			}
			elseif($this->device == 'wp') {
				array_push($buttons, $this->Tv3_model->createButton('Fan Page','https://www.facebook.com/TV.Thailand'));
			}

			$data->buttons = $buttons;
			$memData = json_encode($data);
			$this->cache->redis->save($cache_key, $memData, $this->cache_time);
		}
		$this->output->set_content_type('application/json')->set_output($memData);
	}

	public function advertise()
	{
		$cache_key = "$this->namespace_prefix:advertise:$this->device";
		$memData = $this->cache->redis->get($cache_key);
		if(FALSE == $memData)
		{
			$this->load->model('Tv3_model','', TRUE);
			$this->Tv3_model->setDevice($this->device);
			$data = new stdClass();
			$data->delay_start = 1000;
			$data->ads = $this->Tv3_model->getAdvertise();

			$memData = json_encode($data);
			$this->cache->redis->save($cache_key, $memData, $this->cache_time);
		}
		$this->output->set_content_type('application/json')->set_output($memData);
	}

	public function preroll_advertise()
	{
		$cache_key = "$this->namespace_prefix:preroll_advertise:$this->device";
		$memData = $this->cache->redis->get($cache_key);
		if(FALSE == $memData)
		{
			$this->load->model('Tv3_model','', TRUE);
			$this->Tv3_model->setDevice($this->device);
			$data = new stdClass();
			$data->ads = $this->Tv3_model->getPrerollAdvertise();
			$memData = json_encode($data);
			$this->cache->redis->save($cache_key, $memData, $this->cache_time);
		}
		$this->output->set_content_type('application/json')->set_output($memData);
	}

	public function section() {
		$cache_key = sprintf("%s:%s:%s:%s", $this->namespace_prefix, "section", $this->device, $this->country_cache);
		$memData = $this->cache->redis->get($cache_key);
		if(FALSE == $memData)
		{
			$this->load->model('Tv3_model','', TRUE);
			$this->Tv3_model->setDevice($this->device);
			$this->Tv3_model->setIsTH($this->isTH);

			$data = new stdClass();
			$data->categories = $this->Tv3_model->getCategory();
			$data->channels = $this->Tv3_model->getChannel();
			$data->radios = $this->Tv3_model->getRadio();

			$memData = json_encode($data);
			$this->cache->redis->save($cache_key, $memData, $this->cache_time);
		}
		$this->output->set_content_type('application/json')->set_output($memData);
	}

	public function category($id = -1, $start = 0)
	{
		$cache_key = sprintf("%s:%s:%s:%s:%s:%s", $this->namespace_prefix, "category", $id, $start, $this->device, $this->country_cache);
		$memData = $this->cache->redis->get($cache_key);
		if(FALSE == $memData)
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
			$this->cache->redis->save($cache_key, $memData, $this->cache_time);
		}
		$this->output->set_content_type('application/json')->set_output($memData);
	}

	public function channel($id = -1, $start = 0)
	{
		$cache_key = sprintf("%s:%s:%s:%s:%s:%s", $this->namespace_prefix, "channel", $id, $start, $this->device, $this->country_cache);
		$memData = $this->cache->redis->get($cache_key);
		if(FALSE == $memData)
		{
			if (-1 == $id) {
				$memData = $this->_getChannel();
			}
			else {
				$memData = $this->_getProgramByChannel($id, $start);
			}
			$this->cache->redis->save($cache_key, $memData, $this->cache_time);
		}
		$this->output->set_content_type('application/json')->set_output($memData);
	}

	public function radio($id = -1, $start = 0)
	{
		$cache_key = sprintf("%s:%s:%s:%s:%s:%s", $this->namespace_prefix, "radio", $id, $start, $this->device, $this->country_cache);
		$memData = $this->cache->redis->get($cache_key);
		if(FALSE == $memData)
		{
			$memData = $this->_getRadio();
			$this->cache->redis->save($cache_key, $memData, $this->cache_time);
		}
		$this->output->set_content_type('application/json')->set_output($memData);
	}

	public function search($start = 0)
	{
		$keyword = $this->input->get('keyword');
		$cache_key = sprintf("%s:%s:%s:%s:%s:%s", $this->namespace_prefix, "search", $keyword, $start, $this->device, $this->country_cache);
		$memData = $this->cache->redis->get($cache_key);
		if(FALSE == $memData) {
			$memData = $this->_getProgramBySearch($keyword, $start);
			$this->cache->redis->save($cache_key, $memData, $this->cache_time);
		}
		$this->output->set_content_type('application/json')->set_output($memData);
	}

	public function all_program()
	{
		$cache_key = "$this->namespace_prefix:all_program:$this->device:$this->country_cache";
		$cache_key = sprintf("%s:%s:%s:%s", $this->namespace_prefix, "all_program", $this->device, $this->country_cache);
		$memData = $this->cache->redis->get($cache_key);
		if(FALSE == $memData)
		{
			$this->load->model('Tv3_model','', TRUE);
			$this->Tv3_model->setDevice($this->device);
			$this->Tv3_model->setIsTH($this->isTH);

			$data = new stdClass();
			$data->programs = $this->Tv3_model->getAllProgram();
			$memData = json_encode($data);
			$this->cache->redis->save($cache_key, $memData, $this->cache_time);
		}
		$this->output->set_content_type('application/json')->set_output($memData);
	}

	public function whatsnew($start = 0)
	{
		$cache_key = sprintf("%s:%s:%s:%s:%s:%s", $this->namespace_prefix, "whatsnew", $start, $this->device, $this->country_cache);
		$memData = $this->cache->redis->get($cache_key);
		if(FALSE == $memData)
		{
			$memData = $this->_getProgramRecents($start);
			$this->cache->redis->save($cache_key, $memData, $this->cache_time);
			$this->storeKey($this->getWhatsNewKey(), $cache_key);
		}
		$this->output->set_content_type('application/json')->set_output($memData);
	}

	public function tophits($start = 0)
	{
		$cache_key = sprintf("%s:%s:%s:%s:%s:%s", $this->namespace_prefix, "tophits", $start, $this->device, $this->country_cache);
		$memData = $this->cache->redis->get($cache_key);
		if(FALSE == $memData)
		{
			$memData = $this->_getProgramTopHits($start);
			$this->cache->redis->save($cache_key, $memData, $this->cache_time);
		}
		$this->output->set_content_type('application/json')->set_output($memData);
	}

	private function _getCategory()
	{
		$this->load->model('Tv3_model','', TRUE);
		$this->Tv3_model->setDevice($this->device);
		$this->Tv3_model->setIsTH($this->isTH);

		$data = new stdClass();
		$data->categories = $this->Tv3_model->getCategory();
		return json_encode($data);
	}

	private function _getChannel()
	{
		$this->load->model('Tv3_model','', TRUE);
		$this->Tv3_model->setDevice($this->device);
		$this->Tv3_model->setIsTH($this->isTH);

		$data = new stdClass();
		$data->channels = $this->Tv3_model->getChannel();
		return json_encode($data);
	}

	private function _getRadio() {

		$this->load->model('Tv3_model','', TRUE);
		$this->Tv3_model->setDevice($this->device);

		$data = new stdClass();
		$data->radios = $this->Tv3_model->getRadio();
		return json_encode($data);
	}


	private function _getProgramRecents($start = 0) {
		$this->load->model('Tv3_model','', TRUE);
		$this->Tv3_model->setDevice($this->device);
		$this->Tv3_model->setIsTH($this->isTH);

		$data = new stdClass();
		$data->programs = $this->Tv3_model->getWhatsNewProgram($start);

		return json_encode($data);
	}

	private function _getProgramTopHits($start = 0) {
		$this->load->model('Tv3_model','', TRUE);
		$this->Tv3_model->setDevice($this->device);
		$this->Tv3_model->setIsTH($this->isTH);

		$data = new stdClass();
		$data->programs = $this->Tv3_model->getProgramByTopHits($start);

		return json_encode($data);
	}

	private function _getProgramByCategory($id, $start = 0) {
		$this->load->model('Tv3_model','', TRUE);
		$this->Tv3_model->setDevice($this->device);
		$this->Tv3_model->setIsTH($this->isTH);

		$data = new stdClass();
		$data->programs = $this->Tv3_model->getProgramByCategory($id, $start);
		return json_encode($data);
	}

	private function _getProgramByChannel($id, $start = 0) {
		$this->load->model('Tv3_model','', TRUE);
		$this->Tv3_model->setDevice($this->device);
		$this->Tv3_model->setIsTH($this->isTH);

		$data = new stdClass();
		$data->programs = $this->Tv3_model->getProgramByChannel($id, $start);
		return json_encode($data);
	}

	private function _getProgramBySearch($keyword, $start = 0) {
		$keyword = $this->input->get('keyword');

		$this->load->model('Tv3_model','', TRUE);
		$this->Tv3_model->setDevice($this->device);
		$this->Tv3_model->setIsTH($this->isTH);

		$data = new stdClass();
		$data->programs = $this->Tv3_model->getProgramSearch($keyword, $start);
		return json_encode($data);
	}

	public function episode($id, $start = 0) {
		$cache_key = sprintf("%s:%s:%s:%s:%s:%s", $this->namespace_prefix, "episode", $id, $start, $this->device, $this->country_cache);
		$memData = $this->cache->redis->get($cache_key);
		if(FALSE == $memData)
		{
			$this->load->model('Tv3_model','', TRUE);
			$this->Tv3_model->setDevice($this->device);
			$this->Tv3_model->setIsTH($this->isTH);

			$data = new stdClass();
			$data->code = 200;
			if($start == 0) {
				$data->info = $this->Tv3_model->getProgramInfo($id);
			}
			$data->episodes = $this->Tv3_model->getEpisode($id, $start);
			$memData = json_encode($data);
			$this->cache->redis->save($cache_key, $memData, $this->cache_time);
			$this->storeKey($this->getProgramKey($id), $cache_key);
		}
		$this->output->set_content_type('application/json')->set_output($memData);
	}

	public function episode_raw($id, $start = 0) {
		$cache_key = sprintf("%s:%s:%s:%s:%s:%s", $this->namespace_prefix, "episode_raw", $id, $start, $this->device, $this->country_cache);
		$memData = $this->cache->redis->get($cache_key);
		if(FALSE == $memData)
		{
			$this->load->model('Tv3_model','', TRUE);
			$this->Tv3_model->setDevice($this->device);
			$this->Tv3_model->setIsTH($this->isTH);

			$data = new stdClass();
			$data->code = 200;
			if($start == 0) {
				$data->info = $this->Tv3_model->getProgramInfo($id);
			}
			$data->episodes = $this->Tv3_model->getEpisodeRaw($id, $start);
			$memData = json_encode($data);
			$this->cache->redis->save($cache_key, $memData, $this->cache_time);
			$this->storeKey($this->getProgramKey($id), $cache_key);
		}
		$this->output->set_content_type('application/json')->set_output($memData);
	}

	public function program_info($id)
	{
		$cache_key = "$this->namespace_prefix:program_info:$id";
		$memData = $this->cache->redis->get($cache_key);
		if(FALSE == $memData)
		{
			$this->load->model('Tv3_model','', TRUE);
			$data = new stdClass();
			$data = $this->Tv3_model->getProgramInfo($id);
			$memData = json_encode($data);
			$this->cache->redis->save($cache_key, $memData, $this->cache_time);
		}
		$this->output->set_content_type('application/json')->set_output($memData);
	}

	public function program_info_otv($id)
	{
		$cache_key = "$this->namespace_prefix:program_info_otv:$id";
		$memData = $this->cache->redis->get($cache_key);
		if(FALSE == $memData)
		{
			$this->load->model('Tv3_model','', TRUE);
			$data = new stdClass();
			$data = $this->Tv3_model->getProgramInfoOtv($id);
			$memData = json_encode($data);
			$this->cache->redis->save($cache_key, $memData, $this->cache_time);
		}
		$this->output->set_content_type('application/json')->set_output($memData);
	}

	public function view_episode($id)
	{
		$this->load->model('Tv3_model','', TRUE);
		$this->Tv3_model->viewEP($id);

	}

	############### private function ##############
	
	public function clearCache($program_id = -1)
	{
		if($program_id == -1)
		{
			$this->cache->redis->clean();
			echo 'Flush MemCached';
		}
		else {
			$this->cache->redis->delete($this->showWhatsNewKey());
			$this->cache->redis->delete($this->getWhatsNewKey());
			$this->cache->redis->delete($this->showProgramKey($program_id));
			$this->cache->redis->delete($this->getProgramKey($program_id));
		}
	}

	public function encryptData()
	{
		$this->load->model('Tv3_model','', TRUE);
		$this->Tv3_model->encryptData();
	}
}
