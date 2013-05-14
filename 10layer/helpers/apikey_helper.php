<?php
	function generate_api_key($len=8) {
		mt_srand();
		$today = date('YMD');
		return md5($today.mt_rand().microtime());
	}
?>