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
						<td data-bind="attr: { class: workflow_status().toLowerCase() }"><a data-bind="text:title, attr: { href: '/edit/' + content_type() + '/' + _id() }" class='content-title-link'></a></td>
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