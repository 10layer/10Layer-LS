<?php
	//This config should be overwritten with /application/config/10layer.php (at least the bits you want to change)

	//You can change this if you need to
	$config['live_base_url']	= 'http'.((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 's' : '').'://'.$_SERVER['HTTP_HOST'].str_replace('//','/',dirname($_SERVER['SCRIPT_NAME']).'/');

	$config["stomp_server"]=$_SERVER['HTTP_HOST'];
	$config["stomp_port"]="61613";
	$config["stomp_protocol"]="tcp";
	
	//Make sure the server knows its own name in /etc/hosts
	$config["comet_server"]=$_SERVER['HTTP_HOST'];
	$config["comet_port"]="8000";
	

	$config["xmpp_server"]=$_SERVER['HTTP_HOST'];
	$config["xmpp_port"]=5222;
	
	$config["google_analytics_email"]="";
	$config["google_analytics_password"]="";
	$config["google_analytics_reports"]=array("ga:12345","ga:67890");
	
	//Rackspace credentials
	$config["rackspace_api_key"]="";
	$config["rackspace_username"]="";
	
	//Amazon credentials
	$config["aws_key"]="";
	$config["aws_secret_key"]="";
	$config["aws_canonical_id"]="";
	//Note: Remove dashes for aws_account_id
	$config["aws_account_id"]="";
	
	//"rackspace" or "aws", or set to false to not use CDN
	$config["cdn_service"]=false;
	
	//The default bucket or container to put your uploads in
	$config["cdn_bucket"]="";
	
	$config["database_autorepair"]=true;
	
	$config["opencalais_api_key"]="";
	$config["opencalais_min_relevance"]=0.5;
	
	$config["memcache_enable"]=true;
	$config["memcache_reset"]=true;
	$config["memcache_write"]=true;
	$config["memcache_servers"]=array(
		array("server"=>"127.0.0.1","port"=>11211),
	);
?>