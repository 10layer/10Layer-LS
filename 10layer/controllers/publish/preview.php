<?php
	/**
	 * Preview class
	 * 
	 * @extends CI_Controller
	 * @package 10Layer
	 * @subpackage Controllers
	 */
	class Preview extends CI_Controller {

		/**
		 * __construct function.
		 * 
		 * @access public
		 * @return void
		 */
		public function __construct() {
			parent::__construct();
			$this->load->library("tlsecurity");
			$this->tlsecurity->ignore_security();
			$this->load->library("templater");
			$this->templater->outline();
		}
		
		public function index() {
			$this->render_header();
			$this->render_home();
			$this->render_footer();
		}
		
		public function render_header() {
			$this->load->view("publish/templates/includes/header");
		}
		
		public function render_footer() {
			$this->load->view("publish/templates/includes/footer");
		}
		
		public function render_home() {
			$this->load->view("publish/templates/sections/home");
		}
	}

/* End of file preview.php */
/* Location: ./system/application/controllers/publish/ */