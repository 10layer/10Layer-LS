<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Model_Site_Sections extends Model_Content {
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
	    	"name"=>"auto",
	    	"type"=>"boolean",
	    ),
	     array(
	    	"name"=>"child_sections",
	    	"type"=>"autocomplete",
	    	"link"=>"true",
	    	"contenttype"=>"section",
	    	"tablename"=>"site_sections",
	    	"multiple"=>true,
	    ),
	    array(
	    	"name"=>"zones",
	    	"type"=>"autocomplete",
	    	"multiple"=>true,
			"contenttype"=>"zones",
			"tablename"=>"section_zones",
	    ),
	);

	public function __construct() {
		parent::__construct("section");
	}
}

?>