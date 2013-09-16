var editors=[];
var editor = false;

function clearCKEditor() {
	while (editors.length > 0) {
		editor=editors.pop();
	}
}

function parsePaste(data) {
	data=data.replace(/<meta(?:.|\s)*?>/g,"");
	
	// data=data.replace(/<span(?:.|\s)*?>/g,"");
	// data=data.replace(/<\/span>/g,"");
	// data=data.replace(/<div(?:.|\s)*?>/g,"");
	// data=data.replace(/<\/div>/g,"");
	data=data.replace(/<font(?:.|\s)*?>/g,"");
	data=data.replace(/<\/font>/g,"");
	
	//data=data.replace(/<iframe(?:.|\s)*?>/g, "");
	//data=data.replace(/<\/iframe>/g,"");
	
	data=data.replace(/<fb:like(?:.|\s)*?>/g, "");
	data=data.replace(/<\/fb:like>/g,"");
	
	// data=data.replace(/<br><br>/g,"<br>");
	// data=data.replace(/<br(?:.|\s)*?>/g,"<p>");
	
	// data=data.replace(/<p>$$/g,"");
	return data;
}

function initCKEditor() {
	clearCKEditor();
	var config = {
		// toolbar: [
		// 	['Source','-','Bold', 'Italic', '-', 'NumberedList', 'BulletedList', '-', 'Link', 'Unlink','-','Image','-','Maximize']
		// ],
		filebrowserImageBrowseUrl : '/workers/picturechooser/browse',
		filebrowserWindowWidth  : 1000,
		filebrowserWindowHeight : 600,
		height: 400,
		extraPlugins: 'youtube',
		// on : { 'paste' : function(ev) {
		// 	ev.data.html=parsePaste(ev.data.html);
		// } }
	};
	$('.wysiwyg').ckeditor(config);
	editors[editors.length] = $('.wysiwyg').ckeditorGet();
}

