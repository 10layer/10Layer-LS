<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 
	/**
	 * 10Layer Socket.io Class
	 *
	 * Connects to a socket.io instance using Elephant.io
	 *
	 * @package		10Layer
	 * @subpackage	Libraries
	 * @category	Libraries
	 * @author		Jason Norwood-Young
	 * @link		http://10layer.com
	 */
	
	include(TLPATH."resources/elephant.io/lib/ElephantIO/Client.php");
	use ElephantIO\Client as Elephant;

	class Socketio {
		protected $ci = false;
		protected $elephant = false;
		protected $server = "http://localhost:8181";
		protected $running = false;
		protected $available = false;
		public $namespace = "";
		
		
		/**
		 * __construct function.
		 * 
		 * @access public
		 * @return void
		 */
		public function __construct() {
			$this->ci=&get_instance();
			$this->namespace = "/".$this->ci->config->item("mongo_db"); //We default to the active Mongo DB for our Socket IO namespace
			$this->server = $this->ci->config->item("socket_io_server") ? $this->ci->config->item("socket_io_server") : "http://".$_SERVER["SERVER_NAME"].":8181";
			
			$this->connect();
		}

		public function connect() {
			$this->elephant = new Elephant($this->server, 'socket.io', 1, true, true, false, "/tenlayer");
			try {
				$this->elephant->init();
				$this->running = true;
			} catch (Exception $e) {
				// show_error("Socket.io error: ".$e->getMessage());
				$this->running = false;
			}
		}

		public function close() {
			if ($this->running) {
				$this->elephant->close();
			}
		}

		public function emit($key, $val) {
			if (!$this->running) {
				$this->connect();
			}
			if ($this->running) {
				// if (is_array($val)) {
				// 	$val["namespace"] = $this->namespace;
				// } else {
				// 	$val = array($val, "namespace" => $this->namespace);
				// }
				$this->elephant->emit($key, $val, "/tenlayer");
				// $this->elephant->send(1, "", "/tenlayer", json_encode(array("name"=>$key, "args"=>$val)));
			}
		}

		public function js() {
			if ($this->running) {
				$data["server"] = $this->server;
				$data["namespace"] = $this->namespace;
				$this->ci->load->view("snippets/socketio_javascript", $data);
			}
		}

		public function is_running() {
			return $this->running;
		}

		public function is_available() {
			return $this->available;
		}

	}
?>