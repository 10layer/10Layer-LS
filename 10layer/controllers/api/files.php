<?php
	require_once('10layer/system/TL_Api.php');
	
	/**
	 * Files class
	 * 
	 * @extends CI_Controller
	 */
	class Files extends TL_Api {
		
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
		 * upload function.
		 * 
		 * Upload a file to the system, returns with filename
		 *
		 * @access public
		 * @return void
		 */
		public function upload() {
			$this->enforce_secure();
			$raw = file_get_contents("php://input");
			$dir = "/content/";
			$parts=date("Y")."/".date("m")."/".date("d")."/";
			
			@mkdir(".".$dir.$parts, 0755, true);
			if (!is_dir(".".$dir.$parts)) {
				$this->show_error("."."$dir$parts is not a directory or doesn't exist");
			}
			if (empty($raw)) {
				$this->show_error("File not received or empty");
			}
			if (isset($this->vars["filename"])) {
				$filename = $this->vars["filename"];
			} else {
				$filename = md5($raw);
			}
			$this->data["message"]=".".$dir.$parts.$filename;
			file_put_contents(".".$dir.$parts.$filename, $raw);
			$this->data["content"]=array("filename"=>$filename, "full_name"=>$dir.$parts.$filename);
			$this->returndata();
		}
		
		public function download() {
			$dir = "/content/";
			$uria = $this->uri->rsegment_array();
			$uria = array_slice($uria, 2);
			$filename = "./".rawurldecode(implode("/", $uria));
			if (!is_file($filename)) {
				show_404();
			}
			header("Pragma: public"); 
			header('Content-type: '.mime_content_type($filename));
			header('Content-Disposition: attachment; filename="'.basename($filename).'"');
			header('Content-Transfer-Encoding: binary'); 
			header('Content-Length: '.filesize($filename));
			ob_clean(); 
			flush(); 
			readfile($filename);
		}
	}

/* End of file files.php */
/* Location: ./system/application/controllers/api/ */