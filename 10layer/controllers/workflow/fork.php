<?php
	/**
	 * Fork class
	 * 
	 * @extends Controller
	 * @package 10Layer
	 * @subpackage Controllers
	 */
	class Fork extends CI_Controller {

		/**
		 * __construct function.
		 * 
		 * @access public
		 * @return void
		 */
		public function __construct() {
			parent::__construct();
		}
		
		public function dofork() {
			$action=$this->input->post("action");
			$content_id=$this->input->post("content_id");
			$platforms=$this->input->post("platform");
			$result=array("error"=>false,"msg"=>"","info"=>"");
			if (empty($platforms)) {
				$result["error"]=true;
				$result["msg"]="No platforms selected";
				$this->_returnResult($result);
				return true;
			}
			if ($action=="fork") {
				$result=$this->_fork($content_id, $platforms);
				$this->_returnResult($result);
				return true;
			} elseif ($action=="link") {
				$obj=$this->model_content->getByIdORM($content_id);
				foreach($platforms as $platform) {
					$obj->linkToPlatform($platform);
				}
				$result["msg"]="Content linked";
				$this->_returnResult($result);
			}
		}
		
		protected function _fork($id, $platforms) {
			$result=array("error"=>false,"msg"=>"","info"=>"");
			foreach($platforms as $platform) {
				$obj=$this->model_content->getByIdORM($id);
				$cloneid=$obj->dbClone();
				$newobj=$this->model_content->getByIdORM($cloneid);
				$newobj->linkToPlatform($platform);
			}
			$result["msg"]="Content forked";
			return $result;
		}
		
		protected function _returnResult($result) {
			//print "<script>document.domain=document.domain;</script><textarea>";
			print json_encode($result);
			//print "</textarea>";
		}
	}

/* End of file fork.php */
/* Location: ./system/application/controllers/workflow */