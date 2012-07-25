<?php
/**
 * Home class.
 * 
 * @extends CI_Controller
 * @package 10Layer
 * @subpackage Controllers
 */
class Home extends CI_Controller {

	function __construct() {
		parent::__construct();
	}
	
	/*function index() {
		$this->load->model("model_site_sections");
		$tmp=$this->model_site_sections->getAll(1);
		if(empty($tmp[0])) {
			show_error("No sections created");
		}
		redirect("publish/section/".$tmp[0]->urlid);
	}*/
	
	public function index() {
		$this->load->model("model_collections");
		$collections=$this->model_collections->getAll();

		redirect("publish/collection/".$collections[0]->urlid);
		/*$data["menu1_active"]="publish";
		$this->load->view('templates/header',$data);
		$this->load->view("templates/footer");*/
	}
}

/* End of file home.php */
/* Location: ./system/application/controllers/publish/home.php */