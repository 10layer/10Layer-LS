<?php
	require_once('10layer/system/TL_Api.php');
	
	/**
	 * Users class
	 * 
	 * @extends Controller
	 */
	 
	class Users extends TL_Api {
		
		/**
		 * __construct function.
		 * 
		 * @access public
		 * @return void
		 */
		public function __construct() {
			parent::__construct();
			$this->secure=$this->_check_secure();
		}
		
		/**
		 * index function.
		 * 
		 * Returns all the content types
		 *
		 * @access public
		 * @return void
		 */
		public function index() {
			if (!$this->secure) {
				//You shouldn't be here. Bail.
				$this->data["error"]=true;
				$this->data["msg"]="Denied";
				$this->returndata();
				return false;
			}
			$this->data["content"] = $this->mongo_db->order_by(array("name"))->get("users");
			$this->returndata();
		}
		
		public function user() {
			
		}
		
		/**
		 * save function.
		 * 
		 * @access public
		 * @return void
		 */
		public function save() {

			if (isset($this->vars->users)) {
				foreach($this->vars->users as $user) {
					$this->_save($user);
				}
			} else {
				$this->_save($this->vars->user);
			}
			$this->returndata();
		}
		
		protected function _save($user) {
			$this->load->library("validation");

			if (empty($user->name)) {
				return false;
			}

			if (empty($user->id)) {
				$user->id=$this->datatransformations->urlid($this, $user->name, false, "users");
			}

			$query=$this->mongo_db->get_where("users", array("_id"=>$user->id));

			$is_new = false;


			if (empty($query)) {
				$is_new = true;
			}

			$this->validation->validate("email", "Email", $user->email, array("required", "valid_email"));
			if ($is_new) {
				$this->validation->validate("email", "Email", $user->email, array("database_nodupe"=>"email in users"));
			}

			$this->validation->validate("name", "Name", $user->name, array("required"));

			if ($is_new) {
				$this->validation->validate("name", "Name", $user->name, array("database_nodupe"=>"Name in users"));
			}

			if (!empty($this->var->password) && !$is_new) {
				$this->validation->validate("password", "Password", $user->password, array("required", "minlen"=>5, "password_strength"=>2));
			}

			if ($is_new) {
				$this->validation->validate("password", "Password", $user->password, array("required", "minlen"=>5, "password_strength"=>2));
			}
			
			$this->validation->validate("permission", "Permission", $user->permission, array("required"));
			if (!$this->validation->passed) {
			    $this->show_error($this->validation->failed_messages);
			} else {
				if ($is_new) {
					$this->model_user->insert((Array) $user);
				} else {
					$this->model_user->update($user->id, (Array) $user);
				}
				return true;
			}
		}

		public function api_keys() {
			$this->data["content"] = $this->tlsecurity->get_api_keys();
			$this->returndata();
		}

		public function generate_api_key() {
			$this->data["content"] = generate_api_key();
			$this->returndata();
		}

		public function save_api_key() {
			if (empty($this->vars->api_key)) {
				$this->data["error"] = true;
				$this->data["message"][] = "API Key cannot be empty";
				$this->returndata();
				return;
			}
			if (!empty($this->vars->_id)) { //Update
				$id = $this->vars->_id;
				if (isset($this->vars->_id->{'$id'})) {
					$id = $this->vars->_id->{'$id'};
				}
				$key = array_pop($this->mongo_db->get_where("api_keys", array("_id" => new MongoId($id))));
				if (!empty($key)) {
					$data = $this->vars;
					unset($data->_id);
					$this->mongo_db->where(array("_id" => new MongoId($id)))->update("api_keys", (Array) $data);

				}
			} else { //Insert
				$data = $this->vars;
				$test = $this->mongo_db->get_where("api_keys", array("api_key" => $data->api_key));
				if (!empty($test)) {
					$this->data["error"] = true;
					$this->data["message"][] = "API Key already exists";
					$this->returndata();
					return;
				}
				unset($data->_id);
				$this->mongo_db->insert("api_keys", (Array) $data);
			}
			$this->returndata();
		}
		
	}

/* End of file .php */
/* Location: ./system/application/controllers/ */