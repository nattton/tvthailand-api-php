<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Api1 extends CI_Controller {
	private $namespace_prefix = 'API_';
	private $cache_time = 3600;
	private $country_code = '';
	private $ex_cache = '';
	private $isTH = FALSE;
/*
	private $memcache_host = 'mc1.ec2.memcachier.com';
	private $memcache_user = '6424f9';
	private $memcache_pwd = '2c9ee8a022eab9d1dd18';
*/
	function __construct()
	{
		parent::__construct();
/* 		$this->load->model('Tv_model','', TRUE); */
 		$this->load->driver('cache');
		
		if (array_key_exists('HTTP_CF_IPCOUNTRY', $_SERVER)) {
			$this->country_code = $_SERVER["HTTP_CF_IPCOUNTRY"];
			if($this->country_code == 'TH')
			{
				$this->ex_cache = '_TH';
				$this->isTH = TRUE;
			}
		}
	}

	public function index()
	{
		$this->load->view('welcome_message');
	}
	
	public function getMessageiOS()
	{
		$cache_key = $this->namespace_prefix.'getMessageiOS';
		$memData = $this->cache->memcached->get($cache_key);
		if(FALSE != $memData)
		{
			echo $memData;
		}
		else
		{	
			$data['json']->id = "201211130200";
			$data['json']->title = "*Notice*";
			$data['json']->message = "Please Rating & Review";
			
			$this->load->model('Tv_model','', FALSE);
			$buttons = array();
/* 			array_push($buttons, $this->Tv_model->createButton('Read Review','http://goo.gl/Vdoba')); */
			array_push($buttons, $this->Tv_model->createButton('Rating & Review','https://itunes.apple.com/us/app/tv-thailand/id458429827?ls=1&mt=8'));
			array_push($buttons, $this->Tv_model->createButton('Fan Page','https://www.facebook.com/TV.Thailand'));
			
			$data['json']->buttons = $buttons;
			$json = $this->load->view('json', $data, TRUE);
			$this->cache->memcached->save($cache_key, $json, $this->cache_time);
			echo $json;
		}
	}
	
	public function getMessageAndroid()
	{
		$cache_key = $this->namespace_prefix.'getMessageAndroid';
		$memData = $this->cache->memcached->get($cache_key);
		if(FALSE != $memData)
		{
			echo $memData;
		}
		else
		{
			$data['json']->id = "201211130200";
			$data['json']->title = "*Notice*";
			$data['json']->message = "Please Rating & Review";
			$obj = new stdClass();
			$obj->versionCode = 58;
			$obj->apk = "http://goo.gl/1BJYa";
			$data['json']->app_version = $obj;
			
			$this->load->model('Tv_model','', FALSE);
			$buttons = array();
 			array_push($buttons, $this->Tv_model->createButton('Rating & Review','http://goo.gl/1BJYa')); 
/* 			array_push($buttons, $this->Tv_model->createButton('Read','http://goo.gl/NeDgB')); */
			array_push($buttons, $this->Tv_model->createButton('Fan Page','https://www.facebook.com/TV.Thailand'));
			
			$data['json']->buttons = $buttons;

			$json = $this->load->view('json', $data, TRUE);
			$this->cache->memcached->save($cache_key, $json, $this->cache_time);
			echo $json;
		}
	}
	
	public function getInHouseAd()
	{
		$cache_key = $this->namespace_prefix.'getInHouseAd';
		$memData = $this->cache->memcached->get($cache_key);
		if(FALSE != $memData)
		{
			echo $memData;
		}
		else
		{
			$this->load->model('Tv_model','', TRUE);
			$data['json']->delayStart = 5000;
			$data['json']->ads = $this->Tv_model->getAds();
			$json = $this->load->view('json', $data, TRUE);
			$this->cache->memcached->save($cache_key, $json, $this->cache_time);
			echo $json;
		}
	}

	public function getCategory()
	{	
		$cache_key = $this->namespace_prefix.'getCategory';
		$memData = $this->cache->memcached->get($cache_key);
		if(FALSE != $memData)
		{
			echo $memData;
		}
		else
		{
			$this->load->model('Tv_model','', TRUE);
			$data['json']->ads = $this->Tv_model->getAds();
			$data['json']->categories = $this->Tv_model->getCategory();
			$json = $this->load->view('json', $data, TRUE);
			$this->cache->memcached->save($cache_key, $json, $this->cache_time);
			echo $json;
		}

		/* $this->load->view('json',$data,TRUE) */;
	}
	
	public function getProgram($cat_id = 0, $start = 0)
	{
		$cache_key = $this->namespace_prefix.'getProgram_'.$cat_id.'_'.$start.$this->ex_cache;
		$memData = $this->cache->memcached->get($cache_key);
		if(FALSE != $memData)
		{
			echo $memData;
		}
		else
		{
			$this->load->model('Tv_model','', TRUE);
			$data['json']->thumbnail_path = $this->Tv_model->thumbnail_path;
			$data['json']->programs = $this->Tv_model->getProgram($cat_id, $start, 20, $this->isTH);
			$json = $this->load->view('json',$data,TRUE);
			$this->cache->memcached->save($cache_key, $json, $this->cache_time);
			echo $json;
		}
		
/* 		$this->load->view('json',$data); */
	}
	
	public function getWhatsNew($start = 0)
	{
		$cache_key = $this->namespace_prefix.'getWhatsNew_'.$start.$this->ex_cache;;
		$memData = $this->cache->memcached->get($cache_key);
		if(FALSE != $memData)
		{
			echo $memData;
		}
		else
		{
			$this->load->model('Tv_model','', TRUE);
			$data['json']->thumbnail_path = $this->Tv_model->thumbnail_path;
			$data['json']->programs = $this->Tv_model->getProgram(0, $start, 20, $this->isTH);
			$json = $this->load->view('json',$data,TRUE);
			$this->cache->memcached->save($cache_key, $json, $this->cache_time);
			echo $json;
		}
		
/* 		$this->load->view('json',$data); */
	}
	
	public function getProgramSearch($start = 0)
	{
		$keyword = $this->input->get('keyword');
		$cache_key = $this->namespace_prefix.'getProgramSearch_'.$keyword.'_'.$start.$this->ex_cache;
		$memData = $this->cache->memcached->get($cache_key);
		if(FALSE != $memData)
		{
			echo $memData;
		}
		else
		{
			$this->load->model('Tv_model','', TRUE);
			$data['json']->thumbnail_path = $this->Tv_model->thumbnail_path;
			$data['json']->programs = $this->Tv_model->getProgramSearch($keyword, $start, $this->isTH);
			$json = $this->load->view('json',$data,TRUE);
			$this->cache->memcached->save($cache_key, $json, $this->cache_time);
			echo $json;
		}
	}

	public function getProgramlist($program_id, $start = 0)
	{
		$cache_key = $this->namespace_prefix.'getProgramlist_'.$program_id.'_'.$start;
		$memData = $this->cache->memcached->get($cache_key);
		if(FALSE != $memData)
		{
			echo $memData;
		}
		else
		{
			$this->load->model('Tv_model','', TRUE);
			$data['json']->programlists = $this->Tv_model->getProgramlist($program_id,$start);
			$json = $this->load->view('json', $data, TRUE);
			$this->cache->memcached->save($cache_key, $json, $this->cache_time * 12);
			echo $json;
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
		$cache_key = $this->namespace_prefix.'getProgramDetail_'.$program_id;
		$memData = $this->cache->memcached->get($cache_key);
		if(FALSE != $memData)
		{
			echo $memData;
		}
		else
		{
			$this->load->model('Tv_model','', TRUE);
			$data['json'] = $this->Tv_model->getProgramDetail($program_id);
			$json = $this->load->view('json', $data, TRUE);
			$this->cache->memcached->save($cache_key, $json, $this->cache_time);
			echo $json;
		}
	}
	

	public function viewProgramlist($programlist_id)
	{
		$this->load->model('Tv_model','', TRUE);
		$this->Tv_model->viewProgramlist($programlist_id);
		
		## TEST ##
/*
		$this->load->helper('url');
		$url = "http://www.thetvthailand.com/api/view_programlist/22869/";
		redirect($url, 'location', 301);
*/
	}
	
	public function iosToken($deviceid,$devicetoken)
	{
		$this->load->model('Tv_model','', TRUE);
  		$this->Tv_model->iosToken($deviceid,$devicetoken);
  		
		## TEST ##
/*
		$this->load->helper('url');
		$url = "http://www.thetvthailand.com/api/register_ios_token/$deviceid/$devicetoken/";
		redirect($url, 'location', 301);
*/
		
	}
	
	public function apns()
	{
		$getdata = $this->input->get();
		
	}
	
	############### private function ##############

	public function clearCache($program_id = 0)
	{
		if($program_id == 0)
		{
			$this->cache->memcached->flush();
			echo 'Flush MemCached';
		}
		else {
			for($start = 0;$start <= 400;$start = $start + 20)
			{
				$this->cache->memcached->delete($this->namespace_prefix.'getProgram_0_'.$start);
				$this->cache->memcached->delete($this->namespace_prefix.'getProgram_0_'.$start.'_TH');
				$this->cache->memcached->delete($this->namespace_prefix.'getProgramlist_'.$program_id.'_'.$start);
			}
						
			$this->load->model('Tv_model','', TRUE);
			$program = $this->Tv_model->getProgramInfo($program_id);
			if($program != FALSE)
			{
				print_r($program);
				for($start = 0;$start <= 400;$start = $start + 20)
				{
					$this->cache->memcached->delete($this->namespace_prefix.'getProgram_'.$program->category_id.'_'.$start);
					$this->cache->memcached->delete($this->namespace_prefix.'getProgram_'.$program->channel_id.'_'.$start);
					$this->cache->memcached->delete($this->namespace_prefix.'getProgram_'.$program->category_id.'_'.$start.'_TH');
					$this->cache->memcached->delete($this->namespace_prefix.'getProgram_'.$program->channel_id.'_'.$start).'_TH';
				}
			}
			else
			{
				echo 'getProgramInfo False';
			}
		}
		
		
	}
	
	public function encryptData()
	{
		$this->load->model('Tv_model','', TRUE);
		$this->Tv_model->encryptData();
	}
}