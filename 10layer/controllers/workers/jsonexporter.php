<?php
	/**
	 * JsonExporter class
	 * 
	 * Used to export data sets to JSON objects, good for moving to NoSql db's for instance
	 *
	 * @extends Controller
	 */
	class JsonExporter extends CI_Controller {

		/**
		 * __construct function.
		 * 
		 * @access public
		 * @return void
		 */
		public function __construct() {
			parent::__construct();
		}
		
		protected function _content($content_type, $limit=2, $start=0) {
			$query=$this->db->get_where('content_types', array('urlid'=>$content_type));
			$result=array();
			if ($query->num_rows()==0) {
				show_error("Can't find content type $content_type");
			}
			$content_type=$query->row();
			$query=$this->db->from('content')->where('content_type_id', $content_type->id)->limit($limit, $start)->get();
			foreach($query->result() as $content) {
				$query2=$this->db->get_where($content_type->table_name, array('content_id'=>$content->id));
				$linktable=$query2->row();
				$obj=new stdClass();
				$obj->content_type=$content_type->urlid;
				foreach($content as $key=>$val) {
					$obj->$key=$val;
				}
				$obj->content_id=$content->id;
				foreach($linktable as $key=>$val) {
					$obj->$key=$val;
				}
				$query3=$this->db->select('content.urlid, content_content.fieldname, content_types.urlid AS content_type')->from('content_content')->join('content', 'content_content.content_link_id=content.id')->join('content_types', 'content.content_type_id=content_types.id')->where('content_content.content_id', $obj->content_id)->get();
				if ($query3->num_rows() < 100) {
					foreach($query3->result() as $link) {
						$fieldname=$link->fieldname;
						if (empty($fieldname)) {
							$fieldname=$link->content_type;
						}
						if (isset($obj->$fieldname)) {
							if (!is_array($obj->$fieldname)) {
								$tmp=$obj->$fieldname;
								$obj->$fieldname=array($tmp, $link->urlid);
							} else {
								array_push($obj->$fieldname, $link->urlid);
							}
						} else {
							$obj->$fieldname=$link->urlid;
						}
					}
				}
				$obj->id=$obj->urlid;
				unset($obj->urlid);
				$result[]=$obj;
			}
			return $result;
		}
		
		protected function _get_item($urlid) {
			$content=$this->db->get_where('content', array('urlid'=>$urlid))->row();
			$content_type=$this->db->get_where('content_types', array('id'=>$content->content_type_id))->row();
			$query2=$this->db->get_where($content_type->table_name, array('content_id'=>$content->id));
			$linktable=$query2->row();
			$obj=new stdClass();
			$obj->content_type=$content_type->urlid;
			foreach($content as $key=>$val) {
			    $obj->$key=$val;
			}
			$obj->content_id=$content->id;
			foreach($linktable as $key=>$val) {
			    $obj->$key=$val;
			}
			$count=$this->db->select('count(*) AS count')->from('content_content')->where('content_content.content_id', $obj->content_id)->get()->row()->count;
			if ($count < 100) {
				$query3=$this->db->select('content.urlid, content_content.fieldname, content_types.urlid AS content_type')->from('content_content')->join('content', 'content_content.content_link_id=content.id')->join('content_types', 'content.content_type_id=content_types.id')->where('content_content.content_id', $obj->content_id)->get();
			    foreach($query3->result() as $link) {
			    	$fieldname=$link->fieldname;
			    	if (empty($fieldname)) {
			    		$fieldname=$link->content_type;
			    	}
			    	if (isset($obj->$fieldname)) {
			    		if (!is_array($obj->$fieldname)) {
			    			$tmp=$obj->$fieldname;
			    			$obj->$fieldname=array($tmp, $link->urlid);
			    		} else {
			    			array_push($obj->$fieldname, $link->urlid);
			    		}
			    	} else {
			    		$obj->$fieldname=$link->urlid;
			    	}
			    }
			}
			$obj->id=$obj->urlid;
			unset($obj->urlid);
			return $obj;
		}
		
		/**
		 * export_item_couchdb function.
		 * 
		 * Export a single record into CouchDB.
		 * Usage: http://<hostname>/workers/jsonexporter/export_item/test/<urlid>
		 *
		 * @access public
		 * @param string $dbname Database target
		 * @param string $urlid UrlID to look up
		 * @return void
		 */
		public function export_item_couchdb($dbname, $urlid) {
			$obj=$this->_get_item($urlid);
			$options['host'] = "localhost"; 
			$options['port'] = 5984;
			$couch = new CouchSimple($options);
			$resp = $couch->send("PUT", "/$dbname/{$obj->id}", json_encode($obj));
			if (isset($result->error)) {
				print "Failed: {$result->reason}\n";
			} elseif ($result->ok) {
				print "Inserted: {$obj->id}\n";
			} else {
				//print_r(json_decode($resp));
			}
		}
		
		public function export_item_mongodb($dbname, $urlid) {
			$connection = new Mongo("mongodb://localhost");
			$db = $connection->selectDB($dbname);
			$obj=$this->_get_item($urlid);
			$obj->_id=$obj->id;
			unset($obj->id);
			try {
				$db->content->update(array("_id"=>$obj->_id), $obj);
				//$db->content->insert($obj); //Who needs safety?
				print_r($obj);
			} catch(Exception $e) {
				print_r($e);
			}
		}
		
		/**
		 * export_contenttype_couchdb function.
		 * 
		 * Exports all items of a certain content type to CouchDB
		 *
		 * @access public
		 * @param string $dbname
		 * @param string $content_type
		 * @param int $limit. (default: 100)
		 * @param int $start. (default: 0)
		 * @return void
		 */
		public function export_contenttype_couchdb($dbname, $content_type, $limit=100, $start=0) {
			$time_start=microtime(true);
			$options['host'] = "localhost"; 
			$options['port'] = 5984;
			$couch = new CouchSimple($options);
			$resp = $couch->send("PUT", "/$dbname");
			$data=$this->_content($content_type, $limit, $start);
			$tot=0;
			$success=0;
			$failed=0;
			foreach($data as $item) {
				//print "Inserting {$item->id}\n";
				$resp = $couch->send("PUT", "/$dbname/{$item->id}", json_encode($item));
				$result=json_decode($resp);
				if (isset($result->error)) {
					//print "Failed on {$item->id}. {$result->reason}\n";
					$failed++;
				} elseif ($result->ok) {
					//print "Inserted {$item->id}\n";
					$success++;
				}
				$tot++;
			}
			$time_end=microtime(true);
			$time=$time_end-$time_start;
			print "Attempted to insert $tot records. $success success, $failed failed. Took ".round($time,5)." secs.\n";
		}
		
		/**
		 * total_export_couchdb function.
		 * 
		 * Tries to export all content to CouchDB
		 *
		 * @access public
		 * @param mixed $dbname
		 * @param int $start. (default: 0)
		 * @return void
		 */
		public function total_export_couchdb($dbname, $start=0) {
			$this->db->save_queries = false;
			$perpage=1000;
			$time_start=microtime(true);
			$count=$this->db->select('count(*) AS count')->from('content')->get()->row()->count;
			$count=$count-$start;
			$parts=ceil($count/$perpage);
			$f=fopen("./application/logs/exportlog", "w");
			for($x=0; $x<$parts; $x++) {
				set_time_limit(10);
				$contents=$this->db->select('urlid, id, content_type_id')->from('content')->order_by('id')->limit($perpage, (($x*$perpage)+$start))->get();
				ob_start();
				foreach($contents->result() as $content) {
					fwrite($f, "{$content->id}\t{$content->urlid}\t{$content->content_type_id}\n");
					$this->export_item_couchdb($dbname, $content->urlid);
				}
				ob_end_clean();
			}
			fclose($f);
			/*$content_types=$this->db->get('content_types')->result();
			foreach($content_types as $content_type) {
				
				$count=$this->db->select('count(*) AS count')->from('content')->where('content_type_id', $content_type->id)->get()->row()->count;
				$pages=ceil($count/$perpage);
				print "=== Processing {$content_type->urlid} ($count items) ===\n";
				flush();
				for($x=0; $x<$pages; $x++) {
					$this->export_couchdb($dbname, $content_type->urlid, $perpage, $x);
				}
			}*/
			$time_end=microtime(true);
			$time=$time_end-$time_start;
			print "\n\nAll done. Took ".round($time)." seconds and processed ".$count." records (".round($count/$time)." records per second)\n\n";
		}
		
		/**
		 * fix_sections_couchdb function.
		 * 
		 * Reverses the polarity of the hyperdrive. Also reverses cardinality of sections->articles relationship.
		 *
		 * @access public
		 * @param mixed $dbname
		 * @return void
		 */
		public function fix_sections_couchdb($dbname) {
			$options['host'] = "localhost"; 
			$options['port'] = 5984;
			$couch = new CouchSimple($options);
			$sections=$this->db->get_where('content', array('content_type_id'=>11))->result();
			$sectioncount=0;
			$articlecount=0;
			$f=fopen("./application/logs/sectionfixlog", "w");
			foreach($sections as $section) {
				$articles=$this->db->select('content.urlid')->from('content_content')->join('content', 'content_content.content_link_id=content.id')->where('content_content.content_id', $section->id)->where('content.content_type_id',1)->get()->result();
				foreach($articles as $article) {
					$doc=json_decode($couch->send('GET', "/$dbname/{$article->urlid}"));
					$doc->section=$section->urlid;
					$couchresult=$couch->send('PUT', "/$dbname/{$article->urlid}", json_encode($doc));
					fwrite($f, "{$article->id}\t{$article->urlid}\n");
					$articlecount++;
				}
				$sectioncount++;
			}
			fclose($f);
			print "Updated $sectioncount sections and $articlecount articles";
		}
		
		public function fix_relationships_mongodb($dbname) {
			$connection = new Mongo("mongodb://localhost");
			$db = $connection->selectDB($dbname);
			$this->db->save_queries = false;
			$content_types=array('tag_type'=>9, 'source'=>22, 'section'=>11, 'specialreport'=>17); //tag_type, source, section, specialreport
			$tot=0;
			foreach($content_types as $content_type=>$content_type_id) {
				$x=0;
				$content=$this->db->get_where('content', array('content_type_id'=>$content_type_id))->result();
				foreach($content as $item) {
					$query=$this->db->select('content.urlid')->from('content_content')->join('content', 'content.id=content_content.content_link_id')->where('content_content.content_id', $item->id)->get();
					$links=$query->result();
					foreach($links as $item2) {
						$db->content->update(array("_id"=>$item2->urlid), array('$set'=>array($content_type=>$item->urlid)));
						$x++;
						//print 'id = '.$item2->urlid.", $content_type = {$item->urlid}";
						//die();
					}
				}
				print "Completed fixing relationships for $content_type, fixed $x items\n";
				flush();
				$tot=$tot+$x;
			}
			print "All done! $tot items fixed.\n";
		}
		
		public function total_export_mongodb($dbname, $start=0) {
			//$this->load->library("mongo_db");
			//$this->mongo_db->switch_db($dbname);
			$connection = new Mongo("mongodb://localhost");
			$db = $connection->selectDB($dbname);
			$this->db->save_queries = false;
			$perpage=1000;
			$time_start=microtime(true);
			$count=$this->db->select('count(*) AS count')->from('content')->get()->row()->count;
			$count=$count-$start;
			$parts=ceil($count/$perpage);
			$f=fopen("./application/logs/exportlog", "w");
			for($x=0; $x<$parts; $x++) {
				set_time_limit(10);
				$contents=$this->db->select('urlid, id, content_type_id')->from('content')->order_by('id')->limit($perpage, (($x*$perpage)+$start))->get();
				//ob_start();
				foreach($contents->result() as $content) {
					fwrite($f, "{$content->id}\t{$content->urlid}\t{$content->content_type_id}\n");
					$obj=$this->_get_item($content->urlid);
					$obj->_id=$obj->id;
					unset($obj->id);
					try {
						//@$db->content->insert($obj, array("safe"=>true));
						$db->content->insert($obj); //Who needs safety?
					} catch(Exception $e) {
						//print_r($e);
					}
					//$this->mongo_db->insert("content", $obj);
					//break;
				}
				//ob_end_clean();
			}
			fclose($f);
			$time_end=microtime(true);
			$time=$time_end-$time_start;
			print "\n\nAll done. Took ".round($time)." seconds and processed ".$count." records (".round($count/$time)." records per second)\n\n";
			$this->index_mongodb($dbname);
		}
		
		public function index_mongodb($dbname) {
			$time_start=microtime(true);
			$connection = new Mongo("mongodb://localhost");
			$db = $connection->selectDB($dbname);
			$db->content->ensureIndex(array("content_type"=>1, "start_date"=>1, "last_modified"=>1, "major_version"=>1, "live"=>1));
			$db->content->ensureIndex(array("mg_original"=>1, "section"=>1));
			$db->content->ensureIndex("tag");
			$db->content->ensureIndex("author");
			$time_end=microtime(true);
			$time=$time_end-$time_start;
			print "Indexes created. Took $time seconds.\n\n";
		}
		
		public function speedtest($mongodbname, $couchdbname, $limit=100) {
			$content=$this->db->select("urlid")->from("content")->limit($limit)->order_by("RAND()", false)->get()->result();
			//print "Starting MongoDB test - $limit random items\n";
			$time_start=microtime(true);
			$connection = new Mongo("mongodb://localhost");
			$db = $connection->selectDB($mongodbname);
			$collection=$db->content;
			foreach($content as $item) {
				$result=$collection->findOne(array("_id" => $item->urlid));
			}
			
			$time_end=microtime(true);
			$mongotime=$time_end-$time_start;
			$persec=round(($limit/$mongotime), 5);
			//print "MongoDB test complete. Time took: $time.\n$persec queries per second.\n";
			//print "\nStarting CouchDB test - $limit random items\n";
			$options['host'] = "localhost"; 
			$options['port'] = 5984;
			$couch = new CouchSimple($options);
			foreach($content as $item) {
				$result=$couch->send('GET', "/$couchdbname/{$item->urlid}");
			}
			
			$time_end=microtime(true);
			$couchtime=$time_end-$time_start;
			$persec=round(($limit/$couchtime), 5);
			//print "CouchDB test complete. Time took: $time.\n$persec queries per second.\n";
			print "$limit\t$mongotime\t$couchtime\n";
		}
		
		public function speedtests($mongodbname, $couchdbname, $start=1, $end=10, $step=1) {
			if ($step<1) {
				show_error("Step less than 1");
			}
			if ($start>=$end) {
				show_error("Start greater than end");
			}
			ob_start();
			print "Number of Queries\tMongo\tCouch\n";
			for($x=$start; $x<=$end; $x=($x+$step)) {
				$this->speedtest($mongodbname, $couchdbname, $x);
			}
			$s=ob_get_contents();
			ob_clean();
			//header("Content-Type: text/csv");
			//header('Content-disposition: attachment;filename=mongo_couch_result.csv');
			print $s;
		}
		
		public function indexspeedtest($mongodbname, $couchdbname) {
			$time_start=microtime(true);
			$connection = new Mongo("mongodb://localhost");
			$db = $connection->selectDB($mongodbname);
			$collection=$db->content;
			$mongo_connection_time=microtime(true)-$time_start;
			$tmp_time=microtime(true);
			$result=$collection->find(array("content_type" => "article"))->limit(500);
			$articles=array();
			foreach($result as $article) {
				$articles[]=$article;
			}
			$mongo_find_time=microtime(true)-$tmp_time;
			$mongo_total_time=microtime(true)-$time_start;
			$mongo_find_count=sizeof($articles);
			
			
			$time_start=microtime(true);
			$options['host'] = "localhost"; 
			$options['port'] = 5984;
			$couch = new CouchSimple($options);
			$couch_connection_time=microtime(true)-$time_start;
			$tmp_time=microtime(true);
			$articles=json_decode($couch->send('GET', "/$couchdbname/_design/content_types/_view/article?limit=500"));
			$couch_find_time=microtime(true)-$tmp_time;
			$couch_total_time=microtime(true)-$time_start;
			$couch_find_count=sizeof($articles->rows);
			print "Metric\tMongo\tCouch\n";
			print "Connection Time\t$mongo_connection_time\t$couch_connection_time\n";
			print "Find Time\t$mongo_find_time\t$couch_find_time\n";
			print "Total Time\t$mongo_total_time\t$couch_total_time\n";
			print "Counts\t$mongo_find_count\t$couch_find_count\n";
		}
	}

//Straight from http://wiki.apache.org/couchdb/Getting_started_with_PHP	
class CouchSimple {
	protected $s; //Socket
    function CouchSimple($options) {
       foreach($options AS $key => $value) {
          $this->$key = $value;
       }
    } 
   
   function send($method, $url, $post_data = NULL) {
      $s = pfsockopen($this->host, $this->port, $errno, $errstr); 
      if(!$s) {
         echo "$errno: $errstr\n"; 
         return false;
      } 

      $request = "$method $url HTTP/1.0\r\nHost: $this->host\r\n"; 

      /*if ($this->user) {
         $request .= "Authorization: Basic ".base64_encode("$this->user:$this->pass")."\r\n"; 
      }*/

      if($post_data) {
         $request .= "Content-Length: ".strlen($post_data)."\r\n\r\n"; 
         $request .= "$post_data\r\n";
      } 
      else {
         $request .= "\r\n";
      }

      fwrite($s, $request); 
      $response = ""; 

      while(!feof($s)) {
         $response .= fgets($s);
      }

      list($this->headers, $this->body) = explode("\r\n\r\n", $response); 
      return $this->body;
   }
}

/* End of file jsonexporter.php */
/* Location: ./system/application/controllers/workers/ */