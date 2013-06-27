<?php
	/**
	 * Article class
	 * 
	 * @extends Controller
	 */
	class Article extends CI_Controller {

		/**
		 * __construct function.
		 * 
		 * @access public
		 * @return void
		 */
		public function __construct() {
			parent::__construct();
		}
		
		public function _remap() {
			$id = $this->uri->segment(2);
			$data["content"] = $this->tenlayer->get($id);
			if (empty($data["content"])) {
				show_404();
			}
			$this->load->view("article", $data);
		}
	}

/* End of file article.php */
/* Location: ./system/application/controllers/ */