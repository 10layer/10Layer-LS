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
			$this->load->library("socketio");
			$type=$this->uri->rsegment(2);
			$urlid=$this->uri->rsegment(3);
			$data["content_type"]=$type;
			$data["urlid"]=$urlid;
			$data["content_types"]=$this->model_content->get_content_types_list();
			$this->load->view("content/edit",$data);
		}
	}

/* End of file frame.php */
/* Location: ./system/application/controllers/edit/frame */