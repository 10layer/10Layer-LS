<?php
/**
 * Home class.
 * 
 * @extends CI_Controller
 * @package 10Layer
 * @subpackage Controllers
 */
class Home extends CI_Controller {

	function __construct() {
		parent::__construct();
		//$this->load->library("google_analytics");
	}
	
	function index() {
		$data["menu1_active"]="home";
		$this->load->view('templates/header',$data);
		$this->load->view('home/custom',$data);
		$this->load->view("templates/footer");
	}
}

/* End of file home.php */
/* Location: ./system/application/controllers/default.php */