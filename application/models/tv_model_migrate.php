<?php

class Tv2_model extends CI_Model
{
	private $domain = 'http://tvthailand.herokuapp.com';
	public $thumbnail_path = "http://thumbnail.makathon.com/tv/";
	function __construct()
	{
		parent::__construct();
	}
	
	function getContentUrl($url)
	{
		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $url);
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true);
		return  curl_exec($ch); 
		/*
if(strpos($urlWithoutProtocol, "youtube.com") >0 ){
			return file_get_contents($urlWithoutProtocol);
		}
		else
		{
			$request         = "";
			$isRequestHeader = false;
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $urlWithoutProtocol);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
			curl_setopt($ch, CURLOPT_HEADER, (($isRequestHeader) ? 1 : 0));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_USERAGENT, "tv-makathon.herokuapp.com");
			$response = curl_exec($ch);
			curl_close($ch);
			return $response;
		}
*/
	}
	
	function get()
	{
		return $this->db->get('groups')->result();
	}
	
	function createButton($label ='', $url='')
	{
		// type = button , cancel
		$obj = new stdClass();
		$obj->label = $label;
		$obj->url = $url;
		return $obj;
	}
	
	function getAds()
	{
		// Ads
		$url = $this->domain.'/api/get_advertise/';
		$content = $this->getContentUrl($url);
		$jObj = json_decode($content);
		
		return $jObj->response->ads;
	}
	
	
	function getCategory()
	{
		// Categories
		$url = $this->domain.'/api/get_category/';
		$content = $this->getContentUrl($url);
		$jObj = json_decode($content);
		
		$categoryList = array();
		$categories = $jObj->response->categories;
		foreach($categories as $category)
		{
			$cat = new stdClass();
			$cat->category_id = str_replace('/', '_', $category->id);
			$cat->category_name = $category->name;
			array_push($categoryList, $cat);
		}
		
		return $categoryList;
	}
	
	function getProgramSearch($keyword = '',$start = 0)
	{
		if($cat_id==0) $cat_id = 'lastest';
		
		$url = $this->domain.'/api/get_program_search/'.$start.'/?keyword='.$keyword;
		$content = $this->getContentUrl($url);
		$jObj = json_decode($content);
		
		$result = new stdClass();
		$result->thumbnail_path = $jObj->response->thumbnail_path;
		
		$programList = array();
		$programs = $jObj->response->programs;
		foreach($programs as $program)
		{
			$pro = new stdClass();
			$pro->program_id = $program->id;
			$pro->title = $program->title;
			$pro->thumbnail = $program->thumbnail;
			$pro->time = $program->time;
			array_push($programList, $pro);
		}
		$result->programs = $programList;
		
		return $result;
	}
		
	function getProgram($cat_id, $start = 0)
	{
		if($cat_id == '0') {
			$cat_id = 'lastest';
		}
		
		$cat_id = str_replace('_', '/', $cat_id);
		
		$url = $this->domain.'/api/get_program/'.$cat_id.'/'.$start.'/';
		
		$content = $this->getContentUrl($url);
		$jObj = json_decode($content);
		
		$result = new stdClass();
/* 		$result->url = $url; */
		$result->thumbnail_path = $jObj->response->thumbnail_path;
		
		$programList = array();
		$programs = $jObj->response->programs;
		foreach($programs as $program)
		{
			$pro = new stdClass();
			$pro->program_id = $program->id;
			$pro->title = $program->title;
			$pro->thumbnail = $program->thumbnail;
			$pro->time = $program->time;
			array_push($programList, $pro);
		}
		$result->programs = $programList;
		
		return $result;
	}
	
	function getProgramlist($program_id, $start = 0)
	{
		$url = $this->domain.'/api/get_programlist/'.$program_id.'/'.$start.'/';
		$content = $this->getContentUrl($url);
		$jObj = json_decode($content);
		
		
		$programlistList = array();
		$programlists = $jObj->response->programlists;
		foreach($programlists as $programlist)
		{
			$pro = new stdClass();
			$pro->programlist_id = $programlist->id;
			$pro->ep = $programlist->ep;
			$pro->epname = $programlist->epname;
			$pro->youtube_encrypt = $programlist->videoid_encrypt;
			$pro->src_type = strval($programlist->src);
			$pro->date = $programlist->date;
			$pro->count = strval($programlist->views);
			$pro->pwd = $programlist->pwd;
			array_push($programlistList, $pro);
		}
		return $programlistList;
	}
	
	function getProgramDetail($program_id)
	{
		$program_id = intval($program_id);
		
		$url = $this->domain.'/api/get_program_detail/'.$program_id.'/';
		$content = $this->getContentUrl($url);
		$jObj = json_decode($content);
		
		$response = $jObj->response;
		$pro = new stdClass();
		$pro->program_id = $response->id;
		$pro->title = $response->title;
		$pro->thumbnail = $response->thumbnail;
		$pro->detail = $response->detail;
		$pro->time = $response->time;
		$pro->count = $response->views;
		
		return $pro;
	}
}
?>