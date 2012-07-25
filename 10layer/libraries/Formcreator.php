<?php
/**
 * Formcreator class.
 * 
 * Draws a lovely form
 *
 * @package 10Layer
 * @subpackage Libraries
 */
class Formcreator {
	protected $ci;
	protected $_fields=array();
	
	public function __construct() {
		$this->ci=&get_instance();
	}
	
	public function setFields($fields) {
		$this->_fields=$fields;
	}
	
	public function drawFields($draw=true) {
		$result="";
		foreach($this->_fields as $field) {
			if (empty($field->label)) {
				$field->label=$field->name;
			}
			
			if (!$field->hidden) {
				$result.=$this->ci->load->view("snippets/form_field",array("field"=>$field),true);	
			}
			
			//$this->ci->load->view("snippets/form_field",array("field"=>$field));
		}
		if ($draw) {
			print $result;
		} else {
			return $result;
		}
	}
}

?>