<?php
	/**
	 * Setup class
	 *
	 * Sets 10Layer up on a new server
	 * 
	 * @extends Controller
	 */
	class Setup extends CI_Controller {

		/**
		 * __construct function.
		 * 
		 * @access public
		 * @return void
		 */
		public function __construct() {
			parent::__construct();
			$this->load->library("validation");
		}
		
		public function admin() {
			$this->load->model("model_user");
			$data=array();
			$email=$this->input->post("email");
			$password=$this->input->post("password");
			if (!empty($email)) {
				$password_rules=array(
					"required",
					"minlen"=>6,
					"password_strength"=>3
				);
				$email_rules=array(
					"required",
					"valid_email"
				);
				$this->validation->validate("email", "Email", $email, $email_rules);
				$this->validation->validate("password", "Password", $password, $password_rules);
				if (!$this->validation->passed) {
					$data["errors"]=$this->validation->failed_messages;
				} else {

					/*
						we need to create api_keys before everything (role based API keys) 
						This will change when we have user based API keys
					*/
					$keys = $this->tlsecurity->get_api_keys();
					$this->model_user->insert(array("password"=>$password, "email"=>$email, "name"=>"Administrator", "date_created"=>date("c"), "isActive"=>true, "permission"=>"administrator"));
					$this->model_user->login($email,$password);
					redirect("/setup/users");
				}
			}
			$data["wizard"]=true;
			$this->load->view("setup/admin", $data);
		}
		
		public function content_types($content_type_urlid=false) {
			$this->load->model("model_content");
			$content_types=$this->model_content->get_content_types();
			if (empty($content_types)) {
				$templates=glob(TLPATH."resources/content_types/*.json");
				foreach($templates as $filename) {
					try {
						$jsondata=json_decode(file_get_contents($filename));
						$this->json_check($filename);
						$content_types[]=$jsondata;
						$this->mongo_db->insert("content_types", $jsondata);
					} catch(Exception $e) {
						show_error("Error parsing template $filename");
					}
				}
			}
			$data["content_type_urlid"]=$content_types[0]->_id;
			if (!empty($content_type_urlid)) {
				$x=0;
				foreach($content_types as $content_type) {
					if ($content_type->_id == $content_type_urlid) {
						$data["content_type_urlid"]=$content_type_urlid;
					}
					$x++;
				}
			}
			$data["content_types"]=$content_types;
			//print_r($content_types);
			$this->load->view("setup/content_types", $data);
		}
		
		public function users() {
			$this->load->view("setup/users");
		}
		
		public function api_keys() {
			$api_keys = $this->tlsecurity->get_api_keys();
			$this->load->view("setup/api_keys", array("api_keys" => $api_keys));
		}
		
		protected function json_check($filename="") {
			switch (json_last_error()) {
				case JSON_ERROR_NONE:
					break;
				case JSON_ERROR_DEPTH:
					show_error($filename.' JSON error - Maximum stack depth exceeded');
					break;
				case JSON_ERROR_STATE_MISMATCH:
					show_error($filename.' JSON error - Underflow or the modes mismatch');
					break;
				case JSON_ERROR_CTRL_CHAR:
	            	show_error($filename.' JSON error - Unexpected control character found');
					break;
				case JSON_ERROR_SYNTAX:
					show_error($filename.' JSON error - Syntax error, malformed JSON');
					break;
				case JSON_ERROR_UTF8:
					show_error($filename.' JSON error - Malformed UTF-8 characters, possibly incorrectly encoded');
					break;
				default:
					show_error($filename.' JSON error - Unknown error');
				break;
			}
		}
	}

/* End of file setup.php */
/* Location: ./system/application/controllers/ */