<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Model_Cartoons extends Model_Content {
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
	    	"name"=>"filename",
	    	"type"=>"file",
	    	"filetypes"=>array("gif","jpg","jpeg","png"),
	    	"directory"=>"/content/cartoons/{date('Y')}/{date('m')}/{date('d')}/",
	    	"transformations"=>array(
	    		"str_replace"=>array("/content/cartoons/", ""),
	    	),
	    ),
	    /*
array(
	    	"name"=>"thumbnail",
	    	"type"=>"file",
	    	"directory"=>"/content/cartoons/{date('Y')}/{date('m')}/{date('d')}/",
	    ),
	    array(
	    	"name"=>"credits",
	    ),
	    array(
	    	"name"=>"effects",
	    	"type"=>"textarea",
	    ),
	    array(
	    	"name"=>"width",
	    	"rules"=>array(
	    		"numeric",
	    	),
	    ),
	    array(
	    	"name"=>"height",
	    	"rules"=>array(
	    		"numeric",
	    	),
	    ),
	    array(
	    	"name"=>"mime",
	    ),
*/
	    array(
	    	"name"=>"cartoon_type_id",
	    	"label"=>"Cartoon Type",
	    	"options"=>array(
	    		"Zapiro",
				"Madam and Eve",
	    	),
	    	"type"=>"select",
	    	"value"=>1,
	    ),

	);

	public function __construct() {
		parent::__construct("cartoon");
	}
}

?>