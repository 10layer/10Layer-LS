<?php
	/**
	 * Home class
	 * 
	 * @extends Controller
	 */
	class Home extends CI_Controller {

		/**
		 * __construct function.
		 * 
		 * @access public
		 * @return void
		 */
		public function __construct() {
			parent::__construct();
		}
		
		public function index() {
			$data["zones"]=$this->tenlayer->section("home");
			$this->load->view("home", $data);
		}
	}

/* End of file home.php */
/* Location: ./system/application/controllers/ */