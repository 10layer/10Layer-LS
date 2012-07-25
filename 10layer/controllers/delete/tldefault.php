<?php
require_once(APPPATH.'third_party/10layer/system/TL_Controller_Crud.php');
/**
 * TLDefault class.
 * 
 * @extends TL_Controller_Delete
 * @package 10Layer
 * @subpackage Deprecated
 */
class TLDefault extends TL_Controller_Delete {
	public function __construct() {
		parent::__construct();
	}
}
?>