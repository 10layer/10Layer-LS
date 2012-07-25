<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Model_Authors class.
 * 
 * @extends Model_Content
 * @package 10Layer
 * @subpackage Models
 */
class Model_Authors extends Model_Content {
	public $order_by=array(
		"timestamp DESC",
		"start_date"
	);
	
	public $fields=array(
	    
	    
	    array(
	    	"name"=>"email",
	    	"type"=>"text",
	    	"rules"=>array(
	    		"valid_email",
	    	),
	    ),
	    array(
	    	"name"=>"twitter",
	    	"label"=>"Twitter account",
	    	"type"=>"text",
	    ),
	    array(
	    	"name"=>"facebook",
	    	"label"=>"Facebook account",
	    	"type"=>"text",
	    ),
	    array(
	    	"name"=>"telephone",
	    	"type"=>"text",
	    ),
	    array(
	    	"name"=>"bio",
	    	"label"=>"Biography",
	    	"type"=>"textarea",
	    	"class"=>"richedit",
	    ),
	    array(
			"name"=>"authorphoto",
	    	"tablename"=>"authorphotos",
	    	"label"=>"Author Portrait",
	    	"type"=>"rich",
	    	"contenttype"=>"authorphoto",
	    	"multiple"=>false,
	    ),
	);

	public function __construct() {
		parent::__construct("author");
	}
}

?>