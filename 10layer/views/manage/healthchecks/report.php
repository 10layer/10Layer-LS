<div id="healthcheck">
<?php
	foreach($checks as $check) {
?>
<div class="check">
	
	<div class="description">
		<?php 
			if (sizeof($check["result"])==0) {
		?>
		<div class="ui-state-highlight ui-corner-all" style="margin-top: 20px; padding: 0 .7em;"> 
			<p><span class="ui-icon ui-icon-circle-check" style="float: left; margin-right: .3em;"></span>
			<div class="title"><?= $check["title"] ?></div> No errors found</p>
		</div>
		<?php
			} else {
		?>
		<div class="ui-state-error ui-corner-all" style="margin-top: 20px; padding: 0 .7em;"> 
			<p><span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span> <div class="title"><?= $check["title"] ?></div> Errors found</p>
			<div class="results">
		<?php
			foreach($check["result"] as $key=>$val) {
				if (sizeof($val)>0) {
					print "<div class='errorid'>$key</div>";
					foreach($val as $v) {
						print $v."<br />";
					}
				}
			}
		?></div>
		</div>
		<?php
			}
		?>
	</div>
	
</div>
<?php
	}
?>
</div>