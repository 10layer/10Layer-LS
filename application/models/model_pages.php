<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Model_Pages extends Model_Content {
	public $order_by=array(
		"timestamp DESC",
		"start_date"
	);
	
	public $fields=array(
		array(
			"name"=>"end_date",
			"tablename"=>"content",
			"hidden"=>true,
		),
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
			"rules"=>array("required",),
			"class"=>"richedit",
			"libraries"=>array("semantic"=>true,"search"=>"like",),
	    ),
	    
	    
	);

	public function __construct() {
		parent::__construct("page");
	}
}

?>