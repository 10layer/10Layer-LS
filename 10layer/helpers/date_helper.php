<?php 
/** 
 * A sweet interval formatting, will use the two biggest interval parts. 
 * On small intervals, you get minutes and seconds. 
 * On big intervals, you get months and days. 
 * Only the two biggest parts are used. 
 * Source: baptiste dot place at utopiaweb dot fr http://www.php.net/manual/en/dateinterval.format.php
 * 
 * @param DateTime $start 
 * @param DateTime|null $end 
 * @return string 
 */ 
function formatDateDiff($start, $end=null) {
	
	if (strnatcmp(phpversion(),'5.3') < 0) {
		//trigger_error("PHP version must be 5.3 or later", E_USER_WARNING);
		return rel_time($start, $end);
	}
    if(!($start instanceof DateTime)) { 
        $start = new DateTime($start); 
    } 
    
    if($end === null) { 
        $end = new DateTime(); 
    } 
    
    if(!($end instanceof DateTime)) { 
        $end = new DateTime($start); 
    } 
    
    $interval = $end->diff($start); 
    $doPlural = doPlural($nb,$str); // adds plurals 
    
    $format = array(); 
    if($interval->y !== 0) { 
        $format[] = "%yY"; 
    } 
    if($interval->m !== 0) { 
        $format[] = "%mM"; 
    } 
    if($interval->d !== 0) { 
        $format[] = "%dd"; 
    } 
    if($interval->h !== 0) { 
        $format[] = "%hh"; 
    } 
    if($interval->i !== 0) { 
        $format[] = "%im"; 
    } 
    if($interval->s !== 0) { 
        if(!count($format)) { 
            return "less than a minute ago"; 
        } else { 
            $format[] = "%ss"; 
        } 
    } 
    
    // We use the two biggest parts 
    if(count($format) > 1) { 
        $format = array_shift($format).", ".array_shift($format); 
    } else { 
        $format = array_pop($format); 
    } 
    
    // Prepend 'since ' or whatever you like 
    return $interval->format($format); 
}

function doPlural($nb,$str) {
	return $nb>1?$str.'s':$str;
}

/**
 * rel_time function.
 *
 * John Galt, http://php.net/manual/en/function.time.php
 * 
 * @access public
 * @param mixed $from
 * @param mixed $to. (default: null)
 * @return string formatted time difference
 */
function rel_time($from, $to = null) {
	$output="";
	$result=array();
	$to = (($to === null) ? (time()) : ($to));
	$to = ((is_int($to)) ? ($to) : (strtotime($to)));
	$from = ((is_int($from)) ? ($from) : (strtotime($from)));
	$units = array(
		"year"   => 29030400, // seconds in a year   (12 months)
		"month"  => 2419200,  // seconds in a month  (4 weeks)
		"week"   => 604800,   // seconds in a week   (7 days)
		"day"    => 86400,    // seconds in a day    (24 hours)
		"hour"   => 3600,     // seconds in an hour  (60 minutes)
		"minute" => 60,       // seconds in a minute (60 seconds)
		"second" => 1         // 1 second
	);
	$diff = abs($from - $to);
	$suffix = (($from > $to) ? ("from now") : ("ago"));
	foreach($units as $unit => $mult) {
		if($diff >= $mult) {
			//$and = (($mult != 1) ? ("") : ("and "));
			//$output .= ", ".$and.intval($diff / $mult)." ".$unit.((intval($diff / $mult) == 1) ? ("") : ("s"));
			$result[]=intval($diff / $mult)." ".$unit.((intval($diff / $mult) == 1) ? ("") : ("s"));
			$diff -= intval($diff / $mult) * $mult;
		}
	}
	//$output .= " ".$suffix;
	//$output = substr($output, strlen(", "));
	if (sizeof($result)>1) {
		$output=$result[0].", ".$result[1]." ".$suffix;
	} else {
		$output=$result[0]." ".$suffix;
	}
	return $output;
}
?>