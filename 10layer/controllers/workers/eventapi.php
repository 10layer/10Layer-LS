<?php
	/**
	 * EventApi class
	 * 
	 * Catch events thrown up by our middleware and do stuff with it
	 *
	 * @extends Controller
	 * @package 10Layer
	 * @subpackage Controllers
	 */
	class EventApi extends CI_Controller {

		/**
		 * __construct function.
		 * 
		 * @access public
		 * @return void
		 */
		public function __construct() {
			parent::__construct();
			$this->load->library("tlsecurity");
			$this->tlsecurity->ignore_security();
		}
		
		public function delete($urlid) {
			print "Delete";
		}
		
		public function edit($urlid) {
			print "Edit";
		}
		
		public function create($urlid) {
		
		}
		
		public function publish($sectionid) {
		
		}
		
		public function update_content() {
		
		}
	}

/* End of file eventapi.php */
/* Location: ./system/application/controllers/workers */