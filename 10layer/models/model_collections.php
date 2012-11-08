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
		
		public function get_all() {
			return $this->mongo_db->where(array("collection"=>"1"))->get("content_types");
		}
		
		public function get_options($content_type) {
			return $this->mongo_db->where(array("content_type"=>$content_type))->limit(100)->order_by(array("title"))->get("content");
		}
	}

/* End of file model_collections.php */
/* Location: ./system/application/third_party/10layer/models/model_collections.php */