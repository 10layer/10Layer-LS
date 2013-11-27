<?php
	require_once('10layer/system/TL_Content.php');
	
	/**
	 * Model_Content class.
	 * 
	 * Slimmed down Content Model that forms the base of custom and generic content types.
	 *
	 * @extends CI_Model
	 * @package 10Layer
	 * @subpackage Models
	 */
	class Model_Content extends CI_Model {
		/**
		 * content_type
		 * 
		 * Content type object (default value: false)
		 * 
		 * @var object
		 * @access protected
		 */
		protected $content_type=false;
		/**
		 * platform
		 * 
		 * Platform object (default value: false)
		 * 
		 * @var object
		 * @access protected
		 */
		protected $platform=false;
		
		/**
		 * order_by
		 * 
		 * Array to order lists by. Tail with "desc" to order descending. 
		 *
		 * @var mixed
		 * @access protected
		 */
		public $order_by=array();
		 
		public $_default_order_by=array(
			"content.start_date DESC",
			"content.last_modified DESC"
		);
		/**
		 * limit
		 * 
		 * Limit on lists (default value: 100)
		 * 
		 * @var int
		 * @access protected
		 */
		protected $limit=100;
		 
		/**
		 * start
		 * 
		 * Start value on lists (default value: 0)
		 *
		 * @var int
		 * @access protected
		 */
		protected $start=0;
		
		/**
		 * error
		 * 
		 * Set to true if we encounter an error - primarily 
		 * for healthcheck, else we just throw the error
		 *
		 * @var boolean
		 * @access public
		 * @default false
		 */
		public $error=false;
		
		/**
		 * errormsg
		 * 
		 * Set to message if we encounter an error - primarily 
		 * for healthcheck, else we just throw the error
		 *
		 * @var string
		 * @access public
		 * @default false
		 */
		public $errormsg="";
		
		/**
		 * fields
		 * 
		 * An array of fields. See documentation for more details
		 *
		 * @var array
		 * @access public
		 */
		public $fields=array();
		
		public $_default_fields=array(
			array(
				"name"=>"id",
				"type"=>"hidden",
				"tablename"=>"content",
			),
			array(
				"name"=>"urlid",
				"tablename"=>"content",
				"hidden"=>true,
				"transformations"=>array(
					"copy"=>"title",
					"urlid"=>"content.urlid",
				),
			),
			array(
				"name"=>"title",
				"tablename"=>"content",
				"class"=>"bigger",
				"label_class"=>"bigger",
				"rules"=>array(
					"required",
				),
				"transformations"=>array(
					"safetext"
				),
				"libraries"=>array(
					"semantic"=>true,
					"search"=>"like",
				),
				"type"=>"textarea"
			),
			array(
				"name"=>"last_modified",
				"tablename"=>"content",
				"hidden"=>true,
				"transformations"=>array(
					"date('c')",
				),
			),
			array(
				"name"=>"start_date",
				"tablename"=>"content",
				"type"=>"date",
				"value"=>'Today',
			),
			array(
				"name"=>"end_date",
				"tablename"=>"content",
				"type"=>"date",
				"value"=>"2100-01-01",
			),
			array(
				"name"=>"workflow_status",
				"type"=>"select",
				"options"=>array(
					"New",
					"Edited",
					"Published"
				),
			),
		);
		
		
		/**
		 * __construct function.
		 * 
		 * @access public
		 * @return void
		 */
		public function __construct($content_type=false) {
			parent::__construct();
			if (!empty($content_type)) {
				$this->setContentType($content_type);
			}
			$this->fields=array_merge($this->_default_fields, $this->fields);
			if (empty($this->order_by)) {
				$this->order_by=$this->_default_order_by;
			}
		}
		
		/**
		 * get function.
		 * 
		 * If you put in an id number or urlid it'll return a single item, else it'll return a list
		 *
		 * @access public
		 * @param int $id. (default: false)
		 * @return object
		 */
		public function get($id=false) {
			if (empty($id)) {
				return $this->getAll();
			} else {
				return $this->getById($id);
			}
		}
		
		/**
		 * getById function.
		 * 
		 * Gets an item by its id or urlid
		 *
		 * @access public
		 * @param mixed $id
		 * @return object
		 */
		public function getById($id) {
			return $this->getContent($id);
		}
		
		/**
		 * getByIdORM function.
		 * 
		 * Gets an item by id or urlid but returns the ORM representation
		 *
		 * @access public
		 * @param mixed $id
		 * @return TLContent
		 */
		public function getByIdORM($id, $contenttype_id=false) {
			$content=new TLContent($id, $contenttype_id);
			/*if (!empty($this->content_type)) {
				$content->setContentType($this->content_type->urlid);
			}*/
			return $content;
		}
		
		/**
		 * check_content_type function.
		 * 
		 * Returns this object's content_type by content id
		 *
		 * @access public
		 * @param mixed $id
		 * @return object
		 */
		public function check_content_type($id) {
			$cache = $this->mongo_db->state_save();
			$query = $this->mongo_db->select("content_type")->where(array("_id"=>$id))->get_one("content");
			$this->mongo_db->state_restore($cache);
			if (!isset($query->content_type)) {
				return false;
			}
			return $query->content_type;
		}
		
		/**
		 * getAll function.
		 * 
		 * Returns a list of content
		 *
		 * @access public
		 * @param int $limit. (default: false)
		 * @param int $start. (default: false)
		 * @return object
		 */
		public function getAll($limit=false, $start=false, $all=false) {
			$query=$this->mongo_db->where(array('content_type'=>$this->content_type->urlid))->limit($limit, $start)->order_by($this->order_by)->get('content');
			$results = array();
			foreach($query as $item) {
				$results[] = (object) $item;
			}
			return $results;
		}
		
		/**
		 * getContent function.
		 * 
		 * Gets content by ID or urlid
		 *
		 * @access public
		 * @param mixed $id
		 * @return object
		 */
		public function getContent($id) {
			return $this->mongo_db->get_where("content",array("_id"=>$id));
		}
		
		/**
		 * count function.
		 * 
		 * Returns number of results we can expect from a list
		 *
		 * @access public
		 * @param bool $extensions. (default: false)
		 * @return int
		 */
		public function count($extensions=false) {
			return $this->mongo_db->where(array('content_type'=>$this->content_type->urlid))->count('content');
		}
		
		/**
		 * limit function.
		 * 
		 * Sets a limit on our queries
		 *
		 * @access public
		 * @param bool $limit. (default: false)
		 * @param bool $start. (default: false)
		 * @return object this
		 */
		public function limit($limit=false, $start=false) {
			if (!empty($limit)) {
				$this->limit=$limit;
			}
			if (!empty($start)) {
				$this->start=$start;
			}
			return $this;
		}
		
		/**
		 * create function.
		 * 
		 * Super-useful function to create a new object.
		 *
		 * @access public
		 * @return void
		 */
		public function create($data) {
			$contentobj=new TLContent();
			$contentobj->setContentType($this->content_type->id);
			foreach($contentobj->getFields() as $field) {
				if ($field->readonly) {
					//Do NOTHING
				} else {
					if (isset($data[$field->tablename."_".$field->name])) {
						$fieldval=$data[$field->tablename."_".$field->name];
						$contentobj->{$field->name}=$fieldval;
					} else {
						$contentobj->{$field->name}="";
					}
				}
			}
			$contentobj->transformFields();
			$validation=$contentobj->validateFields();
			if (!$validation["passed"]) {
				show_error($validation["failed_messages"]);
				return false;
			}
			$contentobj->insert();
			return $contentobj;
		}
		
		/**
		 * searchCount function.
		 * 
		 * Returns the count of a search
		 *
		 * @access public
		 * @param mixed $content_type
		 * @param mixed $searchstr
		 * @return int
		 */
		public function searchCount($content_type, $searchstr) {
			$contentobj=new TLContent();
			$contentobj->setContentType($content_type);
			$fields=$contentobj->getFields();
			$searchfields = array("content_type"=>$content_type);
			foreach($fields as $field) {
				if (isset($field->libraries["search"])) {
					$this->mongo_db->like($field->name, $searchstr);
				}
			}
			return $this->mongo_db->where($searchfields)->count('content');
		}
		
		/**
		 * search function.
		 * 
		 * Returns the results of a search
		 *
		 * @access public
		 * @param string $content_type
		 * @param string $searchstr
		 * @param int $limit
		 * @param int $start. (default: 0)
		 * @return object
		 */
		public function search($content_type, $searchstr,$limit,$start=0) {
			$contentobj=new TLContent();
			$contentobj->setContentType($content_type);
			$fields=$contentobj->getFields();
			$searchfields = array("content_type"=>$content_type);
			foreach($fields as $field) {
				if (isset($field->libraries["search"])) {
					$this->mongo_db->like($field->name, $searchstr);
				}
			}
			$query=$this->mongo_db->where($searchfields)->limit($limit, $start)->order_by($this->order_by)->get('content');
			$results = array();
			foreach($query as $item) {
				$results[] = (object) $item;
			}
			return $results;
		}

		/**
		 * get_content_types function.
		 * 
		 * Returns a list of possible content types
		 *
		 * @access public
		 * @param $public Show only public content types
		 * @return object
		 */
		public function get_content_types($public=true) {
			if ($public) {
				//$this->mongo_db->where(array("public"=>true));
			}
			$result=$this->mongo_db->order_by(array("_id"))->get("content_types");
			return $result;
		}
		
		/**
		 * get_content_types_list function.
		 * 
		 * Returns a list of possible content types, just id and name
		 *
		 * @access public
		 * @param $public Show only public content types
		 * @return object
		 */
		public function get_content_types_list() {
			return $this->mongo_db->select(array("_id", "name"))->order_by(array("_id"))->get("content_types");
		}
		
		/**
		 * get_content_type function.
		 * 
		 * Returns a single content type
		 *
		 * @access public
		 * @param $id ID or urlid
		 * @return object
		 */
		public function get_content_type($id) {
			$result=$this->mongo_db->get_where("content_types", array("_id"=>$id));
			return $result[0];
		}
		
	}


/* End of file Model_Content.php */
/* Location: ./system/application/models/ */