<?php
	/**
	 * Worker class
	 *
	 * Does all the heavy lifting for the Publish feature
	 * 
	 * @extends CI_Controller
	 * @package 10Layer
	 * @subpackage Controllers
	 */
	class Worker extends CI_Controller {

		/**
		 * __construct function.
		 * 
		 * @access public
		 * @return void
		 */
		public function __construct() {
			parent::__construct();
			$this->load->model("model_section");
			$this->load->model("model_content");
			$this->load->model("model_site_sections","sections");
			$this->load->model("model_zones","zones");
			$this->load->helper("blurb");
		}
		
		public function rank_section() {
			$content=$this->input->post("content");
			$zone_id=$this->input->post("zone_id");
			$zone_name=$this->input->post("zone_name");
			
			$dbdata=array();
			$x=1;
			if (is_array($content)) {
				foreach($content as $content_id) {
					$dbdata[]=array("content_id"=>$content_id,"rank"=>$x,"zone_urlid"=>$zone_id);
					$x++;
				}
			}
			$this->model_section->setContent($zone_id,$dbdata);
			//$this->checkCallback("onAfterUpdate", $zone_id);
			$this->messaging->post_action("publish",$zone_id);
			//$subsection=$this->model_section->getSubSection($subsection_id);
			$the_result["error"] = false;
			$the_result["msg"] = "$zone_id Successfuly ranked...";
			print $the_result["msg"];
			
		}
		
		public function validate($content_type, $content_id){
			$content=$this->model_content->getByIdORM($content_id);		
			$content->transformFields();
			$validation=$content->validateFields();
				
				
				if (!$validation["passed"]) {
					echo implode("<br />\n",$validation["failed_messages"]);
				} else {
					
					echo "passed";
					
				}
		
		}
		
		public function stage_rank_section() {
			$content=$this->input->post("content");
			$zone_id=$this->input->post("zone_id");
			$zone_name=$this->input->post("zone_name");
			
			$dbdata=array();
			$x=1;
			if(is_array($content) AND $content != ""){
				foreach($content as $content_id) {
					$dbdata[]=array("content_id"=>$content_id,"rank"=>$x,"zone_urlid"=>$zone_id);
					$x++;
				}
			}
			
			$this->model_section->stage_changes($zone_id,$dbdata);
			//$this->checkCallback("onAfterUpdate", $zone_id);
			//$this->messaging->post_action("publish",$zone_id);
			//$subsection=$this->model_section->getSubSection($subsection_id);
			print "Staged changes to ".$zone_name;
		}
		
		public function subsection($section_urlid, $zone_urlid, $startdate=false, $enddate=false, $searchstr="", $selecteds="") {
			if ($zone_urlid=="undefined") {
				print "This zone is undefined.";
				return true;
			}
			
			$section=$this->sections->getByIdORM($section_urlid);
			
			$data["zone"]=$this->zones->getByIdORM($zone_urlid)->getData();
			$articles=$this->model_section->getContentInQueue(array("queued_for_publishing", "published"),$zone_urlid,$startdate,$enddate, $searchstr);
			$data["content"]=$articles["unpublished"];
			$data["published_articles"]=$articles["published"];
			
			//print_r($articles["unpublished"]); die();
			
			$data["staged"]=$articles["staged"];
			$data["all"] = $this->input->get('all', TRUE);
			$data["section_id"]=$section->content_id;
			$this->load->view("publish/subsection",$data);
		}
		
		
		public function revert(){
			//print_r($this->input->post());
			
			$zone_id=$this->input->post("zone_id");
			$zone_name=$this->input->post("zone_name");
			$zone = $this->zones->getByIdORM($zone_id)->getData();
			$data["zone"] = $zone;
			$data["items"] = $this->model_section->revertContent($zone->urlid);
			$this->load->view("publish/revert_list",$data);
			
		}
		
		
		public function automate_section($section_id){
			//get the section zones
			$zones=$this->db->select("content2.id")->from("content")->join("content_content","content.id=content_content.content_id")->join("content AS content2", "content_content.content_link_id=content2.id")->where("content.id",$section_id)->where("content2.content_type_id",21)->get()->result();
			
			foreach($zones as $zone){
				//start by setting the zone to auto
				$this->model_section->automate_zone($zone->id);
				//generate the content for the zone and store it
				$this->generate_zone_content($section_id,$zone->id);
			}
			echo "Successfully automated this section";
		
		}
		
		public function generate_zone_content($section_id,$zone_id){
				$zone=$this->zones->getByIdORM($zone_id)->getData();
				
				
				$content_types= (isset($zone->content_types) AND $zone->content_types!="") ? explode(",",$zone->content_types):array();
				if ($zone->auto == 1) {
					//print "Auto-generating content for ".$zone->urlid."\n";
					$articles=$this->db->select("content.id AS content_id, content_content.content_id AS parent")->from("content")->join("content_types", "content.content_type_id=content_types.id")->join("content_content","content_content.content_link_id=content.id")->where_in("content_types.name", $content_types)->where("content_content.content_id",$section_id)->where("content.live",true)->where("content.major_version",4)->order_by("content.start_date DESC")->limit($zone->auto_limit)->get()->result();
					
					$x=1;
					$this->db->where("ranking.zone_urlid",$zone->urlid)->delete("ranking");
					foreach($articles as $article) {
						$data=array();
						$data["rank"]=$x++;
						$data["content_id"]=$article->content_id;
						$data["zone_urlid"]=$zone->urlid;
						$this->db->insert("ranking",$data);
					}
				}
		}
		
		function clean_zone_content($zone_id){
			$zone=$this->zones->getByIdORM($zone_id)->getData();
			$this->db->where("ranking.zone_urlid",$zone->urlid)->delete("ranking");
		}
		
		function automate_zone($section_id,$zone_id){
			$this->model_section->automate_zone($zone_id);
			$this->generate_zone_content($section_id,$zone_id);
			echo "Successfully automated this zone";
		}
		
		function de_automate_section($section_id){
			//get the section zones
			$zones=$this->db->select("content2.id")->from("content")->join("content_content","content.id=content_content.content_id")->join("content AS content2", "content_content.content_link_id=content2.id")->where("content.id",$section_id)->where("content2.content_type_id",21)->get()->result();
			foreach($zones as $zone){
				//start by setting the zone from auto
				$this->model_section->de_automate_zone($zone->id);
				//generate the content for the zone and store it
				$this->clean_zone_content($zone->id);
			}
			echo "Successfully de-automated this section";
		}
		
		function de_automate_zone($zone_id){
			$this->model_section->de_automate_zone($zone_id);
			$this->clean_zone_content($zone_id);
			echo "Successfully de-automated this zone";
		}
		
		
		
	}

/* End of file worker.php */
/* Location: ./system/application/controllers/publish/ */