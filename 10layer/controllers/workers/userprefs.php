<?php
	/**
	 * Userprefs class
	 * 
	 * Records user preferences so that stuff can automagically customise itself to user behaviour
	 *
	 * @extends Controller
	 * @package 10Layer
	 * @subpackage Controllers
	 */
	class Userprefs extends CI_Controller {

		/**
		 * __construct function.
		 * 
		 * @access public
		 * @return void
		 */
		public function __construct() {
			parent::__construct();
		}
		
		public function user_setup() {
			$userid=$this->session->get_userdata("id");
			$this->mongo_db->insert("userprefs",array("userid"=>$userid));
		}
		
		public function click_menu($menuitem) {
			$this->mongo_db->where(array("userid"=>1))->increment("userprefs",array($menuitem=>1));
		}
		
		
	}

/* End of file .php */
/* Location: ./system/application/controllers/ */