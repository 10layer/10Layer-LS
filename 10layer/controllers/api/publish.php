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
				$this->data["msg"][]="Zones must be an array";
				$this->returndata();
				return false;
			}
			$this->enforce_secure();
			$x = 0;
			$this->mongo_db->where(array("_id"=>$id))->delete("published");
			$result = array("_id"=>$id);
			$manifest = array();
			foreach($zones as $key=>$zone) {
				foreach($zone as $doc) {
					$id=$doc["_id"];
					$item=array_pop($this->model_content->get($id));
					$result["zones"][$key][]=$item;
					$manifest[] = array("_id" => $id, "zone" => $key);
				}
			}
			$result["manifest"] = $manifest;
			$this->mongo_db->insert("published", $result);
			$this->data["message"]="Section updated";
			$this->returndata();
		}
		
		public function section($section_id) {
			$result=array_pop($this->mongo_db->where(array("_id"=>$section_id))->get("published"));
			if (empty($result)) {
				$this->show_error("No results found for $section_id");
			}
			$tmp = array();
			//print_r($result);
			foreach($result->zones as $key=>$val) {
				$tmp[$key] = $this->is_published($val);
			}
			$result->zones = $tmp;
			$this->data["content"]=$result;
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
		
		/**
		 * publishable function.
		 * 
		 * Returns the possible sections and zones a content type can be published to
		 *
		 * @access public
		 * @param mixed $content_type
		 * @return void
		 */
		public function available_zones($content_type) {
			$content_types=$this->model_collections->get_all();
			$result = array();
			foreach($content_types as $ct) {
				$collections=$this->mongo_db->where(array("content_type"=>$ct->_id))->get("content");
				foreach($collections as $collection) {
					foreach($collection->zone as $zone) {
						if (in_array($content_type, $zone["zone_content_types"])) {
							$result[$collection->_id][] = array("section_id"=>$collection->_id, "zone_id"=>$zone["zone_urlid"], "section_name"=>$collection->title, "zone_name"=>$zone["zone_name"]);
						}
					}
				}
			}
			$this->data["content"]=$result;
			$this->returndata();
		}
		
		public function document() {
			$this->enforce_secure();
			$section_id = $this->input->get_post("section_id");
			if (empty($section_id)) {
				$this->data["error"]=true;
				$this->data["msg"][]="section_id required";
				$this->returndata();
				return false;
			}
			$zone_id = $this->input->get_post("zone_id");
			if (empty($section_id)) {
				$this->data["error"]=true;
				$this->data["msg"][]="zone_id required";
				$this->returndata();
				return false;
			}
			$id = $this->input->get_post("id");
			if (empty($id)) {
				$this->data["error"]=true;
				$this->data["msg"][]="id required";
				$this->returndata();
				return false;
			}
			$section = array_pop($this->mongo_db->get_where("content",array("_id"=>$section_id)));
			if (empty($section)) {
				$this->data["error"]=true;
				$this->data["msg"][]="Section $section_id not found";
				$this->returndata();
				return false;
			}
			if (!isset($section->zone[$zone_id])) {
				$this->data["error"]=true;
				$this->data["msg"][]="Zone $zone_id not found";
				$this->returndata();
				return false;
			}
			$zone = $section->zone[$zone_id];
			$doc = array_pop($this->mongo_db->get_where("content",array("_id"=>$id)));
			if (empty($doc)) {
				$this->data["error"]=true;
				$this->data["msg"][]="Document $id not found";
				$this->returndata();
				return false;
			}
			if (!in_array($doc->content_type, $zone["zone_content_types"])) {
				$this->data["error"]=true;
				$this->data["msg"][]="Content type {$doc->content_type} not allowed in zone $zone_id";
				$this->returndata();
				return false;
			}
			$published = array_pop($this->mongo_db->get_where("published",array("_id"=>$section_id)));
			//Now we're ready to add this item to the zone
			$newzone = $published->zones[$zone_id];
			//Make sure it's not already in there somewhere
			for($x=0; $x< sizeof($newzone); $x++) {
				if ($newzone[$x]["_id"] == $id) {
					unset($newzone[$x]);
				}
			}
			//Add this item to the start of the zone
			array_unshift($newzone, $doc);
			//Make sure we don't have too many items
			array_splice($newzone, $zone["zone_max_items"]);
			//Set the new zone in Published
			$published->zones[$zone_id] = $newzone;
			$this->mongo_db->where(array("_id"=>$section_id))->update("published", array("zones"=>$published->zones));
			print_r($published);
		}

		/**
		 * is_published function.
		 *
		 * Only return published items
		 * 
		 * @access protected
		 * @return void
		 */
		protected function is_published($items) {
			$published=$this->input->get_post("published");
			if (!$this->secure) {
				$published = true;
			}
			
			if (!empty($published)) {
				for($x=0; $x< sizeof($items); $x++) {
					$item = (Array) $items[$x];
					if (!isset($item["workflow_status"])) {
						unset($items[$x]);
					} else {
						if  ($item["workflow_status"] != "Published") {
							unset($items[$x]);
						}
					}
				}
			}
			$this->data["criteria"]["published"]=false;
			return $items;
		}
	}
?>