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
		
		
		/**
		 * __construct function.
		 * 
		 * @access public
		 * @return void
		 */
		public function __construct() {
			$this->ci=&get_instance();
			$this->server = $this->ci->config->item("socket_io_server") ? $this->ci->config->item("socket_io_server") : "http://".$_SERVER["SERVER_NAME"].":8181";
			$this->elephant = new Elephant($this->server, 'socket.io', 1, false, true, true);
			try {
				$this->elephant->init();
				$this->running = true;
			} catch (Exception $e) {
				// show_error("Socket.io error: ".$e->getMessage());
				$this->running = false;
			}
		}

		public function emit($key, $val) {
			if ($this->running) {
				$this->elephant->emit($key, $val, null);
			}
		}

		public function js() {
			if ($this->running) {
				$data["server"] = $this->server;
				$this->ci->load->view("snippets/socketio_javascript", $data);
			}
		}

		public function is_running() {
			return $this->running;
		}

	}
?>