<?php
	
	/**
	 * Model_Publications class.
	 * 
	 * @extends CI_Model
	 * @package 10Layer
	 * @subpackage Models
	 */
	class Model_Publications extends CI_Model {
	
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
			//$this->db->join("tl_publication_types","tl_publication_types.id=publications.type_id");
			$this->db->where("publications.id",$id);
			$query=$this->db->get("publications");
			return $query->row();
		}
		
		public function get_all() {
			//$this->db->join("tl_publication_types","tl_publication_types.id=publications.type_id");
			$query=$this->db->get("publications");
			return $query->result();
		}
		
	}

/* End of file Model_Publications.php */
/* Location: ./system/application/models/ */