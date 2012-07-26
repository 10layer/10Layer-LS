function cometInit(userid,stomp_server,stomp_port,connectFunction) {
	stomp = new STOMPClient();
	stomp.onopen = function(){
		//console.log("opening stomp client");
	};
	stomp.onclose = function(c){
		//console.log('Lost Connection, Code: ' + c);
	};
	stomp.onerror = function(error){
		//console.log("Error: " + error);
	};
	stomp.onerrorframe = function(frame){
		//console.log("Error: " + frame.body);
	};
	stomp.onconnectedframe = function() {
		//console.log("Connected. Subscribing to /queue/"+userid);
		stomp.subscribe("/queue/"+userid);
		stomp.subscribe("/action");
		stomp.subscribe("/locations");
		if (connectFunction) {
			processHook(connectFunction);
		}
		var msg=JSON.stringify({
			"uid":userid,
			"location": location.href
		});
		//console.log(msg);
		stomp.send(msg, "/locations")
		
	};
	stomp.onmessageframe = function(frame){
		//console.log("Received message frame");
		process_comet(frame.headers.destination,JSON.parse(frame.body));
	};
	stomp.connect(stomp_server, stomp_port);
	
}

function process_comet(comethead, cometobj) {
	if (comethead.substr(0,6)=="/queue") {
		cometProcessQueue(cometobj);
	} else if (comethead.substr(0,10)=="/locations") {
		cometProcessLocation(cometobj);
	} else {
		cometProcessAction(cometobj);
	}
}

function cometProcessQueue(cometobj) {
	//alert("Override me!");
}

function cometProcessAction(cometobj) {
	//console.log(cometobj);
	var func=cometobj.body.func;
	var params=cometobj.body.params;
	if (eval ('typeof '+func+'=="function"')){
		eval(func+'.apply(this, params)');
	} else {
		//console.log("Could not find "+func)
	}
}

/*function testmessaging(param1, param2) {
	alert(param1);
}*/

function cometProcessLocation(cometobj) {

}

function processHook(functionname) {
	if (eval ('typeof '+functionname+'=="function"')){
		eval(functionname+'()');
	} else {
		//console.log("Could not find "+functionname)
	}
}

function cometPostAction(func, params) {
	$.ajax({ type: "POST", url: "/ajax/comet/post_action", async:false, data: {"func": func, "params": params }});
}

//Some more specific stuff
function update_content(contenttype,urlid) {
	$("#row_"+urlid).load("/edit/row/"+contenttype+"/"+urlid);
}

	function unlock_content(contenttype,urlid) {
		update_content(contenttype,urlid);
		//$("#row_"+urlid).children(".lock_container").children("span").fadeOut("medium");
	}
	
	function lock_content(urlid) {
		$("#row_"+urlid).children(".lock_container").children("span").fadeIn("medium");
	}
