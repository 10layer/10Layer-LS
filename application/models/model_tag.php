<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Model_Tag extends Model_Content {
	public $order_by=array(
		"title ASC",
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
			"hidden"=>true,
		),
		array(
			"name"=>"urlid",
			"tablename"=>"content",
			"type"=>"hidden",
			"transformations"=>array(
			"copy"=>"title",
			"urlid"=>array("content.urlid",false)
		)
	),
);

	public function __construct() {
		parent::__construct("tag");
	}
}

?>