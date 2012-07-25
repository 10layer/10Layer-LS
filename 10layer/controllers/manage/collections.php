<?php
	/**
	 * Collections class
	 * 
	 * Handles the configuration of sections
	 *
	 * @extends Controller
	 * @package 10Layer
	 * @subpackage Controllers
	 */
	class Collections extends CI_Controller {
		/**
		 * __construct function.
		 * 
		 * @access public
		 * @return void
		 */
		public function __construct() {
			parent::__construct();
			$this->load->model("model_collections");
			$this->load->model("model_zones","zones");
		}
		
		public function index() {
			$data["menu1_active"]="manage";
			$data["menu2_active"]="manage/collections";
			$data["collections"]=$this->model_collections->getAll();
			$this->load->view('templates/header',$data);
			$this->load->view("manage/sections/collections");
			$this->load->view("templates/footer");
		}
		
		public function collection($urlid) {
			$collection=$this->model_collections->get($urlid);
			$this->load->model($collection->model,"collection");
			$data["sections"]=$this->collection->getAll(false,false,true);
			$data["collectionurlid"]=$collection->urlid;
			$data["menu1_active"]="manage";
			$data["menu2_active"]="manage/collections";
			$this->load->view('templates/header',$data);
			$this->load->view("manage/sections/section_chooser");
			$this->load->view("templates/footer");
		}
		
		public function section($collectionurlid,$urlid) {
			$collection=$this->model_collections->get($collectionurlid);
			$this->load->model($collection->model,"sections");
			$data["menu1_active"]="manage";
			$data["menu2_active"]="manage/collections";
			$section=$this->sections->getByIdORM($urlid);
			
			
			

			
			$data["section"]=$section;
			$data["content_types"]=$this->model_content->get_content_types();
			$zones=array();
			//pull zones directly
			$the_zones = $this->db->query("select c.id, c.title, c.urlid from content c join content_content cc on c.id = cc.content_link_id where  c.content_type_id = 21 and cc.content_id = ". $section->content_id)->result();
			
			//echo $this->db->last_query();
			
			if(sizeof($the_zones) > 0){
				foreach($the_zones as $zone){
					$zones[]=$this->zones->getByIdORM($zone->id)->getData();
				}
			}
				
			$data["zones"]=$zones;
			
			//print_r($zones); die();
			
			$this->load->view('templates/header',$data);
			$this->load->view("manage/sections/section_config");
			$this->load->view("templates/footer");
		}
		
		public function dosave($urlid) {
			$returndata=array("error"=>false,"msg"=>"");
			$section=$this->sections->getByIdORM($urlid);
			$data=$section->getData();
			//Find and delete existing Zones
			if (is_array($data->zones)) {
				foreach($data->zones as $zone) {
					$this->db->where("content_link_id",$zone)->delete("content_content");
					$this->db->where("content_id",$zone)->delete("content_content");
					$this->db->where("content_id",$zone)->delete("section_zones");
					$this->db->where("id",$zone)->delete("content");
				}
			}
			//Add new zones
			$titles=$this->input->post("content_title");
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
				}
			}
			//Link them
			if (!$returndata["error"]) {
				foreach($content_ids as $content_id) {
					$this->db->insert("content_content", array("content_id"=>$data->content_id, "content_link_id"=>$content_id));
				}
				print "<script>document.domain=document.domain;</script><textarea>";
				print json_encode($returndata);
				print "</textarea>";
			}
		}

	}
?>