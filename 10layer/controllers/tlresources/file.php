<?php
	/**
	 * File class
	 * 
	 * Loads files in application/third_party/resources
	 *
	 * @extends Controller
	 * @package 10Layer
	 * @subpackage Controllers
	 */
	class File extends CI_Controller {

		/**
		 * __construct function.
		 * 
		 * @access public
		 * @return void
		 */
		public function __construct() {
			parent::__construct();
			$this->load->library("tlsecurity");
			$this->tlsecurity->ignore_security();
			$this->load->helper("file");
		}
		
		/**
		 * _remap function.
		 * 
		 * Shows a specific file with header
		 *
		 * @access public
		 * @return void
		 */
		public function _remap() {
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
			$segments=$this->uri->segment_array();
			array_shift($segments);
			array_shift($segments);
			$filename=APPPATH."third_party/10layer/resources/".implode("/",$segments);
			if (!file_exists($filename)) {
				show_404($segments[sizeof($segments)-1]);
				return true;
			}
			
			$lastmod=filemtime($filename);
			$filesize=filesize($filename);
			$cachemins=120*60;
			$headers = apache_request_headers();
			if (isset($headers['If-Modified-Since']) && (strtotime($headers['If-Modified-Since']) == $lastmod)) {
			    header('Last-Modified: '.gmdate('D, d M Y H:i:s', $lastmod).' GMT', true, 304);
			} else {
				header("content-type: ".get_mime_by_extension($filename));
				header("Pragma: public");
			    header("Cache-Control: private");
			    header('Expires: ' . gmdate('D, d M Y H:i:s', time()+$cachemins) . ' GMT');
			    header('Last-Modified: '.gmdate('D, d M Y H:i:s',$lastmod) . ' GMT',true,200);
			    header('Content-Length: '.$filesize);
			    print file_get_contents($filename);
			}
			
			
		}
	}

/* End of file tlresources.php */
/* Location: ./system/application/controllers/ */