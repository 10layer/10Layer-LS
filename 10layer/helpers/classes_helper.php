<?php
	function file_get_php_classes($filepath,$onlypublic=true) {
		$php_code = file_get_contents($filepath);
		$classes = get_php_classes($php_code,$onlypublic);
		return $classes;
	}

	function get_php_classes($php_code,$onlypublic) {
		$classes = array();
		$methods=array();
		$tokens = token_get_all($php_code);
		$count = count($tokens);
		for ($i = 2; $i < $count; $i++) {
			if ($tokens[$i - 2][0] == T_CLASS
        	&& $tokens[$i - 1][0] == T_WHITESPACE
	        && $tokens[$i][0] == T_STRING) {
		        $class_name = $tokens[$i][1];
    		    $methods[$class_name] = array();
			}
			if ($tokens[$i - 2][0] == T_FUNCTION
        	&& $tokens[$i - 1][0] == T_WHITESPACE
	        && $tokens[$i][0] == T_STRING) {
	        	if ($onlypublic) {
	        		if ( !in_array($tokens[$i-4][0],array(T_PROTECTED, T_PRIVATE))) {
	        			$method_name = $tokens[$i][1];
	    			    $methods[$class_name][] = $method_name;
	        		}
	        	} else {
			        $method_name = $tokens[$i][1];
    			    $methods[$class_name][] = $method_name;
				}
			}
		}
		return $methods;
	}
	
	function mapSystemClasses($controllerdir="./application/controllers/",$onlypublic=true) {
		$result=array();
		$dh=opendir($controllerdir);
		while (($file = readdir($dh)) !== false) {
			if (substr($file,0,1)!=".") {
				if (filetype($controllerdir.$file)=="file") {
					$classes=file_get_php_classes($controllerdir.$file,$onlypublic);
					foreach($classes as $class=>$method) {
						$result[]=array("file"=>$controllerdir.$file,"class"=>$class,"method"=>$method);
						
					}
				} else {
					$result=array_merge($result,mapSystemClasses($controllerdir.$file."/",$onlypublic));
				}
			}
		}
		closedir($dh);
		return $result;
	}
	
	function mapAvailableCIUrls() {
		$result=array();
		$classes=mapSystemClasses();
		foreach($classes as $class) {
			$dirname=dirname($class["file"]);
			$dirname=substr($dirname,strlen("./application/controllers"));
			$classname=strtolower($class["class"]);
			foreach($class["method"] as $method) {
				if (substr($method,0,1)!="_") {
					if (strpos("/index",$method)===false) {
						$result[]=$dirname."/".$classname."/".$method."\n";
					} else {
						$result[]=$dirname."/".$classname."\n";
					}
				} else {
					if (strpos("_remap",$method)!==false) {
						$result[]=$dirname."/".$classname."/*\n";
					}
				}
			}
		}
		sort($result);
		//print_r($result);
		return $result;
	}
?>