<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Model_Contributors extends Model_Content {
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
				"name"=>"start_date",
				"tablename"=>"content",
				"rules"=>array("required",),
				"type"=>"date",
				"value"=>'Today',
			),
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
	    	"name"=>"pic",
	    	"type"=>"file",
	    	"label"=>"Author Portrait",
	    	"filetypes"=>array("gif","jpg","png"),
	    ),
	);

	public function __construct() {
		parent::__construct("contributor");
	}
}

?>