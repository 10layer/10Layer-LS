<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Model_Specialreports extends Model_Content {
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
			"name"=>"blurb",
			"type"=>"textarea",
	    	"label"=>"Blurb",
			"rules"=>array("required","maxlen"=>150),
			"libraries"=>array("semantic"=>true,"search"=>"like",),
			"showcount"=>150,
		),
	    array(
	    	"name"=>"auto",
	    	"type"=>"boolean",
	    ),
		array(
			'name'=>'supplement',
			'type'=>'boolean',
		),
       array(
		   "name"=>"sidebar",
		   "label"=>"Sidebar",
		   "type"=>"textarea",
	    ),
       array(
		   "name"=>"underbar",
		   "label"=>"Underbar",
		   "type"=>"textarea",
	    ),
	    array(
	    	"name"=>"zones",
	    	"type"=>"hidden",
	    	"multiple"=>true,
	    	"tablename"=>"section_zones",
	    	"contenttype"=>"zones",
	    ),
	    array(
	    	"name"=>"banner_file",
	    	"type"=>"file",
	    	"label"=>"Banner Image",
	    	"filetypes"=>array("gif","jpg","jpeg","png"),
	    	"cdn_link"=>"cdn_link",
	    	"cdn"=>true,
	    	"directory"=>"/content/images/{date('Y')}/{date('m')}/{date('d')}/",
	    	"transformations"=>array(
	    		"str_replace"=>array("/content/images/", ""),
	    	),

	    ),
	    array(
	    	"name"=>"tags",
	    	"tablename"=>"tag",
	    	"type"=>"autocomplete",
	    	"contenttype"=>"tag",
	    	"multiple"=>true,
	    ),
	    array(
	    	"name"=>"thoughtleader_tag",
	    	"type"=>"text",
	    	"label"=>"Thoughtleader Feed Tag",
	    ),
	    array(
	    	"name"=>"promopic",
	    	"tablename"=>"pictures",
	    	"label"=>"Promo picture",
	    	"type"=>"rich",
	    	"contenttype"=>"picture",
	    	"multiple"=>false,
	    ),
	    array(
	    	"name"=>"zones",
	    	"type"=>"hidden",
	    	"multiple"=>true,
			"contenttype"=>"zones",
			"tablename"=>"section_zones",
	    ),
	);

	public function __construct() {
		parent::__construct("specialreport");
	}
}