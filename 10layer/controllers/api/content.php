<?php
	require_once('10layer/system/TL_Api.php');
	
	/**
	 * Content class
	 * 
	 * @extends CI_Controller
	 */
	class Content extends TL_Api {
		
		/**
		 * __construct function.
		 * 
		 * @access public
		 * @return void
		 */
		public function __construct() {
			parent::__construct();
			if (empty($this->vars)) {
				$this->data["error"]=true;
				$this->data["msg"]="No GET or POST variables";
				$this->returndata();
				return true;
			}
		}
		
		/**
		 * index function.
		 * 
		 * Shortcut to "listing", so you don't need to call the listing
		 * method in your url.
		 *
		 * @access public
		 * @return void
		 */
		public function index() {
			$this->listing();
		}
		
		/**
		 * listing function.
		 * 
		 * Used to return a list, but can also return a single item, 
		 * although in that case "get" would be faster. Includes a 
		 * total count.
		 *
		 * @access public
		 * @return void
		 */
		public function listing() {
			
			$this->published();
			$this->_check_callbacks();
			$this->data["content"]=$this->mongo_db->get("content");
			if (!empty($this->data["criteria"]["limit"])) {
				$this->_render=false;
				$this->count();
				$this->_render=true;
			} else {
				$this->data["count"]=sizeof($this->data["content"]);
			}
			$this->returndata();
			return true;
		}
		
		/**
		 * count function.
		 * 
		 * Returns a count matching the criteria
		 * 
		 * @access public
		 * @return void
		 */
		public function count() {
			$this->_check_callbacks();
			$content_type=$this->input->get_post("content_type");
			if (!empty($content_type)) {
				if (is_array($content_type)) {
					$this->mongo_db->where_in("content_type", $content_type);
				} else {
					$this->mongo_db->where(array("content_type"=>$content_type));
				}
			}
			$this->data["count"]=$this->mongo_db->count("content");
			$this->returndata();
			return true;
		}
		
		/**
		 * get function.
		 * 
		 * Returns a single item, a bit faster than listing because we
		 * don't do a count.
		 *
		 * @access public
		 * @return void
		 */
		public function get() {
			$this->_check_callbacks();
			$this->data["count"]=1;
			$this->mongo_db->limit(1);
			$this->data["criteria"]["limit"]=1;
			$content=array_pop($this->mongo_db->limit(1)->get("content"));
			if (isset($content)) {
				$this->data["content"]=$content;
			} else {
				$this->data["error"]=true;
				$this->data["msg"]="Content not found";
			}
			$this->returndata();
		}


		/**
		 * get_linked_object function.
		 * 
		 * Returns a single item with its linked items (urlid and title), a bit faster than listing because we
		 * don't do a count.
		 *
		 * @access public
		 * @return void
		 */
		public function get_linked_object() {
			function array_values_recursive($ary) {
				$lst = array();
				foreach( array_keys($ary) as $k ){
					$v = $ary[$k];
					if (is_scalar($v)) {
						$lst[] = $v;
					} elseif (is_array($v)) {
						$lst = array_merge( $lst,
						array_values_recursive($v)
						);
					}
				}
				return $lst;
			}
			$this->_check_callbacks();
			$this->data["count"]=1;
			$this->mongo_db->limit(1);
			$this->data["criteria"]["limit"]=1;
			$content=array_pop($this->mongo_db->limit(1)->get("content"));

			$content_type=$this->get_content_type();
			$fields=$this->get_field_data($content_type);

			$observed = array();
			//scan the fields...
			foreach($fields as $field){
				if(isset($field->content_types) AND !is_array($field->content_types)){
					array_push($observed, $field);
				}
			}
			foreach($observed as $the_field){
				$field_name = $the_field->name;
				$value = $content->$field_name;
				if(is_array($value)){
					$vals = array_values_recursive($value);
					$content->$field_name = $this->mongo_db->where_in("_id", $vals)->get('content');
				} else {
					$content->$field_name = $this->mongo_db->where(array("_id"=>$value))->get('content');
				}
			}
				
			if (isset($content)) {
				$this->data["content"]=$content;
			} else {
				$this->data["error"]=true;
				$this->data["msg"]="Content not found";
			}
			$this->returndata();
		}

		
		/**
		 * save function.
		 * 
		 * @access public
		 * @return void
		 */
		public function save() {
			if(empty($_POST) && empty($_GET)){
				$this->data["error"]=true;
				$this->data["msg"][]="We did not receive data";
				$this->returndata();
				return false;
			}
			if (!$this->secure) {
				$this->data["error"]=true;
				$this->data["msg"][]="You do not have permission to save";
				$this->returndata();
				return false;
			}
			$content_type=$this->get_content_type();
			if (empty($content_type)) {
				$this->data["error"]=true;
				$this->data["msg"][]="Content type not found";
				$this->returndata();
				return false;
			}
			$contentobj=new TLContent($content_type);
			//Populate
			foreach($contentobj->getFields() as $field) {
				if ($field->readonly) {
					continue;
				}
				$fieldval=$this->input->post($field->contenttype."_".$field->name);
				//If it's empty, try just the name
				if (empty($fieldval)) {
					$fieldval=$this->input->post($field->name);
				}
				//Check if it's JSON, if so extract
				@$json = json_decode($fieldval);
				if (!empty($json)) {
					$fieldval = $json;
				}
				//Check if it's JSON in an array
				if (is_array($fieldval)) {
					foreach($fieldval as $key=>$val) {
						@$json = json_decode($val);
						if (!empty($json)) {
							$fieldval[$key]=$json;
						}
					}
				}
				if (empty($fieldval)) {
					$contentobj->{$field->name}="";
				} else {
					$contentobj->{$field->name}=$fieldval;
				}
			}
			//Files
			//Transform
			$contentobj->transformFields();
			//Validate
			$validation=$contentobj->validateFields();
			if (!$validation["passed"]) {
				$this->data["error"]=true;
				$this->data["msg"][]="Failed to save {$content_type}";
				$this->data["info"]=$validation["failed_messages"];
				$this->returndata();
				return false;
			}
			//Save
			$data=$contentobj->getData();
			$urlid=$contentobj->fields["urlid"]->value;
			unset($data->id);
			unset($data->urlid);
			unset($data->content_id);
			$data->last_modified=time();
			$content_title = $data->title;
			$user=$this->model_user->get_by_id($this->session->userdata("id"));
			if (empty($user)) {
				$data->last_editor = $this->input->get_post("api_key");
			} else {
				$data->last_editor=$user->name;
			}
			$this->load->helper('data');
			foreach($data as $key => $value){
				if(is_array($data->$key)){
					if(array_empty($value)){
						$data->$key = false;
					}
				}
			}
			$id=$this->input->get_post("id");
			if (!empty($id)) {
				//Update
				$this->id();
				$result=$this->mongo_db->upsert('content', $data);
			} else {
				$data->content_type=$content_type;
				$data->timestamp=time();
				$data->_id=$urlid;
				$result=$this->mongo_db->insert('content', $data);
			}
			//unset($data->id);
			$this->data["id"]=$urlid;
			if (!$result) {
				$this->data["error"]=true;
				$this->data["msg"][]="Failed to save {$content_type} - Mongo DB error";
				$this->returndata();
				return false;
			}
			$this->data["title"]=$content_title;
			$this->data["msg"]="Saved $content_type";
			$this->returndata();
		}
		
		/**
		 * delete function.
		 * 
		 * Moves a document to "content_deleted" section. Requires "id".
		 *
		 * @access public
		 * @return void
		 */
		public function delete() {
			if(empty($_POST) && empty($_GET)){
				$this->data["error"]=true;
				$this->data["msg"][]="We did not receive data";
				$this->returndata();
				return false;
			}
			if (!$this->secure) {
				$this->data["error"]=true;
				$this->data["msg"][]="You do not have permission to delete";
				$this->returndata();
				return false;
			}
			$id=$this->input->get_post("id");
			if (empty($id)) {
				$this->data["error"]=true;
				$this->data["msg"][]="ID must be set";
				$this->returndata();
				return false;
			}
			$result=array_pop($this->mongo_db->get_where("content", array("_id"=>$id)));
			if (empty($result)) {
				$this->data["error"]=true;
				$this->data["msg"][]="ID $id not found";
				$this->returndata();
				return false;
			}
			if ($this->mongo_db->insert("content_deleted", $result)) {
				$this->mongo_db->where(array("_id"=>$id))->delete("content");
				$this->data["msg"]="Item $id deleted";
			} else {
				$this->data["error"]=true;
				$this->data["msg"][]="Error deleting $id";
			}
			$this->returndata();
		}
		
		/**
		 * undelete function.
		 *
		 * Moves a document from "content_deleted" collection back to "content" collection. Requires "id".
		 * 
		 * @access public
		 * @return void
		 */
		public function undelete() {
			if(empty($_POST) && empty($_GET)){
				$this->data["error"]=true;
				$this->data["msg"][]="We did not receive data";
				$this->returndata();
				return false;
			}
			if (!$this->secure) {
				$this->data["error"]=true;
				$this->data["msg"][]="You do not have permission to undelete";
				$this->returndata();
				return false;
			}
			$id=$this->input->get_post("id");
			if (empty($id)) {
				$this->data["error"]=true;
				$this->data["msg"][]="ID must be set";
				$this->returndata();
				return false;
			}
			$result=array_pop($this->mongo_db->get_where("content_deleted", array("_id"=>$id)));
			if (empty($result)) {
				$this->data["error"]=true;
				$this->data["msg"][]="ID $id not found";
				$this->returndata();
				return false;
			}
			if ($this->mongo_db->insert("content", $result)) {
				$this->mongo_db->where(array("_id"=>$id))->delete("content_deleted");
				$this->data["msg"]="Item $id undeleted";
			} else {
				$this->data["error"]=true;
				$this->data["msg"][]="Error undeleting $id";
			}
			$this->returndata();
		}
		
		/**
		 * multiple function.
		 * 
		 * Allows for multiple ops to be executed with one request, eg. delete a number of files.
		 * Expects an array called items
		 *
		 * @access public
		 * @param mixed $action
		 * @return void
		 */
		public function multiple($action) {
			if(empty($_POST) && empty($_GET)){
				$this->data["error"]=true;
				$this->data["msg"][]="We did not receive data";
				$this->returndata();
				return false;
			}
			if (!method_exists($this, $action)) {
				$this->data["error"]=true;
				$this->data["msg"][]="Action $action not found";
				$this->returndata();
				return false;
			}
			$items = $this->input->get_post("items");
			if (empty($items) || !is_array($items)) {
				$this->data["error"]=true;
				$this->data["msg"][]="Items must be an array";
				$this->returndata();
				return false;
			}
			$api_key = $this->input->get_post("api_key");
			$url = base_url()."api/content/$action?api_key=$api_key";
			$this->data["content"]=array();
			foreach($items as $item) {
				$ch = curl_init(); 
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $item);
				$output = curl_exec($ch); 
				curl_close($ch);
				$this->data["content"][] = $output;
			}
			$this->returndata();
		}
		
		/**
		 * blank function.
		 * 
		 * Used to return data without doing a MongoDB lookup, for instance to get the meta data for a content type
		 * @access public
		 * @return void
		 */
		public function blank() {
			$this->_check_callbacks();
			$this->data["count"]=0;
			$this->data["criteria"]["limit"]=0;
			$this->data["content"]=false;
			$this->returndata();
		}
		
		//Callbacks
		
		/**
		 * content_type function.
		 * 
		 * Only return content of a certain type. Send an array to return from multiple content types.
		 *
		 * @access protected
		 * @return void
		 */
		protected function content_type() {
			$content_type=$this->input->get_post("content_type");
			if (!empty($content_type)) {
				if (is_array($content_type)) {
					$this->mongo_db->where_in("content_type", $content_type);
				} else {
					$this->mongo_db->where(array("content_type"=>$content_type));
				}
				$this->data["criteria"]["content_type"]=$content_type;
			}
		}
		
		/**
		 * limit function.
		 * 
		 * Limit results. Usually a good idea.
		 *
		 * @access protected
		 * @return void
		 */
		protected function limit() {
			$limit=$this->input->get_post("limit");
			if (!empty($limit)) {
				$this->mongo_db->limit($limit);
				$this->data["criteria"]["limit"]=$limit;
			}
		}
		
		/**
		 * offset function.
		 * 
		 * Listing offset
		 *
		 * @access protected
		 * @return void
		 */
		protected function offset() {
			$offset=$this->input->get_post("offset");
			if (!empty($offset)) {
				$this->mongo_db->offset($offset);
				$this->data["criteria"]["offset"]=$offset;
			}
		}
		
		/**
		 * order_by function.
		 * 
		 * Order by - can be an array, and can have DESC to order descending
		 *
		 * @access protected
		 * @return void
		 */
		protected function order_by() {
			$order_by=$this->input->get_post("order_by");
			if (!empty($order_by)) {
				if (!is_array($order_by)) {
					$order_by=array($order_by);
				}
				$this->mongo_db->order_by($order_by);
				$this->data["criteria"]["order_by"]=$order_by;
			}
		}
		
		/**
		 * id function.
		 * 
		 * Return content matching ID
		 *
		 * @access protected
		 * @return void
		 */
		protected function id() {
			$id=$this->input->get_post("id");
			if (!empty($id)) {
				$this->mongo_db->where(array("_id"=>$id));
				$this->data["criteria"]["id"]=$id;
			}
		}
		
		/**
		 * ids function.
		 * 
		 * Used for looking up multiple IDs in one shot
		 *
		 * @access protected
		 * @return void
		 */
		protected function ids() {
			$ids=$this->input->get_post("ids");
			$this->mongo_db->where_in("_id", $ids);
			$this->data["criteria"]["id"]=$ids;
		}
		
		/**
		 * published function.
		 *
		 * Only return published items
		 * 
		 * @access protected
		 * @return void
		 */
		protected function published() {
			if (!$this->secure) {
				$this->mongo_db->where(array("workflow_status"=>3));
				return true;
			}
			$published=$this->input->get_post("published");
			if (!empty($published)) {
				$this->mongo_db->where(array("workflow_status"=>3));
				$this->data["criteria"]["published"]=true;
				return true;
			}
			$this->data["criteria"]["published"]=false;
		}
		
		/**
		 * search function.
		 * 
		 * Searches title for a search string
		 *
		 * @access protected
		 * @return void
		 */
		protected function search() {
			$search=$this->input->get_post("search");
			if (!empty($search)) {
				$this->mongo_db->like("title", $search);
				$this->data["criteria"]["search"]=$search;
			}

		}
		
		/**
		 * fields function.
		 * 
		 * Send a list of fields to limit amount of data returned
		 *
		 * @access protected
		 * @return void
		 */
		protected function fields() {
			$fields=$this->input->get_post("fields");
			if (!is_array($fields)) {
				$fields=array($fields);
			}
			$this->mongo_db->select($fields);
		}
		
		/**
		 * exclude function.
		 *
		 * Exclude items with _id from the results
		 * 
		 * @access protected
		 * @return void
		 */
		protected function exclude() {
			$exclude=$this->vars["exclude"];
			if (!empty($exclude)) {
				if (!is_array($exclude)) {
					$exclude=array($exclude);
				}
				$this->mongo_db->where_not_in("_id", $exclude); //Only ID for now - we should probably allow any field
				$this->data["criteria"]["exclude"]=$exclude;
			}
		}
		
		/**
		 * meta function.
		 * 
		 * Return meta data about the fields
		 *
		 * @access protected
		 * @return void
		 */
		protected function meta() {
			$content_type=$this->get_content_type();
			if (empty($content_type)) {
				$this->data["error"]=true;
				$this->data["msg"][]="Could not find content type to retrieve meta information";
				return true;
			}
			$cache=$this->mongo_db->state_save();
			$fields=$this->get_field_data($content_type);
			$this->mongo_db->state_restore($cache);
			if (empty($fields)) {
				$this->data["error"]=true;
				$this->data["msg"][]="Could not find model for content type $content_type to retrieve meta information";
				return true;
			}
			$this->data["meta"]=$fields;
		}
		
		/**
		 * start_date function.
		 * 
		 * Ensure that "start_date" is greater than value. Value must be a Unix timestamp.
		 *
		 * @access protected
		 * @return void
		 */
		protected function start_date() {
			$start_date=$this->vars["start_date"];
			if (!empty($start_date)) {
				$this->mongo_db->where_gte("start_date", (Int) $start_date); //Only ID for now - we should probably allow any field
				$this->data["criteria"]["start_date"]=date("c", $start_date);
			}
		}
		
		/**
		 * end_date function.
		 * 
		 * Ensure that "start_date" is less than value. Value must be a Unix timestamp.
		 *
		 * @access protected
		 * @return void
		 */
		protected function end_date() {
			$end_date=$this->vars["end_date"];
			if (!empty($end_date)) {
				$this->mongo_db->where_lte("start_date", (Int) $end_date); //Only ID for now - we should probably allow any field
				$this->data["criteria"]["end_date"]=date("c", $end_date);
			}
		}
		
		/**
		 * get_content_type function.
		 * 
		 * Returns the content type of an item by id
		 *
		 * @access protected
		 * @return String content_type
		 */
		protected function get_content_type() {
			$content_type=$this->input->get_post("content_type");
			if (empty($content_type)) {
				$id=$this->input->get_post("id");
				if (empty($id)) {
					return false;
				}
				$cache=$this->mongo_db->state_save();
				$result=$this->mongo_db->get_where("content", array("_id"=>$id));
				if (empty($result)) {
					$this->data["error"]=true;
					$this->data["msg"][]="Could not find content ID $id";
					return false;
				}
				$this->mongo_db->state_restore($cache);
				$content_type=$result[0]->content_type;
			}
			return $content_type;
		}
		
		/**
		 * get_field_data function.
		 * 
		 * @access protected
		 * @param String $content_type
		 * @return array
		 */
		protected function get_field_data($content_type) {
			try {
				$contentobj=new TLContent($content_type);
				$fields=$contentobj->getFields();
				return $fields;
			} catch(Exception $e) {
				return false;
			}
		}
		
		/**
		 * _check_callbacks function.
		 * 
		 * @access private
		 * @return void
		 */
		private function _check_callbacks() {
			foreach($this->vars as $key=>$val) {
				if (method_exists($this, $key)) {
					call_user_func(array($this, $key));
				} elseif ($this->secure) {
					if (substr($key, 0, 6)=="where_") {
						$key=substr($key, 6);
						if (is_array($val)) {
							$this->mongo_db->where_in($key, $val);
						} else {
							if (is_numeric($val)) {
								$val = (Integer) $val;
							}
							$this->mongo_db->where(array($key=>$val));
						}
						$this->data["criteria"]["where_$key"]=$val;
					} 
				}
			}
		}
		
	}

/* End of file content.php */
/* Location: ./system/application/controllers/api/ */