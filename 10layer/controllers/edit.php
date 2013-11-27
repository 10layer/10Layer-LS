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
			if ($this->uri->total_rsegments() > 2) {
				$content_type=$this->uri->rsegment(2);
				$urlid=$this->uri->rsegment(3);
				if ($content_type == "undefined") {
					$content_type = $this->model_content->check_content_type($urlid);
				}
			} else {
				$urlid = $this->uri->rsegment(2);
				$content_type = $this->model_content->check_content_type($urlid);
			}
			if (empty($content_type)) {
				show_error("Could not find content type");
			}
			$data["content_type"]=$content_type;
			$data["urlid"]=$urlid;
			$data["content_types"]=$this->model_content->get_content_types_list();
			$this->load->view("content/edit",$data);
		}
	}

/* End of file frame.php */
/* Location: ./system/application/controllers/edit/frame */