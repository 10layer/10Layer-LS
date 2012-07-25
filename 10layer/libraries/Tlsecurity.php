<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 
	/**
	 * 10Layer Security Class
	 *
	 * This class handles security
	 *
	 * @package		10Layer
	 * @subpackage	Libraries
	 * @category	Libraries
	 * @author		Jason Norwood-Young
	 * @link		http://10layer.com
	 */
	
	class Tlsecurity {
		protected $ci=false;
		protected $_ignore_security=false;
		
		/**
		 * __construct function.
		 * 
		 * @access public
		 * @return void
		 */
		public function __construct() {
			$this->ci=&get_instance();
			//Inheret ignore_security settings from previously initiated Security library
			if (isset($this->ci->tlsecurity)) {
				$this->_ignore_security=$this->ci->tlsecurity->checkIgnoreSecurity();
			}
		}
		
		public function securityHook() {
			//Check if the controller specifically tells us to ignore security checks
			if ($this->_ignore_security) {
				return true;
			}
			//Check if this path is available without logging in
			$path=$this->ci->uri->segment_array(0);
			if ($this->ci->model_user->security_check_exclude_path($path)) {
				return true;
			}
			$this->checkLogin();
			$this->checkStatus();
			$this->checkUrl();
		}
		
		/**
		 * checkLogin function.
		 * 
		 * @access public
		 * @return boolean
		 */
		public function checkLogin() {
			$dologin=$this->ci->input->post("dologin");
			if (!empty($dologin)) {
			    $data=array("id"=>false,"name"=>false,"urlid"=>false);
			    $this->ci->session->unset_userdata($data);
			    $result=$this->doLogin();
			    if ($result) {
			    	$this->ci->load->library("tluserprefs");
			    	$this->ci->tluserprefs->user_setup();
			    	return true;
			    } else {
			    	$data["error"]=1;
			    	$this->ci->load->view("user/login",$data);
					print $this->ci->output->get_output();
					die();
				}
			}

			$loggedin=$this->ci->model_user->checklogin();
			if (!$loggedin) {
				$data["error"]=0;
				$this->ci->load->view("user/login",$data);
				print $this->ci->output->get_output();
				die();
			}
			return true;
		}
		
		public function checkOtp($otp) {
			$result=$this->ci->model_user->otpLogin($otp);
			if ($result) {
				redirect("/manage/users/my_account");
			} else {
				$this->logout();
				redirect("/home");
			}
		}
		
		/**
		 * logout function.
		 * 
		 * @access public
		 * @return void
		 */
		public function logout() {
			$data=array("id"=>false,"name"=>false,"urlid"=>false);
			$this->ci->session->unset_userdata($data);
			redirect(base_url());
		}
		
		protected function doLogin() {
			$email=$this->ci->input->post("email");
			$password=$this->ci->input->post("password");
			return $this->ci->model_user->login($email,$password);
		}
		
		protected function checkExcludePath($path) {
			$paths=$this->ci->model_user->security_exclude_paths();
			
		}
		
		protected function checkStatus() {
			//$status_id=$this->ci->session->userdata("status_id");
			//print $this->ci->session->userdata("id");
			$status_id=$this->ci->model_user->get_user_status($this->ci->session->userdata("id"));
			//print $status_id;
			if ($status_id==1) {
				return true;
			}
			//$data=array("id"=>false,"name"=>false,"urlid"=>false);
			//$this->ci->session->unset_userdata($data);
			$status=$this->ci->model_user->get_status($status_id);
			$this->ci->load->view("user/denied",array("status"=>"You cannot log in to your account. Your account status is: {$status->name}"));
			print $this->ci->output->get_output();
			die();
		}
		
		protected function checkUrl() {
			$url=$this->ci->uri->uri_string();
			if (!empty($url) && $url[0]!='/') {
				$url='/'.$url;
			}
			//Root url
			if ($url=="/home" || empty($url)) {
				return true;
			}
			
			//User accounts
			if ($url=="/manage/users/my_account") {
				return true;
			}
			//Exact match
			$permission=$this->ci->model_user->checkUrlPermission($this->ci->session->userdata("id"), $url);
			if ($permission) {
				$this->_permissionDeny();
			}
			//One of the index urls
			$permission=$this->ci->model_user->checkUrlPermission($this->ci->session->userdata("id"), $url."/home");
			if ($permission) {
				$this->_permissionDeny();
			}
			//Remap url
			$pieces=$this->ci->uri->segment_array();
			unset($pieces[sizeof($pieces)-1]);
			$url="/".implode("/",$pieces)."/*";
			$permission=$this->ci->model_user->checkUrlPermission($this->ci->session->userdata("id"), $url);
			if ($permission) {
				$this->_permissionDeny();
			}
			//Last check - look for inheretence
			$permission_id=$this->ci->model_user->getUserPermission($this->ci->session->userdata("id"));
			$allowedurls=$this->ci->model_user->getUrlsByPermission($permission_id);
			foreach($allowedurls as $allowedurl) {
				$pieces=$this->ci->uri->segment_array();
				while(!empty($pieces)) {
					array_pop($pieces);
					$url="/".implode("/",$pieces);
					if ($url==$allowedurl->url) {
						$this->_permissionDeny();
					}
				}
			}
			//You made it!
			return true;
		}
		
		protected function _permissionDeny() {
			$this->ci->load->view("user/denied",array("status"=>"Denied"));
			print $this->ci->output->get_output();
			die();
		}
		
		public function ignore_security() {
			$this->_ignore_security=true;
		}
		
		public function checkIgnoreSecurity() {
			return $this->_ignore_security;
		}
		
		public function user_id() {
			return $this->ci->session->userdata("id");
		}
		
	}

?>