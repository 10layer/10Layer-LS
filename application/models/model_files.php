<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Model_Files extends Model_Content {
	public $order_by=array(
		"timestamp DESC"
	);
	
	public $fields=array(
		array(
			"name"=>"end_date",
			"tablename"=>"content",
			"hidden"=>true,
		),
	    array(
	    	"name"=>"title",
			"tablename"=>"content",
	    	"label"=>"Title",
	    	"type"=>"text",
	    ),
		array(
			"name"=>"live",
			"tablename"=>"content",
			"type"=>"hidden",
			"value"=>1,
		),
		array(
			"name"=>"start_date",
			"tablename"=>"content",
			"type"=>"hidden",
			"value"=>"1985-01-01",
		),
	    array(
	    	"name"=>"caption",
	    	"label"=>"Caption",
	    	"type"=>"textarea",
	    ),
	    array(
	    	"name"=>"credits",
	    	"label"=>"Credits",
	    	"type"=>"text",
	    ),
	    array(
	    	"name"=>"filename",
	    	"type"=>"file",
	    	"label"=>"File",
	    	"rules"=>array("required",),
	    	"filetypes"=>array("gif","jpg","png","docx","doc","pdf","zip","xls","ppt","zip","jpeg"),
	    	"directory"=>"/content/documents/{date('Y')}/{date('m')}/{date('d')}/",
	    	"linkformat"=>"http://cdn.mg.co.za{filename}",
	    ),
	);

	public function __construct() {
		parent::__construct("file");
	}
}

?>