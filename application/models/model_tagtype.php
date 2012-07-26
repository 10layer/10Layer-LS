<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Model_Tagtype extends Model_Content {
	public $order_by=array(
		"title DESC",
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
	    	"name"=>"tag",
	    	"tablename"=>"tag",
	    	"label"=>"Tags",
	    	"type"=>"drilldown",
	    	"contenttype"=>"tag",
	    	"multiple"=>true,
	    ),
	);

	public function __construct() {
		parent::__construct("tagtype");
	}
}

?>