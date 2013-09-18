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
			$this->load->library('user_agent');
			if ($this->agent->is_robot()) {
				//Robot, don't log a hit
				$this->returndata();
				return;
			}
			$dbdata = array();
			$referrer = $this->agent->referrer();
			$parts = explode("/", $referrer);
			$id = array_pop($parts);
			if ($this->mongo_db->where(array("_id" => $id))->count("content") > 0) {
				$dbdata["content_type"] = array_pop($parts);
				$dbdata["content_id"] = $id;
			}
			
			$dbdata["url"] = $referrer;
			
			$dbdata["ip_address"] = $this->input->ip_address();
			
			$dbdata["user_agent"] = $this->input->user_agent();
			
			$dbdata["is_mobile"] = $this->agent->is_mobile();

			$dbdata["timestamp"] = time();

			$this->mongo_db->insert("hits", $dbdata);
			
			$this->returndata();
		}

	}