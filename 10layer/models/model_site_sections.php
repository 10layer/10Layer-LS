<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Model_Site_Sections class.
 * 
 * @extends Model_Content
 * @package 10Layer
 * @subpackage Models
 */
class Model_Site_Sections extends Model_Content {
	public $order_by=array(
		"title ASC",
		"start_date"
	);
	
	public $fields=array(
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
	    	"name"=>"start_date",
	    	"tablename"=>"content",
	    	"type"=>"hidden",
	    ),
	    array(
	    	"name"=>"end_date",
	    	"tablename"=>"content",
	    	"type"=>"hidden",
	    ),
	    array(
	    	"name"=>"live",
	    	"tablename"=>"content",
	    	"type"=>"hidden",
	    ),
	    array(
	    	"name"=>"auto",
	    	"type"=>"boolean",
	    ),
	    array(
	    	"name"=>"child_sections",
	    	"type"=>"select",
	    	"link"=>"true",
	    	"contenttype"=>"section",
	    	"tablename"=>"site_sections",
	    	"multiple"=>true,
	    ),
	    array(
	    	"name"=>"zones",
	    	"type"=>"autocomplete",
	    	"multiple"=>true,
			"contenttype"=>"zones",
			"tablename"=>"site_zones",
	    ),
	    array(
	    	"name"=>"articles",
	    	"tablename"=>"article",
	    	"contenttype"=>"article",
	    	"type"=>"autocomplete",
	    	"multiple"=>true,
	    ),
	);

	public function __construct() {
		parent::__construct("section");
	}
}

?>