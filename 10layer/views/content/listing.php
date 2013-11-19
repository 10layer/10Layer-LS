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
<script type="text/javascript" src="/resources/js/models/listing-ck.js"></script>
<script>
	$(function() {
		
		$(document.body).data('api_key', '<?= $this->session->userdata('api_key') ?>');
		$(document.body).data('content_type', '<?= $content_type ?>');
		$(document.body).data('page', 'list');
		$(document.body).data('base_url', '<?= base_url() ?>');

		ko.applyBindings(new ListingModelView());
				
	}); //End of $(function)
	
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
						<!-- ko foreach: fields -->
						<th style="min-width: 100px"><span data-bind="text: name"></span> <a href="#" data-bind="click: $parent.clickChangeOrder, clickBubble: false"><i data-bind="css: { 'border-bottom': selected() == 'desc' }" data-order="DESC" class="icon-chevron-up pull-right border-bottom"></i></a> <a href="#" data-bind="click: $parent.clickChangeOrder, clickBubble: false"><i data-bind="css: { 'border-bottom': selected() == 'asc' }" class="icon-chevron-down pull-right"></i></a> </th>
						<!-- /ko -->
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

<?php
	$this->load->view("templates/footer");
?>