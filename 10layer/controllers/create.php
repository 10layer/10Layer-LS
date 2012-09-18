<?php
	/**
	 * Create class
	 * 
	 * @extends CI_Controller
	 * @package 10Layer
	 * @subpackage Controllers
	 */
	class Create extends CI_Controller {

		/**
		 * __construct function.
		 * 
		 * @access public
		 * @return void
		 */
		public function __construct() {
			parent::__construct();
		}
		
		public function _remap() {
			$data["type"]=$this->uri->segment(1);
			$this->load->view("content/create",$data);
		}
	}

/* End of file frame.php */
/* Location: ./system/application/controllers/edit/frame */