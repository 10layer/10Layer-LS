<script src="/resources/knockout/knockout-2.2.0.js"></script>
<script type="text/javascript">

	var Content = function(data) {
		var self = this;
		self.title = ko.observable(data.title);
		self.id = ko.observable(data._id);
		self.content_type = ko.observable(data.content_type);
		self.isPublished = ko.observable(false);
		self.zone = ko.observable(0);
		
		self.clickEdit = function() {
			window.open("/edit/"+this.content_type()+"/"+this.id());
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
					return item.id;
				});
				self.published(mapped);
			} else {
				exclude=[];
			}
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
		
		self.clickUnpublish= function () {
			var pos = self.published.indexOf(this);
			var item = self.published.splice(pos, 1)[0];
			item.isPublished(false);
			self.content.unshift(item);
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

function capitaliseFirstLetter(string)
{
    return string.charAt(0).toUpperCase() + string.slice(1);
}


$(function() {

	/*$('#publish').click(function(){
		publish(zone_id);
	});
	//$('.menu').menu();

	$('#publishSearch').focus(function(){
		$(this).val('');
	});

	$('#publishSearch').keyup(function(e){
		if(e.keyCode == '13'){
			if($(this).val() != ''){
				if(collection != ''){
					update_panel();
				}else{
					show_pop('Info', 'please select a collection');
				}
				
			}else{
				show_pop('Info', 'please enter a search value');
			}
		}
	});
	
	$("#btnSearch").on("click", function() {
		update_panel();
	});


	$('#reportrange').daterangepicker(
    {
        ranges: {
            'Today': ['today', 'today'],
            'Yesterday': ['yesterday', 'yesterday'],
            'Last 7 Days': [Date.today().add({ days: -6 }), 'today'],
            'Last 30 Days': [Date.today().add({ days: -29 }), 'today'],
            'This Month': [Date.today().moveToFirstDayOfMonth(), Date.today().moveToLastDayOfMonth()],
            'Last Month': [Date.today().moveToFirstDayOfMonth().add({ months: -1 }), Date.today().moveToFirstDayOfMonth().add({ days: -1 })]
        }
    }, 
    function(start, end) {
        $('#reportrange span').html(start.toString('MMMM d, yyyy') + ' - ' + end.toString('MMMM d, yyyy'));
        update_panel();
    });




	$("#reset_search").click(function(){
        	reset_panel();
     });

	$(".collection_selector").click(function(e){
		e.stopPropagation();
		collection = $(this).attr('id');
		$.getJSON('/publish/get_collection/'+collection, function(data) {
			var collection_display = (data.title.length > 20) ? "<span class='tooltips' data-placement='top' data-original-title='"+capitaliseFirstLetter(data.content_type)+" - "+data.title+"'>"+data.title.substring(0, 20) + '...</span>' : data.title;
			$('#collection_header').html(collection_display);
			//collection
			var zone_options = '';
			$.each(data.zones, function(i,zone) {
				zone_options += "<li><a tabindex='-1' href='#' class='zone_selector' id='"+zone._id+"'>"+zone.title+"</a></li>";
				$('#zone_indicator').text('Select a Zone');
				$('#zone_options').html(zone_options);
				$('.tooltips').tooltip();
				$('#collection_selector').modal('hide')
			});

			

  		});

	});

	$('.zone_selector').live('click', function(){
		zone_id = $(this).attr('id');
		$.getJSON('/publish/get_zone/'+zone_id, function(data) {
			zone_details(data);
			if(data.auto == 0){
				$('#search_results').html(_.template($("#publishable_items_template").html(), { data:data.available_items }));
			}else{
				$('#search_results').html('');
			}
			
			$('#publish_pane').html(_.template($("#unpublishable_items_template").html(), { data:data.content }));
	
			
  		});

	});

	$('#auto_switch').live('click', function(){
		var pointer = $(this);
		var auto_switch = pointer.hasClass('label-important') ? 0 : 1;
		var params = {'switch':auto_switch, 'zone_id':zone_id};

		$.getJSON('/publish/auto_switch', params, function(results){
			if(auto_switch == 1){
				$("#search_results").html('');
				$('#publish_pane').html(_.template($("#unpublishable_items_template").html(), { data:results.item.content }));
				zone_details(results.item);
			}else{
				$('#search_results').html(_.template($("#publishable_items_template").html(), { data:results.item.available_items }));
				$('#publish_pane').html(_.template($("#unpublishable_items_template").html(), { data:results.item.content }));
			}

			zone_details(results.item);
			show_pop(results.info, results.message);
			$('.tooltip').hide();
			//pointer.removeClass(present_class).addClass(destination_class).text(switch_label);
		});


	});

	$(".move_over").live('click', function(){
		if(can_add()){
			$(this).removeClass('move_over').addClass('unpublish').text('Unpublish');
			id = $(this).parent().parent().parent().attr('id');
			move_to_publish(id);
			update_count('add');
		}else{
			show_pop('info', 'The publish pane has reached maximum allowed items');
		}
		
	});

	$(".unpublish").live('click', function(){
		
		if(can_remove()){
			$(this).removeClass('unpublish').addClass('move_over').text('Publish');
			pointer = $(this).parent().parent().parent();//('id');
			move_from_publish(pointer);
			update_count('subtract');
		}else{
			show_pop('info', 'The publish pane has reached minimum allowed items');
		}
	});
	

	$(".fly_edit").live('click', function(){
		id = $(this).parent().parent().parent().attr('id');
		content_type = $(this).parent().parent().parent().attr('content_type');
		location = '/edit/'+content_type+"/"+id;
		this.document.location.href = location
	});

	function move_to_publish(id){
		$('#'+id).remove().prependTo($('#publish_pane'));
	}

	function move_from_publish(id){
		pointer.remove().prependTo($('#search_results'));
	}


	function show_pop(title, message){
		$("#pop_message").html(message);
		$("#pop_label").html(title);
		$('#pop').modal('show');
	}

	function reset_panel(){
		collection = '';
		$('#search_results').html('');
		$('#publish_pane').html('');
		//var d1=date_slider_options[$("#date_slider").slider( "values", 0 )];
		//var d2=date_slider_options[$("#date_slider").slider( "values", 1 )];
		var today = new Date();
		var start = Date.today().add({ days: -6 });
		$('#reportrange span').html(start.toString('MMMM d, yyyy') + ' - ' + today.toString('MMMM d, yyyy'));
		$("#date_slider_value").html(d1+" to "+d2);
		$('#publishSearch').val('Search...');
		$('#collection_header').html("Select a <?php echo ucfirst($collection_type); ?>");
		zone_options = "<li><a href='#'>Please select a <?php echo ucfirst($collection_type); ?></a></li>";
		$('#zone_indicator').text('Select a Zone');
		$('#zone_options').html(zone_options);
		$('#zone_details').html('');
	}

	function update_panel(){
		//var start_date = date_slider_options[$("#date_slider").slider( "values", 0)];
		//var end_date = date_slider_options[$("#date_slider").slider( "values", 1 )];

		var date_range = $("#range_value").text();
		var pieces = date_range.split("-");

		var start_date = pieces[0].trim();
		var end_date = pieces[1].trim();


		var searchstr = $("#publishSearch").val() ;
		var selecteds = get_selected();
		var params = {'criteria':1, 'start_date':start_date, 'end_date':end_date,'searchstr':searchstr, 'selecteds[]': selecteds};

		$.getJSON('/publish/get_zone/'+zone_id, params, function(data) {
			$('#search_results').html(_.template($("#publishable_items_template").html(), { data:data.available_items }));
		});
	}

	function zone_details(data){
		var zone_display = (data.title.length > 15) ? "<span class='tooltips' data-placement='top' data-original-title='"+data.title+"'>"+data.title.substring(0, 15) + '...</span>' : data.title;
		$('#zone_indicator').html(zone_display);
		var details = "<div class='tooltips' data-placement='top' data-original-title='"+data.content_types+"'><span class='small_text' rel='tooltip' >Content Types</span></div>";
		details += "<div class='tooltips' data-placement='right' id='max_count' count='"+data.max_count+"' data-original-title='"+data.max_count+"'><span class='small_text' rel='tooltip' >Max Items</span></div>";
		details += "<div class='tooltips' data-placement='right' id='min_count' count='"+data.min_count+"' data-original-title='"+data.min_count+"'><span class='small_text' rel='tooltip' >Min Items</span></div>";
		var automated = (data.auto == 1) ? 'label-important' : 'label-success';
		var instruction = (data.auto == 1) ? 'Click to de-automate' : 'Click to automate';
		var label = (data.auto == 1) ? 'Deautomate' : 'Automate';
		details += "<div class='tooltips' data-placement='right' data-original-title='"+instruction+"'><span class='label clickable small_text "+automated+"' rel='tooltip' id='auto_switch' >"+label+"</span></div>";
		details += "<div class='tooltips' data-placement='right' data-original-title='Current No. of Items'><span class='label label-success' id='current_count' rel='tooltip' >"+data.content.length+"</span></div>";
		$('#zone_details').html(details);
		$('.tooltips').tooltip();
	}

	function can_add(){
		var max_count = parseInt($('#max_count').attr('count'));
		var current_count =  parseInt($('#current_count').html());
		if(max_count > current_count){
			return true;
		}else{
			return false;
		}
	}

	function can_remove(){
		var min_count = parseInt($('#min_count').attr('count'));
		var current_count =  parseInt($('#current_count').html());
		if(current_count > min_count ){
			return true;
		}else{
			return false;
		}
	}

	function update_count(what){
		if(what == 'add'){
			var current_count =  parseInt($('#current_count').html())  + 1;
			$('#current_count').html(current_count);
		}
		if(what == 'subtract'){
			var current_count =  parseInt($('#current_count').html()) - 1;
			$('#current_count').html(current_count);
		}
	}


	function publish(zone_id){
		var selecteds = get_selected();
		var params = {'published[]': selecteds};
		$.getJSON('/publish/save_zone_content/'+zone_id,params, function(data) {
			show_pop('results','content saved...');
		});
	}

	function get_selected(){
		selecteds = [];
		$('#publish_pane').children('div').each(function(idx, elm) {
	  		selecteds.push(elm.id);
		});
		return selecteds;
		
	}

	function count_selected(){
		selecteds = [];
		$('#publish_pane').children('div').each(function(idx, elm) {
	  		selecteds.push(elm.id);
		});
		return selecteds.length;
	}*/
	


    
});



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
			<div class="span1"><div id='zone_details'></div></div>
			<div class="span2 well" data-bind="foreach: zones">
				<!-- ko if: isActive -->
				<div data-bind="foreach: published">
					<div class="span2">
						<div><a href="#" data-bind="text: title, click: clickEdit"></a></div>
						<a class="label label-warning" data-bind="click: $parent.clickUnpublish">Unpublish</a>
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




