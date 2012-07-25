<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 
	/**
	 * 10Layer User Prefs Class
	 *
	 * Records user preferences so that stuff can automagically customise itself to user behaviour
	 *
	 * @package		10Layer
	 * @subpackage	Libraries
	 * @category	Libraries
	 * @author		Jason Norwood-Young
	 * @link		http://10layer.com
	 */
	
	class Tluserprefs {
		public $ci;
		protected $userid;
		protected $data;
		
		public function __construct() {
			$this->ci=&get_instance();
			$this->ci->load->library("mongo_db");
			$this->userid=$this->ci->session->userdata("id");
			$this->get_data();
		}
		
		public function user_setup() {
			if (empty($this->data->userid)) {
				$this->ci->mongo_db->insert("userprefs",array("userid"=>$this->userid, "last_login"=>time(), "login_count"=>1));
			} else {
				$this->ci->mongo_db->where(array("userid"=>$this->userid))->update("userprefs",array("last_login"=>time()));
				$this->ci->mongo_db->where(array("userid"=>$this->userid))->increment("userprefs",array("login_count"=>1));
			}
		}
		
		public function click_menu($menuitem) {
			if (empty($this->data->menus)) {
				$types=$this->ci->model_content->get_content_types();
				$data=array();
				foreach($types as $type) {
					$data[$type->urlid]["click_count"]=0;
				}
				$this->ci->mongo_db->where(array("userid"=>$this->userid))->update("userprefs",array("menus"=>$data));
				$this->get_data();
			}
			$data=false;
			if (isset($this->data->menus)) {
				$data=$this->data->menus;
			}
			if (!empty($data[$menuitem]["click_count"])) {
				$data[$menuitem]["click_count"]++;
				$data[$menuitem]["last_click"]=time();
			} else {
				$data[$menuitem]["click_count"]=1;
				$data[$menuitem]["last_click"]=time();
			}
			$this->ci->mongo_db->where(array("userid"=>$this->userid))->update("userprefs",array("menus"=>$data));
			$this->get_data();
			//$this->ci->mongo_db->where(array("userid"=>$this->userid))->increment("userprefs",array($menuitem=>1));
		}
		
		public function get_menus() {
			if (!isset($this->data->menus)) {
				return array();
			}
			return $this->data->menus;
		}
		
		public function get_last_menu() {
			$menus=$this->get_menus();
			if (empty($menus)) {
				return false;
			}
			$latest=0;
			$item=false;
			foreach($menus as $key=>$menu) {
				if (isset($menu["last_click"]) && $menu["last_click"]>$latest) {
					$item=$key;
					$latest=$menu["last_click"];
				}
			}
			return $item;
		}
		
		public function get_menus_order() {
			$menus=$this->get_menus();
			$sortnames=array();
			$sortvals=array();
			foreach($menus as $key=>$menu) {
				$sortnames[]=$key;
				$sortvals[]=$menu;
			}
			array_multisort($sortvals, SORT_DESC, $sortnames);
			return $sortnames;
		}
		
		protected function get_data() {
			$data=$this->ci->mongo_db->where(array("userid"=>$this->userid))->get("userprefs");
			if (empty($data)) {
				$this->data=false;
				return false;
			}
			$this->data=$data[0];
			
		}
		
		function get_all_users(){
			$users = $this->ci->db->query("select * from tl_users")->result();//=&get_instance();
			return $users;
		}
		
		function get_personal_pref(){
			//get all prefs
			$all_prefs = $this->ci->mongo_db->get("userprefs");
			foreach($all_prefs as $pref){
				print_r($pref->queues)."<br/><br/>============";
			}
			
		}
		
		function send_to($user_id, $item_id){
			//start by getting the user's prefs
			$raw = $this->ci->mongo_db->where(array("userid"=>$user_id))->get("userprefs");
			$user_data = $raw [0];
			
			//does this user have preferences
			if($user_data == null){
				//no prefs - create them
				$this->ci->mongo_db->insert("userprefs",array("userid"=>$user_id));
				//ok, we have created prefs - reload them
				$user_data = $this->ci->mongo_db->where(array("userid"=>$user_id))->get("userprefs");
			}
					
			//does this user have queues
			if (isset($user_data->queues)) {
				//find the personal queue (to change once we cater for more personal queues)
				
				//find the queue_id for that illusive personal queue
				$queue_id = "";
				foreach($user_data->queues as $queue){
					if(isset($queue["personal"]) AND $queue["personal"] == "personal"){
						$queue_id = $queue["id"];	
					}
				}
				
				//no personal queue
				if($queue_id == ""){
					//this users doesnt have a personal queue - lets see if we can create one for him
					$queue_id = time();
					$user_data->queues[$queue_id]["name"]="new personal queue";
					$user_data->queues[$queue_id]["order"]=1;
					$user_data->queues[$queue_id]["id"]=$queue_id;
					$user_data->queues[$queue_id]["width"]=220;
					$user_data->queues[$queue_id]["height"]=200;
					$user_data->queues[$queue_id]["personal"]="personal";
				}
				
				
				$includes = array();
				if(isset($user_data->queues[$queue_id]["includes"]) AND $user_data->queues[$queue_id]["includes"] != null){
					//echo "we have includes";
					$includes = $user_data->queues[$queue_id]["includes"];
				}
				
				if(!in_array($item_id, $includes)){
					array_push($includes, $item_id);
					$user_data->queues[$queue_id]["includes"] = $includes;
					$this->ci->mongo_db->where(array("userid"=>$user_id))->update("userprefs", array("queues"=>$user_data->queues));
					print "Item sent successfully...";
				}else{
					print "This Item has already been sent to the specified user...";
				}
				
				
				
				
				
			} else {
				//this users doesnt have queues - lets see if we can create one for him
				$queueid = time();
				$queues[$queueid]["name"]="new personal queue";
				$queues[$queueid]["order"]=1;
				$queues[$queueid]["id"]=$queueid;
				$queues[$queueid]["width"]=220;
				$queues[$queueid]["height"]=200;
				$queues[$queueid]["personal"]="personal";
				//create includes
				$queues[$queueid]["includes"]= array($item_id);
				
				$this->ci->mongo_db->where(array("userid"=>$user_id))->update("userprefs", array("queues"=>$queues));
				
				$user_data = $this->ci->mongo_db->where(array("userid"=>$user_id))->get("userprefs");
								
				print "Item sent successfully...";
			}
			
			
		}
		
		function remove_from($user_id, $item_id){
			//start by getting the user's prefs
			$raw = $this->ci->mongo_db->where(array("userid"=>$user_id))->get("userprefs");
			$user_data = $raw [0];
			
			//does this user have preferences
			if($user_data == null){
				//no prefs - create them
				$this->ci->mongo_db->insert("userprefs",array("userid"=>$user_id));
				//ok, we have created prefs - reload them
				$user_data = $this->ci->mongo_db->where(array("userid"=>$user_id))->get("userprefs");
			}
					
			//does this user have queues
			if (isset($user_data->queues)) {
				//find the personal queue (to change once we cater for more personal queues)

				//find the queue_id for that illusive personal queue
				$queue_id = "";
				foreach($user_data->queues as $queue){
					if(isset($queue["personal"]) AND $queue["personal"] == "personal"){
						$queue_id = $queue["id"];	
					}
				}
				
				
				$includes = array();
				if(isset($user_data->queues[$queue_id]["includes"]) AND $user_data->queues[$queue_id]["includes"] != null){
					//echo "we have includes";
					$includes = $user_data->queues[$queue_id]["includes"];
				}
				
				if(in_array($item_id, $includes)){
					
					// remove the elements who's values are yellow or red
					$includes = array_diff($includes, array($item_id));
					$user_data->queues[$queue_id]["includes"] = $includes;
					$this->ci->mongo_db->where(array("userid"=>$user_id))->update("userprefs", array("queues"=>$user_data->queues));
					print "Item removed successfully...";
				}else{
					print "This item is not in the selected user's queues...";
				}
					
				
			} else {				
				print "This user does not have queues...";
			}
			
		}
		
		
		
		public function set_queue($queueid,$data) {
			if (isset($this->data->queues)) {
				$queues=$this->data->queues;
			} else {
				$this->user_setup();
				$queues=array();
			}
			if (isset($this->data->queues[$queueid])) {
				$queues[$queueid]=array_replace_recursive((array) $queues[$queueid], (array) $data);
			} else {
				$queues[$queueid]=(array) $data;
				$queues[$queueid]["id"]=$queueid;
			}
			$this->ci->mongo_db->where(array("userid"=>$this->userid))->update("userprefs", array("queues"=>$queues));
			$this->get_data();
		}
		
		public function set_queue_name($queueid, $name="", $order, $width, $height, $personal="") {
			if (isset($this->data->queues)) {
				$queues=$this->data->queues;
			} else {
				$this->user_setup();
				$queues=array();
			}
			$queues[$queueid]["name"]=$name;
			$queues[$queueid]["order"]=$order;
			$queues[$queueid]["id"]=$queueid;
			$queues[$queueid]["width"]=$width;
			$queues[$queueid]["height"]=$height;
			if($personal == "on"){
				$queues[$queueid]["personal"]="personal";
			}
			if($personal == "off"){
				$queues[$queueid]["personal"]="";
			}			
			$this->ci->mongo_db->where(array("userid"=>$this->userid))->update("userprefs", array("queues"=>$queues));
		}
		
		public function get_queue($queueid) {
			if (isset($this->data->queues[$queueid])) {
				return $this->data->queues[$queueid];
			} else {
				return array();
			}
		}
		
		function personalise_que($id, $message){
			$queue = $this->get_queue($id);
			$this->set_queue_name($id,$queue["name"],$queue["order"],$queue["width"], $queue["height"], $message);
			$this->get_data();
		}
		
		function save_queue_order($id, $order){
			$queue = $this->get_queue($id);
			$queue["order"] = $order + 1;
			$this->set_queue_name($id,$queue["name"],$queue["order"],$queue["width"], $queue["height"]);
			$this->get_data();
		}
		
		function save_queue_size($id, $height, $width){
			$queue = $this->get_queue($id);
			$queue["height"] = $height;
			$queue["width"] = $width;
			$this->set_queue_name($id,$queue["name"],$queue["order"],$queue["width"], $queue["height"]);
			$this->get_data();
		}
		
		public function get_queues() {
			if (isset($this->data->queues)) {

				return $this->data->queues;
			} else {
				return array();
			}
		}
		
		public function delete_queue($queueid) {
			$queues=$this->data->queues;
			unset($queues[$queueid]);
			$this->ci->mongo_db->where(array("userid"=>$this->userid))->update("userprefs", array("queues"=>$queues));
		}
	}
?>