<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Model_Newsletterbanner extends Model_Content {
	public $fields=array(
		array(
			"name"=>"end_date",
			"tablename"=>"content",
			"hidden"=>true,
		),
	    
	    array(
	    	"name"=>"banner",
	    	"type"=>"image",
	    	"label"=>"File",
	    	"filetypes"=>array("gif","jpg","jpeg","png"),
	    	"directory"=>"/content/images/{date('Y')}/{date('m')}/{date('d')}/",
	    	"transformations"=>array(
	    		"str_replace"=>array("/content/images/", ""),
	    	),
	    ),
	
	    array(
	    	"name"=>"banner_url",
	    	"type"=>"text",
	    ),
	    
	);
	
	public $order_by=array(
		"timestamp DESC",
		"start_date"
	);
		
	public function __construct() {
		parent::__construct("picture");
	}
}

?>