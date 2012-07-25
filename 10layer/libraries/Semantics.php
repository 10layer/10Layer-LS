<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

	require_once(APPPATH.'third_party/10layer/system/TL_Content_Library.php');

	/**
	 * 10Layer Semantics Class
	 *
	 * This class handles auto-tagging and categorising of articles, as well as manual tagging
	 *
	 * @package		10Layer
	 * @subpackage	Libraries
	 * @category	Libraries
	 * @author		Jason Norwood-Young
	 * @link		http://10layer.com
	 */
	
	class Semantics extends TL_Content_Library {
				
		public function __construct() {
			parent::__construct();
			$this->table="tl_semantics";
		}
		
		public function getSemantics() {
			$contentobj=$this->ci->model_content->getByIdORM($this->urlid);
			$tmp=array();
			$fields=$contentobj->getFields();
			foreach($fields as $field) {
				if (in_array("semantic",$field->libraries)) {
					$tmp[]=strip_tags($field->value);
				}
			}
			
			$s=implode("\n\n",$tmp);
			if (!$this->checkCache($s)) {
				$this->set(array("tags"=>$this->process($s),"content"=>$s));
			}
			$result=$this->get();
			$confirmed=$this->getConfirmed();
			$return=array();
			for($x=0; $x<sizeof($result->tags);$x++) {
				$found=false;
				foreach($confirmed as $check) {
					if ($result->tags[$x]["name"]==$check->tag) {
						$found=true;
					}
				}
				$return[$x]=$result->tags[$x];
				$return[$x]["confirmed"]=$found;
			}
			$return=$this->checkTags($return);
			//print_r($result);
			return $return;
		}
		
		public function process($content) {
			$output_format="application/json";
			$content_type = "text/html";
			$restURL = "http://api.opencalais.com/enlighten/rest/";
			$apiKey=$this->ci->config->item("opencalais_api_key");
			$min_relevance=$this->ci->config->item("opencalais_min_relevance");
			$paramsXML = "<c:params xmlns:c=\"http://s.opencalais.com/1/pred/\" " . 
				"xmlns:rdf=\"http://www.w3.org/1999/02/22-rdf-syntax-ns#\"> " .
				"<c:processingDirectives c:contentType=\"".$content_type."\" " .
				"c:outputFormat=\"".$output_format."\"".
				"></c:processingDirectives> " .
				"<c:userDirectives c:allowDistribution=\"false\" " .
				"c:allowSearch=\"false\" c:externalID=\" \" " .
				"c:submitter=\"Calais REST Sample\"></c:userDirectives> " .
				"<c:externalMetadata><c:Caller>10Layer.com</c:Caller>" .
				"</c:externalMetadata></c:params>";
			$data = "licenseID=".urlencode($apiKey);
			$data .= "&paramsXML=".urlencode($paramsXML);
			$data .= "&content=".urlencode(strip_tags($content));
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $restURL);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_TIMEOUT, 60);
			$response = curl_exec($ch);
			curl_close($ch);
			$result=json_decode($response);
			$topiclist=array();
			foreach($result as $topics) {
				if (!empty($topics->_type) && !empty($topics->name) && !empty($topics->relevance) && ($topics->relevance >= $min_relevance)) {
					$topiclist[($topics->relevance*1000)]=array("type"=>$topics->_type, "name"=>$topics->name, "relevance"=>$topics->relevance);
				}
			}
			krsort($topiclist);
			//print_r($topiclist);
			return array_values($topiclist);
		}
		
		public function getConfirmed() {
			$this->ci->load->model("model_tags");
			return $this->ci->model_tags->getTags($this->content->id);
		}
		
		public function checkTags($tags) {
			$this->ci->load->helper("smarturl_helper");
			for($x=0; $x<sizeof($tags); $x++) {
				$type_smarturl=smarturl($tags[$x]["type"],false,true);
				$tag_smarturl=smarturl($tags[$x]["name"],false,true);
				$query=$this->ci->db->get_where("tag_type",array("urlid"=>$type_smarturl));
				if ($query->num_rows()==0) {
					$this->ci->db->insert("tag_type",array("urlid"=>$type_smarturl,"name"=>$tags[$x]["name"]));
					$tag_type_id=$this->ci->db->insert_id();
				} else {
					$tag_type_id=$query->row()->id;
				}
				$query=$this->ci->db->get_where("tag",array("urlid"=>$tag_smarturl,"type_id"=>$tag_type_id));
				if ($query->num_rows()==0) {
					$this->ci->db->insert("tag",array("urlid"=>$tag_smarturl,"tag"=>$tags[$x]["name"],"type_id"=>$tag_type_id));
					$tag_id=$this->ci->db->insert_id();
				} else {
					$tag_id=$query->row()->id;
				}
				$tags[$x]["tag_id"]=$tag_id;
				$tags[$x]["type_id"]=$tag_type_id;
			}
			return $tags;
		}
		
		public function linkTag($tag_id,$content_id,$content_type_id) {
			$this->unlinkTag($tag_id, $content_id, $content_type_id);
			$this->ci->db->insert("content_tag_link",array("tag_id"=>$tag_id, "content_id"=>$content_id, "content_type_id"=>$content_type_id));
		}
		
		public function unlinkTag($tag_id,$content_id,$content_type_id) {
			$this->ci->db->where(array("tag_id"=>$tag_id, "content_id"=>$content_id, "content_type_id"=>$content_type_id));
			$this->ci->db->delete("content_tag_link");
		}
		
		protected function checkCache($content) {
			$result=$this->get();
			if (!isset($result->content)) {
				return false;
			}
			return ($result->content==$content);
		}
		
	}
?>