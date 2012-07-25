<?php
/**
 * Files class.
 * 
 * @extends CI_Controller
 * @package 10Layer
 * @subpackage Controllers
 */
class Files extends CI_Controller {

	public function __construct() {
		parent::__construct();
		$this->load->library("validation");
		$this->load->library("cdn");
		$this->cdn->init();
	}
	
	public function index() {
		$createbucket=$this->input->post("dobucketcreate");
		if (!empty($createbucket)) {
			if ($this->cdn->createBucket($this->input->post("bucketname"))) {
				$returndata["msg"]="Bucket created successfully";
			} else {
				$returndata["error"]=true;
				$returndata["info"]="";
				$returndata["msg"]="Bucket creation failed";
			}
			$data["msg"]=$returndata;
		}
		$buckets=$this->cdn->listBuckets();
		
		$data["buckets"]=$buckets;
		$data["menu1_active"]="manage";
		$this->load->view('templates/header',$data);
		$this->load->view("manage/files/list");
		$this->load->view("templates/footer");
	}
	
	public function bucket($bucketname) {
		$doupload=$this->input->post("doupload");
		if (!empty($doupload)) {
			$config['upload_path'] = './resources/uploads/';
			$config['allowed_types'] = 'gif|jpg|png|mp3|avi|mpg|mp4|m4v|flv|doc|pdf|odf|txt|htm|html';
			$this->load->library("upload",$config);
			if (!$this->upload->do_upload("uploadfile")) {
				$returndata["error"]=true;
				$returndata["info"]=$this->upload->display_errors();
				$returndata["msg"]="File Upload failed";
			} else {
				$uploaddata = $this->upload->data();
				$uri=$this->cdn->uploadFile($uploaddata["full_path"],$bucketname);
				$returndata["msg"]="File uploaded successfully";
				$returndata["info"]="File CDN URI: <a href='$uri' target='_blank'>$uri</a>";
			}
			$data["msg"]=$returndata;
		}
		$objects=$this->cdn->listObjects(rawurldecode($bucketname));
		$data["objects"]=$objects;
		$data["bucket"]=$bucketname;
		$data["menu1_active"]="manage";
		$this->load->view('templates/header',$data);
		$this->load->view("manage/files/bucket");
		$this->load->view("templates/footer");
	}
	
	public function delete_bucket($bucket) {
		$this->cdn->deleteBucket($bucket);
		redirect("/manage/files");
	}
	
	public function change_acl($acl,$bucket) {
		if ($acl=="private") {
			$this->cdn->makePrivate($bucket);
		} else {
			$this->cdn->makePublic($bucket);
		}
		redirect("/manage/files");
	}
	
	public function delete_object($bucket,$filename) {
		$this->cdn->deleteObject($filename,$bucket);
		redirect("/manage/files/bucket/$bucket");
	}
	
	public function output_object($bucket,$filename) {
		header("content-type: ".$this->cdn->getContentType($filename,$bucket));
		print $this->cdn->getFileContents($filename,$bucket);
	}
	
}
?>