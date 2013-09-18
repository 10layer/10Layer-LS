<?php
	require_once('10layer/system/TL_Api.php');
	
	/**
	 * Logging class
	 * 
	 * Example: logging/hit/article/2013-01-01-blah
	 *
	 * @extends CI_Controller
	 */
	class Logging extends TL_Api {
		
		/**
		 * __construct function.
		 * 
		 * @access public
		 * @return void
		 */
		public function __construct() {
			parent::__construct();
		}

		/**
		 * Hit method
		 * 
		 * Records a hit against an item
		 *
		 */
		public function hit() {
			$content_type = $this->uri->segment(4);
			$id = $this->uri->segment(5);
			if (isset($this->vars["ip_address"])) {
				$ip_address = $this->vars["ip_address"];
			} else {
				$ip_address = $this->input->ip_address();
			}
			if (isset($this->vars["user_agent"])) {
				$user_agent = $this->vars["user_agent"];
			} else {
				$user_agent = $this->input->user_agent();
			}
			$this->mongo_db->insert("hits", array("content_id"=>$id, "content_type"=>$content_type, "timestamp"=>time(), "ip_address" => $ip_address, "user_agent" => $user_agent));
			$this->returndata();
		}

	}