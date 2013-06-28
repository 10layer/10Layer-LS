	</div>
	<div class="footer" id="footer">
		<div class="container">
			Copyright <?= date("Y") ?> 10Layer Pty (Ltd) | <a href="mailto:info@10layer.com">info@10layer.com</a> | 
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
		$("a").click(function(e) {
			var href = $(this).attr("href");
			if (href.indexOf("#") > -1) {
				href = href.substr(href.lastIndexOf("#"));
				if($(href).offset()) {
					e.preventDefault();
					s.animateTo($(href).offset().top, { duration: 500 });
					return false;
				}
			}
			return true;
		});
	});
	</script>
</html>