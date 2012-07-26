<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Model_Redirects extends Model_Content {
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
			"name"=>"old_url",
			"label"=>"When users go to",
			"rules"=>array("required",),
	    ),
	    array(
			
			"name"=>"new_url",
			"label"=>"they get redirected to",
			"rules"=>array("required",),
	    ),
	    
	    array(
			"name"=>"hit_counter",
			"readonly"=>true,
			"type"=>"readonly"
	    ),
	    array(
			"name"=>"last_access",
			"readonly"=>true,
			"type"=>"readonly"
	    ),
	);

	public function __construct() {
		parent::__construct("redirect");
	}
}

?>