<script type="text/javascript">

var collection_type = '<?php echo $collection_type; ?>';
var collection = '';
var zone_id = '';

function capitaliseFirstLetter(string)
{
    return string.charAt(0).toUpperCase() + string.slice(1);
}
var date_slider_options=new Array("Forever","2 years ago","1 year ago","6 months ago","2 months ago","1 month ago","2 weeks ago","1 week ago","2 days ago","1 day ago","8 hours ago","1 hour ago","Now");
var max_slider=12;
var min_slider=0;
var def_max_slider=12;
var def_min_slider=8;

$(function() {

	$('#publish').click(function(){
		publish(zone_id);
	});
	$('.menu').menu();

	$('#publishSearch').focus(function(){
		$(this).val('');
	});

	$('#publishSearch').blur(function(){
		if($(this).val() == ''){
			$(this).val('Search...');
		}
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

	$('#open_collection_selector').click(function(){
		$( "#collection_selector" ).dialog({
            modal: true,
            height:500,
            width:600,
            buttons: {
                Cancel: function() {
                    $( this ).dialog( "close" );
                }
            }
        });
	});
	
	$("#date_slider").slider({
	    range: true,
	    min: min_slider,
	    max: max_slider,
	    values: [ def_min_slider, def_max_slider ],
	    stop: function(event, ui) {
	    	
	    	if(collection != ''){
	    		update_panel();
	    	}else{
	    		show_pop('info', 'please select '+collection_type);
	    	}
	    	
	    },
	    slide: function(event, ui) {
	    	$("#date_slider_value").html(date_slider_options[ui.values[0]]+" to "+date_slider_options[ui.values[1]]);
	    }
	});
	
	var d1=date_slider_options[$("#date_slider").slider( "values", 0 )];
	var d2=date_slider_options[$("#date_slider").slider( "values", 1 )];
	$("#date_slider_value").html(d1+" to "+d2);


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
				$( "#collection_selector" ).dialog( "close" );
				$('.tooltips').tooltip();
			});

			

  		});

	});

	$('.zone_selector').live('click', function(){
		zone_id = $(this).attr('id');
		$.getJSON('/publish/get_zone/'+zone_id, function(data) {
			var zone_display = (data.title.length > 15) ? "<span class='tooltips' data-placement='top' data-original-title='"+data.title+"'>"+data.title.substring(0, 15) + '...</span>' : data.title;
			$('#zone_indicator').html(zone_display);
			var details = "<div class='tooltips' data-placement='top' data-original-title='"+data.content_types+"'><span class='small_text' rel='tooltip' >Content Types</span></div>";
			details += "<div class='tooltips' data-placement='right' id='max_count' count='"+data.max_count+"' data-original-title='"+data.max_count+"'><span class='small_text' rel='tooltip' >Max Items</span></div>";
			details += "<div class='tooltips' data-placement='right' id='min_count' count='"+data.min_count+"' data-original-title='"+data.min_count+"'><span class='small_text' rel='tooltip' >Min Items</span></div>";
			var automated = (data.auto == 1) ? 'label-important' : 'label-success';
			var instruction = (data.auto == 1) ? 'Click to de-automate' : 'Click to automate';
			var label = (data.auto == 1) ? 'Deautomate' : 'Automate';
			details += "<div class='tooltips' data-placement='right' data-original-title='"+instruction+"'><span class='label small_text "+automated+"' rel='tooltip' >"+label+"</span></div>";
			details += "<div class='tooltips' data-placement='right' data-original-title='Current No. of Items'><span class='label label-success' id='current_count' rel='tooltip' >"+data.content.length+"</span></div>";
			$('#zone_details').html(details);

			$('#search_results').html(_.template($("#publishable_items_template").html(), { data:data.available_items }));
			$('#publish_pane').html(_.template($("#unpublishable_items_template").html(), { data:data.content }));
			

			$('.tooltips').tooltip();
  			
  			//var items = [];
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
		$( "#pop" ).attr('title', title).html(message).dialog({
            modal: true,
            buttons: {
                Ok: function() {
                    $( this ).dialog( "close" );
                }
            }
        });
	}

	function reset_panel(){
		collection = '';
		$('#search_results').html('');
		$('#publish_pane').html('');
		var d1=date_slider_options[$("#date_slider").slider( "values", 0 )];
		var d2=date_slider_options[$("#date_slider").slider( "values", 1 )];
		$("#date_slider_value").html(d1+" to "+d2);
		$('#publishSearch').val('Search...');
		$('#collection_header').html("Select a <?php echo ucfirst($collection_type); ?>");
		zone_options = "<li><a href='#'>Please select a <?php echo ucfirst($collection_type); ?></a></li>";
		$('#zone_indicator').text('Select a Zone');
		$('#zone_options').html(zone_options);
		$('#zone_details').html('');
	}

	function update_panel(){
		var start_date = date_slider_options[$("#date_slider").slider( "values", 0)];
		var end_date = date_slider_options[$("#date_slider").slider( "values", 1 )];
		var searchstr = ($("#publishSearch").val() == "Search...") ? "" : $("#publishSearch").val() ;
		var selecteds = get_selected();
		var url = $("#active_zone").val()+"/"+d1+"/"+d2+"/"+searchstr;
		var params = {'criteria':1, 'start_date':start_date, 'end_date':end_date,'searchstr':searchstr, 'selecteds[]': selecteds};

		$.getJSON('/publish/get_zone/'+zone_id,params, function(data) {
			$('#search_results').html(_.template($("#publishable_items_template").html(), { data:data.available_items }));
		});
	}

	function can_add(){
		var max_count = $('#max_count').attr('count');
		var current_count =  $('#current_count').html();
		if(max_count > current_count){
			return true;
		}else{
			return false;
		}
	}

	function can_remove(){
		var min_count = $('#min_count').attr('count');
		var current_count =  $('#current_count').html();
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
	}
	


    
});



</script>



<div class="row show-grid">
  
  <div class="span12" style='height:650px;overflow: auto;'>
  	<div class="row">
      <div class="span12">


      	<div id='search_criterion'>
      		<div id='collection_selector_container'>
				<span id='open_collection_selector' class="btn"><i class="icon-list"></i> Select <?php echo ucfirst($collection_type); ?> here</span>
				<div id='collection_selector'>
						<div><?php echo $options; ?></div>
				</div>
			</div>
      		
	      	<div id="date_slider_container">
				<div id="date_slider_value"></div>
				<div id="date_slider"></div>
			</div>
			<input type="text" id="publishSearch" value="Search..." title="Hit Enter key to search">

			<span class='btn btn-mini' style="margin-top:10px;"  id="reset_search"><i class="icon-refresh"></i></span>

			<div id='config_container'>

				<ul class="nav nav-pills">
	              <li class="dropdown">
	                <a class="dropdown-toggle" id="config_section" role="button" data-toggle="dropdown" href="#">Zone Selector<b class="caret"></b></a>
	                <ul id="zone_options" class="dropdown-menu" role="menu" aria-labelledby="drop5">
	                  <li><a href="#">Please select a <?php echo ucfirst($collection_type); ?></a></li>
	                </ul>
	              </li>

	              <li class="active"> <a> <span id='collection_header'>Select a <?php echo ucfirst($collection_type); ?> </span> | <span id='zone_indicator'>Select a Zone</span></a></li>

	            </ul>
			</div>
			<br clear='both'>

		</div>
	

      </div>
    </div>
    <div class="row">
      <div class="span8"><div id='search_results'></div></div>
      <div class="span1"><div id='zone_details'></div></div>
      <div class="span3">
      	<div id='publish_pane'></div>
      	<br />
      	<span style='float:right;margin-right:10px;' class='btn btn-success' id='publish'>Publish</span>
  	  </div>
      
    </div>

  </div>


</div>

<div id='pop'>

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




