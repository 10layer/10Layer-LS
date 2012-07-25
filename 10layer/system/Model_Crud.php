<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Model_CRUD class.
 *
 * This is a model that does all the CRUD work for our TL_Controller_Crud
 *
 * This model is in the process of being DEPRECATED!!!
 * 
 * @extends CI_Model
 * @package 10Layer
 * @subpackage Deprecated
 */
class Model_CRUD_DEPRECATED extends CI_Model {
	protected $_tablename;
	protected $_fields;
	//protected $_relationships;
	protected $_orderby=false;
	protected $_groupby=false;
	protected $_joins=false;
	protected $_where=false;
	
	/**
	 * __construct function.
	 * 
	 * @access public
	 * @param bool $tablename. (default: false)
	 * @return void
	 */
	public function __construct($tablename=false) {
		parent::__construct();
		if (!empty($tablename)) {
			$this->setTablename($tablename);
		}
		//$this->relationships=array();
	}
	
	/**
	 * setTablename function.
	 * 
	 * @access public
	 * @param mixed $tablename
	 * @return object
	 */
	public function setTablename($tablename) {
		$this->_tablename=$tablename;
		return $this;
	}
	
	/**
	 * getTablename function.
	 * 
	 * @access public
	 * @return string Active table name
	 */
	public function getTablename() {
		return $this->_tablename;
	}
	
	/**
	 * getById function.
	 * 
	 * @access public
	 * @param mixed $id
	 * @return object Resultant row
	 */
	public function getById($id) {
		//Select
		foreach($this->_fields as $field) {
			if (empty($field->relationship)) {
				$this->db->select($field->tablename.".".$field->name);
			} else {
				$this->db->select($field->tablename.".".$field->name." AS ".$field->tablename."_".$field->name);
			}
		}
		//Set our Joins
		/*foreach($this->_relationships as $relationship) {
			$relationship->dbJoin();
		}*/
		$this->db->where($this->_tablename.".".$this->_lookup_field($id),$id);
		$result=$this->db->get($this->_tablename);
		$this->_populateFields($result->row());
		$this->_populateRelationships($result->result());
		return $result->row();
	}
	
	/**
	 * get function.
	 * 
	 * @access public
	 * @param bool $limit. (default: false)
	 * @param bool $start. (default: false)
	 * @return array Array of rows
	 */
	public function get($limit=false,$start=false) {
		//Select
		foreach($this->_fields as $field) {
			$this->db->select($field->tablename.".".$field->name);
		}
		
		//Set our limit
		$this->db->limit($limit,$start);
		//Set our order by
		if (!empty($this->_orderby)) {
			foreach($this->_orderby as $orderby) {
				$this->db->order_by($orderby);
			}
		}
		//Set our Joins
		foreach($this->_relationships as $relationship) {
			$relationship->dbJoin();
		}
		//Limit by Where clauses
		if (!empty($this->_where)) {
			foreach($this->_where as $where) {
				$this->db->where($where[0],$where[1]);
			}
		}
		//Set our Group By
		if (!empty($this->_groupby)) {
			foreach($this->_groupby as $groupby) {
				$this->db->group_by($groupby);
			}
		}
		//Get our results
		$result=$this->db->get($this->_tablename);
		//Return result set
		return $result->result();
	}
	
	/**
	 * count function.
	 * 
	 * Returns total rows in table
	 *
	 * @access public
	 * @return integer
	 */
	public function count() {
		foreach($this->_relationships as $relationship) {
			$relationship->dbJoin();
		}
		if (!empty($this->_where)) {
			foreach($this->_where as $where) {
				$this->db->where($where[0],$where[1]);
			}
		}
		$this->db->select("COUNT(*) AS numrows");
		//$this->db->group_by("{$this->_tablename}.id");
		$query=$this->db->get($this->_tablename);
		return $query->row()->numrows;
	}
	
	/**
	 * create function.
	 * 
	 * @access public
	 * @param array $data
	 * @return int Insert ID
	 */
	public function create($data) {
		$data["last_modified"]=date("c");
		$this->db->insert($this->_tablename,$data);
		return $this->db->insert_id();
	}
	
	/**
	 * update function.
	 * 
	 * @access public
	 * @param mixed $id
	 * @param array $data
	 * @return bool
	 */
	public function update($id,$data) {
		$data["last_modified"]=date("c");
		$this->db->where($this->_lookup_field($id),$id);
		$this->db->update($this->_tablename,$data);
		//Set our Joins
		foreach($this->_relationships as $relationship) {
			$relationship->dbUpdate($id,$data);
		}
		return true;
	}
	
	/**
	 * delete function.
	 * 
	 * @access public
	 * @param mixed $id
	 * @return bool
	 */
	public function delete($id) {
		$this->db->where($this->_lookup_field($id),$id);
		$this->db->delete($this->_tablename);
		return true;
	}
	
	/**
	 * addWhere function.
	 * 
	 * @access public
	 * @param string $fieldname
	 * @param mixed $value
	 * @return object $this
	 */
	public function addWhere($fieldname,$value) {
		$this->_where[]=array($fieldname,$value);
		return $this;
	}
	
	/**
	 * addGroupBy function.
	 * 
	 * @access public
	 * @param string $fieldname
	 * @return object $this
	 */
	public function addGroupBy($fieldname) {
		$this->_groupby[]=$fieldname;
		return $this;
	}
	
	/**
	 * addFields function.
	 * 
	 * @access public
	 * @param array $fields
	 * @return object $this
	 */
	public function addFields($fields) {
		$this->_fields=array();
		foreach($fields as $field) {
			$this->addField($field);
		}
		return $this;
	}
	
	/**
	 * addField function.
	 * 
	 * @access public
	 * @param array $field
	 * @return object $this
	 */
	public function addField($field) {
		if (empty($field["tablename"])) {
			$field["tablename"]=$this->_tablename;
		}
		$this->_fields[]=new TLField($field);
		if (!empty($field->relationship)) {
			$this->_setRelationship($field);
		}
		return $this;
	}
	
	protected function _setRelationship($field) {
		$relationship=$field["relationship"];
		$type=$relationship["type"];
		$relobj=new TLRelationship();
		switch ($type) {
			case "m2m": 
				$relobj->addJoin(
					$relationship["join_table"],$this->_tablename.".id = ".$relationship["join_table"].".".$relationship["join_key"], true
				)->addJoin(
					$relationship["foreign_table"],$relationship["join_table"].".".$relationship["join_foreign_key"]." = ".$relationship["foreign_table"].".id"
				);
				break;
			case "o2m":
				show_error("One to many relationship not implemented yet");
				break;
			default:
				"Relationship type must be m2m or o2m";
				break;
		}
		$this->addRelationship($relobj);
	}
	
	/**
	 * getFields function.
	 * 
	 * @access public
	 * @return array Field array
	 */
	public function getFields() {
		return $this->_fields;
	}
	
	/**
	 * getField function.
	 * 
	 * @access public
	 * @param mixed $fieldname
	 * @return object TLField
	 */
	public function getField($fieldname) {
		foreach($this->_fields as $field) {
			if ($field->name==$fieldname) {
				return $field;
			}
		}
		return false;
	}
	
	/**
	 * addRelationship function.
	 * 
	 * @access public
	 * @param TLRelationship $relationship
	 * @return object $this
	 */
	public function addRelationship($relationship) {
		$this->_relationships[]=$relationship;
		return $this;
	}
	
	/**
	 * addRelationships function.
	 * 
	 * @access public
	 * @param array $relationships Array of TLRelationships
	 * @return object $this
	 */
	public function addRelationships($relationships) {
		foreach($relationships as $relationship) {
			$this->addRelationship($relationship);
		}
		return $this;
	}
	
	/**
	 * getRelationships function.
	 * 
	 * @access public
	 * @return array Array of TLRelationships
	 */
	public function getRelationships() {
		return $this->_relationships;
	}
	
	/**
	 * clearFieldValues function.
	 * 
	 * Clears our all our fields
	 *
	 * @access public
	 * @return void
	 */
	public function clearFieldValues() {
		foreach($this->_fields as $field) {
			$field->value="";
		}
	}
	
	/**
	 * setOrderBy function.
	 * 
	 * Send an array of fields to order your results by
	 * Add DESC after the field name to order descending
	 * 
	 * @access public
	 * @param array $sortfields
	 * @return void
	 */
	public function setOrderBy($sortfields) {
		if (empty($sortfields)) {
			return false;
		}
		if (is_array($sortfields)) {
			$this->_orderby=$sortfields;
		} else {
			$this->_orderby=array($sortfields);
		}
	}
	
	protected function _lookup_field($id) {
		if (is_numeric($id)) {
			return "id";
		} else {
			return "urlid";
		}
	}
	
	protected function _populateFields($row) {
		foreach($this->_fields as $field) {
			$name=$field->name;
			if (isset($row->$name)) {
				$field->value=$row->$name;
			}
		}
	}
	
}



/**
 * TLRelationship class.
 *
 * DEPRECATED!
 * 
 * Maps relationships between fields and generates SQL Query stuff for get, update, insert and delete
 *
 * Add a field by calling addJoin([tablename], [join command]). This command can be chained. 
 * Eg.
 * $relationship->addJoin(
 *			"article_platform_link", "articles.id=article_platform_link.article_id"
 *		)->addJoin(
 *			"tl_platforms","article_platform_link.platform_id=tl_platforms.id"
 *		);
 */
class TLRelationship {

	protected $_joins;
	
	public function __construct() {
		$this->_joins=array();
		return $this;
	}
	
	public function addJoin($tablename, $cmd, $pivot=false) {
		$this->_joins[]=array(
			"tablename"=>$tablename,
			"cmd"=>$cmd,
			"pivot"=>$pivot,
		);
		return $this;
	}
	
	public function dbJoin() {
		$ci=&get_instance();
		foreach($this->_joins as $join) {
			$ci->db->join($join["tablename"],$join["cmd"]);
		}
	}
	
	public function dbInsert(&$data) {
		$ci=&get_instance();
		foreach($this->_joins as $join) {
			$updatedata=array();
			foreach($data as $key=>$val) {
				if (strpos($key,".")!==0) {
					$parts=explode(".",$key);
					$tablename=$parts[0];
					$field=$parts[1];
					if ($tablename==$join["tablename"]) {
						$updatedata[]=array($field=>$val);
						unset($data[$key]);
					}
				}
			}
			if (sizeof($updatedata) > 0) {
				foreach($updatedata as $d) {
					$ci->db->insert($d);
				}
			}
		}
	}
	
	public function dbUpdate($id,$data) {
		$ci=&get_instance();
		foreach($this->_joins as $join) {
			if ($join["pivot"]) {
				print_r($join);
			}
		}
	}
	
	public function dbDelete() {
			
	}
}


?>