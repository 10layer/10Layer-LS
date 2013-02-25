<?php
	/**
	 * 10Layer User Model
	 *
	 * This model handles user data
	 *
	 * @package		10Layer
	 * @subpackage	Models
	 * @category	Models
	 * @author		Jason Norwood-Young
	 * @link		http://10layer.com
	 */
	class Model_User extends CI_Model {
		
		public function __construct() {
			parent::__construct();
		}
		
		public function login($email,$password) {
			if (empty($password)) {
				return false;
			}
			$encpass=crypt($password, substr($email, 0, 4));
			$user=$this->mongo_db->get_where("users",array("email"=>$email,"password"=>$encpass));
			if (empty($user)) {
				$user=$this->mongo_db->get_where("users",array("email"=>$email,"password"=>$password));
			}
			if (!empty($user)) {
				$user=$user[0];
				$status = ($user->isActive) ? 'Active' : 'Suspended';
				$this->session->set_userdata(array("id"=>$user->_id, "name"=>$user->name, "status"=>$status, "role"=>$user->permission));
				$api_key = array_pop($this->mongo_db->get_where("api_keys", array("role"=>$user->permission)));
				$this->session->set_userdata("api_key", $api_key->api_key);
				return true;
			}
			return false;
		}
		
		public function otpLogin($otp) {
			$otp=trim($otp);
			if (empty($otp)) {
				return false;
			}
			$user=array_shift($this->mongo_db->get_where("users",array("otp"=>$otp))); //We need to limit this to avoid deleted users
			if (!empty($user)) {
				//$this->update($user->_id,array("otp"=>"","status_id"=>1));
				$this->session->set_userdata(array("id"=>$user->_id, "name"=>$user->name, "status_id"=>1, "permission"=>$user->permissions));
				return true;
			}
			return false;
		}
		
		/**
		 * updateOtp function.
		 * 
		 * @access public
		 * @param String $email
		 * @return String OTP key
		 */
		public function updateOtp($email) {
			$user=$this->mongo_db->get_where("users",array("email"=>$email));
			if (empty($user)) {
				return false;
			}
			$user = $user[0];
			$key = genkey($user->email.$user->password.time());
			$this->mongo_db->where(array("_id" => $user->_id))->update("users", array("otp"=>$key));
			return $key;
		}
		
		public function checklogin() {
			$id=$this->session->userdata("id");
			if (!empty($id)) {
				return true;
			}
			return false;
		}
		
		//Deprecated
		public function get_user_roles($user_id) {
			$this->db->select("tl_roles.*");
			$this->db->from("tl_roles");
			$this->db->join("tl_roles_users_link","tl_roles.id=tl_roles_users_link.role_id");
			$this->db->where("tl_roles_users_link.user_id",$user_id);
			$query=$this->db->get();
			return $query->result();
		}
		
		public function get_by_id($id) {
			$this->mongo_db->state_save();
			$users=$this->mongo_db->get_where("users",array("_id"=>$id));
			$this->mongo_db->state_restore();
			return $users[0];
		}
		
		public function get_by_urlid($urlid) {
			//ID is now same as URLID
			return $this->get_by_id($urlid);
		}
		
		public function getByOtp($otp) {
			$otp=trim($otp);
			if (empty($otp)) {
				return false;
			}
			return array_shift($this->mongo_db->get_where("users",array("otp"=>$otp)));
		}
		
		public function update($id, $data) {
			if (!empty($data["password"])) {
				$data["password"]=crypt($data["password"], substr($data["email"],0,4));
			} elseif (isset($data["password"])) {
				unset($data["password"]);
			}
			$this->mongo_db->where(array("_id"=>$id))->update("users",$data);
			return true;
		}
		
		public function insert($data) {
			if (!empty($data["password"])) {
				$data["password"]=crypt($data["password"], substr($data["email"],0,4));
			}
			if (!isset($data["urlid"])) {
				if (!isset($data["urlid"]) || empty($data["urlid"])) {
					$data["urlid"]=url_title(strtolower($data["name"]));
				}
				$data["_id"]=$data["urlid"];
				unset($data["urlid"]);
			}
			$this->mongo_db->insert("users",$data);
			return $data["_id"];
		}
		
		/**
		 * get_password_by_email function.
		 * 
		 * @access public
		 * @param string $email
		 * @return string
		 */
		public function get_password_by_email($email) {
			$user=$this->mongo_db->get_where("users",array("email"=>$email));
			if (empty($user)) {
				return false;
			}
			return $user[0]->password;
		}
		
		/**
		 * security_exclude_paths function.
		 * 
		 * Returns paths that can be accessed without loging in
		 *
		 * @access public
		 * @return array
		 */
		public function security_exclude_paths() {
			return $this->mongo_db->get_where("permissions", array("_id"=>"all"));
			return $result->result();
		}
		
		/**
		 * security_check_exclude_path function.
		 * 
		 * Checks that the path is accessible without logging in. Returns true if it is.
		 *
		 * @access public
		 * @param array $path. (default: array())
		 * @return boolean
		 */
		public function security_check_exclude_path($path) {
			$pathuri=implode("/",$path);
			$count=$this->mongo_db->where(array("_id"=>"all", "allow"=>$pathuri))->count("permissions");
			return ($count > 0);
		}
		
		/**
		 * get_all_users function.
		 * 
		 * @access public
		 * @return array
		 */
		public function getAllUsers() {
			return $this->mongo_db->get("users");
		}
		
		public function get_statuses() {
			$query=$this->db->get("tl_user_status");
			return $query->result();
		}
		
		public function get_status($status_id) {
			$query=$this->db->get_where("tl_user_status",array("id"=>$status_id));
			return $query->row();
		}
		
		public function get_user_status($uid) {
			$user=$this->mongo_db->get_where("users",array("_id"=>$uid));
			if($user[0]->isActive == 1){
				return 'Active';
			}else{
				return 'Suspended';
			}
		}
		
		/*public function getUserPermission($uid) {
			$users=$this->mongo_db->where(array("_id"=>$uid))->get("users");
			return $users[0]->permissions;
		}*/
		
		/*public function getUserPermissions($uid) {
			$this->db->select("tl_permissions_users_link.permission_id")->from("tl_permissions_users_link")->where("user_id",$uid);
			$this->db->select("tl_permissions.name");
			$this->db->join("tl_permissions","tl_permissions_users_link.permission_id=tl_permissions.id");
			$query=$this->db->get();
			return $query->result();
		}*/
		
		public function getUserRoles($uid) {
			$this->db->select("tl_roles_users_link.role_id")->from("tl_roles_users_link")->where("user_id",$uid);
			$this->db->select("tl_roles.name");
			$this->db->join("tl_roles","tl_roles_users_link.role_id=tl_roles.id");
			$query=$this->db->get();
			return $query->result();
		}
		
		/*public function getUserPermissionTypes() {
			$query=$this->db->get("tl_permissions");
			return $query->result();
		}*/
		
		/*public function getUrlsByPermission($permission_id) {
			if (!is_array($permission_id)) {
				$permission_id=array($permission_id);
			}
			$urls=array();
			foreach($permission_id as $pid) {
				$result=$this->mongo_db->get_where("permissions",array("_id"=>$pid));
				foreach($result as $row) {
					if (isset($row->deny)) {
						foreach($row->deny as $url) {
							$urls[]=$url;
						}
					}
				}
			}
			return $urls;
		}
		
		public function checkUrlPermission($uid, $url) {
			$permission=$this->getUserPermission($uid);
			$this->mongo_db->where(array("_id", $permission));
			$this->mongo_db->where(array("deny"=>$url));
			$count=$this->mongo_db->count("permissions");
			return ($count > 0);
		}
		
		public function getPermissionByUrl($url) {
			$query=$this->db->get_where("tl_permissions_urls",array("url"=>$url));
			return $query->result();
		}
		
		public function updatePermissions($urls,$permission_id) {
			$this->db->where("permission_id",$permission_id);
			$this->db->delete("tl_permissions_urls");
			foreach($urls as $url) {
				if (substr($url,0,1)!="/") {
					$url="/".$url;
				}
				$this->db->insert("tl_permissions_urls",array("url"=>$url,"permission_id"=>$permission_id));
			}
		}
		
		public function cleanPermissions() {
			$this->db->truncate("tl_permissions_urls");
		}*/
		
		public function getUserRoleTypes() {
			$query=$this->db->get("tl_roles");
			return $query->result();
		}
		
		public function hasPermission($permission_id,$uid) {
			$query=$this->db->get_where("tl_permissions_users_link",array("user_id"=>$uid, "permission_id"=>$permission_id));
			if ($query->num_rows()==0) {
				return false;
			}
			return true;
		}
		
		public function hasRole($role_id,$uid) {
			$query=$this->db->get_where("tl_roles_users_link",array("user_id"=>$uid, "role_id"=>$role_id));
			if ($query->num_rows()==0) {
				return false;
			}
			return true;
		}
		
		public function updateUserRoles($uid,$roles=array()) {
			$this->db->where("user_id",$uid);
			$this->db->delete("tl_roles_users_link");
			foreach($roles as $role) {
				$this->db->insert("tl_roles_users_link",array("user_id"=>$uid,"role_id"=>$role));
			}
		}
		
		public function updateUserPermissions($uid,$permissions=array()) {
			$this->db->where("user_id",$uid);
			$this->db->delete("tl_permissions_users_link");
			foreach($permissions as $permission) {
				$this->db->insert("tl_permissions_users_link",array("user_id"=>$uid,"permission_id"=>$permission));
			}
		}
	}

?>