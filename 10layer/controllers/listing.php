<?php
	/**
	 * Listing Controller
	 * 
	 * @extends CI_Controller
	 * @package 10Layer
	 * @subpackage Controllers
	 */
	class Listing extends CI_Controller {

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
			$this->load->library("socketio");
			$content_type=$this->uri->rsegment(2);
			$data["content_type"]=$content_type;
			$data["content_types"]=$this->model_content->get_content_types_list();
			$this->load->view("content/listing",$data);
		}
	}

/* End of file frame.php */
/* Location: ./system/application/controllers/edit/frame */