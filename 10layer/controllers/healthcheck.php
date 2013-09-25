<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Healthcheck class
 * 
 * @extends Controller
 */
 
class Healthcheck extends CI_Controller {
	/**
	 * __construct function.
	 * 
	 * @access public
	 * @return void
	 */
	public function __construct() {
		parent::__construct();
	}

	public function index() {
		
	}

	public function content_types($repair = false) {
		$problems = array();
		$_repair = false;
		if ($repair == "repair") {
			$_repair = true;
		}
		$cts = $this->mongo_db->get("content_types");
		$problem_ct = array();
		for($ct_count = 0; $ct_count < sizeof($cts); $ct_count++) {
			for($field_count = 0; $field_count < sizeof($cts[$ct_count]->fields); $field_count++) {
				$field = $cts[$ct_count]->fields[$field_count];
				$fixed_options = array();
				foreach($field["options"] as $key => $option) {
					if (empty($option)) {
						$problems["option_array"][$cts[$ct_count]->_id][$field["name"]] = "Option ".$key;
					} else {
						$fixed_options[$key] = $option;
					}
				}
				if ($_repair) {
					$cts[$ct_count]->fields[$field_count]["options"] = $fixed_options;
				}
			}
		}
		if ($_repair) {
			// print "Fixing problems...";
			$this->mongo_db->delete_all("content_types");
			foreach($cts as $ct) {
				$this->mongo_db->insert("content_types", $ct);
			}
			// print "Fixed problems...";
		}
		return $problems;
	}

}

/* End of file healthcheck.php */
/* Location: .//Volumes/Macintosh HD/Users/jason/Sites/local.cms.tasc.org/10layer/controllers/healthcheck.php */