<?php

/**
 * TLContent class.
 * 
 * A type of ORM for describing content and relationships between content
 *
 * Call with an urlid or content id to autopopulate, or construct and then call setContentType for a clear TLContent
 *
 * @package 10Layer
 * @subpackage Core
 */
class TLContent {
	/**
	 * content_id
	 * 
	 * (default value: false)
	 * 
	 * @var int
	 * @access public
	 */
	public $content_id=false;
	/**
	 * urlid
	 * 
	 * (default value: false)
	 * 
	 * @var string
	 * @access public
	 */
	public $urlid=false;
	/**
	 * fields
	 * 
	 * An array of type TLField (default value: array())
	 * 
	 * @var array
	 * @access public
	 */
	public $fields=array();
	/**
	 * content_type
	 * 
	 * (default value: false)
	 * 
	 * @var string
	 * @access public
	 */
	public $content_type=false;
	
	public function __construct($urlid=false, $contenttype_id=false, $level=0) {
		if (!empty($urlid)) {
			$this->_getContent($urlid, $contenttype_id, $level);
		}
	}
	
	/**
	 * __set function.
	 * 
	 * This dynamic setter allows us to set a value for the object
	 *
	 * @access public
	 * @param mixed $name
	 * @param mixed $val
	 * @return void
	 */
	public function __set($name, $val) {
		if (isset($this->$name)) {
			$this->$name=$val;
			return true;
		}
		foreach($this->fields as $key=>$field) {
			if ($name==$key) {
				if ($this->fields[$key]->multiple) {
					$this->fields[$key]->value[]=$val;
				} else {
					$this->fields[$key]->value=$val;
				}
			}
		}
	}
	
	
	/**
	 * _getContent function.
	 * 
	 * Internal function to populate the object with content
	 *
	 * @access protected
	 * @param mixed $id
	 * @return boolean
	 */
	protected function _getContent($id, $contenttype_id=false, $level=0) {
		$ci=&get_instance();
		if (!empty($contenttype_id)) {
			$this->setContentType($contenttype_id);
		}
		if (is_numeric($id)) {
			$ci->db->where("content.id",$id);
		} else {
			$ci->db->where("content.urlid",$id);
		}
		if (!empty($this->content_type)) {
			$ci->db->where("content_type_id",$this->content_type->id);
		}
		$query=$ci->db->get("content");
		if (empty($query->row()->urlid)) {
			show_error("Failed to find content id $id");
			//Rather throw this error
			//throw new Exception("Failed to find content id $id");
		}
		$this->urlid=$query->row()->urlid;
		$this->content_id=$query->row()->id;
		
		$this->setContentType($query->row()->content_type_id);
		$this->_setData($level);
		return true;
	}
	
	/**
	 * _getContentType function.
	 * 
	 * Returns the content type when we only know the urlid
	 *
	 * @access protected
	 * @return void
	 */
	protected function _getContentType() {
		$ci=&get_instance();
		$ci->db->select("content_types.*");
		$ci->db->from("content_types");
		$ci->db->where("content.urlid",$this->urlid);
		$ci->db->join("content","content.content_type_id=content_types.id");
		$query=$ci->db->get();
		$content_type=$query->row();
		if (empty($content_type->id)) {
			return false;
		}
		$this->content_type=$content_type;
		$this->populateFields();
	}
	
	/**
	 * _setData function.
	 * 
	 * This is the meat of the data fetcher. It grabs what it needs from the DB
	 *
	 * @access public
	 * @return void
	 */
	public function _setData($level=0) {
		$ci=&get_instance();
		$ci->db->join($this->content_type->table_name, "content.id={$this->content_type->table_name}.content_id");
		$ci->db->where("content.id",$this->content_id);
		$query=$ci->db->get("content");
		foreach($query->row() as $key=>$value) {
			$this->$key=$value;
		}
		$content_ids=array();
		$typearr=array();
		$drilldown=false;
		foreach($this->fields as $field) {
			if ($field->type!="drilldown") {
				if ($field->link) {
					$typearr[]="content_content.fieldname=".$ci->db->escape($field->name);
				} else {
					$typearr[$field->contenttype]="content_content.fieldname=".$ci->db->escape($field->name);
				}
			} else {
				$drilldown=true;
			}
		}
		//if (!$drilldown) {
			$typearr[]="content_content.fieldname=''";
		//}
		$ci->db->where("content_id",$this->content_id);
		$ci->db->where("(".implode(" OR ",$typearr).")");
		$ci->db->limit(100);
		$query=$ci->db->get("content_content");
		if ($level>=1) {
			return true;
		}
		foreach($query->result() as $row) {
			$level++;
			$tmp=new TLContent($row->content_link_id, false, $level);
			//print_r("Item {$tmp->urlid}<br />\n");
			if (!empty($row->fieldname)) {
				foreach($this->fields as $key=>$field) {
					if ($field->name==$row->fieldname) {
						if ($this->fields[$key]->multiple) {
							$this->fields[$key]->data[]=$tmp;
							$this->$key=$tmp->content_id;
						} else {
							$this->fields[$key]->data=$tmp;
							$this->$key=$tmp->content_id;
						}
					}
				}
			} else {
				if (isset($tmp->content_type->urlid) && ($tmp->content_type->urlid!=$this->content_type->urlid)) {
					foreach($this->fields as $key=>$field) {
						if ($field->contenttype==$tmp->content_type->urlid) {
							if ($this->fields[$key]->multiple) {
								$this->fields[$key]->data[]=$tmp;
								$this->$key=$tmp->content_id;
							} else {
								$this->fields[$key]->data=$tmp;
								$this->$key=$tmp->content_id;
							}
						}
					}
				} else {
					foreach($this->fields as $key=>$field) {
						if ($field->link==true) {
							$this->fields[$key]->data[]=$tmp;
							$this->$key=$tmp->content_id;
						}
					}
				}
			}
		}
	}
	
	/**
	 * setContentType function.
	 * 
	 * Lets us specify the content type. It'll take an urlid or an ID.
	 *
	 * @access public
	 * @param mixed $id
	 * @return void
	 */
	public function setContentType($id) {
		$ci=&get_instance();
		$ci->db->select("content_types.*");
		$ci->db->from("content_types");
		if (is_numeric($id)) {
			$ci->db->where("content_types.id",$id);
		} else {
			$ci->db->where("content_types.urlid",$id);
		}
		$query=$ci->db->get();
		if (empty($query->row()->id)) {
			show_error("Could not find content type $id");
		}
		$this->content_type=$query->row();
		$ci->load->model($this->content_type->model);
		$this->populateFields();
	}
	
	/**
	 * getContentType function.
	 * 
	 * Returns our content type
	 *
	 * @access public
	 * @param bool $tbl. (default: false)
	 * @return object
	 */
	public function getContentType($tbl=false) {
		return $this->content_type;
	}
	
	/**
	 * populateFields function.
	 * 
	 * This inspects our model and gets our fields
	 *
	 * @access public
	 * @return object
	 */
	public function populateFields() {
		$ci=&get_instance();
		$model=false;
		if (!empty($ci->{$this->content_type->model})) {
			$model=&$ci->{$this->content_type->model};
		} else {
			if (!empty($ci->content)) {
				$model=&$ci->content;
			}
		}
		if (empty($model)) {
			show_error("Content type model not found");
		}
		foreach($model->fields as $field) {
			$this->addField($field);
		}
		$this->getOptions();
		return $this;
	}
	
	/**
	 * addField function.
	 * 
	 * Allows us to add another field with default values.
	 *
	 * @access protected
	 * @param mixed $tbl
	 * @param mixed $field
	 * @return void
	 */
	protected function addField($field) {
		if (empty($field["tablename"])) {
			if (!empty($field["contenttype"])) {
				$ci=&get_instance();
				$query=$ci->db->where("urlid",$field["contenttype"])->get("content_types");
				if ($query->num_rows()>0) {
					$field["tablename"]=$query->row()->tablename;
				}
			} else {
				$field["tablename"]=$this->content_type->table_name;
			}
			
		}
		if (empty($field["contenttype"])) {
			$field["contenttype"]=$this->content_type->urlid;
		}
		if ($field["contenttype"]!=$this->content_type->urlid) {
			$field["external"]=true;
		}
		$this->fields[$field["name"]]=new TLField($field);
		return $this;
	}
	
	/**
	 * setFieldValue function.
	 * 
	 * Sets a specific field's value
	 *
	 * @access public
	 * @param mixed $tbl
	 * @param mixed $name
	 * @param mixed $value
	 * @return void
	 */
	public function setFieldValue($tbl,$name,$value) {
		if (isset($this->{$tbl}->fields[$name])) {
			$this->{$tbl}->fields[$name]->value=$value;
		} else {
			show_error("Couldn't find field $name");
		}
	}
	
	/**
	 * getFields function.
	 * 
	 * Returns all our fields
	 *
	 * @access public
	 * @return void
	 */
	public function getFields() {
		return $this->fields;
	}
	
	/**
	 * getOptions function.
	 * 
	 * Returns all possible options for a specific field
	 *
	 * @access protected
	 * @param mixed $tbl
	 * @return void
	 */
	protected function getOptions() {
		$ci=&get_instance();
		foreach($this->fields as $key=>$field) {
			if (($field->type=="select") && empty($this->fields[$key]->options)) {
				$result=$ci->db->get_where("content_types",array("urlid"=>$field->contenttype));
				if(!empty($result->row()->id)) {
					$content_type_id=$result->row()->id;
					$ci->db->select("content.urlid, content.title, content.id AS content_id");
					$ci->db->order_by("title ASC");
					$ci->db->where("content_type_id",$content_type_id);
					$query=$ci->db->get("content");
					foreach($query->result() as $result) {
						$this->fields[$key]->options[$result->content_id]=$result->title;
					}
				}
			}
		}
	}
	
	
	
	/**
	* transformFields function.
	* 
	* Runs sets of transforms on field values. 
	*
	* Set the transformations property of a field to either a created transformation method in the transformation library
	* or an existing function.
	*
	* Eg.of copying from another field and making the field into an urlid
	* array(
	    		"name"=>"urlid",
	    		"hidden"=>true,
	    		"transformations"=>array(
	    			"copy"=>"title",
	    			"urlid",
	    		)
	    	)
	 *
	 * Eg. of using existing PHP functions
	 * "transformations"=>array(
	    	"substr"=>array(2,5), //Call function with vars (value will be inserted as first var)
	    	"strtoupper" //Call without vars - note that the function name is now a value instead of a key
	    ),
	 *
	 * @access public
	 * @return void
	 */
	public function transformFields($tbl=false) {
	    $ci=&get_instance();
	    $ci->load->library("datatransformations");
	    foreach($this->fields as $field) {
	    	foreach($field->transformations as $key=>$value) {
	    		$params=array();
	    		if (is_numeric($key)) {
	    			$transformation=$value;
	    		} else {
	    			$transformation=$key;
	     			$params=$value;
	     			if (!is_array($params)) {
	     				$params=array($params);
	     			}
	     		}
	     		$params=array_merge(array($field->value),$params);
	     		if (method_exists($ci->datatransformations,$transformation)) {
	     			$params=array_merge(array(&$this),$params);
	     			$field->value=call_user_func_array(array(&$ci->datatransformations, $transformation), $params);
	     		} else {
	     			if (function_exists($transformation)) {
	     				$field->value=call_user_func_array($transformation,$params);
	     			}
	     		}
	     	}
	     }
	}
	
	/**
	 * getField function.
	 * 
	 * @access public
	 * @param mixed $fieldname
	 * @return object TLField
	 */
	public function getField($fieldname,$obj=false,$tbl=false) {
		$alt_fieldname=$fieldname;
		
	    if (empty($obj)) {
	    	$obj=$this;
	    }
	    if (!empty($tbl)) {
		    $alt_fieldname=$fieldname;
		}
	    if (isset($obj->fields) && isset($obj->fields[$alt_fieldname])) {
	    	return $obj->fields[$alt_fieldname];
	    }
	   
		$alt_fieldname="content_".$fieldname;
	    if (isset($obj->fields) && isset($obj->fields[$alt_fieldname])) {
	    	return $obj->fields[$alt_fieldname];
	    }
	    foreach($obj as $el) {
	    	if (is_object($el)) {
	    		if (isset($el->content_type)) {
		    		$val=$this->getField($fieldname,$el,$el->content_type->table_name);
		    		if (!empty($val)) {
	    				return $val;
	    			}
	    		}
	    	}
	    }
	    
	    return false;
	}
	
	/**
	 * validateFields function.
	 * 
	 * This does our validation according to the Validation class. Returns a detailed result.
	 *
	 * @access public
	 * @param bool $tbl. (default: false)
	 * @return object
	 */
	public function validateFields($tbl=false) {
		$ci=&get_instance();
		$ci->load->library("validation");
		foreach($this->fields as $field) {
			$ci->validation->validate($field->name,$field->label,$field->value,$field->rules);
		}
		return $ci->validation->results();
	}
	
	/**
	 * update function.
	 * 
	 * Updates our data - including all the relationships
	 *
	 * @access public
	 * @return void
	 */
	public function update() {
		$ci=&get_instance();
		$ci->load->helper("array_helper");
		$fields=$this->getFields();
		$tables=array();
		$typearr=array();
		foreach($fields as $field) {
			if (!$field->link) {
				$tables[$field->tablename][$field->name]=$field->value;
			}
			if ($field->type=="drilldown") {
				$typearr[$field->contenttype]=$field->contenttype;
			}
			if ($field->tablename=="") {
				$tables["all"][$field->name]=$field->value;
			}
		}
		
		$tables["content"]["last_modified"]=date("c");
		//Update Content table
		$ci->db->where("content.id",$this->content_id);
		//Make sure we maintain the old id and urlid
		unset($tables["content"]["urlid"]);
		unset($tables["content"]["id"]);
		$ci->db->update("content",$tables["content"]);
		//Update extension table
		$ci->db->where("content_id",$this->content_id);
		$content_type=$this->getContentType();
		unset($tables[$content_type->table_name]["id"]);
		//print_r($tables);
		//die();
		if (!empty($tables[$content_type->table_name])) {
			$ci->db->update($content_type->table_name,$tables[$content_type->table_name]);
		}
		unset($tables[$content_type->table_name]);
		unset($tables["content"]);
		//Joins
		foreach($this->fields as $field) {
			if ($field->link) {
				$tables[$field->tablename][$field->name]=$field->value;
			}
		}
		if (sizeof($typearr)>0) {
		//Do it the hard way
			$ci->db->select("content_content.id AS content_content_id, content_types.urlid");
			$ci->db->where("content_id",$this->content_id);
			$ci->db->join("content","content_content.content_link_id=content.id");
			$ci->db->join("content_types","content_types.id=content.content_type_id");
			$links=$ci->db->get("content_content");
			$x=0;
			$y=0;
			foreach($links->result() as $link) {
				if (!in_array($link->urlid, $typearr)) {
					$ci->db->where("id",$link->content_content_id);
					$ci->db->delete("content_content");	
					$x++;
				}
				$y++;
			}
		} else {
		//Do it the easy way
			$ci->db->where("content_id",$this->content_id);
			$ci->db->delete("content_content");
		}
		foreach($tables as $tablename=>$val) {
		//Update the links
			$fieldname=array_keys($val);
			$fieldname=$fieldname[0];
			$vals=array_unique(array_flatten($val));
			foreach($vals as $v) {
				if (!empty($v) && !empty($tablename)) {
					if ($this->content_id!=$v) { //Stops us creating a self-referential link
						$ci->db->insert("content_content",array("content_id"=>$this->content_id, "content_link_id"=>$v, "fieldname"=>$fieldname));
					}
				}
			}
		}
		//Now we look for reverse relationships
		foreach($this->fields as $field) {
			if ($field->type=="reverse") {
				$ci->db->select("content_content.id AS content_content_id");
				$ci->db->join("content", "content.id=content_content.content_id");
				$ci->db->join("content_types","content.content_type_id=content_types.id");
				$ci->db->where("content_content.content_link_id",$this->content_id);
				$ci->db->where("content_types.urlid",$field->contenttype);
				$result=$ci->db->get("content_content");
				foreach($result->result() as $row) {
					$ci->db->where("id",$row->content_content_id);
					$ci->db->delete("content_content");
				}
				$vals=$field->value;
				if (!is_array($field->value)) {
					$vals=array($field->value);
				}
				foreach($vals as $val) {
					if (!empty($val)) {
						$ci->db->insert("content_content",array("content_id"=>$val, "content_link_id"=>$this->content_id, "fieldname"=>$this->content_type->urlid));
					}
				}
			}
		}
		
		//Now we look for nesteditems relationships NB: this is an addition for the MG's need for nested items
		foreach($this->fields as $field) {
			if ($field->type=="nesteditems") {
				$ci->db->select("content_content.id AS content_content_id");
				$ci->db->join("content", "content.id=content_content.content_id");
				$ci->db->join("content_types","content.content_type_id=content_types.id");
				$ci->db->where("content_content.content_link_id",$this->content_id);
				$ci->db->where("content_types.urlid",$field->contenttype);
				$result=$ci->db->get("content_content");
				foreach($result->result() as $row) {
					$ci->db->where("id",$row->content_content_id);
					$ci->db->delete("content_content");
				}
				$vals=$field->value;
				if (!is_array($field->value)) {
					$vals=array($field->value);
				}
				foreach($vals as $val) {
					if (!empty($val)) {
						$ci->db->insert("content_content",array("content_id"=>$val, "content_link_id"=>$this->content_id, "fieldname"=>$this->content_type->urlid));
					}
				}
			}
		}
		
		//Make sure we set urlid
		$this->urlid=$ci->db->where("id",$this->content_id)->get("content")->row()->urlid;
	}
	
	/**
	 * insert function.
	 * 
	 * Inserts a new record, plus all our relationship bits
	 *
	 * @access public
	 * @return void
	 */
	public function insert() {
		$ci=&get_instance();
		$ci->load->helper("array_helper");
		$tables=array();
		foreach($this->fields as $field) {
			if (!$field->link) {
				$tables[$field->tablename][$field->name]=$field->value;
			}
		}
		$tables["content"]["last_modified"]=date("c");
		$tables["content"]["content_type_id"]=$this->content_type->id;
		#FIRST insert into Content table to get content_id
		$ci->db->insert("content",$tables["content"]);
		$this->content_id=$ci->db->insert_id();
		#Set Platform
		$ci->db->insert("content_platforms",array("content_id"=>$this->content_id,"platform_id"=>$ci->platforms->id()));
		#Insert into extension table
		$tables[$this->content_type->table_name]["content_id"]=$this->content_id;
		$ci->db->insert($this->content_type->table_name,$tables[$this->content_type->table_name]);
		#Joins - basically the same as for update
		
		
		$content_type=$this->getContentType();
		unset($tables[$content_type->table_name]);
		unset($tables["content"]);
		
		foreach($this->fields as $field) {
			if ($field->link) {
				$tables[$field->tablename][$field->name]=$field->value;
			}
		}
		foreach($tables as $tablename=>$val) {
			$fieldname=array_keys($val);
			$fieldname=$fieldname[0];
			$vals=array_unique(array_flatten($val));
			foreach($vals as $v) {
				if (!empty($v)) {
					if ($this->content_id!=$v) { //Stops us creating a self-referential link
						$ci->db->insert("content_content",array("content_id"=>$this->content_id, "content_link_id"=>$v, "fieldname"=>$fieldname));
					}
				}
			}
		}
		
		//Now we look for reverse relationships
		foreach($this->fields as $field) {
			if ($field->type=="reverse") {
				$vals=$field->value;
				if (!is_array($field->value)) {
					$vals=array($field->value);
				}
				foreach($vals as $val) {
					if (!empty($val)) {
						$ci->db->insert("content_content",array("content_id"=>$val, "content_link_id"=>$this->content_id, "fieldname"=>$this->content_type->urlid));
					}
				}
			}
		}
		
		//Now we look for nesteditems relationships NB: this is an addition for the MG's need for nested items
		foreach($this->fields as $field) {
			if ($field->type=="nesteditems") {
				$ci->db->select("content_content.id AS content_content_id");
				$ci->db->join("content", "content.id=content_content.content_id");
				$ci->db->join("content_types","content.content_type_id=content_types.id");
				$ci->db->where("content_content.content_link_id",$this->content_id);
				$ci->db->where("content_types.urlid",$field->contenttype);
				$result=$ci->db->get("content_content");
				foreach($result->result() as $row) {
					$ci->db->where("id",$row->content_content_id);
					$ci->db->delete("content_content");
				}
				$vals=$field->value;
				if (!is_array($field->value)) {
					$vals=array($field->value);
				}
				foreach($vals as $val) {
					if (!empty($val)) {
						$ci->db->insert("content_content",array("content_id"=>$val, "content_link_id"=>$this->content_id, "fieldname"=>$this->content_type->urlid));
					}
				}
			}
		}
		return true;
	}
	
	/**
	 * delete function.
	 * 
	 * @access public
	 * @return void
	 */
	public function delete() {
		$ci=&get_instance();
		$content_type=$this->getContentType();
		
		//Delete from Type table
		$ci->db->delete($content_type->table_name,array("content_id"=>$this->content_id));
			
		//Delete from Platforms
		$ci->db->delete("content_platforms",array("content_id"=>$this->content_id));
			
		//Delete links
		$ci->db->delete("content_content",array("content_id"=>$this->content_id));
		$ci->db->delete("content_content",array("content_link_id"=>$this->content_id));
			
		//Delete the primary entry
		$ci->db->delete("content",array("id"=>$this->content_id));
			
		//All done!
		return true;
	}
	
	/**
	 * getData function.
	 * 
	 * A simple data fetcher. Basically it drops all the crap and just gives you the stuff you need.
	 *
	 * @access public
	 * @return void
	 */
	public function getData() {
		$result=new stdClass;
		foreach($this->fields as $key=>$value) {
			$result->$key=$value->value;
		}
		$result->content_id=$this->content_id;
		$result->urlid=$this->urlid;
		return $result;
	}
	
	/**
	 * getFull function.
	 * 
	 * An advanced data fetcher. Gives the data like getData plus the info of the linked objects.
	 *
	 * @access public
	 * @return void
	 */
	public function getFull($level=0) {
		if ($level>4) {
			return false;
		}
		$result=new stdClass;
		foreach($this->fields as $key=>$value) {
			if (!isset($value->data)) {
				$result->$key=$value->value;
			} else {
				if (is_array($value->data)) {
					foreach($value->data as $item) {
						$tmp=new TLContent($item->urlid, $item->content_type->urlid,($level+1));
						$result->$key=$tmp->getFull(($level+1));
					}
				} else {
					$tmp=new TLContent($value->data->urlid, $value->data->content_type->urlid,($level+1));
					$result->$key=$tmp->getFull(($level+1));
				}
			}
		}
		$result->content_id=$this->content_id;
		$result->urlid=$this->urlid;
		return $result;
	}
	
	/**
	 * clearData function.
	 * 
	 * Clears an object out of all data so that you can repopulate it or use it as a template
	 *
	 * @access public
	 * @return void
	 */
	public function clearData() {
		foreach($this->fields as $key=>$field) {
			$this->fields[$key]->value="";
		}
	}
	
	/**
	 * dbClone function.
	 * 
	 * This'll clone our ORM, say for forking
	 *
	 * @access public
	 * @return int content_id
	 */
	public function dbClone() {
		$linktable=$this->content_type->table_name;
		$urlid=$this->urlid;
		$ci=&get_instance();
		$ci->load->library("datatransformations");
		$newurlid=$ci->datatransformations->safe_urlid($urlid, "content", "urlid");
		$fields = $ci->db->list_fields("content");
		for($x=0; $x<sizeof($fields); $x++) {
			if (($fields[$x]=="id") || ($fields[$x]=="urlid")) {
				unset($fields[$x]);
			}
		}
		$query="INSERT INTO content (".implode(",",$fields).", urlid) SELECT ".implode(",",$fields).", ".$ci->db->escape($newurlid)." FROM content WHERE id=".$ci->db->escape($this->content_id);
		$ci->db->query($query);
		$query=$ci->db->get_where("content",array("urlid"=>$newurlid));
		$content_id=$query->row()->id;
		$fields = $ci->db->list_fields($linktable);
		for($x=0; $x<sizeof($fields); $x++) {
			if (($fields[$x]=="id") || ($fields[$x]=="content_id")) {
				unset($fields[$x]);
			}
		}
		$query="INSERT INTO $linktable (".implode(",",$fields).", content_id) SELECT ".implode(",",$fields).", ".$ci->db->escape($content_id)." FROM $linktable WHERE content_id=".$ci->db->escape($this->content_id);
		$ci->db->query($query);
		return $content_id;
	}
	
	/**
	 * linkToPlatform function.
	 * 
	 * This links our current content to a new platform (or a new publication)
	 *
	 * @access public
	 * @param mixed $platform_id
	 * @return bool
	 */
	public function linkToPlatform($platform_id) {
		$ci=&get_instance();
		$ci->db->where(array("content_id"=>$this->content_id, "platform_id"=>$platform_id));
		$ci->db->delete("content_platforms");
		$ci->db->insert("content_platforms", array("content_id"=>$this->content_id, "platform_id"=>$platform_id));
		return true;
	}
	
}

/**
 * TLField class.
 * 
 * This class defines a single field and its possible options
 *
 */
class TLField {
	
	/**
	 * name
	 * 
	 * Name of the field in the table (default value: false)
	 * 
	 * @var string
	 * @access public
	 */
	public $name=false;
	
	/**
	 * field
	 * 
	 * (default value: false)
	 * 
	 * @var bool
	 * @access public
	 */
	public $field=false;
	
	/**
	 * tablename
	 * 
	 * Table name, specifically if it's an external table or a content field (default value: false)
	 * 
	 * @var string
	 * @access public
	 */
	public $tablename=false;
	
	/**
	 * external
	 * 
	 * Is this an external table field?
	 *
	 * (default value: false)
	 * 
	 * @var bool
	 * @access public
	 */
	public $external=false;
	
	/**
	 * label
	 * 
	 * A friendly name for display (default value: false)
	 * 
	 * @var string
	 * @access public
	 */
	public $label=false;
	
	/**
	 * value
	 * 
	 * Default value or set value (default value: false)
	 * 
	 * @var string
	 * @access public
	 */
	public $value=false;
	
	/**
	 * rules
	 * 
	 * Rules the field has to comply to or else it won't submit (default value: array())
	 * 
	 * @var array
	 * @access public
	 */
	public $rules=array();
	
	/**
	 * hidden
	 * 
	 * Set to true to hide the field in forms (default value: false)
	 * 
	 * @var bool
	 * @access public
	 */
	public $hidden=false;
	
	/**
	 * type
	 * 
	 * Defines how the field is displayed on forms (default value: "text")
	 * 
	 * @var string
	 * @access public
	 */
	public $type="text";
	
	/**
	 * class
	 * 
	 * Set a specific class for styling in CSS (default value: "")
	 * 
	 * @var string
	 * @access public
	 */
	public $class="";
	
	/**
	 * label_class
	 * 
	 * Set a specific class for the label for styling in CSS (default value: "")
	 * 
	 * @var string
	 * @access public
	 */
	public $label_class="";
	
	/**
	 * transformations
	 * 
	 * Change the value of the field in some way during submit (default value: array())
	 * 
	 * @var array
	 * @access public
	 */
	public $transformations=array();
	
	/**
	 * contenttype
	 * 
	 * Defines the content type (default value: "")
	 * 
	 * @var string
	 * @access public
	 */
	public $contenttype="";
	
	/**
	 * libraries
	 * 
	 * Use external libraries like search or tagging (default value: array())
	 * Set to 'any' when used to link to any content type or 'mixed' in conjunction with 'contenttypes' for a list of types
	 * 
	 * @var array
	 * @access public
	 */
	public $libraries=array();
	
	/**
	 * options
	 * 
	 * For dropdowns etc, the possible values (default value: false)
	 * 
	 * @var array
	 * @access public
	 */
	public $options=false;
	
	/**
	 * link
	 * 
	 * For self-referential joins, set to true (default value: false)
	 * 
	 * @var bool
	 * @access public
	 */
	public $link=false;	
	
	/**
	 * filetypes
	 * 
	 * For files, these types are allowed (default value: array("gif","jpg","png"))
	 * 
	 * @var array
	 * @access public
	 */
	public $filetypes=array("gif","jpg","png");
	
	/**
	 * multiple
	 * 
	 * (default value: false)
	 * 
	 * @var bool
	 * @access public
	 */
	public $multiple=false;
	
	/**
	 * cdn
	 * 
	 * Set to false to stop files being uploaded to the CDN
	 *
	 * (default value: true)
	 * 
	 * @var bool
	 * @access public
	 */
	public $cdn=true;
	
	/**
	 * cdn_link
	 * 
	 * Set to the field you want to store the CDN url in
	 *
	 * (default value: true)
	 * 
	 * @var bool
	 * @access public
	 */
	public $cdn_link=false;
	
	/**
	 * readonly
	 * 
	 * Makes a field read-only (like for something you programatically set)
	 *
	 * (default value: false)
	 * 
	 * @var bool
	 * @access public
	 */
	public $readonly=false;
	
	/**
	 * directory
	 * 
	 * Override the default directory for saving content
	 *
	 * (default value: false)
	 * 
	 * @var string
	 * @access public
	 */
	public $directory=false;
	
	/**
	 * contenttypes
	 * 
	 * An array of content types (primarily for searching when contenttype is 'mixed'
	 *
	 * (default value: array())
	 * 
	 * @var bool
	 * @access public
	 */
	public $contenttypes=array();
	
	/**
	 * showcount
	 * 
	 * Shows a count of chars used. Optionally, set to an int value to show a countdown instead
	 *
	 * (default value: false)
	 * 
	 * @var int
	 * @access public
	 */
	public $showcount=false;
	
	/**
	 * linkformat
	 * 
	 * If you want to show a link to an external file, set this to the external filename. 
	 * Use {filename} as a placeholder for the filename.
	 * Eg. http://myfiles.blah.com/images/{filename}
	 *
	 * (default value: false)
	 * 
	 * @var string
	 * @access public
	 */
	public $linkformat=false;
	
	/**
	 * hidenew
	 * 
	 * Set hidenew to true to hide the "New" button below the field.
	 *
	 * (default value: false)
	 * 
	 * @var bool
	 * @access public
	 */
	public $hidenew=false;
	
	/**
	 * __construct function.
	 * 
	 * @access public
	 * @param bool $data. (default: false)
	 * @return void
	 */
	public function __construct($data=false) {
		if (is_array($data)) {
			foreach($data as $key=>$val) {
				if (isset($this->$key)) {
					$this->$key=$val;
				}
			}
		}
		if (empty($this->label) && (!$this->hidden)) {
			$this->label=ucfirst(str_replace("_"," ",$this->name));
		} elseif (!empty($this->label)) {
			$this->hidden=false;
		}
	}
	
}
