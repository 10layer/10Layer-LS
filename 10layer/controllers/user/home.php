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
	}
	
	function index() {
		$data["menu1_active"]="user";
		$this->load->view('templates/header',$data);
		$this->load->view("templates/footer");
	}
}

/* End of file home.php */
/* Location: ./system/application/controllers/user/home.php */