<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Api extends CI_Controller {
	private $cache_time = 600;
/*
	private $memcache_host = 'mc1.ec2.memcachier.com';
	private $memcache_user = '6424f9';
	private $memcache_pwd = '2c9ee8a022eab9d1dd18';
*/
	function __construct()
	{
		parent::__construct();
/* 		$this->load->model('Tv_model','', TRUE); */
 		$this->load->library('MemcacheSASL','','memcached');
 		$this->memcached->addServer(getenv("MEMCACHIER_SERVERS"), '11211');
		$this->memcached->setSaslAuthData(getenv("MEMCACHIER_USERNAME"), getenv("MEMCACHIER_PASSWORD"));
/*
 		$this->memcached->addServer($this->memcache_host, '11211');
		$this->memcached->setSaslAuthData($this->memcache_user, $this->memcache_pwd);
*/
	}

	public function index()
	{
		$this->load->view('welcome_message');
	}
	
	public function getMessageiOS()
	{
		$memData = $this->memcached->get('getMessageiOS');
		if(FALSE != $memData)
		{
			echo $memData;
		}
		else
		{	
			$data['json']->id = "201210131545";
			$data['json']->title = "*ประกาศ*";
			$data['json']->message = "TV Thailand version 2.2\n- Support iOS 6\n- Support iPhone 5";
			
			$this->load->model('Tv_model','', FALSE);
			$buttons = array();
/* 			array_push($buttons, $this->Tv_model->createButton('Read Review','http://goo.gl/Vdoba')); */
			array_push($buttons, $this->Tv_model->createButton('Upgrade','https://itunes.apple.com/us/app/tv-thailand/id458429827?ls=1&mt=8'));
			array_push($buttons, $this->Tv_model->createButton('Fan Page','https://www.facebook.com/TV.Thailand'));
			
			$data['json']->buttons = $buttons;
			$json = $this->load->view('json', $data, TRUE);
			$this->memcached->add('getMessageiOS', $json, $this->cache_time);
			echo $json;
		}
	}
	
	public function getMessageAndroid()
	{
		$memData = $this->memcached->get('getMessageAndroid');
		if(FALSE != $memData)
		{
			echo $memData;
		}
		else
		{
			$data['json']->id = "201210051951";
			$data['json']->title = "*ประกาศ*";
			$data['json']->message = "ขณะนี้ Server กำลังมีปัญหาทำงานช้า ทีมงานกำลังเร่งแก้ไขอยู่ครับ";
			$obj = new stdClass();
			$obj->versionCode = 58;
			$obj->apk = "http://goo.gl/1BJYa";
			$data['json']->app_version = $obj;
			
			$this->load->model('Tv_model','', FALSE);
			$buttons = array();
/* 			array_push($buttons, $this->Tv_model->createButton('Update Fix Bug','http://goo.gl/1BJYa')); */
/* 			array_push($buttons, $this->Tv_model->createButton('Read','http://goo.gl/NeDgB')); */
			array_push($buttons, $this->Tv_model->createButton('Fan Page','https://www.facebook.com/TV.Thailand'));
			
			$data['json']->buttons = $buttons;

			$json = $this->load->view('json', $data, TRUE);
			$this->memcached->add('getMessageAndroid', $json, $this->cache_time);
			echo $json;
		}
	}
	
	public function getInHouseAd()
	{
		$memData = $this->memcached->get('getInHouseAd');
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
			$this->memcached->add('getInHouseAd', $json, $this->cache_time);
			echo $json;
		}
	}

	public function getCategory()
	{	
		$memData = $this->memcached->get('getCategory');
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
			$this->memcached->add('getCategory', $json, $this->cache_time);
			echo $json;
		}

		/* $this->load->view('json',$data,TRUE) */;
	}
	
	public function getProgram($cat_id = 0, $start = 0)
	{
		$memData = $this->memcached->get('getProgram-'.$cat_id.'-'.$start);
		if(FALSE != $memData)
		{
			echo $memData;
		}
		else
		{
			$this->load->model('Tv_model','', TRUE);
			$data['json']->thumbnail_path = $this->Tv_model->thumbnail_path;
			$data['json']->programs = $this->Tv_model->getProgram($cat_id,$start);
			$json = $this->load->view('json',$data,TRUE);
			$this->memcached->add(('getProgram-'.$cat_id.'-'.$start), $json, $this->cache_time);
			echo $json;
		}
		
/* 		$this->load->view('json',$data); */
	}
	
	public function getWhatsNew($start = 0)
	{
		$memData = $this->memcached->get('getWhatsNew-'.$start);
		if(FALSE != $memData)
		{
			echo $memData;
		}
		else
		{
			$this->load->model('Tv_model','', TRUE);
			$data['json']->thumbnail_path = $this->Tv_model->thumbnail_path;
			$data['json']->programs = $this->Tv_model->getProgram(0,$start,40);
			$json = $this->load->view('json',$data,TRUE);
			$this->memcached->add(('getWhatsNew-'.$start), $json, $this->cache_time);
			echo $json;
		}
		
/* 		$this->load->view('json',$data); */
	}
	
	public function getProgramSearch($start = 0)
	{
		$keyword = $this->input->get('keyword');
		$memData = $this->memcached->get('getProgramSearch-'.$keyword.'-'.$start);
		if(FALSE != $memData)
		{
			echo $memData;
		}
		else
		{
			$this->load->model('Tv_model','', TRUE);
			$data['json']->thumbnail_path = $this->Tv_model->thumbnail_path;
			$data['json']->programs = $this->Tv_model->getProgramSearch($keyword,$start);
			$json = $this->load->view('json',$data,TRUE);
			$this->memcached->add(('getProgramSearch-'.$keyword.'-'.$start), $json, $this->cache_time);
			echo $json;
		}
	}

	public function getProgramlist($program_id, $start = 0)
	{
		$memData = $this->memcached->get('getProgramlist-'.$program_id.'-'.$start);
		if(FALSE != $memData)
		{
			echo $memData;
		}
		else
		{
			$this->load->model('Tv_model','', TRUE);
			$data['json']->programlists = $this->Tv_model->getProgramlist($program_id,$start);
			$json = $this->load->view('json', $data, TRUE);
			$this->memcached->add(('getProgramlist-'.$program_id.'-'.$start), $json, $this->cache_time);
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
		$memData = $this->memcached->get('getProgramDetail-'.$program_id);
		if(FALSE != $memData)
		{
			echo $memData;
		}
		else
		{
			$this->load->model('Tv_model','', TRUE);
			$data['json'] = $this->Tv_model->getProgramDetail($program_id);
			$json = $this->load->view('json', $data, TRUE);
			$this->memcached->add(('getProgramDetail-'.$program_id), $json, $this->cache_time);
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
/*
		$this->load->model('Tv_model','', TRUE);
  		$this->Tv_model->iosToken($deviceid,$devicetoken);
*/
  		
		## TEST ##
		$this->load->helper('url');
		$url = "http://www.thetvthailand.com/api/register_ios_token/$deviceid/$devicetoken/";
		redirect($url, 'location', 301);
		
	}
	
	public function apns()
	{
		$getdata = $this->input->get();
		
	}
	
	############### private function ##############
	
	public function requeryProgramLastest()
	{
		$this->load->model('Tv_model','', TRUE);
		$this->Tv_model->requeryProgramLastest();
	}
	
	public function memFlush()
	{
		$this->memcached->flush();
		echo 'Flush MemCached';
	}
	public function clearCache()
	{
		/*
		$this->requeryProgramLastest();
*/
		$this->memFlush();
	}
	
	public function encryptData()
	{
		$this->load->model('Tv_model','', TRUE);
		$this->Tv_model->encryptData();
	}
}