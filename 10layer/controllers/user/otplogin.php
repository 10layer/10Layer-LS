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
				$password = $this->input->post("password");
				$error = false;
				if (!empty($password)) {
					$password = trim($password);
					$password_confirm = $this->input->post("password_confirm");
					if ($password != $password_confirm) {
						$error="Passwords don't match";
					}
					if (strlen($password) < 6) {
						$error="Password must be at least 6 characters long";
					}
					if (empty($password)) {
						$error="Password cannot be empty";
					}
					if (empty($error)) {
						$this->model_user->update($user->_id, array("otp"=>"", "password"=>$password, "email"=>$user->email));
						redirect("/home");
					}
				}
				$this->load->view("user/reset_password", array("error"=>$error));
			}
		}
	
	/* End of file otp.php */
	/* Location: ./system/application/controllers/users/otp.php */
	
?>