<?php
	function generate_random_password($len=8) {
		$chars = "abcdefghijkmnopqrstuvwxyz023456789"; 
		srand((double)microtime()*1000000); 
		$i = 0; 
		$pass = '' ;
		while ($i <= $len) { 
			$num = rand() % 33; 
			$tmp = substr($chars, $num, 1); 
			$pass = $pass . $tmp; 
			$i++; 
		}
		return $pass;
	}
	
	function genkey($seed="blah") {
		return md5(substr($seed,4).mt_rand(10000,90000).microtime());
	}
	
?>