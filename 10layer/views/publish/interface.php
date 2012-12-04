<script src="/resources/knockout/knockout-2.2.0.js"></script>
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
		self.name = ko.observable(data.zone_name);
		self.id = ko.observable(key);
		self.content_types = ko.observableArray(data.zone_content_types);
		self.isActive = ko.observable(false);
		self.content = ko.observableArray([]);
		self.published = ko.observableArray([]);
		
		$.getJSON("/api/publish/zone/<?= $collection->_id ?>/"+self.id(), function(data) {
			if (data.content.length) {
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
			console.log(self.content_types());
			$.getJSON("/api/content/listing?api_key=<?= $this->config->item("api_key") ?>", { content_type: self.content_types(), exclude: exclude, limit: 20, order_by: "last_modified DESC" }, function(data) {
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
	}
	
	var Section = function() {
		var self = this;
		self.zones = ko.observableArray();
		
		$.getJSON("/api/content/listing?api_key=<?= $this->config->item("api_key") ?>", { id: "<?= $collection->_id ?>" }, function(data) {
			mapped = _.map(data.content[0].zone, function(item, key) { return new Zone(item, key) });
			self.zones(mapped);
			self.zones()[0].isActive(true);
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
		};
		
		self.save = function() {
			$.ajax("/api/publish/save?api_key=<?= $this->config->item("api_key") ?>", {
				data: ko.toJSON({ _id: "<?= $collection->_id ?>", zones: _.map(self.zones(), function(item) { return item.published() }) }),
				type: "post", contentType: "application/json",
				success: function(result) { 
					if (result.error) {
						$("#save_fail").slideDown(1000).delay(3000).slideUp(1000);
					} else {
						$("#save_success").slideDown(1000).delay(3000).slideUp(1000);
					}
				}
			});
		}		
	}
	
	$(function() {
		ko.applyBindings(new Section());
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
				<ul class="nav nav-tabs" data-bind="foreach: zones">
					<li data-bind="css: { active: isActive }"><a href="#" data-bind="text: name, click: $parent.clickZone"></a></li>
				</ul>
			</div>
		</div>
		<div class="row">
			<div class="span10">
				<div class="well">
					<div class="span3">
						<div class="input-append">
							<input class="span2" id="search" type="text" placeholder="Search...">
							<a class="btn" href="#" id="btnSearch">Search</a>
						</div>
					</div>
					<div class="span4">
						<div id="date_slider_container">
							<div id="reportrange">
							    <i class="icon-calendar"></i>
						    	<span id='range_value'><?php echo date("F j, Y", strtotime('-30 day')); ?> - <?php echo date("F j, Y"); ?></span> <b class="caret"></b>
							</div>
						</div>
					</div>
					<div class="span1 pull-right">
						<span style='float:right;margin-right:10px;' class='btn btn-success' data-bind="click: save">Publish</span>
					</div>
					<div class="row">
						<div class="span2 pull-right alert alert-error" style="display: none" id="save_fail">Failed to save section</div>
						<div class="span2 pull-right alert alert-success" style="display: none" id="save_success">Section saved</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="span7" data-bind="foreach: zones">
				<!-- ko if: isActive -->
				<div data-bind="foreach: content">
					<div class="span2">
						<div><a href="#" data-bind="text: title, click: clickEdit"></a></div>
						<a class="label label-info" data-bind="click: $parent.clickPublish">Publish</a>
					</div>
				</div>
				<!-- /ko -->
			</div>
			<div class="span2" data-bind="foreach: zones">
				<!-- ko if: isActive -->
				<div data-bind="foreach: published">
					<div class="span2 well">
						<div><a href="#" data-bind="text: title, click: clickEdit"></a></div>
						<a class="label label-warning" data-bind="click: $parent.clickUnpublish">Unpublish</a>
						<a href="#" data-bind="click: $parent.clickUp"><i class="icon-arrow-up"></i></a>
						<a href="#" data-bind="click: $parent.clickDown"><i class="icon-arrow-down"></i></a>
					</div>
				</div>
				<!-- /ko -->
			</div>
		</div>
	</div> <!-- End main body -->
	
</div>
<div class="span12" data-bind="text: ko.toJSON($root)"></div>
<div id='pop' class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-header">
	    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
	    <h4 id="pop_label"></h4>
	</div>
	<div id='pop_message' class="modal-body">
	    
	</div>
	<div class="modal-footer">
	    <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
	</div>

</div>


<script type='text/template' id='publishable_items_template'>
		<% _.each(data, function(item) { %>
				<%= _.template($('#publishable_item_template').html(), { item: item}) %>
		<% }); %>
</script>

<script type='text/template' id='publishable_item_template'>
	<div id='<%= item._id %>' class="publishable_item" content_type='<%= item.content_type %>'>
		<span class='content_type_label label'><%= item.content_type %></span>
		<div class='settings'>
			<div class="btn-group">
			  <span class="fly_edit btn btn-mini">edit</span>
			  <span class="move_over btn btn-mini">publish</span>
			</div>
		</div>
		<br clear='both' />
		<%= item.title %>
	</div>
</script>

<script type='text/template' id='unpublishable_items_template'>
		<% _.each(data, function(item) { %>
				<%= _.template($('#unpublishable_item_template').html(), { item: item}) %>
		<% }); %>
</script>

<script type='text/template' id='unpublishable_item_template'>
	<div id='<%= item._id %>' class="publishable_item" content_type='<%= item.content_type %>'>
		<span class='content_type_label label'><%= item.content_type %></span>
		<div class='settings'>
			<div class="btn-group">
			  <span class="fly_edit btn btn-mini">edit</span>
			  <span class="unpublish btn btn-mini">unpublish</span>
			</div>
		</div>
		<br clear='both' />
		<%= item.title %>
	</div>
</script>




