<?php
	/**
	 * ContentContent_Fix class
	 *
	 * Fixes content_content to add fieldnames where applicable
	 * 
	 * @extends Controller
	 * @package 10Layer
	 * @subpackage Controllers
	 */
	class ContentContent_Fix extends CI_Controller {

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
			$starttime=microtime(true);
			$content_types=$this->model_content->get_content_types();
			$this->db->where("fieldname","");
			$this->db->limit(1000);
			$query=$this->db->get("content_content");
			print "Found ".$query->num_rows()." links without fieldname. Starting to make it all better...<br />";
			$x=0;
			$del=0;
			//print_r($query->row());
			foreach($query->result() as $row) {
				$this->db->select("content.id, content_types.contenttype, content_types.model");
				$this->db->where("content.id",$row->content_id);
				$this->db->join("content_types", "content_types.id=content.content_type_id");
				$query=$this->db->get("content");
				if ($query->num_rows()==0) { //Someting very wrong here
					print "Deleting ".$row->id;
					$this->db->where("id",$row->id);
					$this->db->delete("content_content");
					$del++;
					$found=true;
				} else {
					$content=$query->row();
					$model=$content->model;
					$this->load->model($model);
					//$fields=$this->{$model}->getByIdORM($row->content_id)->getFields();
					$data=$this->{$model}->getByIdORM($row->content_id)->getData();
					//print_r($data);
					
					//print_r($data);
					//print_r($row);
					$found=false;
					foreach($data as $key=>$val) {
						//print "$key => $val<br />\n";
						if (is_array($val)) {
							foreach($val as $v) {
								//print "$v == {$row->content_link_id}\n";
								if ($v==$row->content_link_id) {
									//print "Matched $v for $key\n";
									$this->db->where("id",$row->id);
									$this->db->update("content_content",array("fieldname"=>$key));
									$found=true;
									//break;
								}
							}
						} else {
							if ($val==$row->content_link_id) {
								$this->db->where("id",$row->id);
								$this->db->update("content_content",array("fieldname"=>$key));
								$found=true;
								//break;
							}
						}
						
					}
				}
				if (!$found) {
					//Okay, this looks like a reverse link.
					$this->db->select("content_types.urlid");
					$this->db->where("content.id",$row->content_link_id);
					$this->db->join("content_types", "content_types.id=content.content_type_id");
					$query=$this->db->get("content");
					if ($query->num_rows()>0) {
						$this->db->where("id",$row->id);
						$this->db->update("content_content",array("fieldname"=>$query->row()->urlid));
						$found=true;
					}
				}
				if (!$found) { //This link shouldn't exist. It is a perversion of nature! Kill it dead!
					$this->db->where("id",$row->id);
					$this->db->delete("content_content");
					$del++;
				}
				$x++;
			}
			$endtime=microtime(true);
			$timediff=($endtime-$starttime);
			print "Finished converting $x rows. Deleted $del rows. Took $timediff (".($x/$timediff)."/s)<br />";
		}
	}
?>