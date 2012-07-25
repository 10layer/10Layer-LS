<?php
		/**
		 * OtpLogin class
		 * 
		 * @extends CI_Controller
		 * @package 10Layer
		 * @subpackage Controllers
		 */
		class OtpLogin extends CI_Controller {
	
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
			
			public function _remap() {
				$otp=$this->uri->segment(3);
				
				$user=$this->model_user->getByOtp($otp);
				if (empty($user->id)) {
					show_404("user/otplogin");
				}
				$this->tlsecurity->checkOtp($otp);
				$this->load->view("user/reset_password");
			}
		}
	
	/* End of file otp.php */
	/* Location: ./system/application/controllers/users/otp.php */
	
?>