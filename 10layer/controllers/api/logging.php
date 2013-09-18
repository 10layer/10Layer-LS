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
			$this->mongo_db->insert("hits", array("content_id"=>$id, "content_type"=>$content_type, "timestamp"=>time()));
			$this->returndata();
		}

	}