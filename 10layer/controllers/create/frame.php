<?php
	/**
	 * Create Frame class
	 * 
	 * @extends CI_Controller
	 * @package 10Layer
	 * @subpackage Controllers
	 */
	class Frame extends CI_Controller {

		/**
		 * __construct function.
		 * 
		 * @access public
		 * @return void
		 */
		public function __construct() {
			parent::__construct();
		}
		
		public function display($type) {
			$data["type"]=$type;
			$this->load->library("tluserprefs");
			//$this->tluserprefs->click_menu($type);
			$this->load->view("content/frames/create",$data);
		}
	}

/* End of file frame.php */
/* Location: ./system/application/controllers/edit/frame */