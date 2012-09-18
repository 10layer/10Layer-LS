<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

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
		if($this->uri->segment(2)=="jsonlist") { //A simple list
			$this->jsonlist();
			return true;
		} elseif($this->uri->segment(2)=="jsonnested") { //A simple list
			$this->jsonnested();
			return true;
		} elseif($this->uri->segment(2)=="jsonfilelist") { //Returns links to files
			$this->jsonfilelist();
			return true;
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
			$data["search"]=$searchstring;
			$data["count"]=$this->content->searchCount($this->_contenttypeurlid,$searchstring);
			$data["content"]=$this->content->search($this->_contenttypeurlid,$searchstring,$this->_pg_perpage, $this->_pg_offset);
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
	public function __construct($content_type=false) {
		parent::__construct();
		$this->load->model("model_content");
		if (!empty($content_type)) {
		//Try get the contenttype from our constructor
			$this->_contenttypeurlid=$content_type;
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
		//10LayerLS - Change following two lines to use MongoDB
		$this->_contenttype=$this->db->get_where("content_types",array("urlid"=>$this->_contenttypeurlid))->row();
		$this->load->model($this->_contenttype->model, "content");
		
		//10LayerLS - Change to use model_content
		$this->content->setContentType($this->_contenttypeurlid);
		$this->content->setPlatform($this->platforms->id());
		
		//Send where we are thru Stomp
		$stompinfo=array("user"=>$this->model_user->get_by_id($this->session->userdata("id")), "url"=>$this->uri->segment_array());
		//$this->messaging->post_message("all",json_encode($stompinfo));
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