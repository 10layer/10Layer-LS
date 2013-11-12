var editors=[];
var editor = false;



function initCKEditor() {
	var config = {
		// toolbar: [
		// 	['Source','-','Bold', 'Italic', '-', 'NumberedList', 'BulletedList', '-', 'Link', 'Unlink','-','Image','-','Maximize']
		// ],
		filebrowserImageBrowseUrl : '/workers/picturechooser/browse',
		filebrowserWindowWidth  : 1000,
		filebrowserWindowHeight : 600,
		height: 400,
		extraPlugins: 'youtube',
		contentsCss: '/resources/bootstrap/css/bootstrap.min.css',
		extraAllowedContent: ['*(*)', 'div(*)', 'i(*)'],
		// allowedContent: true,
		// stylesSet: "bootstrap:/resources/js/styles.js",
		protectedSource: [( /<i[\s\S]*?\>/g ), ( /<\/i[\s\S]*?\>/g ) ], //allows <i> tag
	};
	$('.wysiwyg').ckeditor(config);
	editor = $('.wysiwyg').ckeditorGet();
	var filter = new CKEDITOR.filter( 'i' );
}

