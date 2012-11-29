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
			$this->enforce_secure();
			$x = 0;
			//foreach($this->vars->zones as $zone) {
			$this->mongo_db->where(array("_id"=>$this->vars->_id))->delete("published");
			for($x=0; $x<sizeof($this->vars->zones); $x++) {
				for($y=0; $y<sizeof($this->vars->zones[$x]); $y++) {
					$id=$this->vars->zones[$x][$y]->_id;
					$item=$this->model_content->get($id);
					$this->vars->zones[$x][$y]=$item[0];
				}
			}
			$this->mongo_db->insert("published", $this->vars);
			$this->data["message"]="Section updated";
			$this->returndata();
			//}
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
			$this->data["content"] = $section->zones[$zone_id];
			$this->returndata();
		}
	}
?>