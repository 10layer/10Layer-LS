<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
	/**
	 * 10Layer Interactive Class
	 *
	 * DEPRECATED!
	 *
	 * Class to handle interactivity between browser and back-end with json and ajax, with a queueing system ActiveMQ (STOMP)
	 *
	 * @package		10Layer
	 * @subpackage	Libraries
	 * @category	Libraries
	 * @author		Jason Norwood-Young
	 * @link		http://10layer.com
	 */

	
	class Interactive {
		protected $ci=false;
		protected $uid=false;
		protected $conn=false;
		
		/**
		 * Constructor
		 *
		 * @return void
		 * @author Jason Norwood-Young
		 **/
		
		public function __construct() {
			$this->ci=&get_instance();
			//$this->ci->load->model("model_user");
				include("resources/stomp/stomp.php");
			$this->uid=$this->ci->session->userdata("id");
			$this->con = new Stomp("tcp://localhost:61613");
			$this->con->connect();
			$this->con->setReadTimeout(1);
		}
		
		/**
		 * queue_check DEPRECATED
		 *
		 * Checks the queue to see if anything's waiting. 
		 * @return bool
		 * @author Jason Norwood-Young
		 **/
		public function queue_check_deprecated() {
			$result=$this->ci->model_user->queue_size($this->uid);
			if (empty($result)) {
				return false;
			}
			return true;
		}
		
		/**
		 * broadcast
		 *
		 * Adds something on the queue for everyone, return a unique id to be used to avoid duplication
		 * @var $uid String Unique identifier
		 * @var $action String
		 * @var $data Mixed
		 * @return String Unique identifier
		 * @author Jason Norwood-Young
		 **/
		public function broadcast($action,$data=false) {
			$this->con->subscribe("/queue/all", array('ack' => 'client','activemq.prefetchSize' => 1 ));
			$this->con->begin("tx1");
			$this->con->send("/queue/all", serialize(array("action"=>$action,"data"=>$data)));
			$this->con->commit("tx1");
		}
		
		public function consume_broadcast() {
			$this->con->subscribe("/queue/all", array('ack' => 'client','activemq.prefetchSize' => 1 ));
			if (($msg = $conn->readFrame()) !== false) {
			    echo (string)$msg;
			    $this->con->ack($msg);
				flush();
			}
		}
		
	}
?>