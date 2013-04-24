<?php
	/**
	 * 10Layer Redirect Model
	 *
	 * This model handles redirect data
	 *
	 * @package		10Layer
	 * @subpackage	Models
	 * @category	Models
	 * @author		Tumelo T Mphafe
	 * @link		http://10layer.com
	 */
	class Model_Redirect extends CI_Model {

		public function __construct() {
			parent::__construct();
		}


		public function get_by_id($id) {
			$this->mongo_db->state_save();
			$redirects=$this->mongo_db->get_where("redirects",array("_id"=>$id));
			$this->mongo_db->state_restore();
			return $redirects[0];
		}



		public function update($id, $data) {
			$this->mongo_db->where(array("_id"=>$id))->update("redirects",$data);
			return true;
		}

		public function insert($data) {
			$this->mongo_db->insert("redirects",$data);
			return $data["_id"];
		}

		public function delete_redirect($id){
			$this->mongo_db->where(array("_id"=>$id))->delete("redirects");
		}





		/**
		 * get_all_redirects function.
		 *
		 * @access public
		 * @return array
		 */
		public function getAllRedirects() {
			return $this->mongo_db->get("redirects");
		}



	}

?>