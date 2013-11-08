<script src="<?= $server ?>/socket.io/socket.io.js"></script>
<script>
	$(function(){
		var iosocket = io.connect("<?= $server ?>");
		iosocket.on('connect', function () {
			//Some online indication
			$("#online_indicator").show();
			$("#offline_indicator").hide();

			iosocket.on('update', function(message) {
				console.log("update", message);
				
			});
			
			iosocket.on('disconnect', function() {
				//Some offline indication
				$("#online_indicator").hide();
				$("#offline_indicator").show();
			});
		});

	});
</script>