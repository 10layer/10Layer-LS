<?php
	/**
	 * Section class
	 * 
	 * @extends CI_Controller
	 * @package 10Layer
	 * @subpackage Controllers
	 */
	class Section extends CI_Controller {

		/**
		 * __construct function.
		 * 
		 * @access public
		 * @return void
		 */
		public function __construct() {
			parent::__construct();
			$this->load->model("model_collections");
			$this->load->model("model_site_sections","sections");
			$this->load->model("model_zones","zones");
			//$this->output->enable_profiler(true);
		}
	
		function _remap() {
			$urlid=$this->uri->segment(3);
			$data["menu1_active"]="publish";
			$data["menu2_active"]="publish/section/$urlid";
			//$this->load->model("model_site_sections");
			//$section=$this->model_site_sections->getByIdORM($urlid)->getData();
			//print_r($section);
			$section=$this->sections->getByIdORM($urlid);
			$sectiondata=$section->getData();
			
			$zones=array();
			if(is_array($sectiondata->zones)) {
				foreach($sectiondata->zones as $zone) {
					$zones[]=$this->zones->getByIdORM($zone);
				}
			}
			//$data["layouts"]=$this->model_section->getLayouts($urlid);
			//$data["subsections"]=$this->model_section->getSubSections($urlid);
			$data["content"]=array();
			$data["section_id"]=$section->content_id;
			$data["section_urlid"]=$section->urlid;
			$data["zones"]=$zones;
			$data["section"]=$section;
			$this->load->view('templates/header',$data);
			$this->load->view("publish/section");
			$this->load->view("templates/footer");
		}
	}

/* End of file section.php */
/* Location: ./system/application/controllers/publish/ */