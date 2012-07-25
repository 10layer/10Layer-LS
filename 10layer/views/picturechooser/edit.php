<script>
	var urlid='<?= $urlid ?>';
	$(function() {
		
		function renderImg() {
			$.getJSON("/workers/picture/getsize/"+urlid, function(data) {
				var url="/workers/picture/display/"+urlid+"/"+$("#transform").val();
				url=url.replace(/%(.*?)%/g, function(key) {
					//console.log(key);
					return $(key.replace(/%/g,"")).val();
				});
				$("#renderedimg").attr("src",url);
			});
		}
		
		function changeImg() {
			$.getJSON("/workers/picture/getsize/"+urlid, function(data) {
				var w=data.width;
				var h=data.height;
				var msg="";
				if (w>300) {
					w=300;
					msg="Message was resized down from "+data.width+"x"+data.height;
				}
				if (h>300) {
					h=300;
					msg="Message was resized down from "+data.width+"x"+data.height;
				}
				$("#picmsg").html(msg);
				$("#width").val(w);
				$("#height").val(h);
				
			});
		}
		
		changeImg();
		renderImg();
		$("#transform").change(renderImg);
		$("#dorender").click(renderImg);
		
		$("#close").click(function() {
			window.opener.CKEDITOR.tools.callFunction('<?= $CKEditorFuncNum ?>',$("#renderedimg").attr("src"),  function() {
				// Get the reference to a dialog window.
				var element, dialog = this.getDialog();
				//console.log(dialog);
				// Check if this is the Image dialog window.
				if (dialog.getName() == 'image') {
					// Get the reference to a text field that holds the "alt" attribute.
					element = dialog.getContentElement( 'info', 'txtAlt' );
					// Assign the new value.
					if ( element ) 
						element.setValue( "<?= $title ?>" );
				}
			});
			window.close();
		});
	});
</script>
Transformation
<select name="transform" id="transform">
	<option value="scaleImage/%#width%/%#height%/true">Resize</option>
	<option value="cropThumbnailImage/%#width%/%#height%">Crop</option>
</select>
<br />
Width <input id="width" name="width" value="" /><br />
Height <input id="height" name="height" value="" /><br />
<div id="picmsg"></div>
<input type="button" id="dorender" value="Render" /><br />
<div id="rendered">
	<img id="renderedimg" src="/tlresources/file/images/ajax-loader.gif" />
</div>
<input type="button" id="close" value="Done" /><br />