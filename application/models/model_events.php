<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Model_Events extends Model_Content {
	public $order_by=array(
		"content.last_modified DESC",
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
				"name"=>"start_date",
				"tablename"=>"content",
				"rules"=>array("required",),
				"type"=>"date",
				"value"=>'Today',
			),
		array(
			"name"=>"blurb",
			"type"=>"textarea",
			"rules"=>array("required"),
			"transformations"=>array("safetext"),
			"libraries"=>array("semantic"=>true,"search"=>"like",),
		),
		array(
	    	"name"=>"photo",
	    	"type"=>"file",
	    	"label"=>"Photo",
	    	"cdn_link"=>"cdn_link",
	    	"cdn"=>true,
	    	"directory"=>"/content/images/{date('Y')}/{date('m')}/{date('d')}/",
	    	"transformations"=>array(
	    		"str_replace"=>array("/content/images/", ""),
	    	),
	    ),
		array(
	    	"name"=>"city",
	    	"label"=>"City",
	    	"options"=>array(
	    		"Johannesburg",
				"Cape Town",
				"Durban",
	    	),
	    	"type"=>"select",
	    	"value"=>1,
	    ),
		array(
			"name"=>"venue",
			"type"=>"text",
			"label"=>"venue",
			"rules"=>array("required",),
		),
		array(
			"name"=>"price",
			"type"=>"text",
			"label"=>"Price",
			"rules"=>array("required",),
		),
		array(
				"name"=>"date_time",
				"type"=>"text",
				"label"=>"Date and Time",
				"rules"=>array("required",),
				"type"=>"text",
				"value"=>'Today',
			),
		
		array(
			"name"=>"contact_details",
			"type"=>"textarea",
			"label"=>"Contact Details",
			"rules"=>array("required",),
		),
		array(
			"name"=>"event_details",
			"type"=>"textarea",
			"label"=>"Event Details",
			"rules"=>array("required",),
			"class"=>"richedit",
			"libraries"=>array("semantic"=>true,"search"=>"like",),
		),
		
		
array(
			"name"=>"article",
			"tablename"=>"articles",
			"label"=>"Related Articles",
			"type"=>"deepsearch",
			"link"=>true,
			"contenttype"=>"article",
			"multiple"=>true,
		),

		
		/*array(
	    	"name"=>"articles",
	    	"label"=>"Related Articles",
	    	"type"=>"autocomplete",
	    	"contenttype"=>"article",
	    	"tablename"=>"articles",
	    	"multiple"=>true,
	    ),*/
	
	

		
	);

	public function __construct() {
		parent::__construct("event");
	}
}

?>
