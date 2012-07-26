<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Model_Podcast extends Model_Content {
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
	    	"filetypes"=>array("mp3"),
	    	"directory"=>"/multimedia/podcasts/{date('Y')}/{date('m')}/{date('d')}/",
	    	"transformations"=>array(
	    		"str_replace"=>array("/multimedia/podcasts/", ""),
	    	),
	    	"linkformat"=>"http://cdn.mg.co.za/multimedia/podcasts/{filename}",
	    ),
	    array(
	    	"name"=>"cdn_link",
	    	"type"=>"cdn",
	    	"readonly"=>true,
	    ),
	    array(
	    	"name"=>"caption",
	    	"type"=>"textarea",
			"rules"=>array("required","maxlen"=>150),
			"transformations"=>array("safetext"),
			"showcount"=>150,
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
	    	"label"=>"Running Time",
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
	    	"name"=>"relatedpodcasts",
	    	"label"=>"Related podcasts",
	    	"link"=>true,
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
		parent::__construct("podcast");
	}
}

?>