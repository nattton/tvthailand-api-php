<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Api2 extends CI_Controller {
	private $namespacePrefix = 'API2';
	private $cacheTime = 1800;
	private $countryCode;
	private $countryCacheKey;
	private $device;
	private $appId;
	private $build = 0;
	private $version;
	private $suffixCacheKey;
	private $limitOffset = 1000;

	function __construct()
	{
		parent::__construct();
		
 		$this->load->driver('cache');

		$this->device = $this->input->get_post('device');
		$this->appId = $this->input->get_post('appId');
		$this->build = intval($this->input->get_post('build'));
		$this->version = $this->input->get_post('version');

		// Location
		if (ENVIRONMENT == 'development') {
			$this->countryCode = 'TH';
			$this->countryCacheKey = 'TH';	
		}
		if (array_key_exists('GEOIP_COUNTRY_CODE', $_SERVER)) {
			$this->countryCode = $_SERVER['GEOIP_COUNTRY_CODE'];
			if($this->countryCode == 'TH') {
				$this->countryCacheKey = 'TH';
			}
		}
		
		$this->suffixCacheKey = "/$this->countryCacheKey/$this->device";
	}

	public function index() {
		$this->load->view('welcome_message');
	}

	private function getCategoryKey($id) {
		return "API_CATEGORY:$id";
	}

	private function getChannelKey($id) {
		return "API_CHANNEL:$id";
	}

	private function getRadioKey($id) {
		return "API_RADIO:$id";
	}

	private function getWhatsNewKey() {
		return "API_RECENTLY";
	}

	private function getProgramKey($id) {
		return "API_SHOW:$id";
	}

	private function storeKey($key, $hashKey) {
		return $this->cache->redis->hSet($key, $hashKey, 0);
	}

	public function showKey($key) {
		return $this->cache->redis->hKeys($key);
	}

	public function showCategoryKey($id) {
		$key =  $this->getCategoryKey($id);
		echo $key;
		echo "<br/>";
		$memData = $this->cache->redis->hKeys($key);
		echo var_dump($memData);
		return $memData;
	}

	public function showWhatsNewKey() {
		$key =  $this->getWhatsNewKey();
		echo $key;
		echo "<br/>";
		$memData = $this->cache->redis->hKeys($key);
		echo var_dump($memData);
		return $memData;
	}

	public function showProgramKey($id) {
		$key =  $this->getProgramKey($id);
		echo $key;
		echo "<br/>";
		$memData = $this->cache->redis->hKeys($key);
		echo var_dump($memData);
		return $memData;
	}

	public function message() {
		$cache_key = "$this->namespacePrefix/message:$this->device";
		$memData = $this->cache->redis->get($cache_key);
		if(!$memData)
		{
			$this->load->model('Tv2_model','', TRUE);
			$this->Tv2_model->setClientInfo($this->countryCode, $this->device, $this->appId, $this->build, $this->version);
			$result = $this->Tv2_model->getMessage();

			$data = new stdClass();
			$data->id = $result->id;
			$data->title = $result->title;
			$data->message = $result->message;

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

			$data->buttons = $buttons;
			$memData = json_encode($data);
			$this->cache->redis->save($cache_key, $memData, $this->cacheTime);
		}
		$this->output->set_content_type('application/json')->set_output($memData);
	}

	public function advertise()
	{
		$cache_key = "$this->namespacePrefix/advertise:$this->suffixCacheKey/$this->build";
		$memData = $this->cache->redis->get($cache_key);
		if(!$memData)
		{
			$this->load->model('Tv2_model','', TRUE);
			$this->Tv2_model->setClientInfo($this->countryCode, $this->device, $this->appId, $this->build, $this->version);
			$data = new stdClass();
			$data->delay_start = 1000;
			$data->ads = $this->Tv2_model->getAdvertise();

			$memData = json_encode($data);
			$this->cache->redis->save($cache_key, $memData, $this->cacheTime);
		}
		$this->output->set_content_type('application/json')->set_output($memData);
	}

	public function preroll_advertise()
	{
		$cache_key = "$this->namespacePrefix/preroll_advertise:$this->device";
		$memData = $this->cache->redis->get($cache_key);
		if(!$memData)
		{
			$this->load->model('Tv2_model','', TRUE);
			$this->Tv2_model->setClientInfo($this->countryCode, $this->device, $this->appId, $this->build, $this->version);
			$data = new stdClass();
			$data->ads = $this->Tv2_model->getPrerollAdvertise();
			$memData = json_encode($data);
			$this->cache->redis->save($cache_key, $memData, $this->cacheTime);
		}
		$this->output->set_content_type('application/json')->set_output($memData);
	}

	public function section() {
		$cache_key = "$this->namespacePrefix/section:$this->device/$this->suffixCacheKey";
		$memData = $this->cache->redis->get($cache_key);
		if(!$memData)
		{
			$this->load->model('Tv2_model','', TRUE);
			$this->Tv2_model->setClientInfo($this->countryCode, $this->device, $this->appId, $this->build, $this->version);

			$data = new stdClass();
			$data->categories = $this->Tv2_model->getCategory();
			$data->channels = $this->Tv2_model->getChannel();
			$data->radios = $this->Tv2_model->getRadio();

			$memData = json_encode($data);
			$this->cache->redis->save($cache_key, $memData, $this->cacheTime);
		}
		$this->output->set_content_type('application/json')->set_output($memData);
	}

	public function category($id = -1, $start = 0)
	{
		if ($start > $this->limitOffset) {
			$start = $this->limitOffset;
		} 
		$cache_key = "$this->namespacePrefix/category:$id/$start/$this->suffixCacheKey/$this->build";
		$memData = $this->cache->redis->get($cache_key);
		if(!$memData)
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
			$this->cache->redis->save($cache_key, $memData, $this->cacheTime);
		}
		$this->output->set_content_type('application/json')->set_output($memData);
	}

	public function channel($id = -1, $start = 0)
	{
		$cache_key = "$this->namespacePrefix/channel:$id/$start/$this->device/$this->countryCacheKey";
		$memData = $this->cache->redis->get($cache_key);
		if(!$memData)
		{
			if (-1 == $id) {
				$memData = $this->_getChannel();
			}
			else {
				$memData = $this->_getProgramByChannel($id, $start);
			}
			$this->cache->redis->save($cache_key, $memData, $this->cacheTime);
		}
		$this->output->set_content_type('application/json')->set_output($memData);
	}

	public function radio($id = -1, $start = 0)
	{
		$cache_key = "$this->namespacePrefix/radio:$id/$start/$this->suffixCacheKey";
		$memData = $this->cache->redis->get($cache_key);
		if(!$memData)
		{
			$memData = $this->_getRadio();
			$this->cache->redis->save($cache_key, $memData, $this->cacheTime);
		}
		$this->output->set_content_type('application/json')->set_output($memData);
	}

	public function search($start = 0)
	{
		$keyword = $this->input->get('keyword');
		$cache_key = "$this->namespacePrefix/search:$keyword/$start/$this->suffixCacheKey";
		$memData = $this->cache->redis->get($cache_key);
		if(!$memData) {
			$memData = $this->_getProgramBySearch($keyword, $start);
			$this->cache->redis->save($cache_key, $memData, $this->cacheTime);
		}
		$this->output->set_content_type('application/json')->set_output($memData);
	}

	public function all_program()
	{
		$cache_key = "$this->namespacePrefix/all_program:$this->suffixCacheKey";
		$memData = $this->cache->redis->get($cache_key);
		if(!$memData)
		{
			$this->load->model('Tv2_model','', TRUE);
			$this->Tv2_model->setClientInfo($this->countryCode, $this->device, $this->appId, $this->build, $this->version);

			$data = new stdClass();
			$data->programs = $this->Tv2_model->getAllProgram();
			$memData = json_encode($data);
			$this->cache->redis->save($cache_key, $memData, $this->cacheTime);
		}
		$this->output->set_content_type('application/json')->set_output($memData);
	}

	public function whatsnew($start = 0)
	{
		$this->category('recents', $start);
	}

	public function tophits($start = 0)
	{
		$cache_key = "$this->namespacePrefix/tophits:$start/$this->suffixCacheKey";
		$memData = $this->cache->redis->get($cache_key);
		if(!$memData)
		{
			$memData = $this->_getProgramTopHits($start);
			$this->cache->redis->save($cache_key, $memData, $this->cacheTime);
		}
		$this->output->set_content_type('application/json')->set_output($memData);
	}

	private function _getCategory()
	{
		$this->load->model('Tv2_model','', TRUE);
		$this->Tv2_model->setClientInfo($this->countryCode, $this->device, $this->appId, $this->build, $this->version);

		$data = new stdClass();
		$data->categories = $this->Tv2_model->getCategory();
		return json_encode($data);
	}

	private function _getChannel()
	{
		$this->load->model('Tv2_model','', TRUE);
		$this->Tv2_model->setClientInfo($this->countryCode, $this->device, $this->appId, $this->build, $this->version);

		$data = new stdClass();
		$data->channels = $this->Tv2_model->getChannel();
		return json_encode($data);
	}

	private function _getRadio() {

		$this->load->model('Tv2_model','', TRUE);
		$this->Tv2_model->setClientInfo($this->countryCode, $this->device, $this->appId, $this->build, $this->version);

		$data = new stdClass();
		$data->radios = $this->Tv2_model->getRadio();
		return json_encode($data);
	}


	private function _getProgramRecents($start = 0) {
		$this->load->model('Tv2_model','', TRUE);
		$this->Tv2_model->setClientInfo($this->countryCode, $this->device, $this->appId, $this->build, $this->version);

		$data = new stdClass();
		$data->programs = ($start < $this->limitOffset) ? $this->Tv2_model->getWhatsNewProgram($start) : array();

		return json_encode($data);
	}

	private function _getProgramTopHits($start = 0) {
		$this->load->model('Tv2_model','', TRUE);
		$this->Tv2_model->setClientInfo($this->countryCode, $this->device, $this->appId, $this->build, $this->version);

		$data = new stdClass();
		$data->programs = ($start < $this->limitOffset) ? $this->Tv2_model->getProgramByTopHits($start) : array();

		return json_encode($data);
	}

	private function _getProgramByCategory($id, $start = 0) {
		$this->load->model('Tv2_model','', TRUE);
		$this->Tv2_model->setClientInfo($this->countryCode, $this->device, $this->appId, $this->build, $this->version);

		$data = new stdClass();
		$data->programs = $this->Tv2_model->getProgramByCategory($id, $start);
		return json_encode($data);
	}

	private function _getProgramByChannel($id, $start = 0) {
		$this->load->model('Tv2_model','', TRUE);
		$this->Tv2_model->setClientInfo($this->countryCode, $this->device, $this->appId, $this->build, $this->version);

		$data = new stdClass();
		$data->programs = $this->Tv2_model->getProgramByChannel($id, $start);
		return json_encode($data);
	}

	private function _getProgramBySearch($keyword, $start = 0) {
		$keyword = $this->input->get('keyword');

		$this->load->model('Tv2_model','', TRUE);
		$this->Tv2_model->setClientInfo($this->countryCode, $this->device, $this->appId, $this->build, $this->version);

		$data = new stdClass();
		$data->programs = $this->Tv2_model->getProgramSearch($keyword, $start);
		return json_encode($data);
	}

	public function episode($id, $start = 0) {
		$cache_key = "$this->namespacePrefix/episode:$id/$start/$this->device";
		$memData = $this->cache->redis->get($cache_key);
		if(!$memData)
		{
			$this->load->model('Tv2_model','', TRUE);
			$this->Tv2_model->setClientInfo($this->countryCode, $this->device, $this->appId, $this->build, $this->version);

			$data = new stdClass();
			$data->code = 200;
			if($start == 0) {
				$data->info = $this->Tv2_model->getProgramInfo($id);
			}
			$data->episodes = $this->Tv2_model->getEpisode($id, $start);
			$memData = json_encode($data);
			$this->cache->redis->save($cache_key, $memData, $this->cacheTime);
			$this->storeKey($this->getProgramKey($id), $cache_key);
		}
		$this->output->set_content_type('application/json')->set_output($memData);
	}

	public function episode_raw($id, $start = 0) {
		$cache_key = "$this->namespacePrefix/episode_raw:$id/$start/$this->device";
		$memData = $this->cache->redis->get($cache_key);
		if(!$memData)
		{
			$this->load->model('Tv2_model','', TRUE);
			$this->Tv2_model->setClientInfo($this->countryCode, $this->device, $this->appId, $this->build, $this->version);

			$data = new stdClass();
			$data->code = 200;
			if($start == 0) {
				$data->info = $this->Tv2_model->getProgramInfo($id);
			}
			$data->episodes = $this->Tv2_model->getEpisodeRaw($id, $start);
			$memData = json_encode($data);
			$this->cache->redis->save($cache_key, $memData, $this->cacheTime);
			$this->storeKey($this->getProgramKey($id), $cache_key);
		}
		$this->output->set_content_type('application/json')->set_output($memData);
	}

	public function program_info($id)
	{
		$cache_key = "$this->namespacePrefix/program_info:$id";
		$memData = $this->cache->redis->get($cache_key);
		if(!$memData)
		{
			$this->load->model('Tv2_model','', TRUE);
			$data = new stdClass();
			$data = $this->Tv2_model->getProgramInfo($id);
			$memData = json_encode($data);
			$this->cache->redis->save($cache_key, $memData, $this->cacheTime);
		}
		$this->output->set_content_type('application/json')->set_output($memData);
	}

	public function program_info_otv($id)
	{
		$cache_key = "$this->namespacePrefix/program_info_otv:$id";
		$memData = $this->cache->redis->get($cache_key);
		if(!$memData)
		{
			$this->load->model('Tv2_model','', TRUE);
			$data = new stdClass();
			$data = $this->Tv2_model->getProgramInfoOtv($id);
			$memData = json_encode($data);
			$this->cache->redis->save($cache_key, $memData, $this->cacheTime);
		}
		$this->output->set_content_type('application/json')->set_output($memData);
	}

	public function view_episode($id)
	{
		$this->load->model('Tv2_model','', TRUE);
		$this->Tv2_model->viewEP($id);

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
		$this->load->model('Tv2_model','', TRUE);
		$this->Tv2_model->encryptData();
	}
}
