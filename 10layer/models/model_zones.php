<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Model_Zones class.
 * 
 * @extends Model_Content
 * @package		10Layer
 * @subpackage	Models
 * @category	Models
 * @author		Jason Norwood-Young
 * @link		http://10layer.com
 */
class Model_Zones extends Model_Content {
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
	    	"name"=>"content_types",
	    	"type"=>"text",
	    ),
	    array(
	    	"name"=>"position",
	    	"type"=>"text",
	    ),
	    array(
	    	"name"=>"max_count",
	    	"type"=>"text",
	    ),
	    array(
	    	"name"=>"min_count",
	    	"type"=>"text",
	    ),
	    array(
	    	"name"=>"auto_where",
	    	"type"=>"text",
	    ),
	    array(
	    	"name"=>"auto_limit",
	    	"type"=>"text",
	    ),
	    array(
	    	"name"=>"auto_order_by",
	    	"type"=>"text",
	    ),
	    array(
	    	"name"=>"auto_join_table",
	    	"type"=>"text",
	    ),
	    array(
	    	"name"=>"auto_join_direction",
	    	"title"=>"One-to-many",
	    	"type"=>"checkbox",
	    ),
	    array(
	    	"name"=>"auto",
	    	"type"=>"boolean",
	    ),
	);

	public function __construct() {
		parent::__construct("zones");
	}
}

?>