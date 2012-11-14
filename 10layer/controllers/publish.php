<?php
	/**
	 * Publish class
	 * 
	 * @extends Controller
	 */
	class Publish extends CI_Controller {

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
			$collection_type=$this->uri->segment(2);
			
		}
	}

/* End of file .php */
/* Location: ./system/application/controllers/ */