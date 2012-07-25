<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

	require_once('10layer/system/TL_Content_Library.php');

	/**
	 * 10Layer Checkout Class
	 *
	 * This class handles checkouts of content stored by the CMS to avoid multiple edit clashes
	 *
	 * @package		10Layer
	 * @subpackage	Libraries
	 * @category	Libraries
	 * @author		Jason Norwood-Young
	 * @link		http://10layer.com
	 */
	
	class Checkout extends TL_Content_Library {
				
		public function __construct() {
			parent::__construct();
			$action=$this->ci->uri->segment(1);
			if (($action=="edit") && (!empty($this->urlid))) {
				//$this->lock();
			}
		}
		
		public function lock() {
			$this->set(array("lock"=>true));
			$type=$this->ci->model_content->checkContentType($this->urlid);
			$this->ci->messaging->post_action("update_content",array($type->urlid,$this->urlid));
		}
		
		public function unlock() {
			$this->set(array("lock"=>false));
			
			$type=$this->ci->model_content->checkContentType($this->urlid);
			$this->ci->messaging->post_action("update_content",array($type->urlid,$this->urlid));
		}
		
		public function check($urlid=false, $content_type=false) {
			if (!empty($urlid)) {
				$this->urlid=$urlid;
			}
			if (!empty($content_type)) {
				$this->type=$content_type;
			}
			
			$result=$this->get();
			if (!isset($result->lock)) {
				return false;
			}
			return $result->lock;
		}
	}
?>