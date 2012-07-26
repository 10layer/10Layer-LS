<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Model_Articles extends Model_Content {
	public $order_by=array(
		"content.last_modified DESC",
		"start_date DESC",
		"title ASC",
		
	);
	
	public $fields=array(
		array(
			"name"=>"urlid",
			"tablename"=>"content",
			"hidden"=>true,
			"transformations"=>array(
				"copymultiple"=> array(" ", array("start_date", "title")),
				"substr_replace" => array("", 11, 6),
				"urlid"=> array("content.urlid", false),

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
				"type"=>"datetime",
				"value"=>'now',
			),
		array(
			"name"=>"blurb",
			"type"=>"textarea",
			"rules"=>array("required","maxlen"=>150),
			"transformations"=>array("safetext"),
			"libraries"=>array("semantic"=>true,"search"=>"fulltext",),
			"showcount"=>150,
		),
		array(
			"name"=>"body",
			"type"=>"textarea",
			"rules"=>array("required",),
			"class"=>"richedit",
			"libraries"=>array("semantic"=>true,"search"=>"fulltext",),
		),
		array(
			"name"=>"mainpic",
			"label"=>"Main photo",
			"type"=>"rich",
			"contenttype"=>"picture",
			"tablename"=>"pictures",
			"multiple"=>false,
		),
		
		array(
			"name"=>"caption",
			"label"=>"Main photo caption",
			"type"=>"textarea",
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
		array(
			"name"=>"author",
			"label"=>"Authors",
			"type"=>"autocomplete",
			"rules"=>array("required","min_count=1", "max_count=5"),
			"contenttype"=>"author",
			"multiple"=>true,
			"tablename"=>"authors",
		),
		array(
			"name"=>"tags",
			"type"=>"autocomplete",
			"contenttype"=>"tag",
			"tablename"=>"tags",
			"multiple"=>true,
		),
		array(
			"label"=>"M&amp;G Original",
			"name"=>"mg_original",
			"type"=>"checkbox",
		),
		array(
			"name"=>"section",
			"type"=>"nesteditems",
			"contenttype"=>"section",
			"rules"=>array("required",),
			"tablename"=>"site_sections",
		),
		array(
			"name"=>"source",
			"type"=>"reverse",
			"rules"=>array("required",),
			"contenttype"=>"source",
			"tablename"=>"sources",
		),

		array(
			"name"=>"special_focus",
	    	"label"=>"Special Focus",
	    	"type"=>"autocomplete",
	    	"rules"=>array("max_count=3"),
	    	"contenttype"=>"mixed",
	    	"contenttypes"=>array("promo","specialreport","video"),
	    	"tablename"=>"mixed",
	    	"multiple"=>true,
	    	"hidenew"=>true,
		),
		
		/*
array(
	    	"name"=>"special_focus",
	    	"label"=>"Special Focus",
	    	"type"=>"autocomplete",
	    	"contenttype"=>"specialreport",
	    	"tablename"=>"specialreports",
	    	"multiple"=>true,
	    ),
*/
		
	);

	public function __construct() {
		parent::__construct("article");
	}
}

?>
