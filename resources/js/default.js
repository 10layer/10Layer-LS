$(function() {
	set_title();

	$(".button").each(function() {
		$(this).button();
	});
	
	$(window).scroll(function() {
		var scroll=$(this).scrollTop();
		var headerheight=$("#header").height();
		$(".pin").each(function() {
			if (scroll > headerheight) {
				$(this).addClass("pinned");
			} else {
				$(this).removeClass("pinned");
			}
		});
	});
	
	findErrorMsgs();
});

function set_title(){
	var str = location.pathname;	
	var title = "10Layer CMS :" + str.split('/').join(' -> ') ;
	document.title = title;
}


function findErrorMsgs() {
	$(".errormsg").each(function() {
		$(this).dialog({
			width: 600,
			modal: true,
			buttons: {
				"Show Backtrace": function() {
					$(this).find(".backtrace").show();
				},
				"Report": function() {
					alert("Coming soon");
				},
				"Dismiss": function() {
					$(this).dialog("close");
				},
				"Dismiss all": function() {
					$(".errormsg").each(function() {
						$(this).dialog("close");
					});
				},
			}
		});
	});
}