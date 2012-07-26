<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Model_Pictures extends Model_Content {
	public $fields=array(
		array(
			"name"=>"end_date",
			"tablename"=>"content",
			"hidden"=>true,
		),
	    
	    array(
	    	"name"=>"filename",
	    	"type"=>"image",
	    	"label"=>"File",
	    	"filetypes"=>array("gif","jpg","jpeg","png"),
	    	"directory"=>"/content/images/{date('Y')}/{date('m')}/{date('d')}/",
	    	"transformations"=>array(
	    		"str_replace"=>array("/content/images/", ""),
	    	),
	    	"linkformat"=>"http://cdn.mg.co.za/crop/content/images/{filename}/600x600",
	    ),
	    array(
	    	"name"=>"caption",
	    	"type"=>"textarea",
	    	"rules"=>array("maxlen"=>200),
	    	"showcount"=>200,
	    ),
	    array(
	    	"name"=>"url",
	    	"type"=>"text",
	    ),
	    
	);
	
	public $order_by=array(
		"last_modified DESC",
		"start_date DESC",
		"timestamp DESC"
	);
		
	public function __construct() {
		parent::__construct("picture");
	}
}

?>