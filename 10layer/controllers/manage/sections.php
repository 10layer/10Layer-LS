<?php
	/**
	 * Sections class
	 * 
	 * Handles the configuration of sections
	 *
	 * @extends Controller
	 * @package 10Layer
	 * @subpackage Controllers
	 */
	class Sections extends CI_Controller {

		/**
		 * __construct function.
		 * 
		 * @access public
		 * @return void
		 */
		public function __construct() {
			parent::__construct();
			$this->load->model("model_site_sections","sections");
			$this->load->model("model_section");
			$this->load->model("model_zones","zones");
		}
		
		public function index() {
			$data["menu1_active"]="manage";
			$data["menu2_active"]="manage/sections";
			$data["sections"]=$this->sections->getAll();
			$this->load->view('templates/header',$data);
			$this->load->view("manage/sections/section_chooser");
			$this->load->view("templates/footer");
		}
		
		public function section($urlid) {
			$data["menu1_active"]="manage";
			$data["menu2_active"]="manage/sections";
			$section=$this->sections->getByIdORM($urlid);
			$data["section"]=$section;
			$data["content_types"]=$this->model_content->get_content_types();
			$zones=array();
			if (!empty($section->getData()->zones)) {
				foreach($section->getData()->zones as $zone) {
					$zones[]=$this->zones->getByIdORM($zone)->getData();
				}
			}
			$data["zones"]=$zones;
			$this->load->view('templates/header',$data);
			$this->load->view("manage/sections/section_config");
			$this->load->view("templates/footer");
		}
		
		
		
		
		
		
		public function dosave($urlid) {
			$returndata=array("error"=>false,"msg"=>"");
			//$section=$this->sections->getByIdORM($urlid);
			
			$section = $this->db->query("select * from content where urlid='".$urlid."'")->row();
			
			$existing_zones = $this->db->query("select c.id, c.title, c.urlid from content c join content_content cc on c.id = cc.content_link_id where  c.content_type_id = 21 and cc.content_id = ". $section->id)->result();
			
			
			foreach($existing_zones as $zone) {
					
					//delete ranked items
					$this->db->where("zone_urlid",$zone->urlid)->delete("ranking");
					$this->db->where("zone_urlid",$zone->urlid)->delete("ranking_stage");
					
					$this->db->where("content_link_id",$zone->id)->delete("content_content");
					$this->db->where("content_id",$zone->id)->delete("content_content");
					$this->db->where("content_id",$zone->id)->delete("section_zones");
					$this->db->where("id",$zone->id)->delete("content");
			}
			
			
			//grab the defined zones
			
			$titles=$this->input->post("content_title");
			
			//print_r($section); die();
			//echo $section->content_id." - ".$urlid; die();
			
			$max=sizeof($titles);
			
			$content_ids=array();
			$contentobj=new TLContent();

			$contentobj->setContentType("zones");

			for($x=0;$x<$max;$x++) {
				$contentobj->clearData();
				
				foreach($contentobj->getFields() as $field) {
					$fieldval=$this->input->post($field->tablename."_".$field->name);
					if (empty($fieldval)) {
						$contentobj->{$field->name}="";
					} else {
						$contentobj->{$field->name}=$fieldval[$x];
					}
				}
				
				$contentobj->transformFields();
				$validation=$contentobj->validateFields();
				
				if (!$validation["passed"]) {
					$returndata["error"]=true;
					$returndata["msg"]="Failed to create {$this->_contenttypeurlid}";
					$returndata["info"]=implode("<br />\n",$validation["failed_messages"]);
				} else {
					
					$contentobj->insert();
					$content_ids[]=$contentobj->getData()->content_id;
					//generate auto content
					if($contentobj->getData()->auto == 1){
						$this->model_section->generate_zone_content($section->id, $contentobj->getData()->content_id);
							
					}
					
				}
			}
			
			//print_r($contentobj);
			
			//Link them
			if (!$returndata["error"]) {
				foreach($content_ids as $content_id) {
					$this->db->insert("content_content", array("content_id"=>$section->id, "content_link_id"=>$content_id));
				}
				print "<script>document.domain=document.domain;</script><textarea>";
				print json_encode($returndata);
				print "</textarea>";
			}
		}
	}

/* End of file sections.php */
/* Location: ./system/application/controllers/manage/ */