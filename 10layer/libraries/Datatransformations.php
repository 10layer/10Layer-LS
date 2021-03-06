<?php
/**
 * Datatransformations class.
 * 
 * You can use this library to transform data entered into the CMS. You can chain these and they'll execute in order.
 *
 * @package 10Layer
 * @subpackage Libraries
 *
 */
class Datatransformations {

	/**
	 * urlid function.
	 * 
	 * Returns an urlid, optionally with a date component
	 *
	 * @access public
	 * @param mixed &$sender
	 * @param string $value
	 * @param string $tableinfo
	 * @param bool $usedate. (default: true)
	 * @return string
	 */
	public function urlid(&$sender, $value, $usedate=true, $collection = "content") {
		$ci=&get_instance();
		$ci->load->helper("smarturl");
		$urlid=smarturl($value, false, !$usedate);
		$urlid=$this->safe_urlid($urlid, $collection);
		return $urlid;
	}
	
	/**
	 * copy function.
	 * 
	 * Copies the data from one field to another
	 *
	 * @access public
	 * @param mixed &$sender
	 * @param string $value
	 * @param string $field
	 * @return string
	 */
	public function copy(&$sender, $value, $field) {
		return $sender->getField($field)->value;
	}
	
	/**
	 * copymultiple function.
	 * 
	 * Copy from multiple fields and join with $join
	 *
	 * @access public
	 * @param mixed &$sender
	 * @param string $value
	 * @param string $join. (default: "")
	 * @param array $fields
	 * @return string
	 */
	public function copymultiple(&$sender, $value, $join="", $fields) {
		$tmp=array();
		if (!is_array($fields)) {
			show_error("Fields must be of type array");
			return "";
		}
		
		foreach($fields as $field) {
			$tmp[]=$sender->getField($field)->value;
		}
		return implode($join, $tmp);
	}
	
	/**
	 * concat function.
	 * 
	 * Concatenates $value and $s
	 *
	 * @access public
	 * @param mixed &$sender
	 * @param string $value
	 * @param string $s
	 * @return string
	 */
	public function concat(&$sender, $value, $s) {
		return $value.$s;
	}
	
	/**
	 * soundslide function.
	 *
	 * Unzips a zip and returns the directory. Specific for SoundsSlides as it searches for the file soundslider.swf
	 * 
	 * @access public
	 * @param mixed &$sender
	 * @param string $value The zip file
	 * @return string Directory we unzipped to
	 */
	public function soundslide(&$sender, $value) {
		if (!is_file(".".$value)) {
			return $value;
		}
		$dir = dirname($value);
		$s=exec("/usr/bin/unzip -o -d .".dirname($value)." .{$value} | grep soundslider.swf | head -n 1");
		$s=str_replace("./resources/uploads/files/original/","",$s);
		$parts=explode(" ",$s);
		$the_parts = explode("/",$parts[sizeof($parts)-1]);
		$the_dir = $the_parts[3]."/".$the_parts[4]."/".$the_parts[5]."/".$the_parts[6]."";
		return $the_dir;
	}
	
	/**
	 * safe_urlid function.
	 * 
	 * Makes sure the urlid isn't repeated in the database
	 *
	 * @access public
	 * @param string $urlid
	 * @param string $tablename
	 * @param string $field
	 * @return string
	 */
	public function safe_urlid($urlid, $collection = "content") {
		$ci=&get_instance();
		$query=$ci->mongo_db->get_where($collection, array("_id"=>$urlid), 1);
		$addnum=0;
		while (sizeof($query) > 0) {
			$newnum="";
			while (is_numeric(substr($urlid,-1))) {
				$newnum=substr($urlid,-1).$newnum;
				$urlid=substr($urlid,0,-1);
			}
			if (empty($newnum)) {
				$urlid=$urlid."-1";
			} else {
				$addnum++;
				$urlid=$urlid.$addnum;
			}
			
			$query=$ci->mongo_db->get_where($collection, array("_id"=>$urlid), 1);
		}
		return $urlid;
	}
	
	/**
	 * str_replace function.
	 * 
	 * Replaces $search with $replace from $value
	 *
	 * @access public
	 * @param mixed &$sender
	 * @param string $value
	 * @param string $search
	 * @param string $replace
	 * @return string
	 */
	public function str_replace(&$sender, $value, $search, $replace) {
		return str_replace($search, $replace, $value);
	}
	
	/**
	 * safetext function.
	 * 
	 * Returns very safe text with no tags or weird characters
	 *
	 * @access public
	 * @param mixed &$sender
	 * @param string $s
	 * @return string
	 */
	public function safetext(&$sender, $s) {
		$s=strip_tags($s);
		$s=$this->convert_ascii($s);
		$encoding = mb_detect_encoding($s, "UTF-8,ISO-8859-1,WINDOWS-1252");
		if ($encoding != 'UTF-8') {
			$s=iconv($encoding, 'UTF-8//TRANSLIT', $s);
		}
		return $s;
	}

	/**
	 * extract_image function.
	 * 
	 * Takes a file upload field and extracts an image from it. Useful to get an image from a PDF, for example.
	 *
	 * @access public
	 * @param mixed &$sender
	 * @param string $value
	 * @param string $field
	 * @return string
	 */
	public function extract_image(&$sender, $value, $field) {
		$img = $sender->getField($field)->value;
		if (is_array($img)) {
			$img = array_pop($img);
		}
		if (empty($img)) {
			return false;
		}
		$parts = pathinfo($img);
		$returndir = $parts["dirname"];
		$fullimg = realpath(".".$img);
		if (file_exists(".".$img)) {
			$parts = pathinfo($fullimg);
			$newimg = $parts["dirname"]."/".$parts["filename"].".png";
			//if ($parts[extension] == "pdf") {
				$success = exec("convert '".escapeshellarg($fullimg)."[0]' '{$newimg}'", $result);
			//}
			return $returndir."/".$parts["filename"].".png";
		}
		return false;
	}

	/**
	 * extract_text function.
	 * 
	 * Takes a file upload field and extracts text from it if it's a PDF.
	 *
	 * @access public
	 * @param mixed &$sender
	 * @param string $value
	 * @param string $field
	 * @return string
	 */
	public function extract_pdf_text(&$sender, $value, $field) {
		$pdf = $sender->getField($field)->value;
		if (is_array($pdf)) {
			$pdf = array_pop($pdf);
		}
		if (empty($pdf)) {
			return false;
		}
		$parts = pathinfo($pdf);
		$returndir = $parts["dirname"];
		$fullpdf = realpath(".".$pdf);
		if (file_exists(".".$pdf)) {
			$parts = pathinfo($fullpdf);
			if ($parts["extension"] != "pdf") {
				return false;
			}
			$newpdf = $parts["dirname"]."/".$parts["filename"].".txt";
			//if ($parts[extension] == "pdf") {
				$success = exec("pdftotext -layout '".escapeshellarg($fullpdf)."' '{$newpdf}'", $result);
			//}
			return file_get_contents($newpdf);
		}
		return false;
	}

	/**
	 * extract_html function.
	 * 
	 * Takes a file upload field and extracts text from it if it's a PDF.
	 *
	 * @access public
	 * @param mixed &$sender
	 * @param string $value
	 * @param string $field
	 * @return string
	 */
	public function extract_pdf_html(&$sender, $value, $field) {
		$pdf = $sender->getField($field)->value;
		if (is_array($pdf)) {
			$pdf = array_pop($pdf);
		}
		if (empty($pdf)) {
			return false;
		}
		$parts = pathinfo($pdf);
		$returndir = $parts["dirname"];
		$fullpdf = realpath(".".$pdf);
		if (file_exists(".".$pdf)) {
			$parts = pathinfo($fullpdf);
			if ($parts["extension"] != "pdf") {
				return false;
			}
			$newpdf = $parts["dirname"]."/".$parts["filename"].".html";
			$succss = exec("/usr/bin/pdftohtml -noframes -enc 'UTF-8' ".escapeshellarg($fullpdf));
			$result = file_get_contents($newpdf);
			preg_match('/(<body .*?>)(.*)(<\/body>)/si', $result, $matches);
			// print_r($matches[2]);
			return $matches[2];
		}
		return false;
	}

	
	/**
	 * Remove any non-ASCII characters and convert known non-ASCII characters 
	 * to their ASCII equivalents, if possible.
	 *
	 * @param string $string 
	 * @return string $string
	 * @author Jay Williams <myd3.com>
	 * @license MIT License
	 * @link http://gist.github.com/119517
	 */
	protected function convert_ascii($string) {
		// Replace Single Curly Quotes
		$search[]  = chr(226).chr(128).chr(152);
		$replace[] = "'";
		$search[]  = chr(226).chr(128).chr(153);
		$replace[] = "'";
		
		// Replace Smart Double Curly Quotes
		$search[]  = chr(226).chr(128).chr(156);
		$replace[] = '"';
		$search[]  = chr(226).chr(128).chr(157);
		$replace[] = '"';
		
		// Replace En Dash
		$search[]  = chr(226).chr(128).chr(147);
		$replace[] = '--';
		
		// Replace Em Dash
		$search[]  = chr(226).chr(128).chr(148);
		$replace[] = '---';
		
		// Replace Bullet
		$search[]  = chr(226).chr(128).chr(162);
		$replace[] = '*';
		
		// Replace Middle Dot
		$search[]  = chr(194).chr(183);
		$replace[] = '*';
		
		// Replace Ellipsis with three consecutive dots
		$search[]  = chr(226).chr(128).chr(166);
		$replace[] = '...';
		
		$search[]  = chr(195).chr(169);
		$replace[] = 'e';
		
		
		// Apply Replacements
		$string = str_replace($search, $replace, $string);
		
		// Remove any non-ASCII Characters
		$string = preg_replace("/[^\x01-\x7F]/","", $string);
		
		return $string; 
	}
}
?>