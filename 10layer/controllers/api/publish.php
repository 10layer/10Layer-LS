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
			$this->_save($id, $zones);
			$this->returndata();
		}

		protected function _save($id, $zones) {
			$x = 0;
			$this->mongo_db->where(array("_id"=>$id))->delete("published");
			$result = array("_id"=>$id);
			$manifest = array();
			foreach($zones as $key=>$zone) {
				foreach($zone as $doc) {
					$doc = (array) $doc;
					$id=$doc["_id"];
					$item=array_pop($this->model_content->get($id));
					$result["zones"][$key][]=$item;
					$manifest[] = array("_id" => $id, "zone" => $key);
				}
			}
			$result["manifest"] = $manifest;
			//print_r($manifest);
			$this->mongo_db->insert("published", $result);
			$this->data["message"]="Section updated";
			$this->m->flush();
		}
		
		public function section($section_id) {
			if ($this->cached) {
				$this->returndata();
				return true;
			}
			$result=array_pop($this->mongo_db->where(array("_id"=>$section_id))->get("published"));
			if (empty($result)) {
				$this->show_error("No results found for $section_id");
			}
			$tmp = array();
			foreach($result->zones as $key=>$val) {
				$tmp[$key] = $this->is_published($val);
			}
			$result->zones = $tmp;
			$this->data["content"]=$result;
			$this->cache();
			$this->returndata();
		}
		
		public function zone($section_id, $zone_id) {
			if ($this->cached) {
				$this->returndata();
				return true;
			}
			$section=array_pop($this->mongo_db->where(array("_id"=>$section_id))->get("published"));
			if (empty($section)) {
				$this->show_error("No results found for $section_id");
			}
			if (isset($section->zones[$zone_id])) {
				$content = $section->zones[$zone_id];
				for($x=0; $x < sizeof($content); $x++) {
					if (empty($content[$x])) {
						unset($content[$x]);
					}
				}
				$this->data["content"] = $content;
			}
			$this->cache();
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
		
		/**
		 * publish_document function.
		 *
		 * Publish a single document to the top of a zone
		 * 
		 * @access protected
		 * @return void
		 */
		public function publish_document() {
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
			$published = $this->mongo_db->get_where_one("published",array("_id"=>$section_id));
			//Now we're ready to add this item to the zone
			if (isset($published->zones[$zone_id])) {
				$newzone = $published->zones[$zone_id];
			} else {
				$newzone=array();
			}
			//Make sure it's not already in there somewhere
			for($x=0; $x< sizeof($newzone); $x++) {
				if ($newzone[$x]["_id"] == $id) {
					unset($newzone[$x]);
				}
			}
			//Add this item to the start of the zone
			array_unshift($newzone, $doc);
			//Make sure we don't have too many items
			if ($zone["zone_max_items"] > 0) {
				array_splice($newzone, $zone["zone_max_items"]);
			}
			//Set the new zone in Published
			$published->zones[$zone_id] = $newzone;
			$this->_save($section_id, $published->zones);
			$this->returndata();
		}

		/**
		 * unpublish_document function.
		 *
		 * Removes all instances of the document from the published table
		 * 
		 * @access protected
		 * @return void
		 */
		public function unpublish_document() {
			$this->enforce_secure();
			$id = $this->input->get_post("id");
			if (empty($id)) {
				$this->data["error"]=true;
				$this->data["msg"][]="id required";
				$this->returndata();
				return false;
			}
			$result = $this->mongo_db->where(array("manifest._id"=>$id))->get("published");
			foreach($result as $section_id=>$section) {
				foreach($section->zones as $key=>$zone) {
					for($x = 0; $x < sizeof($zone); $x++) {
						if ($zone[$x]["_id"] == $id) {
							unset($zone[$x]);
						}
					}
					$section->zones[$key] = $zone;
				}

				$this->_save($section->_id, $section->zones);
			}
		}

		/**
		 * document function.
		 *
		 * Find all the places a document is published
		 * 
		 * @access protected
		 * @return void
		 */
		public function document() {
			//$this->enforce_secure();
			$id = $this->input->get_post("id");
			if (empty($id)) {
				$this->data["error"]=true;
				$this->data["msg"][]="id required";
				$this->returndata();
				return false;
			}
			$result = $this->mongo_db->where(array("manifest._id"=>$id))->get("published");
			$sections = array();
			foreach($result as $section) {
				$sections[$section->_id] = array();
				foreach($section->manifest as $zone) {
					if ($zone["_id"] == $id) {
						$sections[$section->_id][] = $zone["zone"];
					}
				}
			}
			$this->data["sections"] = $sections;
			$this->returndata();
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