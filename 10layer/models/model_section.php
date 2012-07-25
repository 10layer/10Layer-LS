<?php
	
	/**
	 * Model_Section class.
	 * 
	 * @extends CI_Model
	 * @package 10Layer
	 * @subpackage Models
	 */
	class Model_Section extends CI_Model {
	
		/**
		 * __construct function.
		 * 
		 * @access public
		 * @return void
		 */
		public function __construct() {
			parent::__construct();
			$this->load->model("model_site_sections");
		}
		
		/*public function getAll() {
			$this->db->order_by("order","ASC");
			$result=$this->db->get("sections");
			return $result->result();
		}
		
		public function get($urlid) {
			if (is_numeric($urlid)) {
				$result=$this->db->get_where("sections",array("id"=>$urlid));
			} else {
				$result=$this->db->get_where("sections",array("urlid"=>$urlid));
			}
			return $result->row();
		}
		
		public function getSubSection($urlid) {
			if (is_numeric($urlid)) {
				$result=$this->db->get_where("subsections",array("id"=>$urlid));
			} else {
				$result=$this->db->get_where("subsections",array("urlid"=>$urlid));
			}
			return $result->row();
		}*/
		
		/**
		 * get_sectionmap function.
		 * 
		 * Returns a named array of all the sections
		 *
		 * @access public
		 * @param int $content_type_id
		 * @return void
		 */
		public function get_sectionmap($content_type_id) {
			$result=array();
			$query=$this->db->select("content.urlid, content.title, content.id AS content_id")->from("content")->join("content_content","content_content.content_id=content.id")->join("content AS content2","content_content.content_link_id=content2.id")->where("content.content_type_id",$content_type_id)->where("content2.content_type_id",$content_type_id)->group_by("content.urlid")->order_by("content.title")->get();
			$parents=$query->result();
			foreach($parents as $parent) {
				$children=$this->get_subsections($parent->content_id, $content_type_id);
				$result[$parent->urlid]=$parent;
				foreach($children as $child) {
					$result[$parent->urlid]->children[]=$child;
				}
			}

		}
		
		/**
		 * get_subsections function.
		 * 
		 * Given a primary section, returns all data about its subsections
		 *
		 * @access public
		 * @param int $id
		 * @param int $content_type_id
		 * @return void
		 */
		public function get_subsections($id, $content_type_id) {
			$section=$this->db->get_where("content",array("id"=>$id))->row();
			$query=$this->db->select("content.title, content.urlid, content.id AS content_id")->from("content")->join("content_content","content.id=content_content.content_link_id")->where("content_content.content_id",$section->id)->where("content.content_type_id",$content_type_id)->get();
			return $query->result();
		}
		
		public function getLayouts($urlid) {
			$section=$this->get($urlid);
			$result=$this->db->get_where("section_layout",array("section_id"=>$section->id));
			return $result->result();
		}
		
		public function getContent($urlid) {
			$this->db->select("ranking.urlid");
			$this->db->select("ranking.subsection_id");
			$this->db->select("content_types.urlid AS contenttype");
			$this->db->select("content.title AS title");
			$this->db->from("ranking");
			//$this->db->join("sections","ranking.section_id=sections.id");
			//$this->db->join("subsections","ranking.subsection_id=subsections.id");
			$this->db->join("content","content.urlid=ranking.urlid");
			$this->db->join("content_types","content_types.id=content.content_type_id");
			$this->db->order_by("rank ASC");
			$this->db->where("ranking.subsection_id",$urlid);
			$query=$this->db->get();
			
			return $query->result();
		}
		
		
		
		public function setContent($zone_id,$data) {
			$this->db->where("zone_urlid",$zone_id);
			$this->db->delete("ranking");
			foreach($data as $row) {
				$this->db->insert("ranking",$row);
			}
			$this->db->where("zone_urlid",$zone_id);
			$this->db->delete("ranking_stage");
			return true;
		}
		
		public function revertContent($zone_id) {
			$this->db->select("ranking.*");
			//$this->db->select("ranking.subsection_id");
			$this->db->select("content_types.urlid AS content_type_urlid");
			$this->db->select("content.title AS title");
			$this->db->select("content.urlid");
			$this->db->select("content.live");
			$this->db->select("content.major_version");
			$this->db->select("content.id");
			$this->db->from("ranking");
			//$this->db->join("sections","ranking.section_id=sections.id");
			//$this->db->join("subsections","ranking.subsection_id=subsections.id");
			$this->db->join("content","content.id = ranking.content_id");
			$this->db->join("content_types","content_types.id=content.content_type_id");
			$this->db->order_by("rank ASC");
			$this->db->where("ranking.zone_urlid",$zone_id);
			
			$old_items = $this->db->get()->result();
			
			//echo $this->db->last_query(); die();
			
			$this->db->where("zone_urlid",$zone_id);
			$this->db->delete("ranking_stage");
			
			foreach($old_items as $item){
				$object["content_id"] = $item->id;
				$object["zone_urlid"] = $item->zone_urlid;
				$object["rank"] = $item->rank;
				$this->db->insert("ranking_stage",$object);
			}
			
			return $old_items;
		}
		
		public function stage_changes($zone_id,$data) {
			$this->db->where("zone_urlid",$zone_id);
			$this->db->delete("ranking_stage");
			foreach($data as $row) {
				$this->db->insert("ranking_stage",$row);
			}
			return true;
		}
		
		
		public function staged_zone($zone_id){
			$records = $this->db->where("zone_urlid",$zone_id)->count_all_results("ranking_stage");
			if($records > 0){
				return true;
			}else{
				return false;
			}
		}
		
		
		//automate the zone
		public function automate_zone($zone_id){
			$this->db->where("content_id", $zone_id);
			$this->db->update("section_zones", array("auto"=> 1));
		}
		
		//de_automate the zone
		public function de_automate_zone($zone_id){
			$this->db->where("content_id", $zone_id);
			$this->db->update("section_zones", array("auto"=> 0));
		}
		
		public function clean_zone_content($zone_id){
			$this->db->where("content_id", $zone_id);
			$this->db->delete("ranking");
		}

		
		
		public function getContentInQueue($urlid, $zone_id=false, $startdate=false, $enddate=false, $search=false, $limit=100, $start=0) {
			//check if our zone are staged
			$staged = ($this->staged_zone($zone_id)) ? "staged" : "";
			$table = ($staged == "staged") ? "ranking_stage" : "ranking";
			$this->load->model("model_zones","zones");
			$ctids=array();
			$zone=$this->zones->getByIdORM($zone_id)->getData();
			$published_ids=array();
			$published_articles=array();
			$selecteds = $this->input->get('selecteds', TRUE);
			
			//when the frontend hasnt given us a list of selecteds, we find our own
			if(sizeof($selecteds) == 1){
				$published_list = $this->db->where("zone_urlid",$zone_id)->order_by("rank ASC")->get($table)->result();			
				//$published_articles=$this->zones->content;
				
				foreach($published_list as $article) {
					//We need to make sure the ID exists
					if (isset($article->content_id)) {
						$query=$this->db->select("content.*, content_types.urlid AS content_type_urlid")->join("content_types","content_types.id=content.content_type_id")->where("content.id", $article->content_id)->get("content");
						if ($query->num_rows()==1) {
							$published_ids[]=$article->content_id;
							//print $article->content_id;
							try {
								$published_articles[]=$query->row();
							} catch(exception $e) {
							
							}
						}
					}
				}
			}else{
				
					
				$published_list = $this->db->where("zone_urlid",$zone_id)->order_by("rank ASC")->get($table)->result(); 
				foreach($published_list as $article) {
					//We need to make sure the ID exists
					if (isset($article->content_id)) {
						$query=$this->db->select("content.*, content_types.urlid AS content_type_urlid")->join("content_types","content_types.id=content.content_type_id")->where("content.id", $article->content_id)->get("content");
						if ($query->num_rows()==1) {
							//$published_ids[]=$article->content_id;
							//print $article->content_id;
							try {
								$published_articles[]=$query->row();
							} catch(exception $e) {
							
							}
						}
					}
				}
			
				$published_ids = $this->input->get('selecteds', TRUE);
				
					
			}
			
			$contenttypes=explode(",",$zone->content_types);
			if (is_array($contenttypes)) {
				foreach($contenttypes as $ct) {
					$query=$this->db->get_where("content_types",array("urlid"=>$ct));
					if (!empty($query->row()->id)) {
						$ctids[]=$query->row()->id;
					}
				}
			}
			
			
			
			if (is_array($urlid)) {
				$major_versions=array();
				foreach($urlid as $val) {
					$workflow=$this->model_workflow->getByUrlId($val);
					$major_versions[]="content.major_version={$workflow->major_version}";
				}
				$this->db->where(array("content_platforms.platform_id"=>$this->platforms->id(), "content.live"=>true));
				
				$this->db->where("(".implode(" OR ",$major_versions).")");
			} else {
				$workflow=$this->model_workflow->getByUrlId($urlid);
				$major_version=(Int) $workflow->major_version;
				$this->db->where(array("content.major_version"=>$major_version, "content_platforms.platform_id"=>$this->platforms->id(), "content.live"=>true));
			}
			
			$this->db->limit($limit, $start);
			$this->db->select("content.*");
			$this->db->select("content_types.urlid AS contenttype");
			$this->db->from("content");
			$this->db->join("content_types","content_types.id=content.content_type_id");
						
			if (!empty($startdate)) {
				$this->db->where("content.start_date >=",date("Y-m-d H:i:s",strtotime(rawurldecode($startdate))));
			}
			if (!empty($enddate)) {
				$this->db->where("content.start_date <=",date("Y-m-d H:i:s",strtotime(rawurldecode($enddate))));
			}
			if (!empty($search)) {
				$the_search = urldecode($search);
				$this->db->like("title",$the_search);
			}

			$this->db->join("content_platforms","content_platforms.content_id=content.id");
			$cts=array();
			foreach($ctids as $ctid) {
				$cts[]="content.content_type_id = $ctid";
			}
			$this->db->where("(".implode(" OR ",$cts).")");
			if (!empty($published_ids)) {
				$this->db->where_not_in("content.id",$published_ids);
			}
			$this->db->order_by("content.start_date","DESC");
			$query=$this->db->get();
			
			$result=array();
			
			$result["staged"]=$staged;
			$result["published"]=$published_articles;
			$result["unpublished"]=$query->result();

			return $result;
			
		}
		
		
		public function generate_zone_content($section_id,$zone_id){
				$zone=$this->zones->getByIdORM($zone_id)->getData();
				
				//auto values
				$limit = ($zone->auto_limit != "" || $zone->auto_limit != 0 ) ? $zone->auto_limit : 10;
				
				$content_types= (isset($zone->content_types) AND $zone->content_types!="") ? explode(",",$zone->content_types):array();
				if ($zone->auto == 1) {
					//print "Auto-generating content for ".$zone->urlid."\n";
					$articles=$this->db->select("content.id AS content_id, content_content.content_id AS parent")->from("content")->join("content_types", "content.content_type_id=content_types.id")->join("content_content","content_content.content_link_id=content.id")->where_in("content_types.name", $content_types)->where("content_content.content_id",$section_id)->where("content.live",true)->where("content.major_version",4)->order_by("content.start_date DESC")->limit($limit)->get()->result();
					
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

		
		
	}
	

/* End of file Model_Section.php */
/* Location: ./system/application/models/ */