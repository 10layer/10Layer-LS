<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Model_Promo extends Model_Content {
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
			"name"=>"id",
			"hidden"=>true,
		),
	    array(
	    	"name"=>"type",
	    	"type"=>"text",
	    	"rules"=>array("required",),
	    ),
		array(
			"name"=>"blurb",
			"type"=>"textarea",
			"rules"=>array("required",),
			"libraries"=>array("semantic"=>true,"search"=>"like",),
		),
		array(
			"name"=>"promopic",
			"label"=>"Promo photo",
			"type"=>"rich",
			"rules"=>array("required",),
			"contenttype"=>"picture",
			"tablename"=>"pictures",
			"multiple"=>false,
		),
		array(
			"name"=>"url",
			"label"=>"URL",
			"type"=>"text",
			"rules"=>array("required",),
		),
	);

	public function __construct() {
		parent::__construct("promo");
	}
}

?>
