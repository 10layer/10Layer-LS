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
			$this->ci->benchmark->mark('api_get_'.$id.'_start');
			$result=json_decode(file_get_contents($this->apiurl."content/get?id=$id"));
			$this->ci->benchmark->mark('api_get_'.$id.'_end');
			if (empty($result)) {
				return false;
			}
		} catch(Exception $e) {
			return false;
		}
		if (!empty($result->error)) {
			return false;
		}
		return $result->content;
	}
	
	public function listing($config) {
		if (!is_array($config)) {
			show_error("config should be an array");
		}
		try {
			$this->ci->benchmark->mark('api_listing_start');
			$result=json_decode(file_get_contents($this->apiurl."content/listing?".http_build_query($config)));
			$this->ci->benchmark->mark('api_listing_end');
			if (empty($result)) {
				return false;
			}
		} catch(Exception $e) {
			return false;
		}
		if (!empty($result->error)) {
			return false;
		}
		return $result;
	}
	
	public function section($section_name) {
		try {
			$this->ci->benchmark->mark('api_section_'.$section_name.'_start');
			$result=json_decode(file_get_contents($this->apiurl."publish/section/$section_name"));
			$this->ci->benchmark->mark('api_section_'.$section_name.'_end');
			if (empty($result)) {
				return false;
			}
		} catch(Exception $e) {
			return false;
		}
		if (!empty($result->error)) {
			return false;
		}
		return $result->content->zones;
	}
	
	public function zone($section, $zone) {
		try {
			$this->ci->benchmark->mark('api_zone_'.$section.'_'.$zone.'_start');
			$result=json_decode(file_get_contents($this->apiurl."publish/zone/$section/$zone"));
			$this->ci->benchmark->mark('api_zone_'.$section.'_'.$zone.'_end');
			if (empty($result)) {
				return false;
			}
		} catch(Exception $e) {
			return false;
		}
		if (!empty($result->error)) {
			return false;
		}
		return $result->content;
	}
	
	public function nid_to_url($nid) {
		$api_key = $this->ci->config->item("api_key");
		try {
			$this->ci->benchmark->mark('api_nid_to_url_'.$nid.'_start');
			$result=json_decode(file_get_contents($this->apiurl."content/get?where_nid=$nid&api_key=$api_key"));
			$this->ci->benchmark->mark('api_nid_to_url_'.$nid.'_end');
			if (empty($result)) {
				return false;
			}
		} catch(Exception $e) {
			return false;
		}
		if (!empty($result->error)) {
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
			$this->ci->benchmark->mark('api_random_'.$content_type.'_start');
			$result=json_decode(file_get_contents($this->apiurl."content/listing/?content_type=$content_type"));
			$this->ci->benchmark->mark('api_random_'.$content_type.'_end');
			if (empty($result)) {
				return false;
			}
		} catch(Exception $e) {
			return false;
		}
		if (!empty($result->error)) {
			return false;
		}
		$items = $result->content;
		$rand = rand(0, sizeof($items)-1);
		return $items[$rand];
	}

	public function image($url, $width = 1000, $height = 1000, $bounding = true, $grey=false, $render = false, $format = 'jpg') {
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
		$filename = "content/cache/".$parts["dirname"]."/".$this->smarturl($parts["filename"], false, true)."-".$width."-".$height."-".$quality."-".$opstr.$greystr.".".$format;
		if (file_exists("./".$filename)) {
			return $this->_render("/".$filename, $render);
		}
		try {
			$result=json_decode(file_get_contents($this->apiurl."files/image/?filename=$url&width=$width&height=$height&bounding=$_bounding&greyscale=$_grey&format=$format"));
			if (empty($result)) {
				return false;
			}
		} catch(Exception $e) {
			return false;
		}
		if (!empty($result->error)) {
			return false;
		}
		return $this->_render("/".$result->filename, $render);
	}

	protected function _render($filename, $render) {
		if ($render) {
			header("content-type: image/jpeg");
			header('Last-Modified: '.gmdate('D, d M Y H:i:s', filemtime(".".$filename)).' GMT', true, 200);
			header('Content-Length: '.filesize(".".$filename));
			echo file_get_contents(".".$filename);
			return 1;
		} else {
			return $filename;
		}
	}

	public function shorturl($id, $url) {
		$api_key = $this->ci->config->item("api_key");
		$this->ci->benchmark->mark('api_shorturl_'.$url.'_start');
		$result=json_decode(file_get_contents($this->apiurl."content/shorturl/?api_key=$api_key&id=$id&url=".rawurlencode($url)));
		$this->ci->benchmark->mark('api_shorturl_'.$url.'_end');
		if (!empty($result->_shorturl)) {
			return $result->_shorturl;	
		}
		return $url;
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