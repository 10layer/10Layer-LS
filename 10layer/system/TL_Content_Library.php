<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 
	/**
	 * 10Layer Content Library Base Class
	 *
	 * This class handles plugins for documents utilising MongoDB as a meta store
	 *
	 * @package		10Layer
	 * @subpackage	Core
	 * @author		Jason Norwood-Young
	 * @link		http://10layer.com
	 */
	
	class TL_Content_Library {
		protected $ci;
		public $type_id=false;
		public $platform_id=array();
		public $last_modified=false;
		public $urlid=null;
		public $content=false;
		protected $table="tl_content";
		

		/**
		 * Constructor
		 *
		 * Links to CodeIgniter object
		 *
		 * @access	public
		 */
		public function __construct($urlid=false) {
			$this->ci=&get_instance();
			if (empty($urlid)) {
				$segments=$this->ci->uri->segment_array();
				if (empty($segments)) {
					return true;
				}
				$urlid=$segments[sizeof($segments)];
			}
			$this->ci->load->library("mongo_db");
			$this->ci->load->model("model_content");
			$this->content=$this->ci->model_content->getById($urlid);
			if (empty($this->content->id)) {
				return true;
			}
			$this->urlid=$this->content->urlid;
			$this->type_id=$this->content->content_type_id;
			$this->last_modified=$this->content->last_modified;
			//Get Platforms
			$query=$this->ci->db->get_where("content_platforms",array("content_id"=>$this->content->id));
			foreach($query->result() as $row) {
				$this->platform_id[]=$row->platform_id;
			}
		}
		
		/**
		 * Override the default preferences
		 *
		 * Accepts an associative array to override what we pick up from the uri. 
		 * table is the mongodb content metacontent table name
		 * type is the content type
		 * urlid is the unique identifier for the content item
		 *
		 * @access	public
		 * @param	array	config preferences
		 * @return	void
		 */
		public function initialize($config=array()) {
			foreach ($config as $key => $val) {
				if (isset($this->$key)) {
					$this->$key = $val;
				}
			}			
		}
		
		/**
		 * Returns the content item's metadata as an array
		 *
		 * @access	public
		 * @return	array		The content item
		 */
		public function get() {
			$result=$this->ci->mongo_db->where(array("urlid"=>$this->urlid))->get($this->table);
			if (sizeof($result)==0) {
				$this->_initContent();
				return false;
			}
			return $result[0];
		}
		
		/**
		 * Sets the meta data associated with the content
		 *
		 * @access public
		 * @param array $data
		 * @return void
		 */
		public function set($data) {
			$result=$this->get();
			if (empty($result->urlid)) {
				$this->_initContent();
			}
			$this->ci->mongo_db->where(array("urlid"=>$this->urlid))->update($this->table,$data);
			return true;
		}
		
		/**
		 * normalize function.
		 * 
		 * Fixes fuckups between this model and the mongodb database
		 *
		 * @access public
		 * @return void
		 */
		public function normalize() {
			$data=array("urlid"=>$this->urlid, "type_id"=>$this->type_id, "platform_id"=>$this->platform_id, "last_modified"=>$this->last_modified);
			$this->ci->mongo_db->where(array("urlid"=>$this->urlid))->update($this->table,$data);
		}
		
		protected function _initContent() {
			$this->ci->mongo_db->insert($this->table,array("urlid"=>$this->urlid, "type_id"=>$this->type_id, "platform_id"=>$this->platform_id, "last_modified"=>$this->last_modified));
		}
	}