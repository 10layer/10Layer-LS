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
		
		
		/**
		 * __construct function.
		 * 
		 * @access public
		 * @return void
		 */
		public function __construct() {
			$this->ci=&get_instance();
			$this->server = $this->ci->config->item("socket_io_server") ? $this->ci->config->item("socket_io_server") : "http://localhost:8181";
			$this->elephant = new Elephant($this->server, 'socket.io', 1, false, true, true);
			try {
				$this->elephant->init();
			} catch (Exception $e) {
				show_error("Socket.io error: ".$e->getMessage());
			}
		}

		public function emit($key, $val) {
			$this->elephant->emit($key, $val, null);
		}

		public function js() {
			$data["server"] = $this->server;
			$this->ci->load->view("snippets/socketio_javascript", $data);
		}

	}
?>