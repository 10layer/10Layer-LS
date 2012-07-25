<?php
/**
 * Article class.
 * 
 * @extends TL_Controller_Edit
 * @package 10Layer
 * @subpackage Controllers
 */
class Article extends TL_Controller_Edit {
	public function __construct() {
		parent::__construct();
		$this->output->enable_profiler(true);
	}
}
?>