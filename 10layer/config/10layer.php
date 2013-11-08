<?php
	//This config should be overwritten with /application/config/10layer.php (at least the bits you want to change)
	
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

	$config["google_api_key"] = "";

	$config["socket_io_enable"] = true;
	$config["socket_io_server"] = "http://localhost:8181"; # Include port
?>