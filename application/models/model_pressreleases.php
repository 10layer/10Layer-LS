<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Model_Pressreleases extends Model_Content {
	public $order_by=array(
		"timestamp DESC",
		"start_date"
	);
	
	public $fields=array(
	    array(
	    	"name"=>"issuedate",
	    	"tablename"=>"content",
	    	"type"=>"date",
	    ),
	    array(
	    	"name"=>"author",
	    ),
	    array(
	    	"name"=>"link",
	    ),
	    array(
	    	"name"=>"company",
	    ),
	    array(
	    	"name"=>"pressoffice",
	    ),
	    array(
	    	"name"=>"section",
	    ),
	    array(
	    	"name"=>"uid",
	    ),
	    array(
	    	"name"=>"homedisplay",
	    	"type"=>"boolean",
	    ),
	    array(
	    	"name"=>"sectiondisplay",
	    	"type"=>"boolean"
	    ),
	    array(
	    	"name"=>"blurb",
	    	"type"=>"textarea"
	    ),
	);

	public function __construct() {
		parent::__construct("pressrelease");
	}
}

?>