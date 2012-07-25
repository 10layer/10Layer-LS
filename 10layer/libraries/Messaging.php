<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 
	/**
	 * 10Layer Messaging Class
	 *
	 * This class extends the Stomp class for some nifty messaging through a message queueing service
	 *
	 * @package		10Layer
	 * @subpackage	Libraries
	 * @category	Libraries
	 * @author		Jason Norwood-Young
	 * @link		http://10layer.com
	 */
	
	include_once("stomp/Stomp.php");
	require_once("stomp/Stomp/Map.php");
	
	class Messaging extends Stomp {
		protected $ci=false;
		protected $userid="";
		protected $sendmsg=array();
		public $error=false;
		public $errormsg="";
		
		public function __construct () {
			$this->ci=&get_instance();
			$this->userid=$this->ci->session->userdata("id");
			$this->sendmsg=array(
				"from"=>array(
					"name"=>$this->ci->session->userdata("name"),
					"urlid"=>$this->ci->session->userdata("urlid"),
					"id"=>$this->ci->session->userdata("id")
				),
				"body"=>""
			);
			$this->sync = false;
			$connectstring = $this->ci->config->item("stomp_protocol")."://".$this->ci->config->item("stomp_server").":".$this->ci->config->item("stomp_port");
			$this->_brokerUri = $connectstring;
			try{
				$this->_init();
				$this->connect();
			} catch(Exception $e) {
				$this->error=true;
				$this->errormsg=$e->getMessage();
			}
		}
		
		public function post_message($uid,$msg) {
			$result=$this->send("/queue/".$this->userid, $this->process_msg($msg), array('persistent'=>'true'));
		}
		
		public function post_action($func,$params=array()) {
			//if (is_array($actionarray)) {
				$this->send("/action", $this->process_msg(array("func"=>$func, "params"=>$params)));
			//}
		}
		
		protected function process_msg($body) {
			$this->sendmsg["body"]=$body;
			$header=array();
			$header['transformation'] = 'jms-map-json';
			$msg= new StompMessageMap($this->sendmsg, $header);
			//print_r($msg);
			return $msg;
		}
		
		public function get_message() {
			$consumer->clientId=$this->userid;
			
			$this->setReadTimeout(1);
			$this->subscribe("/queue/".$this->userid);
			/*while (($msg = $this->readFrame()) == false) {
				sleep(1);
			}
			echo (string)$msg;*/
			while (($msg = $this->readFrame()) !== false) {
				echo (string)$msg;
			}
		}
		
	}

?>
