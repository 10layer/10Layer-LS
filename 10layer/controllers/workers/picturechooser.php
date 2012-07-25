<?php
	/**
	 * PictureChooser class
	 * 
	 * @extends CI_Controller
	 * @package 10Layer
	 * @subpackage Controllers
	 */
	class PictureChooser extends CI_Controller {

		/**
		 * __construct function.
		 * 
		 * @access public
		 * @return void
		 */
		public function __construct() {
			parent::__construct();
		}
		
		public function browse() {
			$this->load->view("templates/frameheader");
			$this->load->view("picturechooser/browse");
		}
		
		public function edit($id, $CKEditorFuncNum=4) {
			
			$pic=$this->model_content->getById($id);
			$data["title"]=$pic->title;
			$data["urlid"]=$pic->urlid;
			$data["CKEditorFuncNum"]=$CKEditorFuncNum;
			$this->load->view("templates/frameheader");
			$this->load->view("picturechooser/edit",$data);
		}
	}

/* End of file .php */
/* Location: ./system/application/controllers/ */