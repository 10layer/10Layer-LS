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
			//Check that we actually have a system. Else let's bail to the Setup
			if ($this->checkSetup()) {
				return true;
			}
			//Check if the controller specifically tells us to ignore security checks
			if ($this->_ignore_security) {
				return true;
			}
			//Check if this path is available without logging in
			$path=$this->ci->uri->segment_array(0);
			if ($this->ci->model_user->security_check_exclude_path($path)) {
				return true;
			}
			$this->_check_security_data(); //Make sure we have the correct collection "permissions" in the DB
			$this->checkLogin();
			$this->checkStatus();
			$this->checkUrl();
		}
		
		public function checkSetup() {
			$collections=$this->ci->mongo_db->collections();
			
			//If we don't have any collections, let us set up an admin user
			$uri=$this->ci->uri->uri_string();
			if ($uri == "setup/admin") {
				return empty($collections);
			}
			
			if (empty($collections)) {
				$this->ci->load->view("setup/first");
				print $this->ci->output->get_output();
				die();
			}
			return false;
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
			    	return true;
			    } else {
			    	$data["error"]=1;
			    	$this->ci->load->view("user/login",$data);
					print $this->ci->output->get_output();
					die();
				}
			}

			$loggedin=$this->ci->model_user->check_login();
			if (!$loggedin) {
				$data["error"]=0;
				$this->ci->load->view("user/login",$data);
				print $this->ci->output->get_output();
				die();
			}
			return true;
		}
		
		public function checkOtp($otp) {
			$result=$this->ci->model_user->otp_login($otp);
			if (!$result) {
				$this->logout();
				redirect("/home");
			}
			return true;
		}
		
		/**
		 * logout function.
		 * 
		 * @access public
		 * @return void
		 */
		public function logout() {
			$data=array("id"=>false,"name"=>false,"urlid"=>false, "permission"=>false);
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
			$status=$this->ci->model_user->get_user_status($this->ci->session->userdata("id"));
			if ($status=="Active") {
				return true;
			}
			$this->ci->load->view("user/denied",array("status"=>"You cannot log in to your account. Your account status is: {$status}"));
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
			$userid = $this->ci->session->userdata("id");
			$my_permission = $this->ci->session->userdata("permission");
			$found = false;
			$permissions = $this->ci->mongo_db->get("permissions");
			$segments = $this->ci->uri->segment_array();
			while ($found == false && (!empty($segments))) {
				$url = implode("/", $segments);
				if (!empty($url) && $url[0]!='/') {
					$url='/'.$url;
				}
				$permission = $this->ci->mongo_db->get_where_one("permissions", array("url"=>$url));
				if (!empty($permission->url)) {
					if (in_array($my_permission, $permission->allow)) {
						$found = true;
						return true; // Totally allowed to do this, bail here
					} elseif (in_array($my_permission, $permission->deny)) {
						$found = true;
						$this->_permissionDeny(); // Access Denied!
					}
				}
				array_pop($segments);
			}
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
		
		public function random_pass($length=6, $strength=0) {
		//Props http://www.webtoolkit.info/php-random-password-generator.html
			$vowels = 'aeuy';
			$consonants = 'bdghjmnpqrstvz';
			if ($strength & 1) {
				$consonants .= 'BDGHJLMNPQRSTVWXZ';
			}
			if ($strength & 2) {
				$vowels .= "AEUY";
			}
			if ($strength & 4) {
				$consonants .= '23456789';
			}
			if ($strength & 8) {
				$consonants .= '@#$%';
			}
			$password = '';
			$alt = time() % 2;
			for ($i = 0; $i < $length; $i++) {
				if ($alt == 1) {
					$password .= $consonants[(rand() % strlen($consonants))];
					$alt = 0;
				} else {
					$password .= $vowels[(rand() % strlen($vowels))];
					$alt = 1;
				}
			}
			return $password;
		}
		
		public function get_api_keys() {
			$api_keys = $this->ci->mongo_db->get("api_keys");
			if (empty($api_keys)) {
				$api_keys = $this->_gen_api_keys();
			}
			return $api_keys;
		}
		
		public function api_key_permission($api_key) {
			$api_key = $this->ci->mongo_db->get_where_one("api_keys", array("api_key"=>$api_key));
			if (empty($api_key->permission)) {
				return false;
			}
			
			return $api_key->permission;
		}
		
		protected function _gen_api_keys() {
			$this->ci->load->helper('string');
			$this->ci->mongo_db->delete("api_keys");
			$permissions = array("viewer", "editor", "administrator");
			foreach($permissions as $permission) {
				$api_key = new stdClass();
				$api_key->api_key = random_string("unique");
				$api_key->permission = $permission;
				$this->ci->mongo_db->insert("api_keys", $api_key);
			}
			return $this->ci->mongo_db->get("api_keys");
		}

		protected function _check_security_data() {
			$permission_test = $this->ci->mongo_db->get_one("permissions");
			if (empty($permission_test->url)) {
				$dbdata = array(
					array("url"=>"/setup", "allow"=>array("administrator"), "deny"=>array("editor", "viewer")),
					array("url"=>"/create", "allow"=>array("editor", "administrator"), "deny"=>array("viewer")),
					array("url"=>"/edit", "allow"=>array("editor", "administrator"), "deny"=>array("viewer")),
					array("url"=>"/publish", "allow"=>array("editor", "administrator"), "deny"=>array("viewer")),
				);
				foreach($dbdata as $row) {
					$this->ci->mongo_db->insert("permissions", $row);
				}
				$this->ci->mongo_db->add_index("permissions", array("url"));
			}
		}
		
	}

?>