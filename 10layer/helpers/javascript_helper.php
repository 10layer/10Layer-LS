<?php
	function link_js($filename) {
		print "<script type='text/javascript' src='$filename'></script>\n";
		return true;
	}
	
	function tinymce() {
	?>
		<script type='text/javascript' src='/tlresources/file/tinymce/jquery.tinymce.js'></script>
		<script type="text/javascript">
			
			function init_tinymce() {
				//TinyMCE
				var spell_timer=false;
				$('.richedit').tinymce({
					script_url: '/tlresources/file/tinymce/tiny_mce.js',
					strict_loading_mode: true,
					
					theme : "advanced",
					theme_advanced_toolbar_location: "top",
					theme_advanced_statusbar_location: "bottom",
					theme_advanced_resizing: "true",
					plugins: "searchreplace, fullscreen, wordcount, spellchecker",
					
					theme_advanced_path : false,
					
					theme_advanced_buttons1: "bold, italic, underline, |, cut, copy, paste, pastetext, pasteword, |, search, replace, |, bullist, numlist, |, undo, redo, |, link, unlink, |, charmap, image, |, code, fullscreen, spellchecker",
					
					theme_advanced_buttons2: null,
					theme_advanced_buttons3: null,
					spellchecker_rpc_url: "/workers/spellcheck",
					spellchecker_languages : "English=en, +English UK=en_GB",
					spellchecker_report_no_misspellings: false,
					gecko_spellcheck: false,
					no_events: true,
					oninit: function() {
						//var ed=this.activeEditor;
						//var tinymce=this;
						//ed.controlManager.setActive('spellchecker', true);
						//this.execCommand('mceSpellCheck', true);
						/*ed.onKeyUp.add(function(ed, e) {
							clearTimeout(spell_timer);
							spell_timer=setTimeout(function() {updateSpelling(tinymce)}, 1000);
							//markDirty(e);
					    });*/
					    var found=false;
					    $("#contentform").children().each(function() {
					    	if (!found && ($(this).attr("type") != "hidden" && $(this).is("input") || $(this).is("textarea"))) {
						    	$(this).focus();
						    	found=true;
					    	}
					    });
					},
				});
				
				function updateSpelling(tinymce) {
					tinymce.execCommand('mceWordcountCheck');
					//tinymce.execCommand('mceActiveSpellCheck');
				}
			
			};
			
		</script>
	<?php
	}
	
	function ckeditor() {
	?>
		<script type='text/javascript' src='/tlresources/file/ckeditor2/ckeditor.js'></script>
		<script type='text/javascript' src='/tlresources/file/ckeditor2/adapters/jquery.js'></script>
		<script type="text/javascript"> 
			$(function() {
				//initCKEditor();
			});
			function clearCKEditor() {
				while (editors.length > 0) {
					editor=editors.pop();
					//editor.destroy();
				}
			}
			
			var editors=new Array();
			
			function initCKEditor() {
				clearCKEditor();
				var config = { 
					toolbar: [
						['Source','-','Bold', 'Italic', '-', 'NumberedList', 'BulletedList', '-', 'Link', 'Unlink','-','Image','-','Maximize']
					],
					skin: 'kama',
					
					filebrowserImageBrowseUrl : '/workers/picturechooser/browse',
					
					filebrowserWindowWidth  : 1000,
					filebrowserWindowHeight : 600,
					on : { 'paste' : function(ev) {
						ev.data.html=parsePaste(ev.data.html);
					} }
					
				};
				$('.richedit').ckeditor(config);
				editors[editors.length] = $('.richedit').ckeditorGet();	
			}
			
			function parsePaste(data) {
				data=data.replace(/<meta(?:.|\s)*?>/g,"");
				
				data=data.replace(/<span(?:.|\s)*?>/g,"");
				data=data.replace(/<\/span>/g,"");
				data=data.replace(/<div(?:.|\s)*?>/g,"");
				data=data.replace(/<\/div>/g,"");
				data=data.replace(/<font(?:.|\s)*?>/g,"");
				data=data.replace(/<\/font>/g,"");
				
				data=data.replace(/<iframe(?:.|\s)*?>/g, "");
				data=data.replace(/<\/iframe>/g,"");
				
				data=data.replace(/<fb:like(?:.|\s)*?>/g, "");
				data=data.replace(/<\/fb:like>/g,"");
				
				data=data.replace(/<br><br>/g,"<br>");
				data=data.replace(/<br(?:.|\s)*?>/g,"<p>");
				
				data=data.replace(/<p>$$/g,"");
  				//console.log(data);
				return data;
			}
			
		</script>
	<?php
	}
?>