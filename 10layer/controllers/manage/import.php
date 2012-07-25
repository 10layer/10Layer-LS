<?php
	/**
	 * Import class
	 * 
	 * @extends CI_Controller
	 * @package 10Layer
	 * @subpackage Controllers
	 */
	class Import extends CI_Controller {
		protected $mapping=array();
		protected $limits=array();
		protected $importdb;
		/**
		 * __construct function.
		 * 
		 * @access public
		 * @return void
		 */
		public function __construct() {
			parent::__construct();
			
		}
		
		public function tdmimport() {
			#$this->limits[]=array("type_id"=>1);
			$this->mapping=array(
				"01content"=>array(
					"headline"=>"content.title",
					"urlid"=>"content.urlid",
					"modified"=>"content.last_modified",
					"created"=>"content.timestamp",
					"live"=>"content.live",
					"startdate"=>"content.start_date",
					"'2100-01-01'"=>"content.end_date",
					"'1'"=>"content.content_type_id"
				),
				"02articles"=>array(
					"%content%"=>"articles.content_id",
					"blurb"=>"articles.blurb",
					"body"=>"articles.body",
					"placeline"=>"articles.placeline"
				)
			);
			
			$db['hostname'] = "localhost";
			$db['username'] = "root";
			$db['password'] = "##%Ad81@$";
			$db['database'] = "mg";
			$db['dbdriver'] = "mysqli";
			$db['dbprefix'] = "";
			$db['pconnect'] = FALSE;
			$db['db_debug'] = TRUE;
			$db['cache_on'] = FALSE;
			$db['cachedir'] = "";
			$db['char_set'] = "utf8";
			$db['dbcollat'] = "utf8_general_ci";
			$ids=$this->doImport($db,"articles",100);
			foreach($ids as $id) {
				set_time_limit(10);
				try {
					#$this->import_photos($id["oldid"], $id["newid"]);
				} catch(Exception $e) {
					print "Failed on pic for contentid ".$id["newid"]."<br />\n";
				}
			}
			print "All done!";
		}
		
		public function columnimport() {
			$this->limits[]=array("type_id"=>3);
			$this->mapping=array(
				"01content"=>array(
					"headline"=>"content.title",
					"urlid"=>"content.urlid",
					"date_edited"=>"content.last_modified",
					"date_created"=>"content.timestamp",
					"live"=>"content.live",
					"start_date"=>"content.start_date",
					"'2100-01-01'"=>"content.end_date",
					"'6'"=>"content.content_type_id",
					"'3'"=>"content.major_version",
					"'1'"=>"content.minor_version",
				),
				"02column"=>array(
					"%content%"=>"columns.content_id",
					"blurb"=>"columns.blurb",
					"body"=>"columns.body"
				)
			);
			
			$db['hostname'] = "localhost";
			$db['username'] = "10layer";
			$db['password'] = "Honstj";
			$db['database'] = "dailymaverick_import";
			$db['dbdriver'] = "mysqli";
			$db['dbprefix'] = "";
			$db['pconnect'] = FALSE;
			$db['db_debug'] = TRUE;
			$db['cache_on'] = FALSE;
			$db['cachedir'] = "";
			$db['char_set'] = "utf8";
			$db['dbcollat'] = "utf8_general_ci";
			$ids=$this->doImport($db,"articles",5000);
			foreach($ids as $id) {
				$this->import_photos($id["oldid"], $id["newid"]);
				$this->linkauthors($id["oldid"], $id["newid"]);
			}
		}
		
		protected function linkauthors($oldid, $newid) {
			$query=$this->importdb->get_where("articles",array("id"=>$oldid));
			$column=$query->row();
			$query=$this->importdb->get_where("authors",array("id"=>$column->author_id));
			$author=$query->row();
			$newauthor=$this->db->get_where("content",array("urlid"=>$author->urlid));
			$this->db->insert("content_content", array("content_id"=>$newid, "content_link_id"=>$newauthor->row()->id));
		}
		
		public function authorimport() {
			//$this->limits[]=array("type_id"=>3);
			$this->mapping=array(
				"01content"=>array(
					"name"=>"content.title",
					"urlid"=>"content.urlid",
					"'2011-06-30'"=>"content.last_modified",
					"'2011-06-30'"=>"content.timestamp",
					"'1'"=>"content.live",
					"'2011-06-30'"=>"content.start_date",
					"'2100-01-01'"=>"content.end_date",
					"'4'"=>"content.content_type_id",
					"'3'"=>"content.major_version",
					"'1'"=>"content.minor_version",
				),
				"02author"=>array(
					"%content%"=>"authors.content_id",
					"name"=>"authors.name",
					"email"=>"authors.email",
					"photo"=>"authors.pic",
                                        "id"=>"authors.legacy_id")
			);
			
			$db['hostname'] = "localhost";
			$db['username'] = "root";
			$db['password'] = "##%Ad81@$";
			$db['database'] = "mg";
			$db['dbdriver'] = "mysqli";
			$db['dbprefix'] = "";
			$db['pconnect'] = FALSE;
			$db['db_debug'] = TRUE;
			$db['cache_on'] = FALSE;
			$db['cachedir'] = "";
			$db['char_set'] = "utf8";
			$db['dbcollat'] = "utf8_general_ci";
			$ids=$this->doImport($db,"authors",6267);
			//print_r($ids);
			foreach($ids as $id) {
				//$this->import_authorphotos($id["oldid"], $id["newid"]);
				
			}
			//$this->import_photos($row->id, $newid);
		}
		
		public function photoimport() {
			$db['hostname'] = "localhost";
			$db['username'] = "10layer";
			$db['password'] = "Honstj";
			$db['database'] = "dailymaverick_import";
			$db['dbdriver'] = "mysqli";
			$db['dbprefix'] = "";
			$db['pconnect'] = FALSE;
			$db['db_debug'] = TRUE;
			$db['cache_on'] = FALSE;
			$db['cachedir'] = "";
			$db['char_set'] = "utf8";
			$db['dbcollat'] = "utf8_general_ci";
			$this->importdb=$this->load->database($db,TRUE);
			$importdir="resources/uploads/pictures/import/";
			$this->importdb->select("photos.*");
			$this->importdb->from("photos");
			$query=$this->importdb->get();
			foreach($query->result() as $row) {
				$newurlid=$row->urlid."-photo";
				$q=$this->db->get_where("content",array("urlid"=>$newurlid));
				if (empty($q->row()->id)) {
					$date=strtotime($row->date_created);
					$dir="./resources/uploads/pictures/original/".date("Y",$date)."/".date("m",$date)."/".date("d",$date)."/";
					if (!file_exists($dir)) {
						if (!mkdir($dir, 0755, true)) {
							print "<b>Failed to create $dir</b><br />";
						}
					}
					if (file_exists($importdir.$row->filename)) {
						//print "Photo ".$row->title." exists<br />";
						
						
						copy($importdir.$row->filename, $dir.$row->filename);
					} else {
						if (!file_exists($dir.$row->filename)) {
							$url="http://www.thedailymaverick.co.za/photo/resize/".$row->urlid."/5000/5000";
							$s=file_get_contents($url);
							file_put_contents($dir.$row->filename,$s);
						}
					}
					$dbdata=array(
						"urlid"=>$newurlid,
						"content_type_id"=>2,
						"title"=>$row->title,
						"last_modified"=>date("c"),
						"timestamp"=>$row->date_created,
						"start_date"=>$row->date_created
					);
					$this->db->insert("content",$dbdata);
					$picid=$this->db->insert_id();
					$dir=$dir="/resources/uploads/pictures/original/".date("Y",$date)."/".date("m",$date)."/".date("d",$date)."/";
					$dbdata=array(
						"content_id"=>$picid,
						"filename"=>$dir.$row->filename
					);
					$this->db->insert("pictures",$dbdata);
					print "New photo: $newurlid<br />";
					flush();
				}
			}
			print "All done!";
		}
		
		protected function doImport($db, $table="articles", $limit=100) {
			$ids=array();
			$this->importdb=$this->load->database($db,TRUE);
			$this->importdb->limit($limit);
			foreach($this->limits as $limit) {
				$this->importdb->where($limit);
			}
			$query=$this->importdb->get($table);
			foreach($query->result() as $row) {
				set_time_limit(30);
				$existing=$this->db->get_where("content",array("urlid"=>$row->urlid));
				$data=$this->make_map($row);
				if (!empty($existing->row()->id)) {
					$this->db->where("urlid",$row->urlid);
					$this->db->delete("content");
					//$this->db->where("content_id",$existing->row()->id);
					//$this->db->delete("articles");
					$this->db->where("content_id",$existing->row()->id);
					$this->db->delete("content_content");
					$this->db->where("content_id",$existing->row()->id);
					$this->db->delete("content_platforms");
					$deps=$this->getDeps($data);
					foreach($deps as $key=>$val) {
						$this->db->where("content_id",$existing->row()->id);
						$this->db->delete($key);
					}
					
				}
				if (isset($row->name)) {
					print $row->name."<br />";
				} else {
					print $row->headline."<br />";
				}
				
				$newid=$this->doinsert($data);
				/*} else {
					print $row->headline." exists. Updating.<br />";
					$data=$this->make_map($row);
					$this->doupdate($data,array("content.id"=>$existing->row()->id));
					$newid=$existing->row()->id;
				}*/
				$this->db->insert("content_platforms",array("content_id"=>$newid, "platform_id"=>1));
				
				//print_r($row);
				$ids[]=array("oldid"=>$row->id, "newid"=>$newid);
				flush();
			}
			return $ids;
		}
		
		protected function getDeps($data) {
			$deps=array();
			foreach($data as $key=>$item) {
				if (strlen($item)>0 && ($item[0]=="%")) {
					$table=str_replace("%","",$item);
					$parts=explode(".",$key);
					$deps[$parts[0]]=$table;
				}
			}
			return $deps;
		}
		
		protected function doinsert($data) {
			//First find all tables
			$tables=array();
			foreach($data as $key=>$item) {
				$parts=explode(".",$key);
				if (!in_array($parts[0],$tables)) {
					$tables[]=$parts[0];
				}
			}
			//Find the primary tables that we need to do inserts on first
			$deps=$this->getDeps($data);
			$insertids=array();
			//Now we do all our primary inserts
			foreach($deps as $dep) {
				$sqldata=array();
				foreach($data as $key=>$item) {
					$parts=explode(".",$key);
					if ($dep==$parts[0]) {
						$sqldata[$key]=$item;
					}
				}
				$this->db->insert($dep, $sqldata);
				$insertids[$dep]=$this->db->insert_id();
			}
			//print_r($deps);
			//And now we do our secondary inserts
			foreach($deps as $dep=>$val) {
				$sqldata=array();
				foreach($data as $key=>$item) {
					$parts=explode(".",$key);
					
					if ($dep==$parts[0]) {
						if ((strlen($item)>0) && ($item[0]!="%")) {
							$sqldata[$key]=$item;
						} elseif ((strlen($item)>0) && ($item[0]=="%")) {
							$sqldata[$key]=$insertids[str_replace("%","",$item)];
						}
					}
				}
				$this->db->insert($dep, $sqldata);
			}
			return $insertids["content"];
		}
		
		protected function doupdate($data, $id) {
			//First find all tables
			$tables=array();
			foreach($data as $key=>$item) {
				$parts=explode(".",$key);
				if (!in_array($parts[0],$tables)) {
					$tables[]=$parts[0];
				}
			}
			//Find the primary tables that we need to do inserts on first
			$deps=array();
			foreach($data as $key=>$item) {
				if (strlen($item)>0 && ($item[0]=="%")) {
					$table=str_replace("%","",$item);
					$parts=explode(".",$key);
					$deps[$parts[0]]=$table;
				}
			}
			$insertids=array();
			//Now we do all our primary inserts
			foreach($deps as $dep) {
				$sqldata=array();
				foreach($data as $key=>$item) {
					$parts=explode(".",$key);
					if ($dep==$parts[0]) {
						$sqldata[$key]=$item;
					}
				}
				$parts=explode(".",key($id));
				if ($parts[0]==$dep) {
					$this->db->where(key($id),$id[key($id)]);
					$this->db->update($dep, $sqldata);
					$insertids[$dep]=$id;
				}
			
			}
			//print_r($deps);
			//And now we do our secondary inserts
			foreach($deps as $dep=>$val) {
				$sqldata=array();
				$where=array();
				foreach($data as $key=>$item) {
					$parts=explode(".",$key);
					
					if ($dep==$parts[0]) {
						if ((strlen($item)>0) && ($item[0]!="%")) {
							$sqldata[$key]=$item;
						} elseif ((strlen($item)>0) && ($item[0]=="%")) {
							$where[$key]=array_pop($insertids[str_replace("%","",$item)]);
						}
					}
				}
				if (!empty($where)) {
					$this->db->where(key($where),$where[key($where)]);
					$this->db->update($dep, $sqldata);
				}
				//$this->db->insert($dep, $sqldata);
			}
		}
		
		function import_photos($origid, $newid) {
			$importdir="resources/uploads/pictures/import/";
			$this->importdb->select("photos.*");
			$this->importdb->from("photos");
			$this->importdb->join("articles_photos_link","articles_photos_link.photo_id=photos.id");
			$this->importdb->where("articles_photos_link.article_id",$origid);
			$query=$this->importdb->get();
			foreach($query->result() as $row) {
				$newurlid=$row->urlid."-photo";
				$q=$this->db->get_where("content",array("urlid"=>$newurlid));
				if (!empty($q->row()->id)) {
					$picid=$q->row()->id;
					$this->db->where("id",$picid);
					$this->db->delete("content");
					$this->db->delete("content_content",array("content_link_id"=>$picid));
				}
					$date=strtotime($row->date_created);
					$dir="./resources/uploads/pictures/original/".date("Y",$date)."/".date("m",$date)."/".date("d",$date)."/";
					if (!file_exists($dir)) {
						if (!mkdir($dir, 0755, true)) {
							print "<b>Failed to create $dir</b><br />";
						}
					}
					if (file_exists($importdir.$row->filename)) {
						//print "Photo ".$row->title." exists<br />";
						
						
						copy($importdir.$row->filename, $dir.$row->filename);
					} else {
						if (!file_exists($dir.$row->filename)) {
							$url="http://www.thedailymaverick.co.za/photo/resize/".$row->urlid."/5000/5000";
							$s=file_get_contents($url);
							file_put_contents($dir.$row->filename,$s);
						}
					}
					$dbdata=array(
						"urlid"=>$newurlid,
						"content_type_id"=>2,
						"title"=>$row->title,
						"last_modified"=>date("c"),
						"timestamp"=>$row->date_created,
						"start_date"=>$row->date_created
					);
					$this->db->insert("content",$dbdata);
					$picid=$this->db->insert_id();
					$dir=$dir="/resources/uploads/pictures/original/".date("Y",$date)."/".date("m",$date)."/".date("d",$date)."/";
					$dbdata=array(
						"content_id"=>$picid,
						"filename"=>$dir.$row->filename
					);
					$this->db->insert("pictures",$dbdata);
					$this->db->insert("content_content",array("content_id"=>$newid, "content_link_id"=>$picid));
			
					
				
			}
		}
		
		function import_authorphotos($origid, $newid) {
			//print "Importing <br />";
			$importdir="resources/uploads/pictures/import/";
			$this->importdb->select("photos.*");
			$this->importdb->from("photos");
			$this->importdb->join("authors","authors.photo_id=photos.id");
			$this->importdb->where("authors.id",$origid);
			$query=$this->importdb->get();
			foreach($query->result() as $row) {
				$newurlid=$row->urlid."-photo";
				$q=$this->db->get_where("content",array("urlid"=>$newurlid));
				if (!empty($q->row()->id)) {
					$picid=$q->row()->id;
					$this->db->where("id",$picid);
					$this->db->delete("content");
					$this->db->delete("content_content",array("content_link_id"=>$picid));
				}
					$date=strtotime($row->date_created);
					$dir="./resources/uploads/pictures/original/".date("Y",$date)."/".date("m",$date)."/".date("d",$date)."/";
					if (!file_exists($dir)) {
						if (!mkdir($dir, 0755, true)) {
							print "<b>Failed to create $dir</b><br />";
						}
					}
					if (file_exists($importdir.$row->filename)) {
						//print "Photo ".$row->title." exists<br />";
						//print "Found pic<br />";
						copy($importdir.$row->filename, $dir.$row->filename);
					} else {
						if (!file_exists($dir.$row->filename)) {
							print "Missing ".$row->urlid;
							//return false;
							$url="http://www.thedailymaverick.co.za/photo/resize/".$row->urlid."/5000/5000";
							$s=file_get_contents($url);
							file_put_contents($dir.$row->filename,$s);
						}
					}
					$dbdata=array(
						"urlid"=>$newurlid,
						"content_type_id"=>5,
						"title"=>$row->title,
						"last_modified"=>date("c"),
						"timestamp"=>$row->date_created,
						"start_date"=>$row->date_created
					);
					$this->db->insert("content",$dbdata);
					$picid=$this->db->insert_id();
					$dir=$dir="/resources/uploads/pictures/original/".date("Y",$date)."/".date("m",$date)."/".date("d",$date)."/";
					$dbdata=array(
						"content_id"=>$picid,
						"filename"=>$dir.$row->filename
					);
					$this->db->insert("authorphotos",$dbdata);
					$this->db->insert("content_content",array("content_id"=>$newid, "content_link_id"=>$picid));
			
					print $this->db->last_query();
				
			}
		}
		
		protected function make_map($row) {
			$newdata=array();
			foreach($this->mapping as $contenttable) {
				foreach($contenttable as $source=>$target) {
					if (($source[0]!='%') && ($source[0]!="'")) {
						$data=$row->$source;
						$newdata[$target]=$data;
					} elseif($source[0]=='%') {
						$newdata[$target]=$source;
					} else {
						$newdata[$target]=str_replace("'","",$source);
					}
				}
			}
			return $newdata;
		}
	}

/* End of file import.php */
/* Location: ./system/application/controllers/manage/ */
