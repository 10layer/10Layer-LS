<?php
	/**
	 * Login class
	 * 
	 * @extends CI_Controller
	 * @package 10Layer
	 * @subpackage Controllers
	 */
	class Login extends CI_Controller {

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
			redirect("/home");
		}
		
		/**
		 * email_password function.
		 * 
		 * @access protected
		 * @param String $password
		 * @return void
		 */
		protected function email_password($email, $password) {
			
		}
	}

/* End of file retrieve_password.php */
/* Location: ./system/application/controllers/user/ */