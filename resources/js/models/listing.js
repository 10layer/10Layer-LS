var Doc = function(data) {
	var self = this;

	self._id = ko.observable(data._id);
	self.title = ko.observable(data.title);
	self.workflow_status = ko.observable(data.workflow_status);
	self.last_editor = ko.observable(data.last_editor);
	self.last_modified = ko.observable(data.last_modified);
	self.start_date = ko.observable(data.start_date);
	self.selected = ko.observable(false);
}

var Field = function(data) {
	var self = this;

	self.name = ko.observable(data.name);
	self.fieldname = ko.observable(data.fieldname);
	self.selected = ko.observable(data.selected || false);
}

var ListingModelView = function() {
	var self = this;
	self.docs = ko.observableArray([]);
	content_type=$(document.body).data('content_type');
	self.content_type = ko.observable(content_type);
	var searchstring=$("#listSearch").val();
	if (searchstring=='Search') {
		searchstring='';
	}
	self.searchstring = ko.observable("");
	self.offset = ko.observable(0);
	self.pg = ko.observable(0);
	self.selected_count = ko.observable(0);
	self.order_by = ko.observable("last_modified DESC");
	self.fields = ko.observableArray([
		new Field({
			name: "Title",
			fieldname: "title"
		}),
		new Field({
			name: "Last Edit",
			fieldname: "last_modified",
			selected: "desc"
		}),
		new Field({
			name: "Edited By",
			fieldname: "last_editor"
		}),
		new Field({
			name: "Start Date",
			fieldname: "start_date"
		}),
		new Field({
			name: "Workflow",
			fieldname: "workflow_status"
		})
	]);

	self.getData = function() {
		$.getJSON($(document.body).data('base_url') + "api/content?jsoncallback=?", { offset: self.offset(), search: self.searchstring(), content_type: self.content_type(), order_by: self.order_by(), api_key: $(document.body).data('api_key'), limit: 100, fields: [ "id", "title", "last_modified", "live", "start_date", "workflow_status", "last_editor" ] }, function(data) {
			var mapped = _.map(data.content, function(item) {
				return new Doc(item);
			});
			self.docs(mapped);
			update_pagination(content_type, data.count, self.pg(), 100);
		}).error(function(jqXHR, textStatus, errorThrown) {
			$("#msgdialog-header").html("Error");
			$("#msgdialog-body").html("<h4>"+textStatus+"</h4><p>"+jqXHR.responseText+"</p>");
			$("#msgdialog-buttons").html('<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>');
			$("#msgdialog").modal();
		});
	}

	self.clickSearch = function() {
		self.getData();
	}

	self.clickWorkflow = function(sender, e) {
		var workflow = $(e.target).attr("data-workflow");
		var items = [];
		_.each(self.docs(), function(doc) {
			if (doc.selected()) {
				items.push({ id: doc._id(), workflow_status: workflow });
			}
		});
		$.getJSON($(document.body).data('base_url') + "api/content/multiple/change_workflow?jsoncallback=?", { items: items, api_key: $(document.body).data('api_key') }, function(data) {
			_.each(self.docs(), function(doc) {
				if (doc.selected()) {
					doc.workflow_status(workflow);
				}
			});
		});
	}

	self.clickDelete = function(sender, e) {
		console.log("Delete clicked");
		var items = [];
		_.each(self.docs(), function(doc) {
			if (doc.selected()) {
				items.push({ id: doc._id() });
			}
		});
		self.selected_count(items.length);
		if (items.length == 0) {
			$("#msgdialog-header").html("Confirm Delete");
			$("#msgdialog-body").html("<h4>No documents selected</h4> <p>Please select some documents by ticking the checkboxes</p>");
			$("#msgdialog-buttons").html('<button class="btn" data-dismiss="modal" aria-hidden="true">Okay</button>');
			$("#msgdialog").modal();
			return false;
		}
		$("#msgdialogDelete").modal("show");
	}

	self.clickDoDelete = function(sender, e) {
		var items = [];
		_.each(self.docs(), function(doc) {
			if (doc.selected()) {
				// console.log(doc);
				items.push({ id: doc._id() });
			}
		});
		$.getJSON($(document.body).data('base_url') + "api/content/multiple/delete?jsoncallback=?", { items: items, api_key: $(document.body).data('api_key') }, function(data) {
			_.each(self.docs(), function(doc) {
				if (doc.selected()) {
					self.docs.remove(doc);
				}
			});
		});
	}

	self.clickChangeOrder = function(sender, e) {
		var order_by = sender.fieldname();
		_.each(self.fields(), function(field) {
			field.selected(false);
		});
		sender.selected("asc");
		if ($(e.target).attr("data-order") == "DESC") {
			order_by += " DESC";
			sender.selected("desc");
		}
		self.order_by(order_by);
		self.getData();
	}

	function update_pagination(content_type, count, offset, perpage) {
		$("#pagination").pagination(
			count,
			{
				items_per_page: perpage,
				current_page: offset,
				callback: function(pg) {
					self.pg(pg);
					var offset=(pg)*perpage;
					self.offset(offset);
					self.getData();
					return false;
				}
			}
		);
	}

	$(document).on("update", function(e, id) {
		_.each(self.docs(), function(doc) {
			if (doc._id() == id) {
				$.getJSON($(document.body).data('base_url') + "api/content/get?jsoncallback=?", { id: id, api_key: $(document.body).data('api_key') }, function(data) {
					self.docs.replace(doc, new Doc(data.content));
				});
			} 
		});
	});

	$(document).on("delete", function(e, id) {
		console.log("Delete", id);
		_.each(self.docs(), function(doc) {
			if (doc._id() == id) {
				self.docs.remove(doc);
			} 
		});
	});

	self.getData();
}