<?php
	/**
	 * Data class
	 * 
	 * @extends Controller
	 */
	class Data extends CI_Controller {

		/**
		 * __construct function.
		 * 
		 * @access public
		 * @return void
		 */
		public function __construct() {
			parent::__construct();
		}
		
		public function index() {
		}

		public function save() {
			if (empty($this->vars->collection)) {
				$this->data["error"] = true;
				$this->["msg"] = "Collection must be set";
				$this->returndata();
				return;
			}
			if (empty($this->vars->ip_addr)) {
				$this->data["error"] = true;
				$this->["msg"] = "Ip_addr must be set";
				$this->returndata();
				return;
			}
			$this->mongo_db->insert("data", $this->vars);
			$this->returndata();
		}
	}

/* End of file data.php */
/* Location: ./system/application/controllers/api/ */