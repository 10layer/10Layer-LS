<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

/**
 * 10Layer Picture class
 *
 * Deals with finding pictures associated with content
 *
 * @package 10Layer
 * @subpackage	Libraries
 * @category	Libraries
 * @author		Jason Norwood-Young
 * @link		http://10layer.com
 * 
 */
 
class TlPicture {
	protected $ci;
	protected $_filename=false;
	protected $_cachedir="./resources/cache/pictures/";
	
	public function __construct() {
		$this->ci=&get_instance();
		$this->ci->load->library("memcacher");
	}
	
	public function findPicture($urlid, $contenttype_urlid) {
		$this->_filename="";
		$content_type=$this->ci->model_content->get_content_type($contenttype_urlid);
		$this->ci->load->model($content_type->model, "content");
		$pic=$this->ci->content->getByIdORM($urlid, $contenttype_urlid);
		if (empty($pic->content_id)) {
			show_error("Content $urlid not found");
		}
		$fields=$pic->getFields();
		foreach($fields as $field) { //CDN file - let's download it
			if (($field->type=="cdn") && (!empty($field->value))) {
				$tmpfile="/resources/cache/pictures/cdn/".basename($field->value);
				if (!file_exists($tmpfile)) {
					file_put_contents(".".$tmpfile,file_get_contents($field->value));
				}
				$this->_filename=$tmpfile;
			}
		}
		foreach($fields as $field) {
			if ($field->type=="file") {
				$this->_filename=$field->value;
			}
		}
		if (empty($this->_filename)) {
		//We didn't find the pic. Let's look elsewhere.
		    foreach($fields as $field) {
		    	if ($field->type=="rich") {
		    		if (!empty($field->data->fields["filename"]->value)) {
		    			$this->_filename=$field->data->fields["filename"]->value;
		    			break;
		    		}
		    	}
		    }
		}
		if (empty($this->_filename)) {
		//Still not found. Try a bit deeper.
		    $this->_filename=$this->breadthSearch($fields);
		}
		
		//Let's just double-check that this is in fact an image
		if (!empty($this->_filename)) {
			$parts=pathinfo($this->_filename);
			$ext=$parts["extension"];
			if (($ext=="png") || ($ext=="gif") || ($ext=="jpg")) {
				//Continue
			} else {
				$this->ci->load->helper('file');
				$mime=str_replace("/","-",get_mime_by_extension($this->_filename)).".png";
				if (file_exists(APPPATH."third_party/10layer/resources/images/mimetypes/{$mime}")) {
					$this->_filename=APPPATH."third_party/10layer/resources/file/images/mimetypes/{$mime}";
				} else {
					$this->_filename=false;
				}
			}
		}
		
		return $this->_filename;
	}
	
	public function hasPic($urlid, $contenttype_urlid) {
		$picture=$this->ci->memcacher->picFilename($contenttype_urlid,$urlid);
		if (!empty($picture) && file_exists(".".$picture)) {
			return true;
		}
		return false;
	}
	
	public function filename($urlid, $contenttype_urlid) {
		$picture=$this->ci->memcacher->picFilename($contenttype_urlid,$urlid);
		if (empty($picture) || !file_exists(".".$picture)) {
			return false;
		}
		return $picture;
	}
	
	public function clearCache($urlid, $contenttype_urlid) {
		exec("rm ".$this->_cachedir.$urlid."*");
		$this->ci->memcacher->clearPic($contenttype_urlid, $urlid);
	}
	
	/*protected function _cachefilename() {
		$segs=$this->uri->segment_array();
		$params=array_slice($segs,3);
		$cachefilename=$this->_cachedir.implode("_",$params).".jpg";
		return $cachefilename;
	}*/
	
	protected function breadthSearch($fields) {
		foreach($fields as $field) {
			if (is_object($field)) {
				if (isset($field->type) && ($field->type=="rich")) {
					if (!empty($field->data->fields["filename"]->value)) {
						return $field->data->fields["filename"]->value;
					}
				}
			}
			if (isset($field->data->fields)) {
				return $this->breadthSearch($field->data->fields);
			}
		}
	}
}