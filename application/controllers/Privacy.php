<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Privacy extends CI_Controller {

	function __construct() {
		parent::__construct();
	}

	public function index() {
		$this->load->view('privacy/mobile');
	}
	
	public function mobile() {
		$this->load->view('privacy/mobile');		
	}
	
	public function windows() {
		$this->load->view('privacy/windows');		
	}
}