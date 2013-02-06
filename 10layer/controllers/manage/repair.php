<?php
	/**
	 * Repair class
	 * 
	 * A bunch of repairs for common problems
	 *
	 * @extends Controller
	 */
	class Repair extends CI_Controller {

		/**
		 * __construct function.
		 * 
		 * @access public
		 * @return void
		 */
		public function __construct() {
			parent::__construct();
		}
		
		public function datetime() {
			$date_fields = array("timestamp", "last_modified", "start_date");
			foreach($date_fields as $date_field) {
				//Find blank timestamps and fix those
				$blank = $this->mongo_db->where(array($date_field=>""))->get("content");
				print "Blank $date_field found: ".sizeof($blank)."<br />";
				foreach($blank as $item) {
					$tmp[$date_field]=time();
					$this->mongo_db->where(array("_id"=>$item->_id))->update("content", $tmp);
				}
				$incorrect=$this->mongo_db->like($date_field, "-")->get("content");
				foreach($incorrect as $item) {
					$tmp[$date_field]=strtotime($item->{$date_field});
					$this->mongo_db->where(array("_id"=>$item->_id))->update("content", $tmp);
				}
				print "Incorrect $date_field found: ".sizeof($incorrect)."<br />";
			}
		}
		
		public function remove_doubles() {
			$items = $this->mongo_db->like("_id","-1", "i", true, false)->get("content");
			foreach($items as $item) {
				$possibles = $this->mongo_db->where(array("title"=>$item->title, "start_date"=>$item->start_date, "blurb"=>$item->blurb))->order_by(array("_id"))->get("content");
				if (sizeof($possibles) > 1) {
					print "Deleting {$item->_id}<br />";
					$this->mongo_db->where(array("_id"=>$item->_id))->delete("content");
				}
			}
		}
		
		public function old_zones() {
			$collections = $this->mongo_db->get_where("content_types", array("collection"=>true));
			foreach($collections as $collection) {
				$sections = $this->mongo_db->get_where("content", array("content_type"=>$collection->_id));
				foreach($sections as $section) {
					$zones = $section->zone;
					$keys = array_keys($zones);
					if ($keys[0] === 0) {
						$newzones = array();
						$newpublised = array();
						$zonelist = array();
						
						print "Need to fix ".$section->_id."<br />\n";
						$published = array_pop($this->mongo_db->get_where("published", array("_id"=>$section->_id)));
						foreach($zones as $zone) {
							if (!isset($zone["zone_urlid"]) || empty($zone["zone_urlid"])) {
								$urlid = url_title($zone["zone_name"],"-", true);
							} else {
								$urlid = $zone["zone_urlid"];
							}
							$newzones[$urlid] = $zone;
							$zonelist[] = $urlid;
						}
						
						for($x=0; $x < sizeof($published->zones); $x++) {
							$newpublished[$zonelist[$x]] = $published->zones[$x];
						}
						$this->mongo_db->where(array("_id"=>$section->_id))->update("content", array("zone"=>$newzones));
						$this->mongo_db->where(array("_id"=>$section->_id))->update("published", array("zones"=>$newpublished));
					}
				}
			}
		}
	}

/* End of file .php */
/* Location: ./system/application/controllers/ */