/*
* Bootstrap Plugin
*
* @author Jason Norwood-Young <jason@10layer.com>
* @version 0.1
*/
( function() {
	CKEDITOR.plugins.add( 'bootstrap', {
		icons: 'icon.png',
		init: function(editor) {
			editor.addCommand( 'bootstrap_scafolding', {
				exec: function(editor) {
					editor.insertHtml("<div class='row'><div class='span3'>Hello</div></div>");
				}
			});
			
			editor.ui.addButton("Scafolding", {
				label: "Bootstrap Scafolding",
				command: "bootstrap_scafolding",
				toolbar: "insert",
				icon : this.path + 'images/icon.png'

			});
			
		}
	});
})();