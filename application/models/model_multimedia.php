<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Model_Multimedia extends Model_Content {
	public $order_by=array(
		"start_date DESC",
		"title ASC",
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
	    	"name"=>"zones",
	    	"type"=>"hidden",
	    	"multiple"=>true,
	    	"tablename"=>"section_zones",
	    	"contenttype"=>"zones",
	    ),
	);

	public function __construct() {
		parent::__construct("multimedia");
	}
}