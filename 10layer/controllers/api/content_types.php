<?php
	/**
	 * Content_Types class
	 * 
	 * @extends Controller
	 */
	class Content_types extends CI_Controller {
		
		protected $secure=false;
		protected $_render=true;
		private $_start_time=0;
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
		}
		
		/**
		 * index function.
		 * 
		 * Returns all the content types
		 *
		 * @access public
		 * @return void
		 */
		public function index() {
			if (!$this->secure) {
				//You shouldn't be here. Bail.
				$this->data["error"]=true;
				$this->data["msg"]="Denied";
				$this->returndata();
				return false;
			}
			$this->data["content"] = $this->mongo_db->order_by(array("_id"))->get("content_types");
			$this->returndata();
		}
		
		/**
		 * save function.
		 * 
		 * @access public
		 * @return void
		 */
		public function save() {
			$data = json_decode(file_get_contents("php://input"));
			$this->json_check();
			if(empty($data)) {
				$this->data["error"]=true;
				$this->data["msg"][]="We did not receive data";
				$this->returndata();
				return false;
			}
			if (!$this->secure) {
				$this->data["error"]=true;
				$this->data["msg"][]="You do not have permission to save";
				$this->returndata();
				return false;
			}
			//Expect JSON
			$content_type=$data->content_type;
			$id = $content_type->id;
			$content_type->_id=$id;
			unset($content_type->id);
			print $id;
			//unset($content_type->_id);
			$this->data["msg"]="Saving";
			$this->mongo_db->where(array("_id"=>$id))->delete("content_types");
			$this->mongo_db->insert("content_types", $content_type);
			$this->data["content"]=$data;
			$this->returndata();
		}
		
		/**
		 * _check_secure function.
		 * 
		 * Returns true if we've sent a valid API key, else false
		 *
		 * @access private
		 * @return boolean
		 */
		private function _check_secure() {
			$api_key=$this->input->get_post("api_key");
			$api_key=trim($api_key);
			if (empty($api_key)) {
				return false;
			}
			$comp_api_key=$this->config->item('api_key');
			if (empty($comp_api_key)) {
				return false;
			}
			if ($comp_api_key==$api_key) {
				return true;
			}
			return false;
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

/* End of file .php */
/* Location: ./system/application/controllers/ */