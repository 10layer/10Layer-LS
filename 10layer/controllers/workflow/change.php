<?php
	/**
	 * Workflow Change class
	 * 
	 * @extends CI_Controller
	 * @package 10Layer
	 * @subpackage Controllers
	 */
	class Change extends CI_Controller {

		/**
		 * __construct function.
		 * 
		 * @access public
		 * @return void
		 */
		public function __construct() {
			parent::__construct();
		}
		
		public function advance($content_type, $urlid) {
			$this->versions->bump_major_version();
			//Tell the world
			$this->messaging->post_action("update_content",array($content_type,$urlid));
			print json_encode(array("error"=>false, "major_version"=>$this->versions->get_major_version()));
		}
		
		public function revert($content_type, $urlid) {
			$this->versions->reduce_major_version();
			//Tell the world
			$this->messaging->post_action("update_content",array($content_type,$urlid));
			print json_encode(array("error"=>false, "major_version"=>$this->versions->get_major_version()));
		}
		
		public function status($content_type, $urlid) {
			$mv=$this->versions->get_major_version();
			if (is_numeric($content_type)) {
				$query=$this->db->get_where("content_types",array("id"=>$content_type));
			} else {
				$query=$this->db->get_where("content_types",array("urlid"=>$content_type));
			}
			if ($query->num_rows()==0) {
				show_error("Could not find content type $content_type");
				return false;
			}
			$workflow=$this->model_workflow->getByContentType($query->row()->id);
			for($x=0; $x<sizeof($workflow); $x++) {
				if ($workflow[$x]->major_version==$mv) {
					$current=$workflow[$x];
					print '<div class="bold">Currently at '.$current->name."</div><br />";
					if ($x>0) {
						$prev=$workflow[$x-1];
						print '<button id="workflow_revert" class="ui-button-text-icons ui-button ui-widget ui-state-default ui-corner-all " role="button" aria-disabled="false"><span class="ui-button-text"><span class="ui-button-icon-primary ui-icon ui-icon-arrowreturnthick-1-w"></span>Revert to '.$prev->name."</button><br /><br />";
					}
					if (isset($workflow[$x+1])) {
						$next=$workflow[$x+1];
						print '<button id="workflow_next" class="ui-button-text-icons ui-button ui-widget ui-state-default ui-corner-all " role="button" aria-disabled="false"><span class="ui-button-text"><span class="ui-button-icon-primary ui-icon ui-icon-arrowreturnthick-1-e"></span>Advance to '.$next->name."</button><br />";
					}
				}
			}
		}
		
		public function togglelive($content_type, $urlid) {
			$contentobj=$this->model_content->getByIdORM($urlid, $content_type);
			//print_r($contentobj->fields);
			//die();
			$contentobj->fields["live"]->value=!$contentobj->fields["live"]->value;
			$contentobj->update();
			print json_encode(array("live"=>$contentobj->fields["live"]->value));
			return true;
		}
	}

/* End of file change.php */
/* Location: ./system/application/controllers/workflow/change */