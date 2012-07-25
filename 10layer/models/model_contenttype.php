<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Model_ContentType class.
 * 
 * DEPRECATED! Just here as a shell for compatibility. Implement Model_Crud directly.
 *
 * @extends Model_Crud
 * @package 10Layer
 * @subpackage Models
 */
class Model_ContentType extends Model_Crud {
	
	public function __construct($tablename=false) {
		parent::__construct($tablename);
	}
		
}

?>