<?php
class Autoloader {
	/**
	 * Finds Javascript based on the uri, using the segments from the last to the first
	 *
	 * @access	public
	 * @param	String	Path to view
	 * @param	String	Default view to load if we don't find the view we want
	 * @return	boolean	True if it finds the view, false if it doesn't
	 * @package 10Layer
	 */
	public function javascript($default=true) {
	    $ci=& get_instance();
	    $found=false;
	    $segments=$ci->uri->segment_array();
	    while(sizeof($segments)>0 && !$found) {
	    	$vname=implode("/",$segments);
	    	if ($ci->exists->javascript($vname)) {
	    		$found=true;
	    		$vname="/tlresources/file/js/autoload/$vname.js";
	    		return $this->_format_javascript($vname);
	    	}
	    	array_pop($segments);
	    }
	    return $found;
	}
	
	protected function _format_javascript($scriptname) {
		return "<script language='javascript' type='text/javascript' src='$scriptname'></script>\n";
	}
	
	/**
	 * Finds Stylesheets based on the uri, using the segments from the last to the first
	 *
	 * @access	public
	 * @param	String	Path to view
	 * @param	String	Default view to load if we don't find the view we want
	 * @return	boolean	True if it finds the view, false if it doesn't
	 */
	public function stylesheet($default=true) {
	    $ci=&get_instance();
	    $found=false;
	    $segments=$ci->uri->segment_array();
	    while(sizeof($segments)>0 && !$found) {
	    	$vname=implode("/",$segments);
	    	if ($ci->exists->stylesheet($vname)) {
	    		$found=true;
	    		$vname="/tlresources/file/css/$vname.css";
	    		//print "Found $vname";
	    		return $this->_format_stylesheet($vname);
	    	}
	    	array_pop($segments);
	    }
	    return $found;
	}
	
	protected function _format_stylesheet($scriptname) {
		return "<link rel='stylesheet' href='$scriptname' type='text/css' media='screen, projection' charset='utf-8' />\n";
	}
}
?>