<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Model_Pictures class.
 * 
 * @extends Model_Content
 * @package 10Layer
 * @subpackage Models
 */
class Model_Pictures extends Model_Content {
	public $fields=array(
	    array(
	    	"name"=>"caption",
	    	"type"=>"textarea",
	    ),
	    array(
	    	"name"=>"filename",
	    	"type"=>"file",
	    	"label"=>"File",
	    	"cdn_link"=>"cdn_link",
	    	"cdn"=>true,
	    ),
	    array(
	    	"name"=>"cdn_link",
	    	"type"=>"cdn",
	    	"readonly"=>true,
	    ),
	);
	
	public $order_by=array(
		"timestamp DESC",
		"start_date"
	);
		
	public function __construct() {
		parent::__construct("picture");
	}
}

?>