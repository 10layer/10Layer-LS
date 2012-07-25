<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Model_Tagtype class.
 * 
 * @extends Model_Content
 * @package 10Layer
 * @subpackage Models
 */
class Model_Tagtype extends Model_Content {
	public $order_by=array(
		"title DESC",
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
	    	"name"=>"tag",
	    	"tablename"=>"tag",
	    	"label"=>"Tags",
	    	"type"=>"autocomplete",
	    	"contenttype"=>"tag",
	    	"multiple"=>true,
	    ),
	);

	public function __construct() {
		parent::__construct("tagtype");
	}
}

?>