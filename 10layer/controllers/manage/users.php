<?php
/**
 * Users class.
 * 
 * @extends CI_Controller
 * @package 10Layer
 * @subpackage Controllers
 */
class Users extends CI_Controller {

	function __construct() {
		parent::__construct();
		$this->load->helper("password");
		$this->load->library("validation");
	}
	
	function index() {
		$data["menu1_active"]="manage";
		$this->load->view('templates/header',$data);
		$this->load->view("templates/footer");
	}
	
	public function accounts() {
		$data["menu1_active"]="manage";
		$data["menu2_active"]="manage/users/accounts";
		$data["users"]=$this->model_user->getAllUsers();
		$this->load->view('templates/header',$data);
		$this->load->view("/manage/user_list");
		$this->load->view("templates/footer");
	}
	
	public function edit($urlid) {
		$doupdate=$this->input->post("doupdate");
		if ($doupdate) {
			$returndata["error"]=false;
			$name=$this->input->post("name");
			$email=$this->input->post("email");
			$roles=$this->input->post("roles");
			$permissions=$this->input->post("permissions");
			$this->validation->validate("name","Name",$name,"required|minlen=4|alpha_dash_space");
			$this->validation->validate("email","Email",$email,"required|valid_email");
			$this->validation->validate("roles","Role",$roles,"required");
			$this->validation->validate("permissions","Permission",$permissions,"required");
			if (!$this->validation->passed) {
				$returndata["error"]=true;
				$returndata["info"]=$this->validation->failed_messages;
				$returndata["msg"]="User update failed";
			}
			if ($this->validation->passed) {
				$key=genkey($this->input->post("email"));
				$data=array(
					"name"=>$name,
					"email"=>$email,
					"status_id"=>$this->input->post("status"),
				);
				$this->model_user->update($this->input->post("id"),$data);
				$this->model_user->updateUserRoles($this->input->post("id"),$this->input->post("roles"));
				$this->model_user->updateUserPermissions($this->input->post("id"),$this->input->post("permissions"));
				$this->email_update_alert($email, $name);
				$returndata["msg"]="User updated";
				$returndata["info"]="An email has been sent to the user to confirm";
				$returndata["error"]=false;
			}
			$data["msg"]=$returndata;
		}
		$data["menu1_active"]="manage";
		$data["menu2_active"]="manage/users/accounts";
		$data["user"]=$this->model_user->get_by_urlid($urlid);
		$data["permissions"]=$this->model_user->getUserPermissionTypes();
		$data["roles"]=$this->model_user->getUserRoleTypes();
		$data["statuses"]=$this->model_user->get_statuses();
		$this->load->view('templates/header',$data);
		$this->load->view("/manage/user_edit");
		$this->load->view("templates/footer");
	}
	
	public function add() {
		$doupdate=$this->input->post("doupdate");
		if ($doupdate) {
			$this->load->helper("smarturl");
			$returndata["error"]=false;
			$name=$this->input->post("name");
			$email=$this->input->post("email");
			$roles=$this->input->post("roles");
			$permissions=$this->input->post("permissions");
			$password=$this->input->post("password");
			$password_confirm=$this->input->post("password_confirm");
			$urlid=smarturl($email);
			$this->validation->validate("name","Name",$name,"required|minlen=4|alpha_dash_space");
			$this->validation->validate("email","Email",$email,"required|valid_email");
			$this->validation->validate("email","Email",$email, array("database_nodupe"=>"email IN tl_users"));
			$this->validation->validate("roles","Role",$roles,"required");
			$this->validation->validate("permissions","Permission",$permissions,"required");
			if (!empty($password)) {
				$this->validation->validate("password","Password",$password,"match=$password_confirm");
				$this->validation->validate("password","Password",$password,"minlen=6|password_strength=2");
			}
			if (!$this->validation->passed) {
				$returndata["error"]=true;
				$returndata["info"]=$this->validation->failed_messages;
				$returndata["msg"]="User update failed";
			}
			if ($this->validation->passed) {
				$key=genkey($this->input->post("email"));
				$data=array(
					"name"=>$name,
					"email"=>$email,
					"status_id"=>2,
					"otp"=>$key,
					"urlid"=>$urlid
				);
				if (!empty($password)) {
					$data["password"]=$password;
				}
				$uid=$this->model_user->insert($data);
				$this->model_user->updateUserRoles($uid,$this->input->post("roles"));
				$this->model_user->updateUserPermissions($uid,$this->input->post("permissions"));
				$this->email_otp($email, $key);
				$returndata["msg"]="User added";
				$returndata["info"]="An email has been sent to the user to confirm their account";
				$returndata["error"]=false;
			}
			$data["msg"]=$returndata;
		}
		$data["menu1_active"]="manage";
		$data["menu2_active"]="manage/users/accounts";
		$data["permissions"]=$this->model_user->getUserPermissionTypes();
		$data["roles"]=$this->model_user->getUserRoleTypes();
		$this->load->view('templates/header',$data);
		$this->load->view("/manage/user/add");
		$this->load->view("templates/footer");
	}
	
	public function my_account() {
		$doupdate=$this->input->post("doupdate");
		if ($doupdate) {
			$returndata["error"]=false;
			$password=$this->input->post("password");
			$check_password=$this->input->post("password_check");
			$name=$this->input->post("name");
			$email=$this->input->post("email");
			if (!empty($password) && ($password!=$check_password)) {
				$this->validation->failed_messages[]="Passwords don't match";
				$this->validation->passed=false;
			}
			$this->validation->validate("password","Password",$password,"required|minlen=6|password_strength=2");
			$this->validation->validate("name","Name",$name,"required|minlen=4|alpha_dash_space");
			$this->validation->validate("email","Email",$email,"required|valid_email");
			if (!$this->validation->passed) {
				$returndata["error"]=true;
				$returndata["info"]=$this->validation->failed_messages;
				$returndata["msg"]="User update failed";
			} else {
				$dbdata=array(
					"name"=>$this->input->post("name"),
					"email"=>$this->input->post("email")
				);
				$password=$this->input->post("password");
				if (!empty($password)) {
					$dbdata["password"]=$password;
				}
				$this->model_user->update($this->session->userdata("id"),$dbdata);
				$returndata["msg"]="Account updated";
				$returndata["info"]="Your user account has been updated";
				$returndata["error"]=false;
			}
			
			$data["msg"]=$returndata;
		}
		$data["menu1_active"]="manage";
		$data["menu2_active"]="manage/users/my_account";
		$data["user"]=$this->model_user->get_by_id($this->session->userdata("id"));
		$this->load->view('templates/header',$data);
		$this->load->view("/manage/self_edit");
		$this->load->view("templates/footer");
	}
	
	public function permissions() {
		$update=$this->input->post("update");
		if (!empty($update)) {
			$urls=$this->input->post("permission");
			//print_r($permissions);
			$this->model_user->cleanPermissions();
			if (!empty($urls)) {
				foreach($urls as $permission=>$url) {
					$this->model_user->updatePermissions($url,$permission);
				}
			}
		}
		$this->load->helper("classes");
		$data["menu1_active"]="manage";
		$data["menu2_active"]="manage/users/permissions";
		$data["user"]=$this->model_user->get_by_id($this->session->userdata("id"));
		$data["urls"]=mapAvailableCIUrls();
		$data["permissionTypes"]=$this->model_user->getUserPermissionTypes();
		$this->load->view('templates/header',$data);
		$this->load->view("/manage/user/permissions");
		$this->load->view("templates/footer");
	}
	
	protected function email_otp($email, $otp) {
		$this->load->library("email");
		$this->email->from('admin@10layer.com', '10Layer CMS');
		$this->email->to($email); 
		$this->email->subject('10Layer CMS password');
		$this->email->message("Please go to the following link to confirm your 10Layer account details. ".base_url()."user/otplogin/$otp");	
		$this->email->send();
	}
	
	protected function email_update_alert($email, $name) {
		$this->load->library("email");
		$this->email->from('admin@10layer.com', '10Layer CMS');
		$this->email->to($email);
		$this->email->subject('10Layer user account updated');
		$this->email->message("The details for user {$name} have been changed.");	
		$this->email->send();
	}
	
	
	
}

/* End of file users.php */
/* Location: ./system/application/controllers/manage/users.php */