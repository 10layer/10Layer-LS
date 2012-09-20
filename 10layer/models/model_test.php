<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Model_Test class.
 * 
 * @extends Model_Content
 * @package 10Layer
 * @subpackage Models
 */
class Model_Test extends Model_Content {
	
	public $fields=array(
	    array(
	    	"name"=>"autocomplete",
	    	"type"=>"autocomplete",
	    	"contenttype"=>"tag",
	    ),
	    array(
	    	"name"=>"boolean",
	    	"type"=>"boolean",
	    ),
	    array(
	    	"name"=>"cdn",
	    	"type"=>"cdn",
	    ),
		array(
			"name"=>"checkbox",
			"type"=>"checkbox",
		),
		array(
			"name"=>"date",
			"type"=>"date",
		),
		array(
			"name"=>"datetime",
			"type"=>"datetime",
		),
		array(
			"name"=>"deepsearch",
			"type"=>"deepsearch",
			"contenttype"=>"articles",
		),
		
		array(
			"name"=>"external",
			"type"=>"external",
			"options"=>"http://local.10layerls.com/resources/tests/external_test.html",
		),
		array(
			"name"=>"file",
			"type"=>"file",
		),
		array(
			"name"=>"hidden",
			"type"=>"hidden",
		),
		array(
			"name"=>"image",
			"type"=>"image",
		),
		array(
			"name"=>"nesteditems",
			"type"=>"nesteditems",
		),
		array(
			"name"=>"password",
			"type"=>"password",
		),
		array(
			"name"=>"radio",
			"type"=>"radio",
			"options"=>array("option 1", "option 2", "option 3"),
		),
		array(
			"name"=>"readonly",
			"type"=>"readonly",
		),
		array(
			"name"=>"reverse",
			"type"=>"reverse",
		),
		array(
			"name"=>"rich",
			"type"=>"rich",
		),
		array(
			"name"=>"select",
			"type"=>"select",
			"contenttype"=>"tag_types"
		),
		array(
			"name"=>"text",
			"type"=>"text",
		),
		array(
			"name"=>"textarea",
			"type"=>"textarea",
		),
	);

	public function __construct() {
		parent::__construct("page");
	}
}

?>