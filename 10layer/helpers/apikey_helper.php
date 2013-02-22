<?php
	function generate_api_key($len=8) {

		$today = date('YMD');
		return md5($today.mt_rand(10000,90000).microtime());
	}
?>