<?php
	/**
	 * 10Layer Content Model
	 *
	 * This model handles workflow data
	 *
	 * @package		10Layer
	 * @subpackage	Models
	 * @category	Models
	 * @author		Jason Norwood-Young
	 * @link		http://10layer.com
	 */
	class Model_Workflow extends CI_Model {
		
		public function __construct() {
			parent::__construct();
		}
		
		public function getByUrlId($urlid) {
			$result=$this->db->get_where("tl_workflows",array("urlid"=>$urlid));
			return $result->row();
		}
		
		public function getByContentType($id) {
			$this->db->select("tl_workflows.*");
			$this->db->join("content_workflows","content_workflows.workflow_id=tl_workflows.id");
			$this->db->where("content_workflows.content_id",$id);
			$this->db->order_by("content_workflows.workflow_id","ASC");
			$query=$this->db->get("tl_workflows");
			return $query->result();
		}
		
		public function getAll() {
			return $this->db->order_by("major_version ASC")->get("tl_workflows")->result();
		}
		
		public function getContentInQueue_deprecated($urlid, $subsection=false, $startdate=false, $enddate=false, $limit=50, $start=0) {
		//This is now managed in model_section - Deprecated
			$ctids=array();
			
			$published_articles=$this->model_section->getContent($subsection);
			print_r($published_articles);
			$query=$this->db->get_where("subsections",array("urlid"=>$subsection));
			$data["subsection"]=$query->row();
			$contenttypes=explode(",",$data["subsection"]->content_types);
			if (is_array($contenttypes)) {
				foreach($contenttypes as $ct) {
					$query=$this->db->get_where("content_types",array("urlid"=>$ct));
					$ctids[]=$query->row()->id;
				}
			}
			$workflow=$this->getByUrlId($urlid);
			$major_version=(Int) $workflow->major_version;
			$this->db->limit($limit, $start);
			$this->db->select("content.content_type_id, content.urlid");
			$this->db->from("content");
			$this->db->where(array("content.major_version"=>$major_version, "content_platforms.platform_id"=>$this->platforms->id(), "content.live"=>true));
			if (!empty($startdate)) {
				$this->db->where("content.start_date >=",date("Y-m-d",strtotime($startdate)));
			}
			if (!empty($enddate)) {
				$this->db->where("content.start_date <=",date("Y-m-d",strtotime($enddate)));
			}
			$this->db->join("content_platforms","content_platforms.content_id=content.id");
			foreach($ctids as $ctid) {
				$this->db->where("content.content_type_id",$ctid);
			}
			
			$this->db->order_by("content.last_modified","DESC");
			$query=$this->db->get();
			//print $this->db->last_query();
			return $query->result();
			//$result=$this->mongo_db->select(array("type","urlid"))->where(array("major_version"=>$major_version, "platform_id"=>$this->platforms->id()))->get("tl_content");
			//return $result;
		}
		
		public function get_workflow_by_id($id) {
			$query=$this->db->get_where("tl_workflows",array("id"=>$id));
			return $query->row();
		}
		
		public function get_workflow_by_content_version($content_type_id, $version) {
			$query=$this->db->get_where("tl_workflows",array("content_type_id"=>$content_type_id,"major_version"=>$version));
			return $query->row();
		}
		
		public function get_queue_by_content_type($content_type_urlid, $limit=50, $start=0) {
			$this->db->where("urlid",$content_type_urlid);
			$query=$this->db->get("content_types");
			$content_type=$query->row();
			//print_r($content_type);
			$content_tablename=$content_type->table_name;
			$content_id=$content_type->id;
			$roles=$this->session->userdata("roles");
			//print_r($roles);
			$versions=array();
			foreach($roles as $role) {
				//print $role;
				$this->db->select("tl_workflows.major_version");
				$this->db->from("tl_workflows");
				$this->db->join("tl_roles_workflows_link","tl_workflows.id=tl_roles_workflows_link.workflow_id");
				$this->db->where("tl_roles_workflows_link.role_id",$role);
				$query=$this->db->get();
				foreach($query->result() as $row) {
					$versions[]=$row->major_version;
				}
			}
			$content=array();
			foreach($versions as $version) {
				$this->db->where("major_version",$version);
				$this->db->from($content_tablename);
				$this->db->limit($limit, $start);
				$this->db->order_by("date_created","DESC");
				$query=$this->db->get();
				$content[$version]=$query->result();
			}
			return $content;
		}
	}

?>