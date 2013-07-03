<link rel="stylesheet" href="/resources/daterangepicker/daterangepicker.css" type="text/css" media="screen, projection" charset="utf-8" />
<script type="text/javascript" src="/resources/daterangepicker/daterangepicker.js"></script>
<script type="text/javascript" src="/resources/daterangepicker/date.js"></script>
<script src="/resources/knockout/knockout-2.2.1.js"></script>
<link rel="stylesheet" href="/resources/chosen/chosen.css">
<script src="/resources/chosen/chosen.jquery.js"></script>
<script type="text/javascript">
	

	var Content = function(data) {
		var self = this;
		self.title = ko.observable(data.title);
		self._id = ko.observable(data._id);
		self.content_type = ko.observable(data.content_type);
		self.isPublished = ko.observable(false);
		self.zone = ko.observable(0);
		self.clickEdit = function() {
			window.open("/edit/"+this.content_type()+"/"+this._id());
		}
	}
	
	var Zone = function(data, key) {
		var self = this;
		self.name = ko.observable((data.zone_name) ? data.zone_name : "");
		self.auto = ko.observable(data.zone_auto);
		self.max_items = ko.observable((data.zone_max_items) ? parseInt(data.zone_max_items) : 0);
		self.min_items = ko.observable((data.zone_min_items) ? parseInt(data.zone_min_items) : 0);
		self.urlid = ko.observable((data.zone_urlid) ? data.zone_urlid : "");
		self.id = ko.observable(key);
		self.content_types = ko.observableArray(data.zone_content_types);
		self.isActive = ko.observable(false);
		self.content = ko.observableArray([]);
		self.published = ko.observableArray([]);
		self.searchStr = ko.observable("");
		self.startDate = ko.observable(<?= date("U", strtotime('-30 day')) ?>);
		self.endDate = ko.observable(<?= time() ?>);
		
		
		$.getJSON("/api/publish/zone/<?= $collection->_id ?>/"+self.id(), function(data) {
			if (data.content && data.content.length) {
				var mapped = _.map( data.content, function(item) {
					return new Content(item);
				});
				var exclude = _.map( data.content, function(item) {
					return item._id;
				});
				self.published(mapped);
			} else {
				exclude=[];
			}
			$.getJSON("/api/content/listing?api_key=<?= $this->session->userdata("api_key") ?>", { content_type: self.content_types(), exclude: exclude, limit: 20, order_by: "last_modified DESC", fields: ["title", "_id", "content_type", "start_date" ] }, function(data) {
				var mapped = _.map(data.content, function(item) { return new Content(item) });
				self.content(mapped);
			});
		});
		
		self.clickPublish = function() {
			var pos = self.content.indexOf(this);
			var item = self.content.splice(pos, 1)[0];
			item.isPublished(true);
			item.zone = self.id;
			self.published.unshift(item);
		}
		
		self.clickUnpublish = function () {
			var pos = self.published.indexOf(this);
			var item = self.published.splice(pos, 1)[0];
			item.isPublished(false);
			self.content.unshift(item);
		}
		
		self.clickUp = function() {
			var pos = self.published.indexOf(this);
			if (pos <= 0) {
				return;
			}
			var tmp = self.published();
			self.published.splice(pos-1, 2, tmp[pos], tmp[pos-1]);
		}
		
		self.clickDown = function() {
			var pos = self.published.indexOf(this);
			if (pos >= self.published().length - 1) {
				return;
			}
			var tmp = self.published();
			self.published.splice(pos, 2, tmp[pos + 1], tmp[pos]);
		}
		
		self.clickSearch = function() {
			self.update();
		}
		
		self.dateRangeChanged = function(obj, e, start, end) {
			self.startDate(start);
			self.endDate(end); //Take it to end of the day
			self.update();
		}
		
		self.update = function() {
			exclude=new Array();
			_.each(self.published(), function(item) {
				exclude.push(item._id());
			});
			$.getJSON("/api/content/listing?api_key=<?= $this->session->userdata("api_key") ?>", { content_type: self.content_types(), exclude: exclude, limit: 20, order_by: "last_modified DESC", search: self.searchStr, start_date: self.startDate, end_date: self.endDate }, function(data) {
				var mapped = _.map(data.content, function(item) { return new Content(item) });
				self.content(mapped);
			});
		}
		
	}
	
	var Section = function() {
		var self = this;
		self.zones = ko.observableArray();
		self.content_type_list = ko.observableArray();
		self.isAjaxRunning = ko.observable(false);
		
		self.ajax_processes = new Array;

		$(document).ajaxSend(function(e, x, s) {
			self.isAjaxRunning(true);
			self.ajax_processes.push(s.url);
		});

		$(document).ajaxComplete(function(e, x, s) {
			self.ajax_processes.splice(self.ajax_processes.indexOf(s.url), 1);
			self.isAjaxRunning((self.ajax_processes.length > 0));
		});
		
		self.newZone = ko.observable(new Zone({
			name: "",
			min_items: 0,
			max_items: 0
		}));
		self.editZone = ko.observable(new Zone({
			name: "",
			min_items: 0,
			max_items: 0
		}));
		
		$.getJSON("/api/content/listing?api_key=<?= $this->session->userdata("api_key") ?>", { id: "<?= $collection->_id ?>" }, function(data) {
			self.collection = data.content[0];
			mapped = _.map(data.content[0].zone, function(item, key) { return new Zone(item, key) });
			self.zones(mapped);
			if (self.zones().length) {
				self.zones()[0].isActive(true);
			}
			$(".daterange").daterangepicker(
				{
					ranges: {
						'Today': ['today', 'today'],
						'Yesterday': ['yesterday', 'yesterday'],
						'Last 7 Days': [Date.today().add({ days: -6 }), 'today'],
						'Last 30 Days': [Date.today().add({ days: -29 }), 'today'],
						'This Month': [Date.today().moveToFirstDayOfMonth(), Date.today().moveToLastDayOfMonth()],
						'Last Month': [Date.today().moveToFirstDayOfMonth().add({ months: -1 }), Date.today().moveToFirstDayOfMonth().add({ days: -1 })],
						'Last 6 Months': [Date.today().add({ months: -6 }), 'today'],
						'Last Year': [Date.today().add({ months: -12 }), 'today']
        			}
				},
				function(start, end) {
					$('.daterange span').html(start.toString('MMMM d, yyyy') + ' - ' + end.toString('MMMM d, yyyy')).trigger("change", [(Date.parse(start) / 1000), (Date.parse(end) / 1000)] );
				}
			);
		});
		
		$.getJSON("/api/content_types?api_key=<?= $this->session->userdata("api_key") ?>", function(data) {
			self.content_type_list(_.map(data.content, function(item) { return { _id: item._id, name: item.name  } }));
		});
		
		self.clickZone = function() {
			var pos = self.zones.indexOf(this);
			var tmparr = self.zones.removeAll();
			_.each(tmparr, function(item, key) {
				item.isActive(false);
				if (key == pos) {
					item.isActive(true);
				}
			});
			self.zones(tmparr);
			$(".daterange").daterangepicker(
				{
					ranges: {
						'Today': ['today', 'today'],
						'Yesterday': ['yesterday', 'yesterday'],
						'Last 7 Days': [Date.today().add({ days: -6 }), 'today'],
						'Last 30 Days': [Date.today().add({ days: -29 }), 'today'],
						'This Month': [Date.today().moveToFirstDayOfMonth(), Date.today().moveToLastDayOfMonth()],
						'Last Month': [Date.today().moveToFirstDayOfMonth().add({ months: -1 }), Date.today().moveToFirstDayOfMonth().add({ days: -1 })],
						'Last 6 Months': [Date.today().add({ months: -6 }), 'today'],
						'Last Year': [Date.today().add({ months: -12 }), 'today']
        			}
				},
				function(start, end) {
					$('.daterange span').html(start.toString('MMMM d, yyyy') + ' - ' + end.toString('MMMM d, yyyy')).trigger("change", [(Date.parse(start) / 1000), (Date.parse(end) / 1000)] );
				}
			);
		};
		
		self.clickZoneLeft = function() {
			var pos = self.zones.indexOf(this);
			if (pos <= 0) {
				return
			}
			var tmp = self.zones();
			self.zones.splice(pos-1, 2, tmp[pos], tmp[pos-1]);
		}
		
		self.clickZoneRight = function() {
			var pos = self.zones.indexOf(this);
			if (pos >= self.zones().length - 1) {
				return;
			}
			var tmp = self.zones();
			self.zones.splice(pos, 2, tmp[pos + 1], tmp[pos]);
		}
		
		self.clickZoneSave = function() {
			var pass = true;
			var msg = [];
			if (self.newZone().name().trim() == "") {
				msg.push("Name cannot be empty");
				pass = false;
			}
			if (self.newZone().urlid().trim() == "") {
				msg.push("UrlID cannot be empty");
				pass = false;
			}
			if (!_.isNumber(self.newZone().max_items())) {
				msg.push("Max items must be numeric");
				pass = false;
			}
			if (!_.isNumber(self.newZone().min_items())) {
				msg.push("Min items must be numeric");
				pass = false;
			}
			if (pass) {
				$('#newModal').modal("hide");
				self.newZone().update();
				self.zones.push(self.newZone());
				self.newZone(new Zone({}));
			} else {
				$("#newZoneErrors").html("");
				_.each(msg, function(s) {
					$("#newZoneErrors").append(s+"<br />");
				});
				$("#newZoneErrors").show();
			}
		}
		
		self.clickZoneDelete = function() {
			self.zones.splice(editId, 1);
			$('#editModal').modal("hide");
		}
		
		var editId = -1;
		self.clickZoneEdit = function(obj) {
			editId = self.zones.indexOf(this);
			self.editZone(this);
			$("#editModal").modal("show");
		}
		
		self.save = function() {
			if (self.isAjaxRunning()) {
				return false;
			}
			self.saveZones();
			var data = {};
			data._id = "<?= $collection->_id ?>";
			data.zones = {};
			_.each(self.zones(), 
				function(item) {
					var key = item.urlid();
					var tmp = {};
					data.zones[key] = JSON.parse(ko.toJSON(item.published())); 
				}
			);
			$.post("/api/publish/save?api_key=<?= $this->session->userdata("api_key") ?>", data, 
				function(result) { 
					if (result.error) {
						$("#save_fail").slideDown(1000).delay(3000).slideUp(1000);
					} else {
						$("#save_success").slideDown(1000).delay(3000).slideUp(1000);
					}
				}
			);
		}
		
		self.clickZoneEditSave = function() {
			var pass = true;
			var msg = [];
			if (self.editZone().name().trim() == "") {
				msg.push("Name cannot be empty");
				pass = false;
			}
			if (self.editZone().urlid().trim() == "") {
				msg.push("UrlID cannot be empty");
				pass = false;
			}
			if (!_.isNumber(self.editZone().max_items())) {
				msg.push("Max items must be numeric");
				pass = false;
			}
			if (!_.isNumber(self.editZone().min_items())) {
				msg.push("Min items must be numeric");
				pass = false;
			}
			if (pass) {
				$('#editModal').modal("hide");
				self.editZone().update();
				self.zones.replace(self.zones()[editId], self.editZone());
			} else {
				$("#editZoneErrors").html("");
				_.each(msg, function(s) {
					$("#editZoneErrors").append(s+"<br />");
				});
				$("#editZoneErrors").show();
			}
		}
		
		self.saveZones = function() {
			var zones = JSON.parse(ko.toJSON(self.zones()));
			var newzones = {};
			_.each(zones, function(zone) {
				var obj = {
					zone_name: zone.name,
					zone_urlid: zone.urlid,
					zone_auto: zone.auto,
					zone_max_items: zone.max_items,
					zone_min_items: zone.min_items,
					zone_content_types: zone.content_types
				};
				newzones[zone.urlid] = obj;
			});
			self.collection.zone = newzones;
			self.collection.id = self.collection._id;
			$.post("/api/content/save?api_key=<?= $this->session->userdata("api_key") ?>", self.collection);
		}	
	}
	
	$(function() {
		ko.applyBindings(new Section());
		$(".chzn-select").chosen();
		$('#newModal').on('show', function () {
			$("#select_content_types").chosen().trigger("liszt:updated");
		});
		$('#editModal').on('show', function () {
			$("#select_content_types_edit").chosen().trigger("liszt:updated");
		});
	});
</script>
<script type="text/javascript">

var collection_type = '<?php echo $collection_type; ?>';
var collection = '';
var zone_id = '';

</script>
<div class="row">
	<div class="span2">
		<ul class="nav nav-pills nav-stacked">
		<?php
			foreach($collections as $c) {
				
		?>
			<li <?= ($c->_id==$collection->_id) ? 'class="active"' : '' ?>><?= anchor("/publish/".$collection_type."/".$c->_id, $c->title) ?></li>
		<?php
			}
		?>
		</ul>
	</div>
	
	<div class="span10">
		<div class="row">
			<div class="span10">
				<ul class="nav nav-tabs">
					<!-- ko foreach: zones -->
					<li data-bind="css: { active: isActive }">
						<a href="#">
							<span data-bind="text: name, click: $parent.clickZone"></span>
							<!-- ko if: $index() > 0 -->
							<i data-bind="click: $parent.clickZoneLeft" class="icon-arrow-left"></i>
							<!-- /ko -->
							<!-- ko if: $index() < $parent.zones().length -1  -->
							<i data-bind="click: $parent.clickZoneRight" class="icon-arrow-right"></i>
							<!-- /ko -->
							<i data-bind="click: $parent.clickZoneEdit" class="icon-edit"></i>
						</a>
					</li>
					<!-- /ko -->
					<li>
						<a href="#newModal" data-toggle="modal">
							<i class="icon-plus"></i>
						</a>
					</li>
				</ul>
			</div>
		</div>
		<div class="row">
			
		</div>
		<div class="row" data-bind="foreach: zones">
			<!-- ko if: isActive -->
			<div class="span10">
				<div class="well">
					<div class="span3">
						<div class="input-append">
							<input class="span2" id="search" type="text" placeholder="Search..." data-bind="value: searchStr">
							<a class="btn" href="#" id="btnSearch" data-bind="click: clickSearch">Search</a>
						</div>
					</div>
					<div class="span4">
						<div class="date_slider_container">
							<div class="daterange">
							    <i class="icon-calendar"></i>
						    	<span  data-bind="event: { change: dateRangeChanged }" class='range_value'><?php echo date("F j, Y", strtotime('-30 day')); ?> - <?php echo date("F j, Y"); ?></span> <b class="caret"></b>
							</div>
						</div>
					</div>
					<div class="span1 pull-right">
						<span style='float:right;margin-right:10px;' class='btn btn-success btn_publish' data-bind="click: $parent.save, css: { disabled: $parent.isAjaxRunning() }">Publish</span>
					</div>
					<div class="row">
						<div class="span2 pull-right alert alert-error" style="display: none" id="save_fail">Failed to save section</div>
						<div class="span2 pull-right alert alert-success" style="display: none" id="save_success">Section saved</div>
					</div>
				</div>
			</div>
			<div class="span7" >
				
				<div data-bind="foreach: content">
					<div class="span2">
						<div><a href="#" data-bind="text: title, click: clickEdit"></a></div>
						<a class="label label-info" data-bind="click: $parent.clickPublish">Publish</a>
					</div>
				</div>
			</div>
			<div class="span2">
				<div data-bind="foreach: published">
					<div class="span2 well">
						<div><a href="#" data-bind="text: title, click: clickEdit"></a></div>
						<a class="label label-warning" data-bind="click: $parent.clickUnpublish">Unpublish</a>
						<a href="#" data-bind="click: $parent.clickUp"><i class="icon-arrow-up"></i></a>
						<a href="#" data-bind="click: $parent.clickDown"><i class="icon-arrow-down"></i></a>
					</div>
				</div>
			</div>
			<!-- /ko -->
		</div>
	</div> <!-- End main body -->
</div>

<div id="newModal" class="modal hide fade">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h3>Add a Zone</h3>
	</div>
	<div class="modal-body">
		<div class="alert alert-error hide" id="newZoneErrors"></div>
		<form data-bind="with: newZone" class="form-horizontal">
			<div class="control-group">
				<label class="control-label">Zone Name</label>
				<div class="controls">
					<input type="text" name="zone_name" data-bind="value: name" required />
				</div>
			</div>
			<div class="control-group">
				<label class="control-label">Zone UrlID</label>
				<div class="controls">
					<input type="text" name="zone_urlid" data-bind="value: urlid" required />
				</div>
			</div>
			<div class="control-group">
				<label class="control-label">Content Types</label>
				<div class="controls">
					<select id="select_content_types" class="chzn-select" multiple="multiple" name="zone_content_types[]" data-bind="options: $parent.content_type_list, optionsText: 'name', optionsValue: '_id', selectedOptions: content_types"></select>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label">Minimum Items</label>
				<div class="controls">
					<input type="text" name="zone_min_items" data-bind="value: min_items" />
				</div>
			</div>
			<div class="control-group">
				<label class="control-label">Maximum Items</label>
				<div class="controls">
					<input type="text" name="zone_max_items" data-bind="value: max_items" />
				</div>
			</div>
		</form>
	</div>
	<div class="modal-footer">
		<a href="#" class="btn" data-dismiss="modal">Close</a>
		<a href="#" class="btn btn-primary" data-bind="click: clickZoneSave">Save changes</a>
	</div>
</div>

<div id="editModal" class="modal hide fade">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h3>Edit a Zone</h3>
	</div>
	<div class="modal-body">
		<div class="alert alert-error hide" id="editZoneErrors"></div>
		<form data-bind="with: editZone" class="form-horizontal">
			<div class="control-group">
				<label class="control-label">Zone Name</label>
				<div class="controls">
					<input type="text" name="zone_name" data-bind="value: name" required />
				</div>
			</div>
			<div class="control-group">
				<label class="control-label">Zone UrlID</label>
				<div class="controls">
					<input type="text" name="zone_urlid" data-bind="value: urlid" required />
				</div>
			</div>
			<div class="control-group">
				<label class="control-label">Content Types</label>
				<div class="controls">
					<select id="select_content_types_edit" class="chzn-select" multiple="multiple" name="zone_content_types[]" data-bind="options: $parent.content_type_list, optionsText: 'name', optionsValue: '_id', selectedOptions: content_types"></select>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label">Minimum Items</label>
				<div class="controls">
					<input type="text" name="zone_min_items" data-bind="value: min_items" />
				</div>
			</div>
			<div class="control-group">
				<label class="control-label">Maximum Items</label>
				<div class="controls">
					<input type="text" name="zone_max_items" data-bind="value: max_items" />
				</div>
			</div>
		</form>
	</div>
	<div class="modal-footer">
		<a href="#" class="btn" data-dismiss="modal">Close</a>
		<a href="#" class="btn btn-warning" data-bind="click: clickZoneDelete">Delete zone</a>
		<a href="#" class="btn btn-primary" data-bind="click: clickZoneEditSave">Save changes</a>
	</div>
</div>