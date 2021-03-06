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
			$this->load->library("socketio");
			$data["type"]=$this->uri->segment(1);
			$data["content_type"]=$this->uri->segment(2);
			$data["content_types"]=$this->model_content->get_content_types_list();
			$this->load->view("content/create",$data);
		}
	}

/* End of file frame.php */
/* Location: ./system/application/controllers/edit/frame */