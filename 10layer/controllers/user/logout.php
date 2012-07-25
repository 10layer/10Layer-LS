<?php
/**
 * Logout class.
 * 
 * @extends CI_Controller
 * @package 10Layer
 * @subpackage Controllers
 */
class Logout extends CI_Controller {

	public function __construct() {
		parent::__construct();
		$this->load->library("tlsecurity");
		$this->tlsecurity->ignore_security();
	}
	
	public function index() {
		$this->tlsecurity->logout();
	}
}

/* End of file logout.php */
/* Location: ./system/application/controllers/user/logout.php */