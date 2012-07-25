<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

/**
 * 10Layer Platforms class
 *
 * Controlls multiple platform publishing
 *
 * @package 10Layer
 * @subpackage	Libraries
 * @category	Libraries
 * @author		Jason Norwood-Young
 * @link		http://10layer.com
 * 
 */
 
class Platforms {
	protected $ci;
	protected $current_platform;
	
	public function __construct() {
		$this->ci=&get_instance();
		$this->ci->load->model("model_platforms");
		$this->current_platform=$this->get_current_platform();
	}
	
	public function draw_dropdown() {
		$data["platforms"]=$this->ci->model_platforms->get_all();
		$data["current_platform"]=$this->current_platform;
		$this->ci->load->view("libraries/platforms/dropdown",$data);
	}
	
	public function get_current_platform() {
		$current_platform_id=$this->ci->session->userdata("platform_id");
		if (empty($current_platform_id)) {
			$platforms=$this->ci->model_platforms->get_all();
			$current_platform=$platforms[0];
			$this->change($current_platform->id);
			return $current_platform;
		}
		$current_platform=$this->ci->model_platforms->get($current_platform_id);
		return $current_platform;
	}
	
	public function current_platform() {
		return $this->get_current_platform();
	}
	
	public function id() {
		$current_platform_id=$this->ci->session->userdata("platform_id");
		if (empty($current_platform_id)) {
			return $this->get_current_platform()->id;
		} else {
			return $current_platform_id;
		}
	}
	
	public function base_url() {
		return $this->get_current_platform()->base_url;
	}
	
	public function change($id) {
		$this->ci->session->set_userdata("platform_id",$id);
	}
}


?>