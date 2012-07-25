<?php 
require_once(APPPATH.'third_party/10layer/system/TL_Controller_Crud.php');
/**
 * TLDefault class.
 * 
 * @extends TL_Controller_Create
 * @package 10Layer
 * @subpackage Controllers
 */
class TLDefault extends TL_Controller_Create {

	public function __construct() {
		parent::__construct();
		//$this->output->enable_profiler(true);
	}
	
}

?>