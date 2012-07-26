<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Model_Authors extends Model_Content {
	public $order_by=array(
		"timestamp DESC",
		"start_date"
	);
	
	public $fields=array(
		array(
			"name"=>"title",
			"label"=>"Name",
			"rules"=>array("required"),
			"tablename"=>"content",
			"libraries"=>array(
				"semantic"=>true,
				"search"=>"like",
			),
		),
		array(
			"name"=>"urlid",
			"tablename"=>"content",
			"hidden"=>true,
			"transformations"=>array(
				"copy"=>"title",
				"urlid"=>array("content.urlid",false)
			),
		),
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
	    	"name"=>"googleplus",
	    	"label"=>"Google Plus account number",
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
	    /*array(
	    	"name"=>"pic",
	    	"type"=>"file",
	    	"label"=>"Author Portrait",
	    	"filetypes"=>array("gif","jpg","png"),
	    	"directory"=>"/content/authors/{date('Y')}/{date('m')}/{date('d')}/",
	    ),*/
	    array(
	    	"name"=>"pic",
	    	"type"=>"image",
	    	"label"=>"Author Portrait",
	    	"filetypes"=>array("gif","jpg","jpeg","png"),
	    	"directory"=>"/content/authors/{date('Y')}/{date('m')}/{date('d')}/",
	    	"transformations"=>array(
	    		"str_replace"=>array("/content/authors/", ""),
	    	),
	    	"linkformat"=>"http://cdn.mg.co.za/crop/content/authors/{filename}/100x100",
		),
	    array(
			"name"=>"linked_content",
	    	"label"=>"Linked Content",
	    	"type"=>"autocomplete",
	    	"contenttype"=>"mixed",
	    	"contenttypes"=>array("specialreport","picture","promo","video","podcast","slideshow"),
	    	"multiple"=>true,
		),
	);

	public function __construct() {
		parent::__construct("author");
	}
}

?>