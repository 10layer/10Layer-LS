<?php 
	$data["menu1_active"]="edit";
	$data["menu2_active"]="edit/".$content_type;
	$this->load->view('templates/header',$data);
?>
<?php
	$this->socketio->js();
?>
<script src="/resources/js/jquery.pagination.js"></script>
<script src="/resources/knockout/knockout-2.2.1.js"></script>
<script src="/resources/bootstrap-datepicker/js/bootstrap-datepicker-ck.js"></script>
<script>
	var currentpage=false;
	
	var content_types=<?= json_encode($content_types); ?>;

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

	var ModelView = function() {
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

		self.getData = function() {
			$.getJSON("<?= base_url() ?>api/content?jsoncallback=?", { offset: self.offset(), search: self.searchstring(), content_type: self.content_type(), order_by: "last_modified DESC", api_key: $(document.body).data('api_key'), limit: 100, fields: [ "id", "title", "last_modified", "live", "start_date", "workflow_status", "last_editor" ] }, function(data) {
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
					// console.log(doc);
					items.push({ id: doc._id(), workflow_status: workflow });
				}
			});
			$.getJSON("<?= base_url() ?>api/content/multiple/change_workflow?jsoncallback=?", { items: items, api_key: $(document.body).data('api_key') }, function(data) {
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
			$.getJSON("<?= base_url() ?>api/content/multiple/delete?jsoncallback=?", { items: items, api_key: $(document.body).data('api_key') }, function(data) {
				_.each(self.docs(), function(doc) {
					if (doc.selected()) {
						self.docs.remove(doc);
					}
				});
			});
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
					$.getJSON("<?= base_url() ?>api/content/get?jsoncallback=?", { id: id, api_key: $(document.body).data('api_key') }, function(data) {
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

	$(function() {
		
		$(document.body).data('api_key', '<?= $this->session->userdata('api_key') ?>');
		$(document.body).data('content_type', '<?= $content_type ?>');
		$(document.body).data('page', 'list');

		ko.applyBindings(new ModelView());

		$(document).on('click', '#select_all', function() {
			$(".select_item").prop("checked", $(this).prop("checked"));
		});
				
	}); //End of $(function)
	
	version_map=new Array( "", "New", "Edited", "Published" );
	
</script>

<div id="contentlist" class="boxed full">
	<div class="row">
		<div id='pagination' class='pagination span7'></div>
		<div id="listSearchContainer" class="input-append span3">
			<input data-bind="value: searchstring" type="text" id="list-search" placeholder="Search..." />
			<input data-bind="click: clickSearch" type="button" class="btn" value="Search" />
		</div>
		<div id="group_actions" class="btn-group span1">
			<a class="btn dropdown-toggle" data-toggle="dropdown" href="#">With selected <span class="caret"></span></a>
			<ul class="dropdown-menu">
				<li><a data-bind="click:clickWorkflow" href="#" class="workflow_change" data-workflow="New">Workflow - New</a></li>
				<li><a data-bind="click:clickWorkflow" href="#" class="workflow_change" data-workflow="Edited">Workflow - Edited</a></li>
				<li><a data-bind="click:clickWorkflow" href="#" class="workflow_change" data-workflow="Published">Workflow - Published</a></li>
				<li><a data-bind="click:clickDelete" href="#" id="_delete_multiple">Delete</a></li>
			</ul>
		</div>
	</div>
	<div class="row">
		<div id='content-table' >
			<table class='table table-bordered table-striped table-condensed'>
	    		<thead>
	    			<tr>
						<th><input type="checkbox" class="select-all" id="select_all" /></th>
						<th>Title</th>
						<th>Last Edit</th>
						<th>Edited by</th> 
						<th>Start Date</th>
						<th>Workflow</th>
					</tr>
				</thead>
				<tbody data-bind="foreach:docs">
					<tr>
						<td><input data-bind="checked: selected" type="checkbox" class="select_item" name="select_item" ></td>
						<td data-bind="attr: { class: workflow_status().toLowerCase() }"><a data-bind="text:title, attr: { href: '/edit/'+content_type+'/'+_id() }" class='content-title-link'></a></td>
						<td style="width: 100px" data-bind="text:dateToString(last_modified())"></td>
						<td data-bind="text:last_editor"></td>
						<td data-bind="text:dateToString(start_date())"></td>
						<td data-bind="text:workflow_status, attr: { class: 'content-workflow-'+workflow_status() }"></td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</div>

<div class="modal hide fade" id="msgdialog" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
		<h3 id="msgdialog-header"></h3>
	</div>
	<div class="modal-body" id="msgdialog-body">
	</div>
	<div class="modal-footer">
		<div id="msgdialog-buttons" class="btn-group">
		</div>
	</div>
</div>

<div class="modal hide fade" id="msgdialogDelete" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
		<h3 id="msgdialog-header">Confirm Delete</h3>
	</div>
	<div class="modal-body" id="msgdialog-body">
		<p>Are you sure you want to delete <span data-bind="text:selected_count"></span> documents?</p>
	</div>
	<div class="modal-footer">
		<div id="msgdialog-buttons" class="btn-group">
			<button data-bind="click:clickDoDelete" class="btn btn-danger" data-dismiss="modal" aria-hidden="true" id="btn_confirm_multi_delete">Delete</button> <button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
		</div>
	</div>
</div>

<div id="dyncontent"></div>

<?php
	$this->load->view("templates/footer");
?>