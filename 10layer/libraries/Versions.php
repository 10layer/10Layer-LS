<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

	require_once('10layer/system/TL_Content_Library.php');
	/**
	 * 10Layer Versions Class
	 *
	 * This class handles versions of documents stored by the CMS
	 *
	 * @package		10Layer
	 * @subpackage	Libraries
	 * @category	Libraries
	 * @author		Jason Norwood-Young
	 * @link		http://10layer.com
	 */
	
	class Versions extends TL_Content_Library {
		
		/**
		 * Constructor
		 *
		 * Links to CodeIgniter object
		 *
		 * @access	public
		 */
		public function __construct() {
			parent::__construct();
		}
		
		
		/**
		 * Returns the current version of the content
		 *
		 * @access	public
		 * @return	real		The content version
		 */
		public function get_version($urlid=false, $content_type=false) {
			if (!empty($urlid)) {
				$this->urlid=$urlid;
			}
			if (!empty($content_type)) {
				$this->type=$content_type;
			}
			$query=$this->ci->db->get_where("content",array("urlid"=>$this->urlid));
			return (Real) $query->row()->major_version.".".$query->row()->minor_version;
			/*
			$result=$this->get();
			if (!isset($result->major_version) || !isset($result->minor_version)) {
				$this->set(array("major_version"=>0,"minor_version"=>1));
				return (Real) 0.1;
			}
			return (Real) $result->major_version.".".$result->minor_version;
			*/
		}
				
		/**
		 * Returns the current minor version of the content
		 *
		 * @access	public
		 * @return	int		The minor content version
		 */
		public function get_minor_version($urlid=false) {
			if (!empty($urlid)) {
				$this->urlid=$urlid;
			}
			$query=$this->ci->db->get_where("content",array("urlid"=>$this->urlid));
			if (!isset($query->row()->minor_version)) {
				return 0;
			}
			return (Int) $query->row()->minor_version;
			/*$result=$this->get();
			if (!isset($result->minor_version)) {
				$this->set(array("major_version"=>0,"minor_version"=>1));
				return (Int) 1;
			}
			return (Int) $result->minor_version;*/
		}
		
		/**
		 * Returns the current major version of the content
		 *
		 * @access	public
		 * @return	int		The major content version
		 */
		public function get_major_version($urlid=false) {
			if (!empty($urlid)) {
				$this->urlid=$urlid;
			}
			$query=$this->ci->db->get_where("content",array("urlid"=>$this->urlid));
			if (!isset($query->row()->major_version)) {
				return 0;
			}
			return (Int) $query->row()->major_version;
			//$result=$this->get();
			//return (Int) $result->major_version;
		}
			
		
		/**
		 * Saves the current version of the article and bumps the working minor version
		 *
		 * @access	public
		 * @return	array		The new version
		 */
		public function bump_minor_version($urlid=false) {
			if (!empty($urlid)) {
				$this->urlid=$urlid;
			}
			$this->_save_change_control();
			$minor_ver=$this->get_minor_version();
			$minor_ver++;
			$this->ci->db->where("urlid",$this->urlid);
			$this->ci->db->update("content",array("minor_version"=>$minor_ver));
			//$this->set(array("minor_version"=>$minor_ver));
		}
		
		/**
		 * Saves the current version of the article and bumps the working major version
		 *
		 * @access	public
		 * @return	array		The new version
		 */
		public function bump_major_version() {
			$this->_save_change_control();
			$major_ver=$this->get_major_version();
			$minor_ver=$this->get_minor_version();
			$major_ver=$this->nextMajorVer($major_ver);
			$minor_ver++;
			$this->ci->db->where("urlid",$this->urlid);
			$this->ci->db->update("content",array("minor_version"=>$minor_ver,"major_version"=>$major_ver));
			//$this->set(array("major_version"=>$major_ver, "minor_version"=>$minor_ver));
		}
		
		/**
		 * Saves the current version of the article and reduces the working major version
		 *
		 * @access	public
		 * @return	array		The new version
		 */
		public function reduce_major_version() {
			$this->_save_change_control();
			$major_ver=$this->get_major_version();
			$minor_ver=$this->get_minor_version();
			if ($major_ver>0) {
				$major_ver=$this->prevMajorVer($major_ver);
			}
			$minor_ver++;
			$this->ci->db->where("urlid",$this->urlid);
			$this->ci->db->update("content",array("minor_version"=>$minor_ver,"major_version"=>$major_ver));
			//$this->set(array("major_version"=>$major_ver, "minor_version"=>$minor_ver));
		}
		
		protected function nextMajorVer($major_ver) {
			$workflow=$this->ci->model_workflow->getByContentType($this->type_id);
			$major_ver++;
			if (isset($workflow[$major_ver])) {
				return $workflow[$major_ver]->major_version;	
			}
			return (Int) ($major_ver-1);
		}
		
		protected function prevMajorVer($major_ver) {
			$workflow=$this->ci->model_workflow->getByContentType($this->type_id);
			while(sizeof($workflow)>0) {
				if (array_pop($workflow)->major_version==$major_ver) {
					break;
				}
			}
			return array_pop($workflow)->major_version;
		}
		
		/**
		 * Saves the current version of the article and returns version info
		 *
		 * @access	protected
		 */
		protected function _save_change_control() {
			$olddata=$this->ci->model_content->getByIdORM($this->urlid)->getData();
			$oldid=$olddata->content_id;
			unset($olddata->content_id);
			unset($olddata->date_created);
			$olddata->date_created=date("c");
			$olddata->original_id=$oldid;
			$olddata->user_id=$this->ci->session->userdata("id");
			//$this->ci->db->insert($this->versions_table,$olddata);
			$olddata->type_id=$this->type_id;
			
			$this->ci->mongo_db->insert("tl_content_versions",(array) $olddata);
			//return array("minor_version"=>$olddata->minor_version, "major_version"=>$olddata->major_version);
			return true;
		}
	}