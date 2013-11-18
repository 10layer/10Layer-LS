<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Image extends CI_Controller {

	public function index() {
		print $this->tenlayer->image($this->input->get("filename"), $this->input->get("width"), $this->input->get("height"), $this->input->get("bounding"), $this->input->get("grey"), true);
	}

}

/* End of file image.php */
/* Location: ./application/controllers/image.php */