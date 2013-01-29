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
	}

/* End of file .php */
/* Location: ./system/application/controllers/ */