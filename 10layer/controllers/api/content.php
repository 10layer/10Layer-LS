<?php
	/**
	 * Content class
	 * 
	 * @extends CI_Controller
	 */
	class Content extends CI_Controller {
		
		protected $secure=false;
		protected $_render=true;
		private $_start_time=0;
		public $vars=array();
		public $data;
		
		/**
		 * __construct function.
		 * 
		 * @access public
		 * @return void
		 */
		public function __construct() {
			$this->_start_time=microtime(true);
			parent::__construct();
			$this->load->library("tlsecurity");
			$this->tlsecurity->ignore_security();
			$this->secure=$this->_check_secure();
			if (!$this->secure) {
				$this->mongo_db->where_gte("major_version", "4");
			}
			$this->data=array(
				"error"=>false,
				"timestamp"=>time(),
				"msg"=>"",
				"content"=>array()
			);
			$this->vars=array_merge($_GET, $_POST);
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
			$this->_render=false;
			$this->count();
			$this->_render=true;
			$this->_check_callbacks();
			$this->data["content"]=$this->mongo_db->get("content");
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
				$this->mongo_db->where(array("content_type"=>$content_type));
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
			$content=$this->mongo_db->get("content");
			$this->data["content"]=$content[0];
			$this->returndata();
		}
		
		/**
		 * save function.
		 * 
		 * @access public
		 * @return void
		 */
		public function save() {

			if(empty($_POST) || empty($_GET)){
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
			$data->last_modified=date("Y-m-d H:i:s");
			$user=$this->model_user->get_by_id($this->session->userdata("id"));
			$data->last_editor=$user->name;
			
			$id=$this->input->get_post("id");
			if (!empty($id)) {
				//Update
				$this->id();
				$result=$this->mongo_db->upsert('content', $data);
			} else {
				$data->content_type=$content_type;
				$data->timestamp=date("Y-m-d H:i:s");
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
			$this->data["msg"]="Saved $content_type";
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
		 * Only return content of a certain type
		 *
		 * @access protected
		 * @return void
		 */
		protected function content_type() {
			$content_type=$this->input->get_post("content_type");
			if (!empty($content_type)) {
				$this->mongo_db->where(array("content_type"=>$content_type));
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
		 * get_content_type function.
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
				}
			}
		}
		
		/**
		 * _check_secure function.
		 * 
		 * Returns true if we've sent a valid API key, else false
		 *
		 * @access private
		 * @return boolean
		 */
		private function _check_secure() {
			$api_key=$this->input->get_post("api_key");
			$api_key=trim($api_key);
			if (empty($api_key)) {
				return false;
			}
			$comp_api_key=$this->config->item('api_key');
			if (empty($comp_api_key)) {
				return false;
			}
			if ($comp_api_key==$api_key) {
				return true;
			}
			return false;
		}
		
		/**
		 * returndata function.
		 * 
		 * If $this->_render is true, print the results as json
		 *
		 * @access protected
		 * @return void
		 */
		protected function returndata() {
			$end_time=microtime(true);
			$processing_time=$end_time-$this->_start_time;
			$this->data["processing_time"]=$processing_time;
			if ($this->_render) {
				$this->load->view("json",array("data"=>$this->data));
			}
		}
	}

/* End of file content.php */
/* Location: ./system/application/controllers/api/ */