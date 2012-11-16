<script type="text/javascript">

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

	$('.menu').menu();

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
	    	if ($("#selected_items").length > 0){
				update_panel(false);
			}else{
				update_panel(true);		
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
        	console.log('testing reset'); 
     });

	$(".collection_selector").click(function(){
		var id = $(this).attr('id');
		$.getJSON('/publish/get_collection/'+id, function(data) {
			$('#collection_header').text(capitaliseFirstLetter(data.content_type) + " - " + data.title);
			var zone_options = '';
			$.each(data.zones, function(i,zone) {
				zone_options += "<li><a tabindex='-1' href='#' class='zone_selector' id='"+zone._id+"'>"+zone.title+"</a></li>";
				
				$('#zone_indicator').text('Select a Zone');
				$('#zone_options').html(zone_options);
				$( "#collection_selector" ).dialog( "close" );
			});

			

  		});

	});

	$('.zone_selector').live('click', function(){
		var id = $(this).attr('id');

		$.getJSON('/publish/get_zone/'+id, function(data) {
			var zone_display = (data.title.length > 20) ? "<span class='tooltips' data-placement='top' data-original-title='"+data.title+"'>"+data.title.substring(0, 20) + '...</span>' : data.title;
			$('#zone_indicator').html(zone_display);
			var details = "<div class='tooltips' data-placement='top' data-original-title='"+data.content_types+"'><span class='small_text' rel='tooltip' >Content Types</span></div>";
			details += "<div class='tooltips' data-placement='right' data-original-title='"+data.max_count+"'><span class='small_text' rel='tooltip' >Max Items</span></div>";
			details += "<div class='tooltips' data-placement='right' data-original-title='"+data.min_count+"'><span class='small_text' rel='tooltip' >Min Items</span></div>";
			var automated = (data.auto == 1) ? 'label-important' : 'label-success';
			var instruction = (data.auto == 1) ? 'Click to de-automate' : 'Click to automate';
			var label = (data.auto == 1) ? 'Deautomate' : 'Automate';
			details += "<div class='tooltips' data-placement='right' data-original-title='"+instruction+"'><span class='label small_text "+automated+"' rel='tooltip' >"+label+"</span></div>";
			$('#zone_details').html(details);

			$('#search_results').html(_.template($("#publishable_items_template").html(), { data:data.available_items }));

			$('.tooltips').tooltip();
  			
  			//var items = [];
  		});

	});

	$(".move_over").live('click', function(){
		$(this).removeClass('move_over').addClass('unpublish').text('Unpublish');
		id = $(this).parent().parent().parent().attr('id');
		move_to_publish(id);
	});

	

	$(".fly_edit").live('click', function(){
		id = $(this).parent().parent().parent().attr('id');
		content_type = $(this).parent().parent().parent().attr('content_type');
		location = '/edit/'+content_type+"/"+id;
		this.document.location.href = location
		//move_to_publish(id);
	});

	function move_to_publish(id){
		$('#'+id).remove().appendTo($('#publish_pane'));
		//console.log(id);
	}



	


    
});



</script>



<div class="row show-grid">
  
  <div class="span12" style='height:650px;overflow: auto;'>
  	<div class="row">
      <div class="span12">


      	<div id='search_criterion'>
      		<div id='collection_selector_container'>
				<span id='open_collection_selector' class="btn"><i class="icon-list"></i> Select Section here</span>
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
	                  <li><a href="#">Please select a collection</a></li>
	                </ul>
	              </li>

	              <li class="active"> <a> <span id='collection_header'>Select a Section </span> | <span id='zone_indicator'>Select a Zone</span></a></li>

	            </ul>
			</div>
			<br clear='both'>

		</div>
	

      </div>
    </div>
    <div class="row">
      <div class="span8"><div id='search_results'></div></div>
      <div class="span1"><div id='zone_details'></div></div>
      <div class="span3"><div id='publish_pane'></div></div>
      
    </div>

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




