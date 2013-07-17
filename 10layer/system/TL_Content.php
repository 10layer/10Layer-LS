<?php

/**
 * TLContent class.
 * 
 * An ORM for describing content and relationships between content
 *
 * Call with an urlid or content id to autopopulate, or construct and then call setContentType for a empty TLContent
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
	
	public function __construct($content_type) {
		$this->setContentType($content_type);
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
				if (($this->fields[$key]->multiple) && (!is_array($val))) {
					$this->fields[$key]->value[]=$val;
				} else {
					$this->fields[$key]->value=$val;
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
		$cache=$ci->mongo_db->state_save();
		$query=$ci->mongo_db->get_where("content_types", array("_id"=>$id));
		$ci->mongo_db->state_restore($cache);
		if (empty($query)) {
			show_error("Could not find content type $id");
		}		
		$this->content_type=$query[0];
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
		foreach($this->content_type->fields as $field) {
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
		if (empty($field["contenttype"])) {
			$field["contenttype"]=$this->content_type->_id;
		}
		if ($field["contenttype"]!=$this->content_type->_id) {
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
			for($x = 0; $x < sizeof($this->fields[$key]->options); $x++) {
				if (empty($this->fields[$key]->options[$x])) {
					unset($this->fields[$key]->options[$x]);
				}
			}
			if (($field->type=="select") && empty($this->fields[$key]->options)) {
				$ci->mongo_db->state_save();
				$result=$ci->mongo_db->where(array("content_type"=>$field->content_types))->limit(500)->order_by(array("title", "desc"))->select(array("title", "_id"))->get("content");
				$ci->mongo_db->state_restore();
				if(!empty($result)) {
					foreach($result as $item) {
						$this->fields[$key]->options[$item->title]=$item->_id;
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
	*    		"name"=>"urlid",
	*    		"hidden"=>true,
	*    		"transformations"=>array(
	*    			"copy"=>"title",
	*    			"urlid",
	*    		)
	*    	)
	*
	* Eg. of using existing PHP functions
	* "transformations"=>array(
	*    	"substr"=>array(2,5), //Call function with vars (value will be inserted as first var)
	*    	"strtoupper" //Call without vars - note that the function name is now a value instead of a key
	*    ),
	*
	* @access public
	* @return void
	*/
	public function transformFields($tbl=false) {
	    $ci=&get_instance();
	    $ci->load->library("datatransformations");
	    foreach($this->fields as $field) {
	    	foreach($field->transformations as $key=>$value) {
	    		if(isset($value["fn"])){
	    			$transformation = $value["fn"];
		    		$params = array();
		    		if (isset($value["params"])) {
			    		$params= $value["params"];
			    	}
		    		if (!is_array($params)) {
		     			$params=array($params);
		     		}
		     		$params=array_merge(array($field->value),$params);
		     		if (method_exists($ci->datatransformations, $transformation)) {
		     			$params=array_merge(array(&$this), $params);
		     			$field->value=call_user_func_array(array(&$ci->datatransformations, $transformation), $params);
		     		} else {
		     			if (function_exists($transformation)) {
		     				$field->value=call_user_func_array($transformation,$params);
		     			}
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
			$ci->validation->validate($field->name, $field->label, $field->value, $field->rules);
		}
		return $ci->validation->results();
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
	 * Set to 'any' when used to link to any content type or 'mixed' in conjunction with 'content_types' for a list of types
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
	 * content_types
	 * 
	 * An array of content types (primarily for searching when contenttype is 'mixed')
	 *
	 * (default value: array())
	 * 
	 * @var bool
	 * @access public
	 */
	public $content_types=array();
	
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
	 * defaultValue
	 * 
	 * (default value: "")
	 * 
	 * @var string
	 * @access public
	 */
	public $defaultValue="";
	
	
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
