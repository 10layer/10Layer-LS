<?php
	function live_base_url() {
		$CI =& get_instance();
		return $CI->config->slash_item('live_base_url');
	}
	
	function live_anchor($uri = '', $title = '', $attributes = '')
	{
		$title = (string) $title;

		if ( ! is_array($uri))
		{
			$site_url = ( ! preg_match('!^\w+://! i', $uri)) ? live_site_url($uri) : $uri;
		}
		else
		{
			$site_url = live_site_url($uri);
		}

		if ($title == '')
		{
			$title = $site_url;
		}

		if ($attributes != '')
		{
			$attributes = _parse_attributes($attributes);
		}

		return '<a href="'.$site_url.'"'.$attributes.'>'.$title.'</a>';
	}
	
	function live_site_url($uri = '') {
		$CI =& get_instance();
		$url=$CI->config->slash_item('live_base_url');
		if (is_array($uri))
		{
			$uri = implode('/', $uri);
		}

		if ($uri == '')
		{
			return $CI->config->slash_item('live_base_url').$CI->config->item('index_page');
		}
		else
		{
			$suffix = ($CI->config->item('url_suffix') == FALSE) ? '' : $CI->config->item('url_suffix');
			return $CI->config->slash_item('live_base_url').$CI->config->slash_item('index_page').trim($uri, '/').$suffix; 
		}
	}
	
	function photo_emulator($width,$height) {
		$s="<div style='width: {$width}px; height: {$height}px; overflow: hidden; text-align: center; font-weight: bold; background-color: #78c1e2; color: #FFF'>$width x $height</div>";
		return $s;
	}
?>