<?php
	
	/**
	 * Model_Collections class.
	 * 
	 * @extends Model
	 * @package 10Layer
	 * @subpackage Models
	 */
	class Model_Collections extends CI_Model {
	
		/**
		 * __construct function.
		 * 
		 * @access public
		 * @return void
		 */
		public function __construct() {
			parent::__construct();
		}
		
		public function getAll() {
			return $this->db->get_where("content_types",array("collection"=>true))->result();
		}
		
		public function get($id) {
			if (is_numeric($id)) {
				return $this->db->get_where("content_types",array("collection"=>true, "id"=>$id))->row();
			} else {
				return $this->db->get_where("content_types",array("collection"=>true, "urlid"=>$id))->row();
			}
		}
	}

/* End of file model_collections.php */
/* Location: ./system/application/third_party/10layer/models/model_collections.php */