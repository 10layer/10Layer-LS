<?php
	/**
	 * Edit Frame class
	 * 
	 * @extends CI_Controller
	 * @package 10Layer
	 * @subpackage Controllers
	 */
	class Edit extends CI_Controller {

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
			$type=$this->uri->segment(1);
			$urlid=$this->uri->segment(2);
			$data["type"]=$type;
			$data["urlid"]=$urlid;
			$this->load->view("content/edit",$data);
		}
	}

/* End of file frame.php */
/* Location: ./system/application/controllers/edit/frame */