<?php
	/**
	 * Drupal class
	 * 
	 * @extends Controller
	 */
	class Drupal extends CI_Controller {
		
		protected $drupal;
		
		/**
		 * __construct function.
		 * 
		 * @access public
		 * @return void
		 */
		public function __construct() {
			parent::__construct();
			$this->load->library("datatransformations");
		}
		
		public function index() {
			
			$dbuser = $this->input->get_post("user");
			$dbpassword = $this->input->get_post("password");
			$dbserver = $this->input->get_post("server");
			$dbdatabase = $this->input->get_post("database");
			
			if (empty($dbuser) || empty($dbpassword) || empty($dbserver) || empty($dbdatabase)) {
				show_error("Fields required: user, password, server, database");
			}
			
			$config['hostname'] = $dbserver;
			$config['username'] = $dbuser;
			$config['password'] = $dbpassword;
			$config['database'] = $dbdatabase;
			$config['dbdriver'] = "mysql";
			$config['dbprefix'] = "";
			$config['pconnect'] = FALSE;
			$config['db_debug'] = TRUE;
			$config['cache_on'] = FALSE;
			$config['cachedir'] = "";
			$config['char_set'] = "utf8";
			$config['dbcollat'] = "utf8_general_ci";
			
			$this->drupal = $this->load->database($config, TRUE);
			
			if (!$this->check_drupal()) {
				show_error("$dbdatabase does not appear to be a Drupal database");
			}
			
			$content_types = $this->drupal->get("node_type")->result();
			$x=0;
			foreach($content_types as $content_type) {
				$nodes=$this->drupal->where("type",$content_type->type)->get("node")->result();
				foreach($nodes as $node) {
					$revision = $this->drupal->order_by("timestamp DESC")->where("nid", $node->nid)->get("node_revisions")->row();
					$data=new stdClass();
					$data->title = $node->title;
					$data->content_type = $content_type->type;
					$data->body = $revision->body;
					$data->blurb = trim(strip_tags($revision->teaser));
					$data->timestamp = (Integer) $node->created;
					$data->last_modified = (Integer) $node->changed;
					$data->start_date = (Integer) $node->created;
					$data->nid = (Integer) $node->nid;
					$data->imported = true;
					$data->workflow_status = 3;
					$data->last_editor = "Administrator";
					$data->_id = $this->datatransformations->urlid($this, $data->title, false);
					$this->mongo_db->insert("content", $data);
					$x++;
				}
			}
			print "Inserted $x items";
		}
		
		public function files() {
			$dbuser = $this->input->get_post("user");
			$dbpassword = $this->input->get_post("password");
			$dbserver = $this->input->get_post("server");
			$dbdatabase = $this->input->get_post("database");
			//$website = $this->input->get_post("website");
			//$download = $this->input->get_post("download");
			
			if (empty($dbuser) || empty($dbpassword) || empty($dbserver) || empty($dbdatabase)) {
				show_error("Fields required: user, password, server, database");
			}
			
			$config['hostname'] = $dbserver;
			$config['username'] = $dbuser;
			$config['password'] = $dbpassword;
			$config['database'] = $dbdatabase;
			$config['dbdriver'] = "mysql";
			$config['dbprefix'] = "";
			$config['pconnect'] = FALSE;
			$config['db_debug'] = TRUE;
			$config['cache_on'] = FALSE;
			$config['cachedir'] = "";
			$config['char_set'] = "utf8";
			$config['dbcollat'] = "utf8_general_ci";
			
			$this->drupal = $this->load->database($config, TRUE);
			
			if (!$this->check_drupal()) {
				show_error("$dbdatabase does not appear to be a Drupal database");
			}
			
			$files=$this->drupal->join("upload", "upload.fid=files.fid")->join("node", "upload.nid=node.nid")->order_by("files.timestamp DESC")->get("files")->result();
			$total_file_size = $this->drupal->select("SUM( filesize ) AS total_file_size")->get("files")->row()->total_file_size;
			$tot = 0;
			$max = 10;
			$count = 0;
			$x = 0;
			foreach($files as $file) {
				$date=$file->timestamp;
				$dir = "content/";
				$parts=date("Y", $date)."/".date("m", $date)."/".date("d", $date)."/";
				$safename = str_replace(" ", "-", $file->filename);
				$safename = preg_replace("/[^A-Za-z0-9._-]/", "", $safename);
				$filename = $dir.$parts.$safename;
				$source = $file->filepath;
				
				print "$source -> $filename<br />\n";
				
				if (!file_exists($filename) && file_exists($source)) {
					@mkdir($dir.$parts, 0755, true);
					if (!is_dir($dir.$parts)) {
						$this->show_error("$dir$parts is not a directory or doesn't exist");
					}
					if (!file_exists($filename)) {
						copy($source, $filename);
						$count++;
					}
					$fsize = filesize($filename);
					$tot = $tot + $fsize;
					$percent = round($tot / $total_file_size, 3);
					print "$percent (".number_format(round($tot/1024), 2)."KB of ".number_format(round($total_file_size/1024), 2).")<br />";
					flush();
					if ($count > $max) {
						//die();
					}
				} else {
					print "Can't find $source or $filename already exists<br />\n";
				}
				$result=$this->mongo_db->get_where("content", array("nid"=>(Int) $file->nid));
				if (!empty($result)) {
					$item = $result[0];
					if (empty($item->attachment)) {
						$this->mongo_db->where(array("_id"=>$item->_id))->update("content", array("attachment"=>ltrim($filename, ".")));
					} else {
						$attachments = array();
						if (is_array($item->attachment)) {
							$attachments = $item->attachment;
						}
						if (!in_array(ltrim($filename, "."), $attachments)) {
							$attachments[] = ltrim($filename, ".");
						}
						$this->mongo_db->where(array("_id"=>$item->_id))->update("content", array("attachment"=>$attachments));
					}
					$x++;
				}
			}
			print "Inserted $x items";
		}
		
		protected function check_drupal() {
			return $this->drupal->table_exists("node");
		}
	}

/* End of file .php */
/* Location: ./system/application/controllers/ */