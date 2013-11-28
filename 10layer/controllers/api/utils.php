<?php
	require_once('10layer/system/TL_Api.php');
	
	/**
	 * Users class
	 * 
	 * Useful Utils that don't really fit in anywhere else
	 *
	 * @extends Controller
	 */
	 
	class Utils extends TL_Api {
		
		/**
		 * __construct function.
		 * 
		 * @access public
		 * @return void
		 */
		public function __construct() {
			parent::__construct();
			$this->secure=$this->_check_secure();
		}
		
		public function shorturl() {
			$this->load->library("shorturl");
			$url = $this->input->get_post("url");
			if (empty($url)) {
				show_error("Url cannot be empty");
			}
			$this->data["shorturl"] = $this->shorturl->url($url);
			$this->data["url"] = $url;
			$this->returndata();
		}

		public function countries() {
			$data["countries"] = $this->mongo_db->get("countries");
			$this->load->view("json", array("data"=>$data));
		}

		public function provinces() {
			if (!isset($this->vars["country"]) || empty($this->vars["country"])) {
				$this->vars["country"] = "South Africa";
			}
			$data["country"] = $this->vars["country"];
			$data["provinces"] = $this->mongo_db->get_where("provinces", array("country"=>$this->vars["country"]));
			$this->load->view("json", array("data"=>$data));
		}
		
	}

/* End of file utils.php */
/* Location: ./system/application/controllers/api/ */