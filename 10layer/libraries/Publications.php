<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

/**
 * 10Layer Publications class
 *
 * Controlls multiple publication publishing
 *
 * @package 10Layer
 * @subpackage	Libraries
 * @category	Libraries
 * @author		Jason Norwood-Young
 * @link		http://10layer.com
 * 
 */
 
class Publications {
	protected $ci;
	protected $current_publication;
	
	public function __construct() {
		$this->ci=&get_instance();
		$this->ci->load->model("model_publications");
		$this->current_publication=$this->get_current_publication();
	}
	
	public function draw_dropdown() {
		$data["publications"]=$this->ci->model_publications->get_all();
		$data["current_publication"]=$this->current_publication;
		$this->ci->load->view("libraries/publications/dropdown",$data);
	}
	
	public function getAll() {
		return $this->ci->model_publications->get_all();
	}
	
	public function get_current_publication() {
		$current_publication_id=$this->ci->session->userdata("publication_id");
		if (empty($current_publication_id)) {
			$publications=$this->ci->model_publications->get_all();
			$current_publication=$publications[0];
			$this->change($current_publication->id);
			return $current_publication;
		}
		$current_publication=$this->ci->model_publications->get($current_publication_id);
		return $current_publication;
	}
	
	public function current_publication() {
		return $this->get_current_publication();
	}
	
	public function id() {
		$current_publication_id=$this->ci->session->userdata("publication_id");
		if (empty($current_publication_id)) {
			return $this->get_current_publication()->id;
		} else {
			return $current_publication_id;
		}
	}
	
	public function base_url() {
		return $this->get_current_publication()->base_url;
	}
	
	public function change($id) {
		$this->ci->session->set_userdata("platform_id",false);
		$this->ci->session->set_userdata("publication_id",$id);
	}
}


?>