<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Model_Articles class.
 * 
 * @extends Model_Content
 * @package 10Layer
 * @subpackage Models
 */
class Model_Articles extends Model_Content {
	public $order_by=array(
		"title ASC",
		"start_date"
	);
	
	public $fields=array(
	    array(
	    	"name"=>"id",
	    	"hidden"=>true,
	    ),
	    
	    
	    array(
	    	"name"=>"blurb",
	    	"type"=>"textarea",
	    	"class"=>"richedit",
	    	"rules"=>array(
	    		"required",
	    	),
	    	"libraries"=>array(
	    		"semantic"=>true,
	    		"search"=>"fulltext",
	    	),
	    ),
	    array(
	    	"name"=>"body",
	    	"type"=>"textarea",
	    	"class"=>"richedit",
	    	"libraries"=>array(
	    		"semantic"=>true,
	    		"search"=>"fulltext",
	    	),
	    ),
	    array(
	    	"name"=>"mainpic",
	    	"tablename"=>"pictures",
	    	"label"=>"Main photo",
	    	"type"=>"rich",
	    	"contenttype"=>"picture",
	    	"multiple"=>false,
	    ),
	    array(
	    	"name"=>"author",
	    	"tablename"=>"authors",
	    	"label"=>"Author",
	    	"type"=>"autocomplete",
	    	"contenttype"=>"author",
	    	"multiple"=>true,
	    ),
	    array(
	    	"name"=>"tags",
	    	"tablename"=>"tags",
	    	"type"=>"autocomplete",
	    	"contenttype"=>"tag",
	    	"multiple"=>true,
	    ),
	    /*array(
	    	"name"=>"section",
	    	"tablename"=>"site_sections",
	    	"contenttype"=>"sections",
	    	"type"=>"select",
	    ),*/
	);

	public function __construct() {
		parent::__construct("article");
	}
}

?>