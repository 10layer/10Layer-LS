<?php
	/**
	 * Content class
	 * 
	 * @extends CI_Controller
	 * @package 10Layer
	 * @subpackage Controllers
	 */
	class Content extends CI_Controller {

		/**
		 * __construct function.
		 * 
		 * @access public
		 * @return void
		 */
		public function __construct() {
			parent::__construct();
		}
		
		public function view($view,$urlid) {
			$contentobj=$this->model_content->getByIdORM($urlid);
			//$content_type=$this->model_content_deprecated->get_content_type_by_urlid($content_type_urlid);
			$data["content"]=$contentobj->getData();
			
			//$data["content_type"]=$content_type;
			if (!empty($data["content"]->id)) {
				$content_type_urlid=$contentobj->getContentType()->urlid;
				$this->load->view("content/snippets/$content_type_urlid/$view",$data);
			} else {
				print "<em>Missing $urlid</em>";
			}
		}
		
		public function semantic($content_type_urlid, $urlid) {
			$this->load->library("semantics");
			//$this->semantics->type=$this->uri->segment(4);
			//$this->semantics->urlid=$this->uri->segment(5);
			
			print json_encode($this->semantics->getSemantics());
		}
		
		public function linkTag($tag_id, $content_id, $content_type_id) {
			$this->load->library("semantics");
			$this->semantics->linkTag($tag_id, $content_id, $content_type_id);
			print json_encode(array("link"=>true));
		}
		
		public function unlinkTag($tag_id, $content_id, $content_type_id) {
			$this->load->library("semantics");
			$this->semantics->unlinkTag($tag_id, $content_id, $content_type_id);
			print json_encode(array("link"=>false));
		}
		
		public function normalizeContent() {
			require_once(BASEPATH."10layer/TL_Content_Library.php");
			$query=$this->db->limit(10000)->get("content");
			$x=0;
			foreach($query->result() as $row) {
				$tmp=new TL_Content_Library($row->urlid);
				$tmp->normalize();
				$x++;
			}
			print "Normalized $x records";
		}
		
		public function unlock($contenttype,$urlid) {
			//$this->load->library("messaging");
			$this->load->library("checkout");
			
			$this->checkout->unlock();
			redirect("/edit/$contenttype");
		}
		
		public function getContentTypes() {
			$result=$this->db->get("content_types");
			print json_encode($result->result());
		}
		
		public function jsonGetLastEditor($content_type, $urlid) {
			$result = $this->mongo_db->where(array("urlid"=>$urlid))->order_by(array("last_modified"=>"DESC"))->limit(1)->get("tl_content_versions");
			$admin=array("value"=>"");
			if (isset($result[0]->user_id) AND $result[0]->user_id != "") {
				$admin["value"]=$this->db->select('name')->where("id",$result[0]->user_id)->get("tl_users")->row()->name;
			}
			$this->load->view("json", array("data"=>$admin));
		}
		
	}

/* End of file content.php */
/* Location: ./system/application/controllers/workers/ */