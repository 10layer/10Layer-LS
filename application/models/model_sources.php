<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Model_Sources extends Model_Content {
	public $order_by=array(
		"title ASC",
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
	    	"name"=>"start_date",
	    	"tablename"=>"content",
	    	"type"=>"hidden",
	    ),
	    array(
	    	"name"=>"end_date",
	    	"tablename"=>"content",
	    	"type"=>"hidden",
	    ),
	    array(
	    	"name"=>"live",
	    	"tablename"=>"content",
	    	"type"=>"hidden",
	    ),
	    array(
			"name"=>"logo",
			"rules"=>array("required",),
			"label"=>"Logo URL",
		),
		array(
			"name"=>"show",
			"type"=>"boolean",
		),
	    array(
	    	"name"=>"articles",
	    	"tablename"=>"article",
	    	"contenttype"=>"article",
	    	"type"=>"drilldown",
	    	"readonly"=>true,
	    	"multiple"=>true,
	    ),
	    
	);

	public function __construct() {
		parent::__construct("source");
	}
}

?>