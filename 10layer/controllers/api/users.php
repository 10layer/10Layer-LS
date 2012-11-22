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
			$this->data["content"] = $this->mongo_db->order_by(array("_id"))->get("users");
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
			if (empty($user->name)) {
				return false;
			}
			if (empty($user->id)) {
				$user->id=$this->datatransformations->urlid($this, $user->name, false, "users");
			}
			$query=$this->mongo_db->get_where("users", array("_id"=>$user->id));
			if (empty($query)) {
				$this->model_user->insert((Array) $user);
			} else {
				$this->model_user->update($user->id, (Array) $user);
			}
			print_r($user);
			return true;
		}
		
	}

/* End of file .php */
/* Location: ./system/application/controllers/ */