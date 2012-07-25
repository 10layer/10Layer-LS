<?php
	/**
	 * Picture class
	 * 
	 * @extends CI_Controller
	 * @package 10Layer
	 * @subpackage Libraries
	 */
	class Picture extends CI_Controller {
		protected $_filename="";
		protected $_im=false;
		protected $_cachedir="./resources/cache/pictures/";
		protected $_cachetime=8760;
		
		/**
		 * __construct function.
		 * 
		 * @access public
		 * @return void
		 */
		public function __construct() {
			parent::__construct();
			$cachefilename=$this->_cachefilename();
			if (is_file($cachefilename)) {
				$cached=$this->header($cachefilename);
				if (!$cached) {
					print file_get_contents($cachefilename);
				}
				return true;
			}
			$urlid=$this->uri->segment(4);
			$content_type=$this->model_content->checkContentType($urlid);
			$this->load->model($content_type->model, "content");
			$pic=$this->content->getByIdORM($urlid, $content_type->id);
			if (empty($pic->content_id)) {
				header("HTTP/1.0 404 Not Found");
				die();
			}
			$fields=$pic->getFields();
			foreach($fields as $field) {
				if (($field->type=="cdn") && (!empty($field->value))) {
					$tmpfile="/resources/cache/pictures/cdn/".basename($field->value);
					if (!file_exists(".".$tmpfile)) {
						file_put_contents(".".$tmpfile,file_get_contents($field->value));
					}
					$this->_filename=$tmpfile;
				}
			}
			if (empty($this->_filename)) {
				foreach($fields as $field) {
					if ($field->type=="file" || $field->type=="image") {
						if (isset($field->linkformat) && !empty($field->linkformat)) {
							$filename=$field->value;
							$filename=str_replace('{filename}', $filename, $field->linkformat);
							$tmpfile="./resources/cache/pictures/cdn/".basename($field->value);
							if (!file_exists($tmpfile)) {
								file_put_contents($tmpfile,file_get_contents($filename));
							}
							$this->_filename=ltrim($tmpfile, '.');
						} else {
							$this->_filename=$field->value;
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
								$this->_filename=$dir.basename($this->_filename);
							}
						}
					}
				}
			}
			if (empty($this->_filename)) {
			//We didn't find the pic. Let's look elsewhere.
				foreach($fields as $field) {
					if ($field->type=="rich") {
						if (!empty($field->data->fields["filename"]->value)) {
							if (isset($field->data->fields["filename"]->linkformat) && !empty($field->data->fields["filename"]->linkformat)) {
								$filename=$field->data->fields["filename"]->value;
								$filename=str_replace('{filename}', $filename, $field->data->fields["filename"]->linkformat);
								$tmpfile="./resources/cache/pictures/cdn/".basename($field->data->fields["filename"]->value);
								if (!file_exists($tmpfile)) {
									file_put_contents($tmpfile,file_get_contents($filename));
								}
								$this->_filename=ltrim($tmpfile, '.');
							} else {
								$this->_filename=$field->data->fields["filename"]->value;
							}
							break;
						}
					}
				}
			}
			
			if (empty($this->_filename)) {
			//Still not found. Try a bit deeper.
				$this->_filename=$this->breadthSearch($fields);
			}
			
			
			
			if (empty($this->_filename)) {
				//header("HTTP/1.0 404 Not Found");
				$this->_filename=APPPATH."third_party/10layer/resources/images/image-missing.jpg";
				//die();
			}
			
			
			
			if (!file_exists(".".$this->_filename)) {
				//header("HTTP/1.0 404 Not Found");
				//$this->_no_image();
				$this->_filename=APPPATH."third_party/10layer/resources/images/image-missing.jpg";
				//die();
			}
			
			//Let's just double-check that this is in fact an image
			$parts=pathinfo($this->_filename);
			$ext=$parts["extension"];
			if (($ext=="png") || ($ext=="gif") || ($ext=="jpg")) {
				//Continue
			} else {
				$this->load->helper('file');
				$mime=str_replace("/","-",get_mime_by_extension($this->_filename)).".png";
				if (file_exists(APPPATH."third_party/10layer/resources/images/mimetypes/{$mime}")) {
					$this->_filename=APPPATH."third_party/10layer/resources/images/mimetypes/{$mime}";
				} else {
					$this->_filename=APPPATH."third_party/10layer/resources/images/mimetypes/unknown.png";
				}
			}
		}
		
		protected function breadthSearch($fields) {
			//$found=false;
			//while(!$found) {
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
			//}
		}
		
		public function getSize() {
			$is=getimagesize(".".$this->_filename);
			print json_encode(array("width"=>$is[0], "height"=>$is[1]));
		}
		
		public function transform() {
			$this->_init($this->_filename);
			$this->_convert();
			$this->_im->writeImage(".".$this->_filename);
			$this->header();
			echo $this->_im;
		}
		
		public function display() {
			if (!$this->_cache()) {
				$this->_init($this->_filename);
				$this->_convert();
				$this->header();
				$this->_storecache();
				echo $this->_im;
			}
		}
		
		protected function _init($filename) {
			if (file_exists($filename)){
				$this->_im=new imagick($filename);
				return true;
			} elseif (file_exists(".".$filename)) {
				$this->_im=new imagick(".".$filename);
				return true;
			}
			show_404();
		}
		
		protected function _convert() {
			$method=$this->uri->segment(5);
			if (method_exists("imagick", $method)) {
				$segs=$this->uri->segment_array();
				$params=array_slice($segs,5);
				call_user_func_array(array(&$this->_im,$method), $params);
				$this->_im->setImageCompression(imagick::COMPRESSION_JPEG); 
				$this->_im->setImageCompressionQuality(80);
				$this->_im->stripImage();
			}
		}
		
		protected function header($file=false) {
			//Snippet from http://php.net/manual/en/function.getallheaders.php for nginx compatibility
			if (!function_exists('apache_request_headers')) { 
				function apache_request_headers() { 
					foreach($_SERVER as $key=>$value) { 
						if (substr($key,0,5)=="HTTP_") { 
							$key=str_replace(" ","-",ucwords(strtolower(str_replace("_"," ",substr($key,5))))); 
							$out[$key]=$value; 
						} else { 
							$out[$key]=$value; 
						}
					}
					return $out; 
				} 
			}
			if (!empty($file)) {
				$lastmod=filemtime($file);
				$filesize=filesize($file);
			} else {
				$lastmod=time();
				$filesize=strlen($this->_im);
			}
			$cachemins=$this->_cachetime*60;
			$headers = apache_request_headers();
			if (isset($headers['If-Modified-Since']) && (strtotime($headers['If-Modified-Since']) == $lastmod)) {
			    header('Last-Modified: '.gmdate('D, d M Y H:i:s', $lastmod).' GMT', true, 304);
			    return false;
			} else {
			    header("Content-Type: image/jpeg");
			    header("Pragma: public");
			    header("Cache-Control: private");
			    header('Expires: ' . gmdate('D, d M Y H:i:s', time()+$cachemins) . ' GMT');
			    header('Last-Modified: '.gmdate('D, d M Y H:i:s',$lastmod) . ' GMT',true,200);
			    header('Content-Length: '.$filesize);
			    return true;
			}
		}
		
		protected function _cache() {
			$cachefilename=$this->_cachefilename();
			if (file_exists($cachefilename)) {
				if ($this->header($cachefilename)) {
					print file_get_contents($cachefilename);
				}
				return true;
			}
		}
		
		protected function _storecache() {
			$cachefilename=$this->_cachefilename();
			file_put_contents($cachefilename,$this->_im);
		}
		
		protected function _cachefilename() {
			$segs=$this->uri->segment_array();
			$params=array_slice($segs,3);
			$cachefilename=$this->_cachedir.implode("_",$params).".jpg";
			return $cachefilename;
		}
		
	}

/* End of file picture.php */
/* Location: ./system/application/controllers/workers/ */