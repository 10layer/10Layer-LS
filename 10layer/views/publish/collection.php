<script language="javascript">
	$(function() {
		$(document).ajaxError(function(e, xhr, settings, exception) { 
			$("#dyncontent").html(xhr.responseText); 
		});
		
		$("#dyncontent").ajaxComplete(function() {
			cl.hide();
		});
		
		$("#dyncontent").ajaxStart(function() {
			cl.show();
		});
		
		$("#dyncontent").load("<?= base_url()."list/simple/{$content_type->urlid}/1" ?>", function() { 
			 
		});
		
		$("#dyncontent").delegate(".pagination > a","click",function() {
			var url=$(this).attr("href");
			$("#dyncontent").load("<?= base_url()?>"+url, function() {  });
			return false;
		});
		
		function search() {
			var s=$("#popupSearch").val();
			$("#dyncontent").load("/list/simple/<?= $content_type->urlid ?>/search/"+escape(s));
		}
		
		$("#dyncontent").delegate("#popupSearch", "click", function() {
			if ($(this).val()=="Search...") {
				$(this).val("");
			}
		});
		
		$("#dyncontent").delegate("#popupSearch","keypress",function() {
			clearTimeout($.data(this, 'timer'));
			var wait = setTimeout(search, 1000);
			$(this).data('timer', wait);
		});
		
		
		$(".fireaction").live("click", function(){
			var urlid=$(this).children(":first").attr("urlid");
			location.href="/publish/collection/<?= $content_type->urlid ?>/"+urlid;
		});
		
		$(".fireaction2").live("click", function(){
			var urlid=$(this).attr("urlid");
			location.href="/publish/collection/<?= $content_type->urlid ?>/"+urlid;
		});
		
		
		$("#dyncontent").delegate(".parents","click",function() {
			var urlid=$(this).children(":first").attr("urlid");
			location.href="/publish/collection/<?= $content_type->urlid ?>/"+urlid;
		});
		
	});
</script>


<div id="msgdialog"></div>
<div id="dyncontent">
</div>
<div id="createdialog"></div>
