<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class View extends CI_Controller {
	private $cache_time = 30;
	function __construct()
	{
		parent::__construct();
		$this->load->model('Tv_model','', TRUE);
	}

	public function index()
	{
		$this->load->view('welcome_message');
	}
	
	public function video($id = '')
	{
		$this->load->helper('url');
		$id = explode('_',$id);
		redirect('http://www.tvthailand.me/watch/'.$id[0].'/0', 'location', 301);
		return;
		
		$key = 'v!3wPr0gr@ml!$t0nW3b'.$id[0];
		if(md5($key) == $id[1]) {
			$data = $this->Tv_model->getProgramlistDetail($id[0]);
			// print_r($data);
			$data['title'] = 'TV Thailand on Web : <strong>'.$data['program_title']
			.'</strong> ตอนที่ '.$data['programlist_ep'].' วันที่ '.$data['programlist_date'].' - '.$data['programlist_epname'];
			// .' View : '.number_format($data['programlist_count']);

			// $data['title'] = 'TV Thailand on Web : '.$data['program_title'];
			// $data['title'] = 'TV Thailand';
			$this->load->view('header',$data);
			$this->load->view('videolist',$data);	
			$this->load->view('footer');
		}
	}
	
	public function viewProgramlist($id = '')
	{
		$this->load->helper('url');
		redirect('http://www.tvthailand.me/watch/'.$id.'/0', 'location', 301);
		return;
		
		$key = 'v!3wPr0gr@ml!$t0nW3b'.$id;
		// echo 'index.php/view/video/'.$id.'_'.md5($key);
		echo $id.'_'.md5($key);
		$data = $this->Tv_model->getProgramlistDetail($id);
		$data['title'] = 'TV Thailand on Web : <strong>'.$data['show_title']
			.'</strong> ตอนที่ '.$data['ep'].' วันที่ '.$data['date'].' - '.$data['title'];
			// .' View : '.number_format($data['programlist_count']);

			// $data['title'] = 'TV Thailand on Web : '.$data['program_title'];
			// $data['title'] = 'TV Thailand';
		$this->load->view('header',$data);
		$this->load->view('videolist',$data);
		$this->load->view('footer');
	}
}