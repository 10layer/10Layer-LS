
var xhr_reqs = [];

function create_autocomplete_item(item, content_type, multiple_status, field_name){
	var field = { multiple:multiple_status, contenttype: content_type, name: field_name, value: item._id };
	var newel = _.template($('#field-autocomplete-item').html(), { title:item.title, field: field, item:item});
	return newel;
}

function update_content(data) {
	var results_data=_.template($('#'+data.template).html(), data);
	$('#'+data.element).html(results_data);
}

$(function() {
	$(".result_container div a.close").live('click', function(){
		$(this).parent().remove();
	});
	
	$('<div class="wordcount_container">Word count:&nbsp;<div class="wordcount_result"></div></div>').insertAfter(".wordcount");
	
	$(".wordcount").keyup(function() {
		get_wordcount($(this));
	});
	
	$(".wordcount").each(function() {
		get_wordcount($(this));
	});
			
	$(".remover").live("click", function() { //Not sure if this is still used?
		$(this).parent().remove();
		return false;
	});
	
	$(document).on('keydown', '.datetime_hour', function(e) {
		var key = e.keyCode;
		if ((key > 33) && (key < 47)) {
			return false;
		}
		if ((key > 58) && (key < 126)) {
			return false;
		}
	});
	
	$(document).on('blur', '.datetime_hour', function() {
		var v = 0;
		if (($(this).val())) {
			v = Number($(this).val());
			$(this).val(leadingZeros(v));
			if ($(this).val().length > 2) {
				var s = $(this).val();
				$(this).val(s.substring(s.length - 2, s.length));
				v = parseInt($(this).val());
			}
			if (v > 23) {
				$(this).val("23");
			}
		} else {
			$(this).val("00");
		}
		if (!isFinite($(this).val())) {
			$(this).val("00");
		}
	});
	
	$(document).on('blur', '.datetime_minute', function() {
		if ($(this).val()) {
			var v = Number($(this).val());
			$(this).val(leadingZeros(v));
			if ($(this).val().length > 2) {
				var s = $(this).val();
				$(this).val(s.substring(s.length - 2, s.length));
				v = parseInt($(this).val());
			}
			if (v > 59) {
				$(this).val("59");
			}
		} else {
			$(this).val("00");
		}
		if (!isFinite($(this).val())) {
			$(this).val("00");
		}
	});
	
	$(document).on('keydown', '.datetime_minute', function(e) {
		var key = e.keyCode;
		if ((key > 33) && (key < 47)) {
			return false;
		}
		if ((key > 58) && (key < 126)) {
			return false;
		}
	});
	
	$(document).on('keyup', '.datetime_change', function() {
		updateDateTime(this);
	});
	
	$(document).on('change', '.datetime_change', function() {
		updateDateTime(this);
	});
	
	function updateDateTime(sender) {
		var parent = $(sender).parent();
		var val = "";
		val += parent.children('.datetime_date').val();
		val += " ";
		val += (parent.children('.datetime_hour').val()) ? parent.children('.datetime_hour').val() : "00";
		val += ":";
		val += (parent.children('.datetime_minute').val()) ? parent.children('.datetime_minute').val() : "00";
		$(sender).siblings(':hidden').val(stringToDate(val));
	}
	
	$(document).on('click', '.deepsearch-search', function() {
		var multiple_status = $(this).prev().attr('multiple');
		var field_name = $(this).prev().attr('fieldname');
		var searchel=$(this).prev();
		var origel = this;
		var search=$(this).prev().val();
		var content_type=$(this).prev().attr("contenttype");
		var optionel=$(this).parent().siblings('.deepsearch-options');
		var resultel=$(this).parent().siblings('.deepsearch-results');
		var indicator = $(this);
		while(xhr_reqs.length>0) {
			jqXHR=xhr_reqs.pop();
			jqXHR.abort();
		}
		indicator.html("<span class='label label-success'>Searching...</span>");
		xhr_reqs.push(
			$.getJSON("/api/content", { search: search, content_type: content_type, limit: 20, fields: ["_id", "title"], order_by: "last_modified" }, function(data) {
				var pos = $.extend({}, searchel.offset(), {
		    		height: origel.offsetHeight
				});
	
				optionel.css({
					top: pos.top + pos.height
					, left: pos.left
				});
				
				optionel.show();
				
				optionel.html('');

				_.each(data.content, function(item) {
					var el=$('<li><a href="#">'+item.title+'</a></li>').click(function(e) {
						e.stopPropagation();
						e.preventDefault();
						optionel.hide();
						resultel.append(create_autocomplete_item(item, content_type, multiple_status, field_name));
						searchel.val("");
					});
					optionel.append(el);
					indicator.html('Search');
				});
			})
		);
	});

	$(document).on('change', 'input[type=file]', function() {
		var files=this.files; //FileList object
		var el=$(this);
		var container=el.parent();
		var multiple = el.attr("data-multiple");
		var contenttype = el.attr("data-contenttype");
		var name = el.attr("data-name");
		container.find('.progress-container').empty();
		_.each(files, function(f, id) {
			var filename = f.name;
			// console.log(f);
			var uid = name + "_" + id;
			
			$.ajax({
				url: "/api/files/upload?api_key=" + $(document.body).data('api_key') + "&filename="+f.name,  //server script to process data
				type: 'POST',
				xhr: function() {  // custom xhr
					var myXhr = $.ajaxSettings.xhr();
					if (myXhr.upload) { // check if upload property exists
						myXhr.upload.addEventListener('progress', function(data) {
							var percentage = Math.round((data.position / data.total) * 100);
							container.find('.progress-container #' + uid + ' .progress .bar').css('width', percentage + '%');
						}, false); // for handling the progress of the upload
					} // else throw an error here maybe?
					return myXhr;
				},
			
				//Ajax events
				beforeSend: function(data) {
					container.find('.progress-container').append(_.template($("#field-image-progress").html(), { uid: uid, filename: filename }));
					container.find('.progress-container #' + uid + ' .progress').show();
					container.find('.progress-container #' + uid + '').slideDown();
					container.find('.progress-container .progress .bar').removeClass('bar-success').removeClass('bar-danger');
				},
				success: function(data) {
					
					if (data.error) {
						container.find('.progress-container #' + uid + ' .progress').hide();
						container.find('.progress-container #' + uid + ' .alert').removeClass('alert-success').addClass('alert-error').html('<h4>File upload failed for '+filename+'</h4> '+data.message).slideDown(500).delay(2000).slideUp(500);
						container.find('.progress-container #' + uid + ' .progress .bar').removeClass('bar-danger').addClass('bar-success');
						return false;
					}
					var fullname = data.content.full_name;
					var filename = data.content.filename;
					container.find('.progress-container #' + uid + ' .progress').hide();
					container.find('.progress-container #' + uid + ' .alert').addClass('alert-success').removeClass('alert-error').html('File '+ filename +' uploaded').show().delay(1000).slideDown(500).delay(2000).slideUp(500);
					
					if (multiple == 1) {
						container.find('.preview-image-items').prepend(_.template($("#field-image-item").html(), { value: fullname, field: { value: fullname, name: name, contenttype: contenttype, multiple: multiple } } ));
					} else {
						container.find('.preview-image-items').html(_.template($("#field-image-item").html(), { value: fullname, field: { value: fullname, name: name, contenttype: contenttype, multiple: multiple } } ));
					}
					el.val(""); //Clear the file upload so we don't upload on form submission
				},
				error: function(xhr, s) {
					container.find('.progress-container #' + uid + ' .alert').removeClass('alert-success').addClass('alert-error').html('File upload failed: '+s).slideDown(500).delay(2000).slideUp(500);
					container.find('.progress-container #' + uid + ' .progress .bar').removeClass('bar-success').addClass('bar-danger');
				},
				// Form data
				data: f,
				//Options to tell JQuery not to process data or worry about content-type
				cache: false,
				contentType: false,
				processData: false,
				timeout: 600000 //10 mins
			});
		});
	});
	
});

function get_wordcount(sender) {
	sender.nextAll(".wordcount_container").first().find(".wordcount_result").load("/workers/ajax_edit/wordcount", {"str": sender.val()});
}

function init_form() {	
	$(".chzn-select").chosen();
	
	$(".my-datepicker").datepicker({ autoclose: true });
	
	var autocomplete_timer = false;

	$(".autocomplete").each(function(){
		var self = $(this);
		var self_val = '';
		var origel = this;
		self.keyup(function(){
			if ($.trim(self_val) != $.trim(self.val())) {
				self_val = self.val();
				clearTimeout(autocomplete_timer);
				autocomplete_timer = setTimeout(function(){
				var searchel=self.prev();
				var multiple_status = self.attr('multiple');
				var field_name = self.attr('fieldname');
				var search=self.prev().val();
				var optionel=self.parent().siblings('.options');
				var resultel=self.parent().siblings('.result_container');
				var search_content_type = self.attr("contenttypes");
				var url="/api/content/listing/";
				var search = self.val();
				var indicator = self.parent().siblings('.indicator');
				while(xhr_reqs.length>0) {
					jqXHR=xhr_reqs.pop();
					jqXHR.abort();
				}
				indicator.show();
				xhr_reqs.push(
					$.getJSON("/api/content", { search: search, content_type: search_content_type, limit: 20, fields: ["_id", "title"], order_by: "last_modified" }, function(data) {
						var pos = $.extend({}, searchel.offset(), {
							height: origel.offsetHeight
						});
				
						optionel.css({
							top: pos.top + pos.height
							, left: pos.left
						});
						
					
						if(data.content.length > 0){
							optionel.show();
						}else{
							optionel.hide();
							indicator.hide();
						}
						optionel.html('');
						_.each(data.content, function(item) {
							var el=$('<li><a href="#">'+item.title+'</a></li>').click(function(e) {
								e.stopPropagation();
								e.preventDefault();
								optionel.hide();
								resultel.append(create_autocomplete_item(item, content_type, multiple_status, field_name));
								searchel.val("");
							});
							optionel.append(el);
							indicator.hide();
						});
					})
				)}, 500);
			}
		});
	});
	
	$(".countchars").each(function() {
		if ($(this).hasClass("countdown")) {
			var max=$(this).attr("max");
			var current=max-$(this).val().length;
			if (current>=0) {
				$(this).after("<div class='charcount'>"+current+" chars remaining</div>");
			} else {
				$(this).after("<div class='charcount red'>"+Math.abs(current)+" chars over</div>");
			}
			
			$(this).keyup(function() {
				var max=$(this).attr("max");
				var current=max-$(this).val().length;
				if (current>=0) {
					$(this).next().html(current+" chars remaining").removeClass("red");
				} else {
					$(this).next().html(Math.abs(current)+" chars over").addClass("red");
				}
			});
		} else {
			var current=$(this).val().length;
			$(this).after("<div class='charcount'>"+current+" chars</div>");
			$(this).keyup(function() {
				current=$(this).val().length;
				$(this).next().html(current+" chars");
			});
		}
		$(this).removeClass("countchars");
	});
	
	if ($(".wysiwyg").length) {
		//init_tinymce();
		//clearCKEditor();
		initCKEditor();
	}
	
	var content_type=$(document.body).data('content_type');
	var urlid=$(document.body).data('urlid');
}