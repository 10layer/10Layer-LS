	</div>
	<div class="footer" id="footer">
		<div class="container">
			Copyright <?= date("Y") ?> 10Layer Pty (Ltd)
		</div>
	</div>
	<script type="text/javascript">
	

	$(function() {
		var s = skrollr.init({
		forceHeight: false,
		easing: {
			vibrate: function(p) {
				return Math.sin(p * 10 * Math.PI);
			}
		}
		});
		$(".scroll").click(function(e) {
			var href = $(this).attr("href");
			console.log("Hello");
			console.log(href.indexOf("#"));
			if (href.indexOf("#") == 0) {
				e.preventDefault();
				//href = href.substr(1);
				console.log(href);
				s.animateTo($(href).offset().top, { duration: 500 });
			}
		});
	});
	</script>
</html>