<?php
/**
 * Home class.
 * 
 * @extends CI_Controller
 * @package 10Layer
 * @subpackage Controllers
 */
class Home extends CI_Controller {

	public function __construct() {
		parent::__construct();
		//$this->load->library("google_analytics");
	}
	
	public function index() {
		$data["menu1_active"]="home";
		$this->load->view('templates/header',$data);
		$this->load->view('home/custom',$data);
		$this->load->view("templates/footer");
	}

	public function _remap() {
		// $data["content_type"] = $this->uri->segment(2);
		// $data["menu1_active"]="home";
		// $this->load->view('templates/header',$data);
		$this->load->library("socketio");
		$this->load->view('home/home');
		// $this->load->view("templates/footer");
	}
	
}

/* End of file home.php */
/* Location: ./system/application/controllers/default.php */