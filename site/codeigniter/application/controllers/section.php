<?php
	/**
	 * Section class
	 * 
	 * @extends Controller
	 */
	class Section extends CI_Controller {

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
			$section = $this->uri->segment(2);
			$data["section"]=$this->tenlayer->get($section);
			if (empty($data["section"])) {
				show_404();
			}
			$this->load->view("section", $data);
		}
	}

/* End of file section.php */
/* Location: ./system/application/controllers/ */