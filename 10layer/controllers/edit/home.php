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
	
	function index() {
		$this->load->library("tluserprefs");
		$contenttype=$this->tluserprefs->get_last_menu();
		if (!empty($contenttype)) {
			redirect("edit/".$contenttype);
		} else {
			$types=$this->model_content->get_content_types(true);
			redirect("edit/".$types[0]->urlid);
		}
	}
}

/* End of file home.php */
/* Location: ./system/application/controllers/edit/home.php */