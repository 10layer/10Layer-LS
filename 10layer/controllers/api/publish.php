<?php
	require_once('10layer/system/TL_Api.php');
	
	/**
	 * Publish class
	 * 
	 * @extends CI_Controller
	 */
	class Publish extends TL_Api {
		
		/**
		 * __construct function.
		 * 
		 * @access public
		 * @return void
		 */
		public function __construct() {
			parent::__construct();
		}
		
		public function save() {
			$id = $this->input->get_post("_id");
			if (empty($id)) {
				$this->data["error"]=true;
				$this->data["msg"][]="Var id required";
				$this->returndata();
				return false;
			}
			$zones = $this->input->get_post("zones");
			if (empty($zones) || !is_array($zones)) {
				$this->data["error"]=true;
				$this->data["msg"][]="zones must be an array";
				$this->returndata();
				return false;
			}
			$this->enforce_secure();
			$x = 0;
			$this->mongo_db->where(array("_id"=>$id))->delete("published");
			$result = array("_id"=>$id);
			foreach($zones as $key=>$zone) {
				foreach($zone as $doc) {
					$id=$doc["_id"];
					$item=array_pop($this->model_content->get($id));
					$result["zones"][$key][]=$item;
				}
			}
			$this->mongo_db->insert("published", $result);
			$this->data["message"]="Section updated";
			$this->returndata();
		}
		
		public function section($section_id) {
			$result=$this->mongo_db->where(array("_id"=>$section_id))->get("published");
			if (empty($result)) {
				$this->show_error("No results found for $section_id");
			}
			$this->data["content"]=$result[0];
			$this->returndata();
		}
		
		public function zone($section_id, $zone_id) {
			$section=array_pop($this->mongo_db->where(array("_id"=>$section_id))->get("published"));
			if (empty($section)) {
				$this->show_error("No results found for $section_id");
			}
			if (isset($section->zones[$zone_id])) {
				$this->data["content"] = $section->zones[$zone_id];
			}
			$this->returndata();
		}
		
		public function document() {
			$this->enforce_secure();
			$section_id = $this->input->get_post("section_id");
			if (empty($section_id)) {
				$this->data["error"]=true;
				$this->data["msg"][]="Var section_id required";
				$this->returndata();
				return false;
			}
			$section=array_pop($this->mongo_db->get_where("content",array("_id"=>$section_id)));
			
			print_r($section);
		}
	}
?>