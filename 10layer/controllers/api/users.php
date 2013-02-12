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
			if (!empty($this->var->password) && !$is_new) {
				$this->validation->validate("password", "Password", $user->password, array("required", "minlen"=>5, "password_strength"=>2));
			}
			
			$this->validation->validate("name", "Name", $user->name, array("required"));
			$this->validation->validate("permission", "Permission", $user->permission, array("required"));
			if (!$this->validation->passed) {
			    $this->show_error($this->validation->failed_messages);
			} else {
				if ($is_new) {
					echo 'inserting...'; 
					$this->model_user->insert((Array) $user);
				} else {
					$this->model_user->update($user->id, (Array) $user);
				}
				return true;
			}
		}
		
	}

/* End of file .php */
/* Location: ./system/application/controllers/ */