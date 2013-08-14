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
		
	}

/* End of file utils.php */
/* Location: ./system/application/controllers/api/ */