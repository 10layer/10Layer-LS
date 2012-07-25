<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Model_Pages class.
 * 
 * @extends Model_Content
 * @package 10Layer
 * @subpackage Models
 */
class Model_Pages extends Model_Content {
	public $order_by=array(
		"timestamp DESC",
		"start_date"
	);
	
	public $fields=array(
	    array(
	    	"name"=>"urlid",
	    	"tablename"=>"content",
	    	"type"=>"hidden",
	    	"transformations"=>array(
	    		"copy"=>"title",
	    		"urlid"=>array("content.urlid",false)
	    	)
	    ),
	    array(
	    	"name"=>"body",
	    	"type"=>"textarea",
	    	"class"=>"richedit",
	    	"libraries"=>array(
	    		"semantic"=>true,
	    		"search"=>"fulltext",
	    	),
	    	"rules"=>array(
	    		"required",
	    	),
	    ),
	    
	    
	);

	public function __construct() {
		parent::__construct("page");
	}
}

?>