<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Model_Flash extends Model_Content {
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
	    	"filetypes"=>array("swf"),
	    	"transformations"=>array(
	    		"soundslide"
	    	),
	    	"cdn"=>false,
	    	"directory"=>"/multimedia/slideshows/{date('Y')}/{date('m')}/{date('d')}/",
	    ),
	    array(
	    	"name"=>"caption",
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
	    	"rules"=>array(
	    		"numeric",
	    	),
	    ),
	    array(
	    	"name"=>"relatedflash",
	    	"label"=>"Related flash",
	    	"link"=>true,
	    	"tablename"=>"flash",
	    	"type"=>"autocomplete",
	    	"contenttype"=>"flash",
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
	    	"name"=>"relatedslideshows",
	    	"label"=>"Related slideshows",
	    	"tablename"=>"slideshow",
	    	"type"=>"autocomplete",
	    	"contenttype"=>"slideshow",
	    	"multiple"=>true,
	    ),
	    array(
	    	"name"=>"relatedvideos",
	    	"label"=>"Related videos",
	    	"tablename"=>"video",
	    	"type"=>"autocomplete",
	    	"contenttype"=>"video",
	    	"multiple"=>true,
	    ),
	    
	);

	public function __construct() {
		parent::__construct("flash");
	}
}

?>