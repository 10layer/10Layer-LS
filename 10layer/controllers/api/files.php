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
				$fparts = pathinfo($filename);
				$filename = url_title($fparts["filename"]).".".$fparts["extension"];
			} else {
				$filename = md5($raw);
			}
			$this->data["message"]=".".$dir.$parts.$filename;
			file_put_contents(".".$dir.$parts.$filename, $raw);
			$this->data["content"]=array("filename"=>$filename, "full_name"=>$dir.$parts.$filename);
			$this->returndata();
		}
		
		/**
		 * download function.
		 * 
		 * Downloads a file. Filename must be in the format yyyy-mm-dd-filename if it is in ./content/yyyy/mm/dd/filename
		 *
		 * @access public
		 * @return void
		 */
		public function download($orig) {
			$this->enforce_secure();
			$substr = substr($orig, 0, 11);
			$filename = "./content/".str_replace("-", "/", $substr).rawurldecode(substr($orig, 11));
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
		
		public function browse() {
			$this->enforce_secure();
			$limit = $this->input->get_post("limit");
			if (empty($limit)) {
				$limit = 50;
			}
			$offset = $this->input->get_post("offset");
			$dir = "content";
			//$filetypes = array("jpg", "jpeg", "png", "svg", "gif", "mp4", "m4v", "doc", "docx", "xls", "xlsx", "pdf");
			$filetypes = array("jpg", "jpeg", "png", "svg", "gif");
			$files = $this->findFiles($dir, $filetypes);
			$this->data["content"]["count"] = sizeof($files);
			usort($files, array($this, "file_mtime_sort"));
			
			$files = array_slice($files, $offset, $limit);
			$this->data["content"]["files"]=$files;
			$this->data["content"]["filetypes"]=$filetypes;
			$this->returndata();
		}
		
		protected function findFiles($directory, $extensions = array()) {
			function glob_recursive($directory, &$directories = array()) {
				foreach(glob($directory, GLOB_ONLYDIR | GLOB_NOSORT) as $folder) {
					if (strpos($folder,"cache")===false) {
						$directories[] = $folder;
						glob_recursive("{$folder}/*", $directories);
					}
				}
				
			}
			glob_recursive($directory, $directories);
			$files = array ();
			foreach($directories as $directory) {
				foreach($extensions as $extension) {
					foreach(glob("{$directory}/*.{$extension}") as $file) {
						if (strpos($file,"cache")===false) {
							$files[] = $file;
						}
					}
				}
			}
			return $files;
		}
		
		protected function file_mtime_sort($f1, $f2) {
			if (filemtime($f1) == filemtime($f2)) {
				return 0;
			}
			return (filemtime($f1) < filemtime($f2)) ? 1 : -1;
		}
		
		public function image() {
			$this->load->helper("smarturl_helper");
			$filename = $this->input->get_post("filename");
			$width = $this->input->get_post("width");
			$height = $this->input->get_post("height");
			$bounding = $this->input->get_post("bounding");
			$greyscale = $this->input->get_post("greyscale");
			$op = "";
			$opstr = "fill";
			$extent = "";
			$grey = "";
			$greystr = "";
			$format = $this->input->get_post("format");
			if (!empty($greyscale)) {
				$grey = "-colorspace gray";
				$greystr = "-greyscale";
			}
			if (empty($bounding)) {
				$op = "^";
				$opstr = "bound";
				$extent = "-extent {$width}x{$height}";
			}
			$quality = $this->input->get_post("quality");
			if (empty($quality)) {
				$quality = 80;
			}
			if (empty($format)) {
				$format = "jpg";
			}
			$render = $this->input->get_post("render");
			$dir = "content";
			$filetypes = array("jpg", "jpeg", "png", "svg", "gif", "mp4", "m4v", "doc", "docx", "xls", "xlsx", "pdf");
			$file = $dir."/".$filename;
			$parts = pathinfo($filename);
			$realpath = realpath(".");

			if (strpos($filename,"..")!==false) {
				//This doesn't look good
				$this->data["error"]=true;
				$this->data["msg"]="Looks like you're trying to break out of the dir";
				$this->returndata();
				return true;
			}
			if (!file_exists($file)) {
				//This doesn't look good
				$this->data["error"]=true;
				$this->data["msg"]="File not found: $file";
				$this->returndata();
				return true;
			}
			if (!in_array(strtolower($parts["extension"]), $filetypes)) {
				$this->data["error"]=true;
				$this->data["msg"]="Cannot access file of that type";
				$this->returndata();
				return true;
			}
			$cache = "content/cache/".$parts["dirname"]."/".smarturl($parts["filename"], false, true)."-".$width."-".$height."-".$quality."-".$opstr.$greystr.".".$format;
			if (file_exists($cache)) {
				if ($render) {
					header("Content-type: image/".$format);
					header('Last-Modified: '.gmdate('D, d M Y H:i:s', filemtime($cache)).' GMT', true, 200);
			        header('Content-Length: '.filesize($cache));
		    	    readfile($cache);
		    	    return true;
				} else {
					$this->data["filename"]=$cache;
					$this->returndata();
					return true;
				}
			}
			if (!is_dir($realpath."/content/cache/".$parts["dirname"])) {
				$result = mkdir($realpath."/content/cache/".$parts["dirname"], 0755, true);
			}
			exec("convert ".escapeshellarg($file)." -auto-level -background transparent -density 72 -depth 8 -strip -resize ".escapeshellarg($width)."x".escapeshellarg($height)."{$op} {$grey} -quality 80 -gravity center $extent '{$cache}'", $result);
			//exec("optipng -o7 '{$cache}'");
			if ($render) {
				header("Content-type: image/".$format);
				header('Last-Modified: '.gmdate('D, d M Y H:i:s', filemtime($cache)).' GMT', true, 200);
				header('Content-Length: '.filesize($cache));
				readfile($cache);
			    return true;
			}
		    $this->data["filename"]=$cache;
			$this->returndata();
		}
		
		
	}

/* End of file files.php */
/* Location: ./system/application/controllers/api/ */