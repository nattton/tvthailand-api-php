<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Api extends CI_Controller {
	private $namespace_prefix = 'API';
	private $cache_time = 2400;
	private $isTH = TRUE;
	private $device = '';

	function __construct()
	{
		parent::__construct();

 		$this->load->driver('cache');
		// Set Device

		($this->device = $this->input->get('device')) or ($this->device = '');
	}

	public function index()
	{
		$this->load->view('welcome_message');
	}


	private function getCategoryKey($id) {
		if($id == 0) return "API:WHATSNEW";

		return "API:CATEGORY:$id";
	}

	private function getProgramKey($id) {
		return "API:PROGRAM:$id";
	}

	private function storeKey($cache_key, $value) {
		return $this->cache->redis->rPush($cache_key, $value);
	}

	public function showKey($cache_key) {
		return $this->cache->redis->get($cache_key);
	}

	public function showCategoryKey($id) {
		$key =  $this->getCategoryKey($id);
		return $this->cache->redis->lAll($key);
	}

	public function showProgramKey($id) {
		$key =  $this->getProgramKey($id);
		return $this->cache->redis->lAll($key);
	}

	public function getMessageiOS()
	{
		$cache_key = $this->namespace_prefix.'getMessageiOS';
		$memData = $this->cache->redis->get($cache_key);
		if(FALSE != $memData)
		{
			$this->output->set_content_type('application/json')->set_output($memData);
		}
		else
		{
			$data = array('json' => new stdClass());
			$data['json']->id = '201502200200';
			$data['json']->title = '*Notice*';
			$data['json']->message = 'Welcome to TV Thailand for iOS';

			$this->load->model('Tv_model','', FALSE);
			$buttons = array();
/* 			array_push($buttons, $this->Tv_model->createButton('Read Review','http://goo.gl/Vdoba')); */
			array_push($buttons, $this->Tv_model->createButton('Rating & Review','https://itunes.apple.com/us/app/tv-thailand/id458429827?ls=1&mt=8'));
			array_push($buttons, $this->Tv_model->createButton('Fan Page','https://www.facebook.com/TV.Thailand'));

			$data['json']->buttons = $buttons;
			$json = $this->load->view('json', $data, TRUE);
			$this->cache->redis->save($cache_key, $json, $this->cache_time);
			$this->output->set_content_type('application/json')->set_output($json);
		}
	}

	public function getMessageAndroid()
	{
		$cache_key = $this->namespace_prefix.'getMessageAndroid';
		$memData = $this->cache->redis->get($cache_key);
		if(FALSE != $memData)
		{
			$this->output->set_content_type('application/json')->set_output($memData);
		}
		else
		{
			$data = array('json' => new stdClass());
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
			array_push($buttons, $this->Tv_model->createButton('Fan Page','https://www.facebook.com/TV.Thailand'));
			
			
/*
			$data = array('json' => new stdClass());
			$data['json']->id = '201510800200';
			$data['json']->title = '* Notice *';
			$data['json']->message = 'Please Update TV Thailand';
			$obj = new stdClass();
			$obj->versionCode = 130;
			$obj->apk = 'http://bit.ly/tvthp270';
			$data['json']->app_version = $obj;

			$this->load->model('Tv_model','', FALSE);
			$buttons = array();
 			array_push($buttons, $this->Tv_model->createButton('Read Update TV Thailand','http://bit.ly/installtvthailand'));
			array_push($buttons, $this->Tv_model->createButton('Fan Page','https://www.facebook.com/TV.Thailand'));
*/

			$data['json']->buttons = $buttons;

			$json = $this->load->view('json', $data, TRUE);
			$this->cache->redis->save($cache_key, $json, $this->cache_time);
			$this->output->set_content_type('application/json')->set_output($json);
		}
	}

	public function getMessageWP()
	{
		$cache_key = $this->namespace_prefix.'getMessageWP';
		$memData = $this->cache->redis->get($cache_key);
		if(FALSE != $memData)
		{
			$this->output->set_content_type('application/json')->set_output($memData);
		}
		else
		{
			$data = array('json' => new stdClass());
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
			$this->cache->redis->save($cache_key, $json, $this->cache_time);
			$this->output->set_content_type('application/json')->set_output($json);
		}
	}

	public function getInHouseAd()
	{
		($device = $this->input->get('device')) or ($device = '');

		$cache_key = "$this->namespace_prefix:getInHouseAd:$this->device";

		$memData = $this->cache->redis->get($cache_key);
		if(FALSE != $memData)
		{
			$this->output->set_content_type('application/json')->set_output($memData);
		}
		else
		{
			$this->load->model('Tv_model','', TRUE);
			$this->Tv_model->setDevice($this->device);
			$data = array('json' => new stdClass());
			$data['json']->delayStart = 1000;
			$data['json']->ads = $this->Tv_model->getAds();

			$pageAds = new stdClass();
			$pageAds->program = "";
			$pageAds->programlist = "";
			$pageAds->ep = "";
			$pageAds->player = "";
			$data['json']->pageAds = $pageAds;

			$json = $this->load->view('json', $data, TRUE);
			$this->cache->redis->save($cache_key, $json, $this->cache_time);
			$this->output->set_content_type('application/json')->set_output($json);
		}
	}

	public function getCategory()
	{
		$cache_key = sprintf("%s:%s", $this->namespace_prefix, "getCategory");
		$memData = $this->cache->redis->get($cache_key);
		if(FALSE != $memData)
		{
			$this->output->set_content_type('application/json')->set_output($memData);
		}
		else
		{
			$this->load->model('Tv_model','', TRUE);
			$this->Tv_model->setIsTH($this->isTH);
			$data = array('json' => new stdClass());
			$data['json']->ads = $this->Tv_model->getAds();
			$data['json']->categories = $this->Tv_model->getCategory();
			$json = $this->load->view('json', $data, TRUE);
			$this->cache->redis->save($cache_key, $json, $this->cache_time);
			$this->output->set_content_type('application/json')->set_output($json);
		}

		/* $this->load->view('json',$data,TRUE) */;
	}

	public function getCategoryV2()
	{
		$cache_key = $this->namespace_prefix.'getCategoryV2';
		$memData = $this->cache->redis->get($cache_key);
		if(FALSE != $memData)
		{
			$this->output->set_content_type('application/json')->set_output($memData);
		}
		else
		{
			$this->load->model('Tv_model','', TRUE);
			$data = array('json' => new stdClass());
			$data['json']->image_path = $this->Tv_model->thumbnail_path;
			$data['json']->categories = $this->Tv_model->getCategoryV2();
			$data['json']->channels = $this->Tv_model->getChannel();
			$json = $this->load->view('json', $data, TRUE);
			$this->cache->redis->save($cache_key, $json, $this->cache_time);
			$this->output->set_content_type('application/json')->set_output($json);
		}

		// $this->load->view('json',$data,TRUE)
	}

	public function getChannel()
	{
		$cache_key = $this->namespace_prefix.'getChannel';
		$memData = $this->cache->redis->get($cache_key);
		if(FALSE != $memData)
		{
			$this->output->set_content_type('application/json')->set_output($memData);
		}
		else
		{
			$this->load->model('Tv_model','', TRUE);
			$data = array('json' => new stdClass());
			$data['json']->image_path = $this->Tv_model->thumbnail_path;
			$data['json']->channels = $this->Tv_model->getChannel();
			$json = $this->load->view('json', $data, TRUE);
			$this->cache->redis->save($cache_key, $json, $this->cache_time);
			$this->output->set_content_type('application/json')->set_output($json);
		}

		/* $this->load->view('json',$data,TRUE) */;
	}

	public function getLiveChannel()
	{
		($device = $this->input->get('device')) or ($device = '');
		$cache_key = $this->namespace_prefix.'getLiveChannel_'.$device;
		$memData = $this->cache->redis->get($cache_key);
		if(FALSE != $memData)
		{
			$this->output->set_content_type('application/json')->set_output($memData);
		}
		else
		{
			$this->load->model('Tv_model','', TRUE);
			$data = array('json' => new stdClass());
			$data['json']->image_path = $this->Tv_model->thumbnail_path;
			$data['json']->channels = $this->Tv_model->getLiveChannel($device);
			$json = $this->load->view('json', $data, TRUE);
			$this->cache->redis->save($cache_key, $json, $this->cache_time);
			$this->output->set_content_type('application/json')->set_output($json);
		}

		/* $this->load->view('json',$data,TRUE) */;
	}

	public function getProgram($cat_id = 0, $start = 0)
	{
		$cache_key = sprintf("%s:%s:%s:%s:%s", $this->namespace_prefix, "getProgram", $cat_id, $start, $this->device);
		$memData = $this->cache->redis->get($cache_key);
		if(FALSE != $memData)
		{
			$this->output->set_content_type('application/json')->set_output($memData);
		}
		else
		{
			$this->load->model('Tv_model','', TRUE);
			$this->Tv_model->setDevice($this->device);
			$this->Tv_model->setIsTH($this->isTH);

			$data = array('json' => new stdClass());
			$data['json']->thumbnail_path = $this->Tv_model->thumbnail_path;
			$data['json']->programs = $this->Tv_model->getProgram($cat_id, $start);
			$json = $this->load->view('json',$data,TRUE);

			$this->cache->redis->save($cache_key, $json, $this->cache_time);

			if($cat_id == 0) $this->storeKey($this->getCategoryKey($cat_id), $cache_key);

			$this->output->set_content_type('application/json')->set_output($json);
		}

/* 		$this->load->view('json',$data); */
	}

	public function getWhatsNew($start = 0)
	{
		$cache_key = sprintf("%s:%s:%s:%s", $this->namespace_prefix, "getWhatsNew", $start, $this->device);

		$memData = $this->cache->redis->get($cache_key);
		if(FALSE != $memData)
		{
			$this->output->set_content_type('application/json')->set_output($memData);
		}
		else
		{
			$this->load->model('Tv_model','', TRUE);
			$this->Tv_model->setDevice($this->device);
			$this->Tv_model->setIsTH($this->isTH);

			$data = array('json' => new stdClass());
			$data['json']->thumbnail_path = $this->Tv_model->thumbnail_path;
			$data['json']->programs = $this->Tv_model->getProgram(0, $start);
			$json = $this->load->view('json',$data,TRUE);
			$this->cache->redis->save($cache_key, $json, $this->cache_time);

			$this->storeKey($this->getCategoryKey(0), $cache_key);

			$this->output->set_content_type('application/json')->set_output($json);
		}

/* 		$this->load->view('json',$data); */
	}

	public function getNewRelease($start = 0)
	{
		$cache_key = $this->namespace_prefix.'getNewRelease_'.$start;;
		$memData = $this->cache->redis->get($cache_key);
		if(FALSE != $memData)
		{
			$this->output->set_content_type('application/json')->set_output($memData);
		}
		else
		{
			$this->load->model('Tv_model','', TRUE);
			$this->Tv_model->setDevice($this->device);
			$this->Tv_model->setIsTH($this->isTH);
			$data = array('json' => new stdClass());
			$data['json']->thumbnail_path = $this->Tv_model->thumbnail_path;
			$data['json']->programs = $this->Tv_model->getProgramNewRelease($start, 30);
			$json = $this->load->view('json',$data,TRUE);
			$this->cache->redis->save($cache_key, $json, $this->cache_time);
			$this->output->set_content_type('application/json')->set_output($json);
		}

/* 		$this->load->view('json',$data); */
	}

	public function getProgramSearch($start = 0)
	{
		$keyword = $this->input->get('keyword');

		$cache_key = "$this->namespace_prefix:getProgramSearch:$keyword:$start:$this->device";

		$memData = $this->cache->redis->get($cache_key);
		if(FALSE != $memData)
		{
			$this->output->set_content_type('application/json')->set_output($memData);
		}
		else
		{
			$this->load->model('Tv_model','', TRUE);
			$this->Tv_model->setDevice($this->device);
			$this->Tv_model->setIsTH($this->isTH);

			$data = array('json' => new stdClass());
			$data['json']->thumbnail_path = $this->Tv_model->thumbnail_path;
			$data['json']->programs = $this->Tv_model->getProgramSearch($keyword, $start);
			$json = $this->load->view('json',$data,TRUE);
			$this->cache->redis->save($cache_key, $json, $this->cache_time);
			$this->output->set_content_type('application/json')->set_output($json);
		}
	}

	public function getProgramlist($program_id, $start = 0)
	{
		// $cache_key = "$this->namespace_prefix:getProgramlist:$program_id:$start";
		$cache_key = sprintf("%s:%s:%s:%s", $this->namespace_prefix, "getProgramlist", $program_id, $start);

		$memData = $this->cache->redis->get($cache_key);
		if(FALSE != $memData)
		{
			$this->output->set_content_type('application/json')->set_output($memData);
		}
		else
		{
			$this->load->model('Tv_model','', TRUE);
			$data = array('json' => new stdClass());
			$data['json']->programlists = $this->Tv_model->getProgramlist($program_id,$start);
			$json = $this->load->view('json', $data, TRUE);
			$this->cache->redis->save($cache_key, $json, $this->cache_time);

			$this->storeKey($this->getProgramKey($program_id), $cache_key);

			$this->output->set_content_type('application/json')->set_output($json);
		}

/* 		$this->load->view('json',$data); */
	}

	public function getProgramlistDetail($programlist_id)
	{
		$this->load->model('Tv_model','', TRUE);
		$data = array('json' => new stdClass());
		$data['json']->programlist = $this->Tv_model->getProgramlistDetail($programlist_id);
		$this->load->view('json',$data);
	}

	public function getProgramDetail($program_id)
	{
		$cache_key = "$this->namespace_prefix:getProgramDetail:$$program_id";

		$memData = $this->cache->redis->get($cache_key);
		if(FALSE != $memData)
		{
			$this->output->set_content_type('application/json')->set_output($memData);
		}
		else
		{
			$this->load->model('Tv_model','', TRUE);
			$data = array('json' => new stdClass());
			$data['json'] = $this->Tv_model->getProgramDetail($program_id);
			$json = $this->load->view('json', $data, TRUE);
			$this->cache->redis->save($cache_key, $json, $this->cache_time);
			$this->output->set_content_type('application/json')->set_output($json);
		}
	}


	public function viewProgramlist($programlist_id)
	{
		$this->load->model('Tv_model','', TRUE);
		$this->Tv_model->viewProgramlist($programlist_id);
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
			$this->cache->redis->clean();
			echo 'Flush MemCached';
		}
		else {
			$this->cache->redis->delete($this->cache->redis->lAll("API:WHATSNEW"));
			$this->cache->redis->delete("API:WHATSNEW");
			$this->cache->redis->delete($this->showProgramKey($program_id));
			$this->cache->redis->delete($this->getProgramKey($program_id));
		}
	}

	public function encryptData()
	{
		$this->load->model('Tv_model','', TRUE);
		$this->Tv_model->encryptData();
	}
}
