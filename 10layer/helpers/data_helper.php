<?php
function array_empty($mixed) {
	if (is_array($mixed)) {
		foreach ($mixed as $value) {
 			if (!array_empty($value)) {	
 					return false;
 			}
 		}
 	}
 	elseif (!empty($mixed)) {
 		return false;
 	}
 	return true;
}
?>