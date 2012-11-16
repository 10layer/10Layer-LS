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
			if (!$this->secure) {
				//You shouldn't be here. Bail.
				$this->data["error"]=true;
				$this->data["msg"]="Denied";
				$this->returndata();
			}
		}
		
		public function index() {
			$this->data["content"] = $this->mongo_db->get("content_types");
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
	}

/* End of file .php */
/* Location: ./system/application/controllers/ */