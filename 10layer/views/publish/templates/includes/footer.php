				<div id="footer">
					<div id="opalogo">
						<a href="http://www.dmma.co.za/"><img src="<?= live_base_url() ?>/images/dmma.png" target="_blank" /></a>
					</div>
					<div id="copyright">Copyright 2009 - 2010. All rights reserved.</div>
					
					
				</div>
			</div>
			<?php
				$admin=$this->uri->segment(1);
				if ($admin!="admin") {
				?>
					<script language="JavaScript">
						$(function() {
							if (!jQuery.support.boxModel) {
								$(window).scroll(function() {
									$('#advert').css('top', $(this).scrollTop() + "px");
								});
								$('#advert').css('top', $(this).scrollTop() + "px");
								$('#advert').css('position', "absolute");
							}
						});
					</script>
					<?php
						//$this->advert->draw();
					?>
			<?php
				}
			?>
		</div>
		<script type="text/javascript">
		var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
		document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
		</script>
		<script type="text/javascript">
		try {
		var pageTracker = _gat._getTracker("UA-10686674-1");
		pageTracker._trackPageview();
		} catch(err) {}</script>
		<!-- START Nielsen Online SiteCensus V6.0 -->

		<!-- COPYRIGHT 2009 Nielsen Online -->
		<script type="text/javascript" src="//secure-za.imrworldwide.com/v60.js">
		</script>
		<script type="text/javascript">
		var pvar = { cid: "za-dailymaverick", content: "0", server: "secure-za" };
		var feat = { surveys_enabled: 1 };
		var trac = nol_t(pvar, feat);
		trac.record().post().invite();
		</script>
		<noscript>
		<div>
		<img src="//secure-za.imrworldwide.com/cgi-bin/m?ci=za-dailymaverick&amp;cg=0&amp;cc=1&amp;ts=noscript"
		width="1" height="1" alt="" />
		</div>
		</noscript>
		<!-- END Nielsen Online SiteCensus V6.0 -->
	</body>
</html>