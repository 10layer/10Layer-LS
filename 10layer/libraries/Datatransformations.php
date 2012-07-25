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
	public function urlid(&$sender, $value, $tableinfo, $usedate=true) {
		$ci=&get_instance();
		$ci->load->helper("smarturl");
		$urlid=smarturl($value, false, !$usedate);
		$table=explode(".",$tableinfo);
		if (sizeof($table)!=2) {
			show_error("Format for urlid transformation: 'urlid'=>'tablename.fieldname'");
		}
		$urlid=$this->safe_urlid($urlid, $table[0], $table[1]);
		
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
	public function safe_urlid($urlid, $tablename, $field) {
		$ci=&get_instance();
		$query=$ci->db->get_where($tablename, array($field=>$urlid));
		$addnum=0;
		while ($query->num_rows()!=0) {
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
			
			$query=$ci->db->get_where($tablename, array($field=>$urlid));
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