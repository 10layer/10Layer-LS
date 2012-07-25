<?php
	/**
	 * Edit Frame class
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
		
		public function fulldisplay($type,$urlid=false) {
			$data["type"]=$type;
			$data["urlid"]=$urlid;
			$this->load->library("tluserprefs");
			//$this->tluserprefs->click_menu($type);
			$this->load->view("content/frames/edit",$data);
		}
	}

/* End of file frame.php */
/* Location: ./system/application/controllers/edit/frame */