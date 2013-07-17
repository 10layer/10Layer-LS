<?php
	require_once('10layer/system/TL_Api.php');
	
	/**
	 * Content_Types class
	 * 
	 * @extends Controller
	 */
	 
	class Content_types extends TL_Api {
		
		/**
		 * __construct function.
		 * 
		 * @access public
		 * @return void
		 */
		public function __construct() {
			parent::__construct();
			$this->enforce_secure();
		}
		
		/**
		 * index function.
		 * 
		 * Returns all the content types
		 *
		 * @access public
		 * @return void
		 */
		public function index() {
			$this->data["content"] = $this->mongo_db->order_by(array("_id"))->get("content_types");
			$this->returndata();
		}
		
		public function listing() {
			$this->data["content"] = $this->mongo_db->select(array("_id", "name"))->order_by(array("_id"))->get("content_types");
			$this->returndata();
		}
		
		/**
		 * save function.
		 * 
		 * @access public
		 * @return void
		 */
		public function save() {
			$data = $this->vars;
			if (!empty($data->delete_all)) {
				$this->mongo_db->delete("content_types");
			}
			if (isset($data->content_types) && is_array($data->content_types)) {
				foreach($data->content_types as $ct) {
					$this->_save($ct);
				}
			} else {
				$this->_save($data->content_type);
			};
			$this->data["content"]=$data;
			$this->returndata();
		}
		
		protected function _save($content_type) {
			$id = $content_type->urlid;
			if (empty($id)) {
				return false;
			}
			$content_type->_id=$id;
			unset($content_type->id);
			
			$this->data["msg"]="Saving";
			$this->mongo_db->where(array("_id"=>$id))->delete("content_types");
			$this->mongo_db->insert("content_types", $content_type);
			return true;
		}
		
	}

/* End of file .php */
/* Location: ./system/application/controllers/ */