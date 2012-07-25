<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

	require_once(APPPATH.'third_party/10layer/system/TL_Content_Library.php');
	/**
	 * 10Layer Search Class
	 *
	 * This class handles Searching of documents stored by the CMS
	 *
	 * @package		10Layer
	 * @subpackage	Libraries
	 * @category	Libraries
	 * @author		Jason Norwood-Young
	 * @link		http://10layer.com
	 */
	
	class Search extends TL_Content_Library {
		
		/**
		 * Constructor
		 *
		 * Links to CodeIgniter object
		 *
		 * @access	public
		 */
		 
		public $result=array();
		public $count=0;
		
		public function __construct() {
			parent::__construct();
		}
	
		public function dosearch($type,$searchstr,$limit=100,$start=0) {
			$ci=&get_instance();
			$this->count=$ci->model_content->smart_count($type,$searchstr);
			
			$this->result=$ci->model_content->smart_search($type,$searchstr,$limit,$start);
			//print $ci->db->last_query();
			return(array("count"=>$this->count, "docs"=>$this->result));
		}
		
		public function suggest($type,$searchstr,$limit) {
			$ci=&get_instance();
			if (is_array($type)) {
				return $ci->model_content->suggest_broad($type,$searchstr,$limit);
			} elseif ($type=="all") {
				return $ci->model_content->suggest_all($searchstr,$limit);
			} else {
				return $ci->model_content->suggest($type,$searchstr,$limit);
			}
		}
		
		public function deep_suggest($type,$searchstr,$limit) {
			$ci=&get_instance();
			if (is_array($type)) {
				return $ci->model_content->deep_suggest_broad($type,$searchstr,$limit);
			} elseif ($type=="all") {
				return $ci->model_content->deep_suggest_all($searchstr,$limit);
			} else {
				return $ci->model_content->deep_suggest($type,$searchstr,$limit);
			}
		}
		
		
		function smart_search($type,$searchstr,$limit)
		{
			$ci=&get_instance();
			return $ci->model_content->smart_search($type,$searchstr,$limit);
		}
	}
?>