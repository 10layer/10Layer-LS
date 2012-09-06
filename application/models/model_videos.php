<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Model_Videos extends Model_Content {	
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
				"name"=>"start_date",
				"tablename"=>"content",
				"rules"=>array("required",),
				"type"=>"datetime",
				"value"=>'now',
			),
		array(
	    	"name"=>"filename",
	    	"type"=>"external",
	    	"options"=>"http://local.10layerls.com/resources/tests/external_test.html",
	    ),
	    array(
	    	"name"=>"caption",
	    	"label"=>"Blurb",
	    	"type"=>"textarea",
			"rules"=>array("required","maxlen"=>160),
			"transformations"=>array("safetext"),
			"libraries"=>array("semantic"=>true,"search"=>"like",),
			"showcount"=>160,
	    ),
	    array(
	    	"name"=>"credits",
	    ),
	    array(
	    	"name"=>"body",
	    	"type"=>"textarea",
	    ),
	    array(
	    	"name"=>"multimedia_section_filter",
	    	"label"=>"Section",
	    	"options"=>array(
	    		"News",
				"Opinion",
				"Business",
				"Arts and Culture",
				"Education",
	    	),
	    	"type"=>"select",
	    ),
	    array(
	    	"name"=>"tags",
	    	"tablename"=>"tags",
	    	"type"=>"autocomplete",
	    	"contenttype"=>"tag",
	    	"multiple"=>true,
	    ),
	    array(
	    	"name"=>"runningtime",
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
	    	"name"=>"relatedvideos",
	    	"label"=>"Related videos",
	    	"link"=>true,
	    	"tablename"=>"video",
	    	"type"=>"autocomplete",
	    	"contenttype"=>"video",
	    	"multiple"=>true,
	    ),
	    array(
	    	"name"=>"relatedslideshows",
	    	"label"=>"Related slideshows",
	    	"tablename"=>"slideshow",
	    	"type"=>"autocomplete",
	    	"contenttype"=>"slideshow",
	    	"multiple"=>true,
	    ),
	    array(
	    	"name"=>"relatedpodcasts",
	    	"label"=>"Related podcasts",
	    	"tablename"=>"podcast",
	    	"type"=>"autocomplete",
	    	"contenttype"=>"podcast",
	    	"multiple"=>true,
	    ),
	    array(
	    	"name"=>"relatedflash",
	    	"label"=>"Related flash",
	    	"tablename"=>"flash",
	    	"type"=>"autocomplete",
	    	"contenttype"=>"flash",
	    	"multiple"=>true,
	    ),
	);

	public function __construct() {
		parent::__construct("video");
	}
}

?>