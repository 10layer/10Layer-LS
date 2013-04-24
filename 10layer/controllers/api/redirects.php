<?php
	require_once('10layer/system/TL_Api.php');

	/**
	 * Users class
	 *
	 * @extends Controller
	 */

	class Redirects extends TL_Api {

		/**
		 * __construct function.
		 *
		 * @access public
		 * @return void
		 */
		public function __construct() {
			parent::__construct();
			$this->secure=$this->_check_secure();
			$this->load->model('Model_Redirect', 'model_redirect');
		}

		/**
		 * index function.
		 *
		 * Returns all the Redirects
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
			$this->data["content"] = $this->mongo_db->order_by(array("from"))->get("redirects");
			$this->returndata();
		}


		/**
		 * save function.
		 *
		 * @access public
		 * @return void
		 */
		public function save() {

			$data['from'] = $this->input->get_post('from');
			$data['to'] = $this->input->get_post('to');
			$data['_id'] = $this->input->get_post('id');
			if ($data['_id'] == '') {
				$r_urlid = $data['from'].$data['to'];
				$urlid = $this->datatransformations->urlid($this, $r_urlid);
				$data['_id'] = $urlid;
				$this->model_redirect->insert($data);
			} else {
				$id = $data['_id'];
				unset($data['_id']);
				$this->model_redirect->update($id, $data);
				$data['_id'] = $id;
			}

			$this->data['id'] = $data['_id'];
			$this->data['msg'] = 'Redirect saved';
			$this->data['error'] = false;
 			$this->returndata();
		}

		public function edit(){
			$id = $this->input->get_post('id');
			$this->data['redirect'] = $this->model_redirect->get_by_id($id);
			$this->returndata();
		}

		public function delete(){
			$id = $this->input->get_post('id');
			$this->model_redirect->delete_redirect($id);
			$this->data['error'] = false;
			$this->data['msg'] = 'Redirect removed';
			$this->returndata();
		}



	}

/* End of file .php */
/* Location: ./system/application/controllers/ */