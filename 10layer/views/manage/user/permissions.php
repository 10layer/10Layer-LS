<script language="JavaScript">
	$(function() {
		$(".checkbox").each(function() {
			$(this).hide();
			if ($(this).is(":checked")) {
				$(this).after("<div class='checkdisplay checked' name='"+$(this).attr("name")+"' value='"+$(this).val()+"'></div>");
			} else {
				$(this).after("<div class='checkdisplay unchecked' name='"+$(this).attr("name")+"' value='"+$(this).val()+"'></div>");
			}
		});
		
		
		
		$("#permission_table").delegate(".checkdisplay","click",function() {
			var name=$(this).attr("name");
			var val=$(this).attr("value");
			var checked=$(this).hasClass("checked");
			$(".checkbox").each(function() {
				if ($(this).attr("name")==name && $(this).attr("value")==val) {
					if (checked) {
						$(this).attr("checked",false);
					} else {
						$(this).attr("checked","checked");
					}
				}
			});
			if (checked) {
				$(this).removeClass("checked");
				$(this).addClass("unchecked");
			} else {
				$(this).removeClass("unchecked");
				$(this).addClass("checked");
			}
		});
	});
</script>

<?php
	$sections=array();
	$subsections=array();
	foreach($urls as $url) {
		if (substr($url,0,1)=="/") {
			$url=substr($url,1);
		}
		$tmp=explode("/",$url);
		if (!in_array($tmp[0],$sections)) {
			$sections[]=$tmp[0];
		}
		$subsections[$tmp[0]][]=$tmp;
	}
?>
<form method="post">
<input type="hidden" name="update" value="1" />
<table id="permission_table" class="colourful bordered">
<?php
	foreach($sections as $section) {
?>	
	<tr>
		<td class="bold big"><?= $section ?></td>
		<?php
			foreach($permissionTypes as $pt) {
		?>
		<td><?= $pt->name ?></td>
		<?php
			}
		?>
	</tr>
	<?php
		$odd="odd";
		foreach($subsections[$section] as $subsection) {
			
	?>
		<tr class="<?= $odd ?>">
			<td><?= url_to_text(implode("/",$subsection)) ?></td>
			<?php
				foreach($permissionTypes as $pt) {
					$perms=$this->model_user->getPermissionByUrl("/".trim(implode("/",$subsection)));
					//print_r($perms);
			?>
			<td>
				<input class='checkbox' type='checkbox' name="permission[<?= $pt->id ?>][]" value='<?= trim(implode("/",$subsection)) ?>' 
				<?php
					foreach($perms as $perm) {
						if ($perm->permission_id==$pt->id) {
							print "checked='checked'";
						}
					}
				?> />
			</td>
			<?php
				}
			?>
		</tr>
	<?php
			if (!empty($odd)) {
				$odd="";
			} else {
				$odd="odd";
			}
		}
	?>
	<tr><td colspan="<?= (sizeof($permissionTypes) + 1) ?>"><input type='submit' name='submit' value='Update' class="button" /></td></tr>
<?php
	}
?>
</table>
</form>