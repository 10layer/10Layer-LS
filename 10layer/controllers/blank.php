<?php
	/**
	 * Blank class
	 * 
	 * Returns a blank page for iframe sources
	 *
	 * @extends Controller
	 */
	class Blank extends CI_Controller {

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
			$this->load->view("blank");
		}
	}

/* End of file .php */
/* Location: ./system/application/controllers/ */