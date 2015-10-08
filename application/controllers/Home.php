<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Home extends CI_Controller {
	
	function __construct()
	{
		parent::__construct();
		$this->load->driver('cache');		
		$this->load->model('Tv_model','', TRUE);
	}
	public function index($cat_id = 0 ,$start = 0)
	{
		$cache_key = 'index';
		$memData = $this->cache->redis->get($cache_key);
		if(FALSE != $memData)
		{
			$this->output->set_output($memData);
		}
		else
		{
			$this->load->library('table');
			$this->load->helper('html');
			$this->load->helper('url');
	
			$data['title'] = 'รายการล่าสุด';
			$data['programs'] = $this->Tv_model->getProgram($cat_id, $start);
			$data['thumbnail_path'] = $this->Tv_model->thumbnail_path;
			$this->load->view('header',$data);
			$this->load->view('home',$data);
			$this->load->view('footer');
			
			$output = $this->output->get_output();
			$this->cache->redis->save($cache_key, $output, 21600);
		}
	}
	
	public function geoip()
	{
		echo $_SERVER['GEOIP_COUNTRY_CODE'].' - '.$_SERVER['GEOIP_COUNTRY_NAME'];
	}
}

/* End of file home.php */
/* Location: ./application/controllers/Home.php */