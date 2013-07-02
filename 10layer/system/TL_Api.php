<?php
	/**
	 * TL_Api class
	 * 
	 * Base class for API controllers
	 *
	 * @extends Controller
	 */
	class TL_Api extends CI_Controller {
		
		protected $secure=false;
		protected $_render=true;
		protected $_start_time=0;
		public $key;
		public $m; //Memcached
		public $cached = false;
		public $vars=array();
		public $data;
		
		/**
		 * __construct function.
		 * 
		 * @access public
		 * @return void
		 */
		public function __construct() {
			$this->_start_time=microtime(true);
			parent::__construct();
			$this->data=array(
				"error"=>false,
				"timestamp"=>time(),
				"msg"=>"",
				"content"=>array()
			);
			$this->load->library("tlsecurity");
			$this->tlsecurity->ignore_security();
			$this->secure=$this->_check_secure();
			$json = json_decode(file_get_contents("php://input"));
			if (!empty($json)) {
				$this->vars = $json;
			} else {
				$this->vars=array_merge($_GET, $_POST);
			}
			$this->m = new Memcached();
			$this->m->addServer('localhost', 11211);
			$akey = $this->vars;
			unset($akey["jsoncallback"]);
			unset($akey["_"]);
			$this->key = md5($this->uri->uri_string()."?".http_build_query($akey));
			$this->data = $this->m->get($this->key);
			if (!empty($this->data)) {
				$this->cached = true;
				$this->data["cached"] = true;
				$this->data["memcached_key"] = $this->key;
			}
		}

		public function cache() {
			$this->m->set($this->key, $this->data);
			return true;
		}
		
		protected function enforce_secure() {
			$this->secure=$this->_check_secure();
			if (!$this->secure) {
				//You shouldn't be here. Bail.
				$this->data["error"]=true;
				$this->data["msg"]="Denied";
				$this->returndata();
				print $this->output->get_output();
				die();
			}
		}
				
		/**
		 * _check_secure function.
		 * 
		 * Returns true if we've sent a valid API key, else false
		 *
		 * @access private
		 * @return boolean
		 */
		protected function _check_secure() {
			$api_key=$this->input->get_post("api_key");
			$api_key=trim($api_key);
			if (empty($api_key)) {
				return false;
			}
			$permission=$this->tlsecurity->api_key_permission($api_key);
			if (empty($permission)) {
				return false;
			}
			
			if ($permission == "viewer") { //Viewer shouldn't be able to do anything that requires security
				return false;
			}
			return true;
		}
		
		/**
		 * returndata function.
		 * 
		 * If $this->_render is true, print the results as json
		 *
		 * @access protected
		 * @return void
		 */
		protected function returndata() {
			$end_time=microtime(true);
			$processing_time=$end_time-$this->_start_time;
			$this->data["processing_time"]=$processing_time;
			if ($this->_render) {
				$this->load->view("json",array("data"=>$this->data));
			}
		}
		
		protected function show_error($msg) {
			$this->data["error"]=true;
			$this->data["message"]=$msg;
			$this->returndata();
			print $this->output->get_output();
			die();
		}
		
		protected function json_check($filename="") {
			switch (json_last_error()) {
				case JSON_ERROR_NONE:
					break;
				case JSON_ERROR_DEPTH:
					show_error($filename.' JSON error - Maximum stack depth exceeded');
					break;
				case JSON_ERROR_STATE_MISMATCH:
					show_error($filename.' JSON error - Underflow or the modes mismatch');
					break;
				case JSON_ERROR_CTRL_CHAR:
	            	show_error($filename.' JSON error - Unexpected control character found');
					break;
				case JSON_ERROR_SYNTAX:
					show_error($filename.' JSON error - Syntax error, malformed JSON');
					break;
				case JSON_ERROR_UTF8:
					show_error($filename.' JSON error - Malformed UTF-8 characters, possibly incorrectly encoded');
					break;
				default:
					show_error($filename.' JSON error - Unknown error');
				break;
			}
		}
	}

/* End of file TL_Api.php */
/* Location: ./10layer/system/ */
