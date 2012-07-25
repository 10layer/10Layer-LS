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
			$query=$this->db->get_where("tl_users",array("email"=>$email,"password"=>$encpass));
			
			if ($query->num_rows()==0) {
				$query=$this->db->get_where("tl_users",array("email"=>$email,"password"=>$password));
			}
			if ($query->num_rows()>0) {
				$result=$query->row();
				
				$this->session->set_userdata(array("id"=>$result->id,"name"=>$result->name,"urlid"=>$result->urlid,"status_id"=>$result->status_id));
				$roles=$this->get_user_roles($result->id);
				
				$tmp=array();
				foreach($roles as $role) {
					$tmp[]=$role->id;
				}
				$this->session->set_userdata(array("roles"=>$tmp));
				return true;
			}
			return false;
		}
		
		public function otpLogin($otp) {
			$otp=trim($otp);
			if (empty($otp)) {
				return false;
			}
			$query=$this->db->get_where("tl_users",array("otp"=>$otp));
			if ($query->num_rows()>0) {
				$result=$query->row();
				$this->update($result->id,array("otp"=>"","status_id"=>1));
				$this->session->set_userdata(array("id"=>$result->id,"name"=>$result->name,"urlid"=>$result->urlid,"status_id"=>1));
				$roles=$this->get_user_roles($result->id);
				$tmp=array();
				foreach($roles as $role) {
					$tmp[]=$role->id;
				}
				$this->session->set_userdata(array("roles"=>$tmp));
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
			$result=$this->db->get_where("tl_users",array("email"=>$email));
			if ($result->num_rows()==0) {
				return false;
			}
			$key=genkey("email");
			$this->db->where("id", $result->row()->id)->update("tl_users", array("otp"=>$key));
			return $key;
		}
		
		public function checklogin() {
			$id=$this->session->userdata("id");
			if (!empty($id)) {
				return true;
			}
			return false;
		}
		
		public function get_user_roles($user_id) {
			$this->db->select("tl_roles.*");
			$this->db->from("tl_roles");
			$this->db->join("tl_roles_users_link","tl_roles.id=tl_roles_users_link.role_id");
			$this->db->where("tl_roles_users_link.user_id",$user_id);
			$query=$this->db->get();
			return $query->result();
		}
		
		public function get_by_id($id) {
			$query=$this->db->get_where("tl_users",array("id"=>$id));
			return $query->row();
		}
		
		public function get_by_urlid($urlid) {
			$query=$this->db->get_where("tl_users",array("urlid"=>$urlid));
			return $query->row();
		}
		
		public function getByOtp($otp) {
			$otp=trim($otp);
			if (empty($otp)) {
				return false;
			}
			$query=$this->db->get_where("tl_users",array("otp"=>$otp));
			return $query->row();
		}
		
		public function update($id,$data) {
			if (!empty($data["password"])) {
				$data["password"]=crypt($data["password"], substr($data["email"],0,4));
			}
			$this->db->where("id",$id);
			$this->db->update("tl_users",$data);
			return true;
		}
		
		public function insert($data) {
			if (!empty($data["password"])) {
				$data["password"]=crypt($data["password"], substr($data["email"],0,4));
			}
			$this->db->insert("tl_users",$data);
			return $this->db->insert_id();
		}
		
		/**
		 * get_password_by_email function.
		 * 
		 * @access public
		 * @param string $email
		 * @return string
		 */
		public function get_password_by_email($email) {
			$result=$this->db->get_where("tl_users",array("email"=>$email));
			if ($result->num_rows==0) {
				return false;
			}
			return $result->row()->password;
		}
		
		/**
		 * function queue_check
		 *
		 * Checks if anything is in the action queue for the user
		 * 
		 * @var $user_id Int
		 * @return dataset CI db result
		 **/
		public function queue_check($user_id) {
			$query=$this->db->get_where("tl_user_queue",array("user_id"=>$user_id));
			return $query->result();
		}
		
		/**
		 * function queue_size
		 *
		 * Checks if anything is in the action queue for the user and returns number of actions
		 * 
		 * @var $user_id Int
		 * @return dataset CI db result
		 **/
		public function queue_size($user_id) {
			$query=$this->db->get_where("tl_user_queue",array("user_id"=>$user_id));
			return $query->num_rows();
		}
		
		/**
		 * function queue_pop
		 *
		 * Checks if anything is in the action queue for the user, and pops the result off the end
		 * 
		 * @var $user_id Int
		 * @return dataset CI db result
		 **/
		public function queue_pop($user_id) {
			$this->db->where("user_id",$user_id);
			$this->db->order_by("timestamp","DESC");
			$this->db->limit(1);
			$query=$this->db->get("tl_user_queue");
			if ($query->num_rows()==0) {
				return false;
			}
			$row=$query->row();
			$this->db->where("id",$row->id);
			$this->db->delete("tl_user_queue");
			return $row;
		}
		
		/**
		 * function queue_shift
		 *
		 * Checks if anything is in the action queue for the user, and pops the result off the beginning
		 * 
		 * @var $user_id Int
		 * @return dataset CI db result, false if empty
		 **/
		public function queue_shift($user_id) {
			$this->db->where("user_id",$user_id);
			$this->db->order_by("timestamp","ASC");
			$this->db->limit(1);
			$query=$this->db->get("tl_user_queue");
			if ($query->num_rows()==0) {
				return false;
			}
			$row=$query->row();
			$this->db->where("id",$row->id);
			$this->db->delete("tl_user_queue");
			return $row;
		}
		
		/**
		 * function queue_push
		 *
		 * Push an action on to the queue
		 * 
		 * @var $user_id Int
		 * @var $all_users Bool false set to true to affect all users in the system
		 * @return dataset CI db result
		 **/
		public function queue_push($data,$all_users=false) {
			if (empty($data["unique_id"])) {
				$uid=$data["action"].microtime();
				$data["unique_id"]=$uid;
			}
			if (!$all_users) {
				$this->db->insert("tl_user_queue",$data);
				return $uid;
			} else {
				$data=array("user_id"=>0,"action"=>$this->db->escape($data["action"]),"data"=>$this->db->escape($data["data"]),"unique_id"=>$this->db->escape($uid));
				$sql="INSERT INTO user_queue (user_id, action, data, unique_id) SELECT id, {$data["action"]}, {$data["data"]}, {$data["unique_id"]} FROM users";
				$this->db->query($sql);
				return $uid;
			}
		}
		
		public function queue_check_duplicate($uid) {
			$query=$this->db->get_where("tl_user_queue",array("unique_id"=>$uid));
			if ($query->num_rows()>0) {
				return true;
			}
			return false;
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
			$result=$this->db->get("tl_security_exclude_paths");
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
		public function security_check_exclude_path($path=array()) {
			$pathuri=implode("/",$path);
			$result=$this->db->get_where("tl_security_exclude_paths",array("path"=>$pathuri));
			return ($result->num_rows()>0);
		}
		
		/**
		 * get_all_users function.
		 * 
		 * @access public
		 * @return array
		 */
		public function getAllUsers() {
			$this->db->select("tl_users.*");
			//$this->db->select("tl_permissions.name AS permission_name");
			//$this->db->select("tl_roles.name AS role_name");
			//$this->db->select("permission_id");
			//$this->db->select("role_id");
			$this->db->from("tl_users");
			//$this->db->join("tl_permissions_users_link","tl_users.id=tl_permissions_users_link.user_id","left");
			//$this->db->join("tl_permissions","tl_permissions_users_link.permission_id=tl_permissions.id","left");
			//$this->db->join("tl_roles_users_link","tl_users.id=tl_roles_users_link.user_id","left");
			//$this->db->join("tl_roles","tl_roles_users_link.role_id=tl_roles.id","left");
			$result=$this->db->get();
			return $result->result();
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
			$query=$this->db->select("status_id")->from("tl_users")->where("id",$uid)->get();
			return $query->row()->status_id;
		}
		
		public function getUserPermission($uid) {
			$query=$this->db->select("permission_id")->from("tl_permissions_users_link")->where("user_id",$uid)->get();
			return $query->row()->permission_id;
		}
		
		public function getUserPermissions($uid) {
			$this->db->select("tl_permissions_users_link.permission_id")->from("tl_permissions_users_link")->where("user_id",$uid);
			$this->db->select("tl_permissions.name");
			$this->db->join("tl_permissions","tl_permissions_users_link.permission_id=tl_permissions.id");
			$query=$this->db->get();
			return $query->result();
		}
		
		public function getUserRoles($uid) {
			$this->db->select("tl_roles_users_link.role_id")->from("tl_roles_users_link")->where("user_id",$uid);
			$this->db->select("tl_roles.name");
			$this->db->join("tl_roles","tl_roles_users_link.role_id=tl_roles.id");
			$query=$this->db->get();
			return $query->result();
		}
		
		public function getUserPermissionTypes() {
			$query=$this->db->get("tl_permissions");
			return $query->result();
		}
		
		public function getUrlsByPermission($permission_id) {
			$query=$this->db->get_where("tl_permissions_urls",array("permission_id"=>$permission_id));
			return $query->result();
		}
		
		public function checkUrlPermission($uid, $url) {
			$permission_id=$this->getUserPermission($uid);
			$this->db->where("url",$url);
			$this->db->where("permission_id",$permission_id);
			$result=$this->db->get("tl_permissions_urls");
			$row=$result->row();
			return (!empty($row->id));
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
		}
		
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