<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * TL_Controller_Create class.
 *
 * This is the main base controller for the CMS.
 * It is used to create the views, and handle the return data in a variety of ways.
 * 
 * @package 10Layer
 * @subpackage Core
 * @extends TL_Controller_CRUD
 */
class TL_Controller_Create extends TL_Controller_CRUD {
	/**
	 * _view
	 * 
	 * (default value: "content/default/create")
	 * 
	 * @var string
	 * @access protected
	 */
	protected $_view="content/default/create";
	
	/**
	 * __construct function.
	 *
	 * Calls the parent constructor.
	 * 
	 * @access public
	 * @param bool $contenttype. (default: false)
	 * @return void
	 */
	public function __construct($contenttype=false) {
		parent::__construct();	
	}
	
	/**
	 * submit function.
	 *
	 * Submits the data. This would usually not be called directly.
	 * 
	 * @access public
	 * @return void
	 */
	public function submit() {
		$returndata=array("error"=>false,"msg"=>"");
		$contentobj=new TLContent();
		$contentobj->setContentType($this->_contenttypeurlid);
		$do_action=$this->input->post("action");
		$urlid="";
		if (!empty($do_action)) {
			$this->checkCallback("onBeforeAction",$contentobj);
			foreach($contentobj->getFields() as $field) {
				if ($field->readonly) {
					//Do NOTHING
				} else {
						
					if (!$this->fileupload($field, $urlid, $contentobj, $returndata)) {
						$fieldval=$this->input->post($field->tablename."_".$field->name);
						if (empty($fieldval)) {
							$contentobj->{$field->name}="";
						} else {
							$contentobj->{$field->name}=$fieldval;
						}
					}
				}
			}
			
			
			$contentobj->transformFields();
			$validation=$contentobj->validateFields();
			if (!$validation["passed"]) {
				$returndata["error"]=true;
				$returndata["msg"]="Failed to create {$this->_contenttypeurlid}";
				$returndata["info"]=implode("<br />\n",$validation["failed_messages"]);
			}
			
			if (!$returndata["error"]) {
				$this->checkCallback("onBeforeSubmit",$contentobj);
				if (!$returndata["error"]) {
					$contentobj->insert();
				}
				$finalobj=$this->content->getByIdORM($contentobj->content_id, $this->_contenttype->id);
				$this->checkCallback("onAfterSubmit",$finalobj);
			}
			
			if (!$returndata["error"]) {
				$returndata["msg"]="Successfully created {$this->_contenttypeurlid}";
				$returndata["id"]=$finalobj->content_id;
				$returndata["data"]=$finalobj->getData();
				$this->checkCallback("onAfterAction",$finalobj);
			}
			
			if (!$returndata["error"]) { //Memcached submission and save
				//$this->cachesave($this->_contenttypeurlid,$contentobj->content_id);			
				$this->messaging->post_action("create",array($this->_contenttypeurlid,$finalobj->urlid));
			}
			return $returndata;
		}
		return array("error"=>true,"msg"=>"No data submitted");
	}
	
	/**
	 * ajaxsubmit function.
	 *
	 * This will do your submit through Ajax, and will also set your document.domain and package it in a textarea for cross-domain safety
	 * This has largely been replaced by the JSON stuff which uses JSONP-friendly return view
	 * 
	 * @access public
	 * @return void
	 */
	public function ajaxsubmit() {
		$result=$this->submit();
		print "<script>document.domain=document.domain;</script><textarea>";
		print json_encode($result);
		print "</textarea>";
	}
	
	/**
	 * view function.
	 *
	 * This draws our view for us.
	 * 
	 * @access public
	 * @return void
	 */
	public function view() {
		$contentobj=new TLContent();
		$contentobj->setContentType($this->_contenttypeurlid);
		$this->checkCallback("onBeforeView",$contentobj);
		$this->load->library("formcreator");
		$fields=$contentobj->getFields();
		$this->formcreator->setFields($fields);
		$data["file_fields"]=array();
		foreach($fields as $field) {
			if ($field->type=="file") {
				$data["file_fields"][]=$field;
			}
		}
		$data["menu1_active"]="create";
		$data["menu2_active"]="create/".$this->_contenttypeurlid;
		$data["contenttype"]=$this->_contenttypeurlid;
		$data["heading"]= $this->format_heading($data["menu2_active"]);
		$this->load->view($this->_view,$data);
		
		$this->checkCallback("onAfterView",$contentobj);
	}
	
	/**
	 * jsoncreate function.
	 * 
	 * Sends the data for drawing the view as a JSONP package. 
	 *
	 * @access public
	 * @param mixed $type
	 * @return void
	 */
	public function jsoncreate($type) {
		$contentobj=new TLContent();
		$contentobj->setContentType($this->_contenttypeurlid);
		$data["content_type_id"]=$contentobj->content_type->id;
		$data["content_type"]=$this->_contenttypeurlid;
		$data["fields"]=$contentobj->getFields();
		$this->tluserprefs->click_menu($this->_contenttypeurlid);
		$this->session->set_userdata("contenttype",$this->_contenttypeurlid);
		$this->load->view("json", array("data"=>$data));
	}
	
	/**
	 * field function.
	 * 
	 * Draws a single field for embedding in a form. Usually used where we haven't converted
	 * a snippet to the new Javascript system.
	 *
	 * @access public
	 * @param mixed $fieldname
	 * @param mixed $type
	 * @return void
	 */
	public function field($fieldname, $type) {
		$contentobj=new TLContent();
		$contentobj->setContentType($this->_contenttypeurlid);
		$fields=$contentobj->getFields();
		foreach($fields as $field) {
			if ($field->name==$fieldname) {
				$fieldtype=$field->type;
				$this->load->view("/snippets/$fieldtype", array("field"=>$field));
				return true;
			}
		}
	}
	
	/**
	 * embed function.
	 *
	 * Used to put an "New" popup in a form to quickly create a different data type
	 * 
	 * @access public
	 * @return void
	 */
	public function embed() {
		$this->_view="content/default/embed";
		$contentobj=new TLContent();
		$contentobj->setContentType($this->_contenttypeurlid);
		$this->checkCallback("onBeforeView",$contentobj);
		$this->load->library("formcreator");
		$fields=$contentobj->getFields();
		$tmp=array();
		foreach($fields as $key=>$field) {
			$fields->$key->name="embed_".$field->name;
		}
		$this->formcreator->setFields($fields);
		$data["file_fields"]=array();
		foreach($fields as $field) {
			if ($field->type=="file") {
				$data["file_fields"][]=$field;
			}
		}
		$data["menu1_active"]="create";
		$data["menu2_active"]="create/".$this->_contenttypeurlid;
		$data["contenttype"]=$this->_contenttypeurlid;
		$this->load->view($this->_view,$data);
		
		$this->checkCallback("onAfterView",$contentobj);
	}
		
}

/**
 * TL_Controller_Edit class.
 *
 * The controller for editing existing content.
 * 
 * @extends TL_Controller_CRUD
 */
class TL_Controller_Edit extends TL_Controller_CRUD {
	
	/**
	 * _pg_perpage
	 * 
	 * Number of rows to display per page (default value: 100)
	 * 
	 * @var int
	 * @access public
	 */
	public  $_pg_perpage=100;
	
	/**
	 * _pg_numlinks
	 * 
	 * Number of pagination links to display (default value: 15)
	 * 
	 * @var int
	 * @access public
	 */
	public $_pg_numlinks=15;
	
	/**
	 * _pg_offset
	 * 
	 * @var mixed
	 * @access protected
	 */
	protected $_pg_offset=0;
	
	
	/**
	 * __construct function.
	 *
	 * Calls parent constructor.
	 * 
	 * @access public
	 * @param bool $contenttype. (default: false)
	 * @return void
	 */
	public function __construct($contenttype=false) {
		parent::__construct($contenttype);
		$this->load->library("memcacher");
	}
	
	/**
	 * submit function.
	 *
	 * Submits the edited data. This shouldn't be called directly.
	 * 
	 * @access public
	 * @param mixed $type
	 * @param mixed $urlid
	 * @return void
	 */
	public function submit($type,$urlid) {
	
		$contentobj=$this->content->getByIdORM($urlid, $this->_contenttype->id);
		$contentobj->clearData();
		if (empty($contentobj->content_id)) {
			//show_404("/edit/".$this->uri->segment(3)."/".$urlid);
			$returndata["error"]=true;
			$returndata["msg"]="Update failed";
			$returndata["info"]="Could not find content with urlid $urld";
			return $returndata;
		}
		
		$do_action=$this->input->get_post("action");
		if (!empty($do_action)) {
			$this->checkCallback("onBeforeAction",$contentobj);
			$dbdata=array();
			$returndata=array("error"=>false);
			$fields=$contentobj->getFields($this->_contenttypeurlid);
			//print_r($fields);
			foreach($fields as $field) {
				//print_r($field);
				if ($field->readonly || ($field->type=="drilldown")) {
					//Do Nothing!
				} else {
					if (!$this->fileupload($field, $urlid, $contentobj, $returndata)) {
						$contentobj->{$field->name}=$this->input->get_post($field->tablename."_".$field->name);
					}
				}
			}
			
			
			$contentobj->transformFields($this->_contenttypeurlid);
			$validation=$contentobj->validateFields($this->_contenttypeurlid);
			
			
			
			if (!$validation["passed"]) {
				$returndata["error"]=true;
				$returndata["msg"]="Failed to update {$this->_contenttypeurlid}";
				$returndata["info"]=implode("<br />\n",$validation["failed_messages"]);
			}
			if (!$returndata["error"]) {
				$this->checkCallback("onBeforeSubmit",$contentobj);
				$this->versions->bump_minor_version();
				//print_r($contentobj);
				$contentobj->update();
				$this->clear_autosave($type, $urlid);
				$returndata["msg"]="Successfully updated {$this->_contenttypeurlid}";
				$this->checkCallback("onAfterSubmit",$contentobj);
				
			}
			
			if (!$returndata["error"]) { //Memcached submission
				$this->cachereset($this->_contenttypeurlid,$urlid);
				//$this->cachesave($this->_contenttypeurlid,$urlid);
			}
			
			//Tell the world
			$this->messaging->post_action("update_content",array($this->_contenttypeurlid,$urlid));
			$this->messaging->post_action("edit",array($this->_contenttypeurlid,$urlid));
			$this->checkCallback("onAfterAction",$contentobj);
			
			return $returndata;
		}
		return array("error"=>true,"msg"=>"No data submitted");
	}
	
	/**
	 * ajaxsubmit function.
	 * 
	 * Packaged our Ajax submit and returns the result in a cross-domain-safe package.
	 *
	 * @access public
	 * @param mixed $type
	 * @param mixed $urlid
	 * @return void
	 */
	public function ajaxsubmit($type,$urlid) {
		$result=$this->submit($type,$urlid);
		$this->load->view("json", array("data"=>$result));
	}
	
	/**
	 * autosave function.
	 * 
	 * Autosaves
	 *
	 * @access public
	 * @param mixed $type
	 * @param mixed $urlid
	 * @return void
	 */
	public function autosave($type, $urlid) {
		$this->load->library("mongo_db");
		$result=$this->mongo_db->where(array("urlid"=>$urlid))->get("tl_content");
		if (empty($result)) {
			$this->mongo_db->insert("tl_content",array("urlid"=>$urlid));
		}
		$this->mongo_db->where(array("urlid"=>$urlid))->update("tl_content",array("autosave"=>$_POST, "autosave_time"=>date("c")));
		$result=$this->check_change($_POST, $urlid);
		print "<script>document.domain=document.domain;</script><textarea>";
		print json_encode($result);
		print "</textarea>";
	}
	
	/**
	 * check_change function.
	 * 
	 * Checks if there is an autosave active
	 *
	 * @access protected
	 * @param mixed $data
	 * @param mixed $urlid
	 * @return void
	 */
	protected function check_change($data, $urlid) {
		$content=$this->content->getByIdORM($urlid)->getData();
		$changedfields=array();
		$unchangedfields=array();
		$missingfields=array();
		$changed=false;
		foreach($data as $longkey=>$val) {
			$key=substr($longkey, strpos($longkey,"_")+1, strlen($longkey));
			if (isset($content->$key)) {
				if ($content->$key!=$val) {
					//Check for datetime vs just date
					if  ((is_string($content->$key)) && (strpos($content->$key,"00:00:00")!==false)) {
						$comp=str_replace(" 00:00:00", "", $content->$key);
						if ($comp!=$val) {
							$changed=true;
							$changedfields[]=array($key=>$val);
						}
					} else {
						$changed=true;
						$changedfields[]=array($key=>$val);
					}
				} else {
					$unchangedfields[]=$key;
				}
			} else {
				$missingfields[]=$key;
			}
		}
		return array("changed"=>$changed, "changed_fields"=>$changedfields, "unchanged_fields"=>$unchangedfields, "missing_fields"=>$missingfields);
	}
	
	/**
	 * clear_autosave function.
	 * 
	 * Clears the autosave
	 *
	 * @access public
	 * @param mixed $type
	 * @param mixed $urlid
	 * @return void
	 */
	public function clear_autosave($type, $urlid) {
		$this->load->library("mongo_db");
		$this->mongo_db->where(array("urlid"=>$urlid))->update("tl_content", array("autosave"=>false));
		$result=$this->mongo_db->where(array("urlid"=>$urlid))->get("tl_content");
		/*print "<script>document.domain=document.domain;</script><textarea>";
		print json_encode($result);
		print "</textarea>";*/
		return true;
	}
	
	/**
	 * field function.
	 * 
	 * Draws a single field for embedding in a form. Usually used where we haven't converted
	 * a snippet to the new Javascript system.
	 *
	 * @access public
	 * @param mixed $fieldname
	 * @param mixed $type
	 * @param mixed $urlid
	 * @return void
	 */
	public function field($fieldname, $type, $urlid) {
		$contentobj=$this->content->getByIdORM($urlid, $type);
		if (empty($contentobj->content_id)) {
			show_404("/edit/".$this->uri->segment(3)."/".$urlid);
		}
		$fields=$contentobj->getFields();
		foreach($fields as $field) {
			if ($field->name==$fieldname) {
				$fieldtype=$field->type;
				$this->load->view("/snippets/$fieldtype", array("field"=>$field));
				return true;
			}
		}
	}
	
	/**
	 * view function.
	 * 
	 * Draws our edit view.
	 *
	 * @access public
	 * @param mixed $type
	 * @param bool $urlid. (default: false)
	 * @param bool $action. (default: false)
	 * @return void
	 */
	public function view($type,$urlid=false,$action=false) {
				
		if (empty($urlid) || ($urlid=="pg")) {
			$this->index();
			return true;
		}
		if ($urlid=="search") {
			$this->search(rawurldecode($action));
			return true;
		}
		$contentobj=$this->content->getByIdORM($urlid, $this->_contenttype->id);
		if (empty($contentobj->content_id)) {
			show_404("/edit/".$this->uri->segment(3)."/".$urlid);
		}
		
		$this->checkout->lock();
		
		if (!empty($action)) {
			if (method_exists($this, $action)) {
				return call_user_method($action,$this);
			}
		}
		$data["urlid"]=$contentobj->urlid;
		$data["id"]=$contentobj->content_id;
		
		$data["contenttype_id"]=$contentobj->content_type->id;
		$data["contenttype"]=$this->_contenttypeurlid;
		$this->load->library("formcreator");
		
		//Check if we have an autosaved version
		$this->load->library("mongo_db");
		$result=$this->mongo_db->where(array("urlid"=>$urlid))->get("tl_content");
		$autosaved=false;
		if (isset($result[0]->autosave) && !empty($result[0]->autosave)) {
			$checkautosave=$this->check_change($result[0]->autosave, $urlid);
			$autosaved=$checkautosave["changed"];
			$autosave=$result[0]->autosave;
			foreach($autosave as $key=>$val) {
				$key=substr($key, strpos($key, "_")+1, strlen($key));
				if (isset($contentobj->fields[$key])) {
					if ($contentobj->fields[$key]->value!=$val) {
						$contentobj->fields[$key]->value=$val;
						if (isset($contentobj->fields[$key]->data)) {
							if (is_array($val)) {
								$x=0;
								foreach($val as $cid) {
									$contentobj->fields[$key]->data[$x++]=$this->content->getByIdORM($cid);
								}
							}
							if (!is_array($val) AND $val != "") {
								$contentobj->fields[$key]->data[0]=$this->content->getByIdORM($val);
							}
						}
					}
				}
			}
		}
		$data["autosaved"]=$autosaved;
		$this->formcreator->setFields($contentobj->getFields());
		$data["menu1_active"]="edit";
		$data["menu2_active"]="edit/".$this->_contenttypeurlid;
		$data["heading"]= $this->format_heading($data["menu2_active"]);
		$this->load->view("content/default/edit",$data);
	}
	
	/**
	 * jsonedit function.
	 * 
	 * Sends the data for displaying a form as a JSONP packet
	 *
	 * @access public
	 * @param mixed $type
	 * @param mixed $urlid
	 * @return void
	 */
	public function jsonedit($type, $urlid) {
		$contentobj=$this->content->getByIdORM($urlid, $this->_contenttype->id);
		if (empty($contentobj->content_id)) {
			show_404();
		}
		$data["urlid"]=$contentobj->urlid;
		$data["id"]=$contentobj->content_id;
		$data["content_type_id"]=$contentobj->content_type->id;
		$data["content_type"]=$this->_contenttypeurlid;
		$data["fields"]=$contentobj->getFields();
		$this->session->set_userdata("contenttype",$this->_contenttypeurlid);
		$this->tluserprefs->click_menu($this->_contenttypeurlid);
		$this->load->view("json", array("data"=>$data));
	}
	
	/**
	 * index function.
	 * 
	 * @access public
	 * @return void
	 */
	public function index() {
		$this->load->library("memcacher");
		$this->load->library("tlpicture");
		$this->paginate();
		$data["content"]=$this->content->getAll($this->_pg_perpage, $this->_pg_offset);
		
		$data["contenttype"]=$this->_contenttypeurlid;
		
		if ($this->exists->view("content/{$this->_contenttypeurlid}/list")) {
			$this->load->view("content/{$this->_contenttypeurlid}/list",$data);
		} else {
			
			$this->load->view("content/default/list",$data);
		}
	}
	
	/**
	 * row function.
	 * 
	 * @access public
	 * @param mixed $type
	 * @param mixed $urlid
	 * @return void
	 */
	public function row($type, $urlid) {
		//$this->content->setContentType($this->_contenttypeurlid);
		//$this->content->setPlatform($this->platforms->id());
		$data["item"]=$this->content->get($urlid);
		$data["contenttype"]=$type;
		if ($this->exists->view("content/{$this->_contenttypeurlid}/row")) {
			$this->load->view("content/{$this->_contenttypeurlid}/row",$data);
		} else {
			$this->load->view("content/default/row",$data);
		}
	}
	
	/**
	 * paginate function.
	 * 
	 * @access public
	 * @return void
	 */
	public function paginate() {
		$this->_pg_offset=$this->uri->segment(5);
		$this->load->library('pagination');
		$config['full_tag_open']="<div class='pagination'>";
		$config['full_tag_close']="</div>";
		$config['uri_segment'] = 5;
		$config['num_links'] = $this->_pg_numlinks;
		$config['base_url'] = "/edit/".$this->uri->segment(2)."/".$this->uri->segment(3)."/pg/";
		$config['total_rows'] = $this->content->count();
		$config['per_page'] = $this->_pg_perpage;
		$this->pagination->initialize($config);
	}
	
	/**
	 * search function.
	 * 
	 * @access public
	 * @param mixed $s
	 * @return void
	 */
	public function search($s) {
		$s=rawurldecode($s);
		$this->load->library("search");
		$this->_pg_offset=$this->uri->segment(7);
		$config['uri_segment'] = 7;
		//$s=$this->uri->segment(4);
		if (($s=="pg") || ($s=="undefined")) {
			$s="";
			$this->_pg_offset=$this->uri->segment(6);
			$config['uri_segment'] = 6;
		}
		$result=$this->search->dosearch($this->_contenttypeurlid, $s, $this->_pg_perpage, $this->_pg_offset);
		$this->load->library('pagination');
		$config['full_tag_open']="<div class='pagination'>";
		$config['full_tag_close']="</div>";
		$config['num_links'] = $this->_pg_numlinks;
		$config['base_url'] = "/edit/".$this->uri->segment(2)."/".$this->uri->segment(3)."/".$this->uri->segment(4)."/".$s."/pg";
		$config['total_rows'] = $result["count"];
		$config['per_page'] = $this->_pg_perpage;
		$this->pagination->initialize($config);
		$data["pagination"]=$this->pagination->create_links();
		$data["content"]=$result["docs"];
		$data['total_rows'] = $result["count"];
		$data["contenttype"]="{$this->_contenttypeurlid}";
		$data["search"]=$s;
		if ($this->exists->view("content/{$this->_contenttypeurlid}/list")) {
			$this->load->view("content/{$this->_contenttypeurlid}/list",$data);
		} else {
			$this->load->view("content/default/list",$data);
		}
	}
	
	/**
	 * checkin function.
	 * 
	 * Saves a copy and does a major version bump
	 *
	 * @access public
	 * @return void
	 */
	public function checkin() {
		$this->versions->bump_major_version();
		print json_encode(array("error"=>false, "major_version"=>$this->versions->get_major_version()));
	}
	
}

/**
 * TL_Controller_Delete class.
 *
 * Deletes stuff. Also takes care of deleting joins. 
 * 
 * @extends TL_Controller_CRUD
 */
class TL_Controller_Delete extends TL_Controller_CRUD {
	
	/**
	 * __construct function.
	 * 
	 * @access public
	 * @return void
	 */
	public function __construct() {
		parent::__construct();
	}
	
	/**
	 * _remap function.
	 * 
	 * Make it dead. This is a remap function so you just pass it the urlid in the 3rd URI segment and it'll take care of the rest.
	 *
	 * @access public
	 * @return void
	 */
	public function _remap() {
		$urlid=$this->uri->segment(3);
		if (empty($urlid)) {
			show_404("/edit/".$this->uri->segment(2));
			return true;
		}
		$contentobj=new TLContent($urlid);
		$contentobj->setContentType($this->_contenttypeurlid);
		$this->checkCallback("onBeforeDelete",$contentobj);
		$contentobj->delete();
		$this->checkCallback("onAfterDelete",$contentobj);
		//if (!$returndata["error"]) { //Memcached submission
			$this->messaging->post_action("delete",array($this->_contenttypeurlid,$urlid));
			//$this->cachereset($this->_contenttypeurlid,$contentobj->urlid);
		//}
		
		redirect("edit/".$this->_contenttypeurlid);
	}
	
}

/**
 * TL_Controller_List class.
 *
 * Displays a list of content, usually pre-editing.
 * 
 * @extends TL_Controller_CRUD
 */
class TL_Controller_List extends TL_Controller_CRUD {
	/**
	 * _pg_perpage
	 * 
	 * Number of rows to display per page (default value: 100)
	 * 
	 * @var int
	 * @access public
	 */
	public  $_pg_perpage=30;
	
	/**
	 * _pg_numlinks
	 * 
	 * Number of pagination links to display (default value: 15)
	 * 
	 * @var int
	 * @access public
	 */
	public $_pg_numlinks=15;
	
	/**
	 * _pg_offset
	 * 
	 * @var mixed
	 * @access protected
	 */
	protected $_pg_offset=0;
	
	/**
	 * _remap function.
	 * 
	 * @access public
	 * @return void
	 */
	public function _remap() {
		//$this->content->setContentType($this->_contenttypeurlid);
		//$this->content->setPlatform($this->platforms->id());
		
		if($this->uri->segment(3)=="item") {
			$this->item();
			return true;
		} elseif($this->uri->segment(3)=="search") {
			$this->search();
			return true;
		} elseif($this->uri->segment(3)=="suggest") {
			$this->suggest();
			return true;
		} elseif($this->uri->segment(2)=="simple") { //A simple list
			$this->simple();
			return true;
		} elseif($this->uri->segment(2)=="jsonlist") { //A simple list
			$this->jsonlist();
			return true;
		} elseif($this->uri->segment(2)=="nested") { //A simple list
			$this->nested();
			return true;
		} elseif($this->uri->segment(2)=="jsonnested") { //A simple list
			$this->jsonnested();
			return true;
		} elseif($this->uri->segment(3)=="deepsearch") { //A simple list
			$this->deepsearch();
			return true;
		} elseif($this->uri->segment(2)=="jsonfilelist") { //Returns links to files
			$this->jsonfilelist();
			return true;
		}
		
		$this->paginate();
		$data["content"]=$this->content->getAll($this->_pg_perpage, $this->_pg_offset);
		$data["multiple"]=$this->uri->segment(3);
		$data["contenttype"]="{$this->_contenttypeurlid}";
		$data["menu1_active"]="edit";
		$data["menu2_active"]="edit/{$this->_contenttypeurlid}";
		if ($this->exists->view("content/{$this->_contenttypeurlid}/selectcontainer")) {
			$this->load->view("content/{$this->_contenttypeurlid}/selectcontainer",$data);
		} else {
			$this->load->view("content/default/selectcontainer",$data);
		}
	}
	
	/**
	 * jsonlist function.
	 * 
	 * Sends a list of content as a JSONP package. If you set 'searchstring' as a get or post, it'll search. 
	 *
	 * @access public
	 * @return void
	 */
	public function jsonlist() {
		$this->_pg_perpage=100;
		$data["search"]='';
		$segments=$this->uri->segment_array();
		$searchstring=$this->input->get_post('searchstring');
		$this->_pg_offset=$this->input->get_post('offset');
		if (!empty($searchstring)) {
			$data["content"]=$this->content->search($this->_contenttypeurlid,$searchstring,$this->_pg_perpage, $this->_pg_offset);
			$data["count"]=$this->content->searchCount($this->_contenttypeurlid,$searchstring);
			$data["search"]=$searchstring;
		} else {
			$data["content"]=$this->content->getAll($this->_pg_perpage, $this->_pg_offset);
			$data["count"]=$this->content->count();
		}
		$data["perpage"]=$this->_pg_perpage;
		$data["offset"]=$this->_pg_offset;
		$data["contenttype"]=$this->_contenttypeurlid;
		//$this->tluserprefs->click_menu($this->_contenttypeurlid);
		$this->load->view("json",array("data"=>$data));
	}
	
	/**
	 * search function.
	 * 
	 * @access public
	 * @return void
	 */
	public function search() {
		$this->load->library("search");
		$this->_pg_offset=$this->uri->segment(6);
		$config['uri_segment'] = 6;
		$s=$this->uri->segment(4);
		if (($s=="pg") || ($s=="undefined")) {
			$s="";
			$this->_pg_offset=$this->uri->segment(5);
			$config['uri_segment'] = 5;
		}

		$result=$this->search->dosearch($this->_contenttypeurlid,$s, $this->_pg_perpage, $this->_pg_offset);
		$this->load->library('pagination');
		$config['num_links'] = $this->_pg_numlinks;
		$config['base_url'] = "/list/".$this->uri->segment(2)."/".$this->uri->segment(3)."/".$s."/pg";
		$config['total_rows'] = $result["count"];
		$config['per_page'] = $this->_pg_perpage;
		$this->pagination->initialize($config);
		$result["pagination"]=$this->pagination->create_links();
		print json_encode($result);
	}
	
	/**
	 * suggest function.
	 * 
	 * Like search, but uses the 'suggest' algorighm
	 *
	 * @access public
	 * @return void
	 */
	public function suggest() {
		$this->load->library("search");
		$s=$this->input->get("term");
		$type=$this->uri->segment(2);
		$limit=20;
		if ($type=="all") {
			print json_encode($this->search->suggest($type,$s,$limit));
		} elseif($type=="mixed") {
			$segs=$this->uri->segment_array();
			$segs=array_slice($segs, 3);
			print json_encode($this->search->suggest($segs,$s,$limit));
		} else {
			print json_encode($this->search->suggest($this->_contenttypeurlid,$s,$limit));
		}
	}
	
	/**
	 * item function.
	 * 
	 * @access public
	 * @return void
	 */
	public function item() {
		$data["item"]=$this->content->getByIdORM($this->uri->segment(4),$this->_contenttype->id);
		if ($this->exists->view("content/{$this->_contenttypeurlid}/selectitem")) {
			$this->load->view("content/{$this->_contenttypeurlid}/selectitem",$data);
		} else {
			$this->load->view("content/default/selectitem",$data);
		}
	}
	
	/**
	 * paginate function.
	 * 
	 * @access public
	 * @return void
	 */
	public function paginate() {
		$this->_pg_offset=$this->uri->segment(5);
		$this->load->library('pagination');
		$config['uri_segment'] = 5;
		$config['num_links'] = $this->_pg_numlinks;
		$config['base_url'] = "/list/".$this->uri->segment(2)."/".$this->uri->segment(3)."/pg/";
		//$config['total_rows'] = $this->{$this->_model}->count();
		$config['total_rows'] = $this->content->count();
		$config['per_page'] = $this->_pg_perpage;
		$this->pagination->initialize($config);
	}
	
	/**
	 * simple function.
	 * 
	 * Used by the publish view
	 *
	 * @access public
	 * @return void
	 */
	public function simple() {
		$this->_pg_perpage=100;
		$data["search"]="";
		$segments=$this->uri->segment_array();
		$searchcheck=array_slice($segments,-2);
		if ($searchcheck[0]=="search") {
			$s=rawurldecode($searchcheck[1]);
			$this->load->library('pagination');
			$data["content"]=$this->content->search($this->_contenttypeurlid,$s,$this->_pg_perpage, $this->_pg_offset);
			$config['num_links'] = $this->_pg_numlinks;
			$config['base_url'] = "/list/".$this->uri->segment(2)."/".$this->uri->segment(3)."/".$searchcheck[1]."/pg";
			$config['total_rows'] = $this->content->searchCount($this->_contenttypeurlid,$s,$this->_pg_perpage, $this->_pg_offset);
			$config['per_page'] = $this->_pg_perpage;
			$data["search"]=$s;
			$this->pagination->initialize($config);
		} else {
			$this->paginate();
			$data["content"]=$this->content->getAll($this->_pg_perpage, $this->_pg_offset);
		}
		//$data["action"]=$this->uri->segment(3);
		
		$data["contenttype"]="{$this->_contenttypeurlid}";
		$this->load->view("content/default/simplelist",$data);
	}
	
	/**
	 * nested function.
	 * 
	 * Display a nested view of an item list
	 *
	 * @access public
	 * @return void
	 */
	function nested() {
		$segments=$this->uri->segment_array();
		$searchcheck=array_slice($segments,-2);
		$tree = $this->content->get_sectionmap($searchcheck[0]);
		$data['tree'] = $this->make_nested_tree($tree,$searchcheck[0] );// $tree;	
		$data["contenttype"]="{$this->_contenttypeurlid}";
		$this->load->view("content/default/nested_sections",$data);
	}
	
	/**
	 * jsonnested function.
	 * 
	 * A nested view of an item list, as JSONP
	 *
	 * @access public
	 * @return void
	 */
	public function jsonnested() {
		$segments=$this->uri->segment_array();
		$searchcheck=array_slice($segments,-2);
		$tree = $this->content->get_sectionmap($searchcheck[0]);
		$data['tree'] = $tree;
		$data["contenttype"]="{$this->_contenttypeurlid}";
		$this->load->view("json",array("data"=>$data));
	}
	
	/**
	 * make_nested_tree function.
	 * 
	 * Iterative function that works with nested()
	 *
	 * @access protected
	 * @param mixed $sections
	 * @param mixed $contenttype
	 * @return void
	 */
	protected function make_nested_tree($sections, $contenttype){
		$string = "<ul class='nested_tree '>";
		foreach($sections as $section){
			//hack to remove the home page section on the list
			if($section->title != "Home Page"){
				$string .= "<li class='main_section'><div id='".$section->content_id."' class='small_item'>".$section->title."</div>";
						if(isset($section->children) && is_array($section->children))
						{
							
							$string .= $this->nested_children($section->children, $contenttype);
						}
						$string .= "</li>";
			}
						
		}
		$string .= "</ul>";
		
		return $string;
	}
	
	/**
	 * nested_children function.
	 * 
	 * @access public
	 * @param mixed $children
	 * @param mixed $contenttype
	 * @return void
	 */
	function nested_children($children, $contenttype)
	{
		$string = "<ul class='small_section'>";
		
		foreach($children as $item){
			if($item->title != "Home Page"){
				$string .= "<li class='nested_section'><div id='".$item->content_link_id."' class='small_item'>".$item->title."</div></li>";
			}
			
		}
		$string .= "</ul>";
		return $string;
	}
	
	/**
	 * deepsearch function.
	 * 
	 * @access public
	 * @return void
	 */
	public function deepsearch() {
		$this->load->library("search");
		$s=$this->input->get("term");
		$type=$this->uri->segment(2);
		$limit=20;
		print json_encode($this->search->smart_search($type,$s,$limit));
	}
	
	/**
	 * jsonfilelist function.
	 * 
	 * Returns files associated to a piece of content
	 *
	 * @access public
	 * @return void
	 */
	public function jsonfilelist() {
		$contentobj=$this->content->getByIdORM($this->uri->segment(4),$this->_contenttype->id);
		$fields=$contentobj->getFields($this->_contenttype->id);
		$result=array();
		foreach($fields as $field) {
			if ($field->type=="image" || $field->type=="file") {
				if (isset($field->linkformat) && !empty($field->linkformat)) {
					$result["value"]=str_replace('{filename}', $field->value, $field->linkformat);
				}
			}
		}
		$this->load->view('json', array('data'=>$result));
	}

}

/**
 * TL_Controller_CRUD class.
 *
 * A base class that extends CodeIgniter's Controller to offer a quick implementation of all our CRUD functionality
 * 
 * @extends CI_Controller
 */
class TL_Controller_CRUD extends CI_Controller {
	
	/**
	 * _contenttype
	 * 
	 * (default value: false)
	 * 
	 * @var object
	 * @access protected
	 */
	protected $_contenttype=false;
	
	protected $_contenttypeurlid=false;
	
	/**
	 * __construct function.
	 * 
	 * You can set a content type as a parameter or let the controller try figure it out from the uri. 
	 * The constructor also tells everyone what you're doing through the Stomp server.
	 * It sets a userdata contenttype which is used when you switch between creating and editing to keep your state.
	 *
	 * @access public
	 * @param bool $contenttype. (default: false)
	 * @return void
	 */
	public function __construct($contenttype=false) {
		parent::__construct();
		$this->load->model("model_content");
		if (!empty($contenttype)) {
		//Try get the contenttype from our constructor
			$this->_contenttypeurlid=$contenttype;
		} else {
		//Try get contenttype from uri segment
			$segs=$this->uri->segment_array();
			$content_types=$this->model_content->get_content_types();
			foreach($content_types as $content_type) {
				if (in_array($content_type->urlid, $segs)) {
					$this->_contenttypeurlid=$content_type->urlid;
				}
			}
		}
		if (empty($this->_contenttypeurlid)) {
			//Only exception is "All" that we use for searching
			if (!in_array("all",$segs) && !in_array("mixed",$segs)) {
				show_error("Must set content type");
			}
		}
		$this->_contenttype=$this->db->get_where("content_types",array("urlid"=>$this->_contenttypeurlid))->row();
		$this->load->model($this->_contenttype->model, "content");
		
		$this->content->setContentType($this->_contenttypeurlid);
		$this->content->setPlatform($this->platforms->id());
		
		//$this->session->set_userdata("contenttype",$this->_contenttypeurlid);
		//Send where we are thru Stomp
		$stompinfo=array("user"=>$this->model_user->get_by_id($this->session->userdata("id")), "url"=>$this->uri->segment_array());
		$this->messaging->post_message("all",json_encode($stompinfo));
	}
	
	/**
	 * cachereset function.
	 * 
	 * @access public
	 * @param mixed $contenttype_urlid
	 * @param mixed $urlid
	 * @return void
	 */
	public function cachereset($contenttype_urlid, $urlid) {
		$this->load->library("tlpicture");
		$this->load->library("memcacher");
		$this->memcacher->clearById($contenttype_urlid, $urlid);
		$this->memcacher->clearPic($contenttype_urlid, $urlid);
		$this->tlpicture->clearCache($urlid, $contenttype_urlid);
	}
	
	/**
	 * cachesave function.
	 * 
	 * @access public
	 * @param mixed $contenttype_urlid
	 * @param mixed $urlid
	 * @return void
	 */
	public function cachesave($contenttype_urlid, $urlid) {
		$this->load->library("memcacher");
		$this->memcacher->addById($contenttype_urlid, $urlid);
		return true;
	}
	
	/**
	 * fileupload function.
	 * 
	 * Handles the uploading of files to the server, creates CDN links etc.
	 * Returns false if it's not a file, and true if it is a file. Everything else is returned by reference.
	 *
	 * @access public
	 * @param object $field
	 * @param string $urlid
	 * @param object &$contentobj
	 * @param mixed &$returndata
	 * @return boolean
	 */
	public function fileupload($field, $urlid="", &$contentobj, &$returndata) {
		if($field->type!="file" && $field->type!="image") {
			return false;
		}
		if(empty($_FILES[$field->tablename."_".$field->name]["name"])){
			$contentobj->{$field->name}=$this->input->post($field->tablename."_".$field->name);
		    $contentobj->{$field->cdn_link}=$this->input->post("cdn_link");
		    return true;
		}
		$dir="/resources/uploads/files/original/".date("Y")."/".date("m")."/".date("d")."/";
		$cachedir="/resources/uploads/pictures/cache/";
		if (!empty($field->directory)) {
			$dir=$field->directory;
			if ($dir[0]!="/") {
				$dir="/".$dir;
			}
			while (strpos($dir,"{")!==false) {
				$part=substr($dir, strpos($dir,"{")+1, strpos($dir,"}")-strpos($dir,"{")-1);
				$replace=eval("return $part;");
				$dir=str_replace("{".$part."}", $replace, $dir);
			}
			if (!is_dir(".".$dir)) {
				mkdir(".".$dir, 0755, true);
			}
			if (!is_dir(".".$dir)) {
				show_error("Unable to create directory $dir");
			}
		}
		$basedir=".".$dir;
		if (!file_exists($basedir)) {
			if (!mkdir($basedir, 0755, true)) {
				$returndata["error"]=true;
				$returndata["msg"]="Failed to create directory structure";
				$returndata["info"]="Tried to create $dir";
			}
		}
		if (!$returndata["error"]) {
			if (!empty($_FILES[$field->tablename."_".$field->name]["name"])) {
				$config['upload_path'] = $basedir;
				$config['allowed_types'] = implode("|",$field->filetypes);
				$this->load->library("upload",$config);
				if (!$this->upload->do_upload($field->tablename."_".$field->name)) {
					$returndata["error"]=true;
					$returndata["info"]=$this->upload->display_errors();
					$returndata["msg"]="File Upload failed";
				} else {
					$uploaddata = $this->upload->data();
					$filename=$dir.$uploaddata["file_name"];
					$contentobj->{$field->name}=$filename;
					//Clear Cache
					exec("rm .".$cachedir.$urlid."*");
					if ($this->config->item("cdn_service") && ($field->cdn)) {
					//Upload to CDN
						$this->load->library("cdn");
						$this->cdn->init();
						if ($this->cdn->hasError()) {
							$returndata["error"]=true;
							$returndata["info"]=$this->cdn->lastError();
							$returndata["msg"]="Error uploading to CDN";
						} else {
							$bucket=$this->config->item("cdn_bucket");
							$this->cdn->createBucket($bucket);
							$cdnurl=$this->cdn->uploadFile(".".$filename, $bucket,$filename);
							if ($this->cdn->hasError()) {
								$returndata["error"]=true;
								$returndata["info"]=$this->cdn->lastError();
								$returndata["msg"]="Error uploading to CDN";
							} else {
								if (!empty($field->cdn_link)) {
									$contentobj->{$field->cdn_link}=$cdnurl;
								}
							}
						}
					}
				}
			}
		}
		return true;
	}
	
	/**
	 * checkCallback function.
	 * 
	 * @access protected
	 * @param mixed $callbackname
	 * @param mixed &$returndata
	 * @return void
	 */
	protected function checkCallback($callbackname,&$returndata) {
		if (method_exists($this,$callbackname)) {
			return call_user_func_array(array(&$this,$callbackname),array($returndata));
		}
	}
	
	/**
	 * format_heading function.
	 * 
	 * @access protected
	 * @param mixed $active_menu
	 * @return void
	 */
	protected function format_heading($active_menu){
		$items = explode("/",$active_menu);
		$string = "";
		foreach($items as $item){
			$string .= ucfirst($item)." - ";
		}
		return substr($string, 0, strlen($string) - 3);
		
	}
}
?>