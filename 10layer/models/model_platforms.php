<?php
	
	/**
	 * Model_Platforms class.
	 * 
	 * @extends CI_Model
	 * @package 10Layer
	 * @subpackage Models
	 */
	class Model_Platforms extends CI_Model {
	
		/**
		 * __construct function.
		 * 
		 * @access public
		 * @return void
		 */
		public function __construct() {
			parent::__construct();
		}
		
		public function get($id) {
			$this->db->join("tl_platform_types","tl_platform_types.id=platforms.type_id");
			$this->db->where("platforms.id",$id);
			$query=$this->db->get("platforms");
			return $query->row();
		}
		
		public function get_all($pubid=false) {
			$this->load->library("publications");
			if (empty($pubid)) {
				$pubid=$this->publications->id();
			}
			$this->db->select("platforms.*");
			$this->db->select("tl_platform_types.name, tl_platform_types.publish_plugin");
			$this->db->join("tl_platform_types","tl_platform_types.id=platforms.type_id");
			$this->db->where("publication_id",$pubid);
			$query=$this->db->get("platforms");
			return $query->result();
		}
		
	}

/* End of file Model_Platforms.php */
/* Location: ./system/application/models/ */