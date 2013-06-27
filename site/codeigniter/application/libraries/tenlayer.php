<?php
/**
 * 10Layer class.
 * 
 * Grabs data from a 10Layer API
 * Author: JNY
 * First created: 26 November 2012
 *
 */
class TenLayer {
	protected $apiurl="";
	protected $ci;
	
	public function __construct() {
		$this->ci=&get_instance();
		$this->apiurl = $this->ci->config->item("apiurl");
		$this->apiurl = rtrim($this->apiurl, "/")."/"; //Enforce trailing slash
	}
	
	public function get($id) {
		try {
			$result=json_decode(file_get_contents($this->apiurl."content/get?id=$id"));
			if (empty($result)) {
				return false;
			}
		} catch(Exception $e) {
			return false;
		}
		if ($result->error) {
			return false;
		}
		return $result->content;
	}
	
	public function listing($config) {
		if (!is_array($config)) {
			show_error("config should be an array");
		}
		try {
			$result=json_decode(file_get_contents($this->apiurl."content/listing?".http_build_query($config)));
			if (empty($result)) {
				return false;
			}
		} catch(Exception $e) {
			return false;
		}
		if ($result->error) {
			return false;
		}
		return $result;
	}
	
	public function section($section_name) {
		try {
			$result=json_decode(file_get_contents($this->apiurl."publish/section/$section_name"));
			if (empty($result)) {
				return false;
			}
		} catch(Exception $e) {
			return false;
		}
		if ($result->error) {
			return false;
		}
		return $result->content->zones;
	}
	
	public function zone($section, $zone) {
		try {
			$result=json_decode(file_get_contents($this->apiurl."publish/zone/$section/$zone"));
			if (empty($result)) {
				return false;
			}
		} catch(Exception $e) {
			return false;
		}
		if ($result->error) {
			return false;
		}
		return $result->content;
	}
	
	public function nid_to_url($nid) {
		$api_key = $this->ci->config->item("api_key");
		try {
			$result=json_decode(file_get_contents($this->apiurl."content/get?where_nid=$nid&api_key=$api_key"));
			if (empty($result)) {
				return false;
			}
		} catch(Exception $e) {
			return false;
		}
		if ($result->error) {
			return false;
		}
		if (empty($result->content)) {
			return false;
		}
		$url = "/{$result->content->content_type}/{$result->content->_id}";
		return $url;
	}
	
	public function random($content_type) {
		try {
			$result=json_decode(file_get_contents($this->apiurl."content/listing/?content_type=$content_type"));
			if (empty($result)) {
				return false;
			}
		} catch(Exception $e) {
			return false;
		}
		if ($result->error) {
			return false;
		}
		$items = $result->content;
		$rand = rand(0, sizeof($items)-1);
		return $items[$rand];
	}

	public function image($url, $width = 1000, $height = 1000, $bounding = true, $grey=false, $render = false) {
		if (is_array($url)) {
			$url = array_pop($url);
		}
		$url = ltrim($url, "/");
		$url = str_replace("content/", "", $url);
		$_bounding = "";
		if ($bounding) {
			$_bounding = "true";
		}
		$_grey = "";
		$greystr = "";
		if ($grey) {
			$_grey = "true";
			$greystr = "-greyscale";
		}
		$parts = pathinfo($url);
		$quality = 80;
		$opstr = "fill";
		
		if (empty($bounding)) {
			$opstr = "bound";
		}
		$filename = "content/cache/".$parts["dirname"]."/".$this->smarturl($parts["filename"], false, true)."-".$width."-".$height."-".$quality."-".$opstr.$greystr.".png";
		if (file_exists("./".$filename)) {
			return "/".$filename;
		}
		try {
			$result=json_decode(file_get_contents($this->apiurl."files/image/?filename=$url&width=$width&height=$height&bounding=$_bounding&greyscale=$_grey"));
			if (empty($result)) {
				return false;
			}
		} catch(Exception $e) {
			return false;
		}
		if ($result->error) {
			return false;
		}
		return "/".$result->filename."?dynamic";
	}

	protected function remove_accent($str) { 
	  $a = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'Ā', 'ā', 'Ă', 'ă', 'Ą', 'ą', 'Ć', 'ć', 'Ĉ', 'ĉ', 'Ċ', 'ċ', 'Č', 'č', 'Ď', 'ď', 'Đ', 'đ', 'Ē', 'ē', 'Ĕ', 'ĕ', 'Ė', 'ė', 'Ę', 'ę', 'Ě', 'ě', 'Ĝ', 'ĝ', 'Ğ', 'ğ', 'Ġ', 'ġ', 'Ģ', 'ģ', 'Ĥ', 'ĥ', 'Ħ', 'ħ', 'Ĩ', 'ĩ', 'Ī', 'ī', 'Ĭ', 'ĭ', 'Į', 'į', 'İ', 'ı', 'Ĳ', 'ĳ', 'Ĵ', 'ĵ', 'Ķ', 'ķ', 'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ', 'ľ', 'Ŀ', 'ŀ', 'Ł', 'ł', 'Ń', 'ń', 'Ņ', 'ņ', 'Ň', 'ň', 'ŉ', 'Ō', 'ō', 'Ŏ', 'ŏ', 'Ő', 'ő', 'Œ', 'œ', 'Ŕ', 'ŕ', 'Ŗ', 'ŗ', 'Ř', 'ř', 'Ś', 'ś', 'Ŝ', 'ŝ', 'Ş', 'ş', 'Š', 'š', 'Ţ', 'ţ', 'Ť', 'ť', 'Ŧ', 'ŧ', 'Ũ', 'ũ', 'Ū', 'ū', 'Ŭ', 'ŭ', 'Ů', 'ů', 'Ű', 'ű', 'Ų', 'ų', 'Ŵ', 'ŵ', 'Ŷ', 'ŷ', 'Ÿ', 'Ź', 'ź', 'Ż', 'ż', 'Ž', 'ž', 'ſ', 'ƒ', 'Ơ', 'ơ', 'Ư', 'ư', 'Ǎ', 'ǎ', 'Ǐ', 'ǐ', 'Ǒ', 'ǒ', 'Ǔ', 'ǔ', 'Ǖ', 'ǖ', 'Ǘ', 'ǘ', 'Ǚ', 'ǚ', 'Ǜ', 'ǜ', 'Ǻ', 'ǻ', 'Ǽ', 'ǽ', 'Ǿ', 'ǿ'); 
	  $b = array('A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 's', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'IJ', 'ij', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'l', 'l', 'N', 'n', 'N', 'n', 'N', 'n', 'n', 'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 'o'); 
	  return str_replace($a, $b, $str); 
	}
	
	protected function smarturl($s,$date=false,$exclude_date=false) {
		if (empty($date)) {
			$d=date("Y-m-d");
		} else {
			$d=date("Y-m-d",strtotime($date));
		}
		$s=preg_replace(array('/[^a-zA-Z0-9 -]/', '/[ -]+/', '/^-|-$/'), 
		  array('', '-', ''), strip_tags(strtolower($this->remove_accent($s))));
		if ($exclude_date) {
			return $s;
		}
		return $d."-".$s;
	}
}
?>