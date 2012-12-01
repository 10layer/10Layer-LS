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
		}
		
		public function index() {
			$this->load->library("datatransformations");
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
					$data->title=$node->title;
					$data->content_type = $content_type->type;
					$data->body = $revision->body;
					$data->blurb = $revision->teaser;
					$data->timestamp = $node->created;
					$data->last_modified = $node->changed;
					$data->start_date = $node->created;
					$data->nid = $node->nid;
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
		
		protected function check_drupal() {
			return $this->drupal->table_exists("node");
		}
	}

/* End of file .php */
/* Location: ./system/application/controllers/ */