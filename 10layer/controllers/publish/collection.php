<?php
	/**
	 * Collection class
	 * 
	 * @extends CI_Controller
	 * @package 10Layer
	 * @subpackage Controllers
	 */
	class Collection extends CI_Controller {

		/**
		 * __construct function.
		 * 
		 * @access public
		 * @return void
		 */
		public function __construct() {
			parent::__construct();
			//$this->load->model("model_section");
			$this->load->model("model_collections");
			$this->load->model("model_site_sections","sections");
			$this->load->model("model_zones","zones");
			//$this->output->enable_profiler(true);
		}
	
		public function _remap() {
			
			if ($this->uri->total_segments()==4) {
				$this->layout();
				return true;
			}
			
			$urlid=$this->uri->segment(3);
			
			if($urlid == ""){
				redirect("publish/collection/section");
			}else{
				$content_type=$this->model_content->get_content_type($urlid);
				$data["content_type"]=$content_type;
				$data["menu1_active"]="publish";
				$data["menu2_active"]="publish/collection/$urlid";
			
				$this->load->view('templates/header',$data);
				$this->load->view("publish/collection");
				$this->load->view("templates/footer");
			}
			
			
			
			
		}
		
		protected function layout() {
			$cturlid=$this->uri->segment(3);
			$urlid=$this->uri->segment(4);
			$content_type=$this->model_content->get_content_type($cturlid);
			$data["content_type"]=$content_type;
			$data["menu1_active"]="publish";
			$data["menu2_active"]="publish/collection/$cturlid";
			$section=$this->sections->getByIdORM($urlid);
			$sectiondata=$section->getData();
			$zone_content_type_id=$this->db->where("urlid","zones")->get("content_types")->row()->id;
			$the_zones = $this->db->query("select c.id, c.title, c.urlid from content c join content_content cc on c.id = cc.content_link_id where  c.content_type_id = $zone_content_type_id and cc.content_id = ". $section->content_id)->result();
			$zones=array();
			if(sizeof($the_zones) > 0){
				foreach($the_zones as $zone){
					$zones[]=$this->zones->getByIdORM($zone->id)->getData();
				}
			}
			
			//print_r($sectiondata); die();
			
			/*
$zones=array();
			if(is_array($sectiondata->zones)) {
				foreach($sectiondata->zones as $zone) {
					$zones[]=$this->zones->getByIdORM($zone);
				}
			}
*/
			
			//$data["layouts"]=$this->model_section->getLayouts($urlid);
			//$data["subsections"]=$this->model_section->getSubSections($urlid);
			$data["content"]=array();
			$data["section_id"]=$section->content_id;
			$data["section_urlid"]=$section->urlid;
			$data["zones"]=$zones;
			//$data["section"]=$section;
			$data["section_data"]=$sectiondata;
			$data["stylesheets"]=array("/tlresources/file/css/publish/section.css");
			$this->load->view('templates/header',$data);
			$this->load->view("publish/section",$data);
			$this->load->view("templates/footer");
		}
	}

/* End of file collection.php */
/* Location: ./system/application/controllers/publish/ */