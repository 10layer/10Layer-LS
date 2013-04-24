

<script type="text/javascript" >
$(function(){
	$(".chzn-select").chosen().change(function(){
		filter();
	});

	$(document.body).data('api_key', '<?= $this->session->userdata('api_key') ?>');
	$('#dyncontent').html("Loading...");
	$.getJSON("<?= base_url() ?>api/redirects?api_key=<?= $this->session->userdata("api_key") ?>", {  order_by: "start_date DESC", api_key: $(document.body).data('api_key'), limit: 50, fields: [ "id", "title", "last_modified", "start_date", "workflow_status", "last_editor", "content_type" ] }, function(data) {
		$('#dyncontent').html(_.template($("#listing-template").html(), { data:data}));
	});

	$("#quick_search_button").live('click',function(){
		filter();
	});

	version_map=new Array(
		"",
		"New",
		"Edited",
		"Published"
	);

	function filter(){
		$("#quick_search_button").text('searching...');
		var edited_by = $("#edited_by").val();
		var content_types = $("#content_types").val();
		var workflows = $("#workflows").val();
		var search_string = $('#search_query').val();
		var params = {order_by: "start_date DESC", api_key: $(document.body).data('api_key'), limit: 50, fields: [ "id", "title", "last_modified", "start_date", "workflow_status", "last_editor", "content_type" ] }
		if(search_string != ''){
			params.search = search_string;
		}
		if(content_types != ''){
			params.content_type = content_types;
		}
		if(workflows != ''){
			params.workflow = workflows;
		}
		if(edited_by != ''){
			params.last_editor = edited_by;
		}


		$.getJSON("<?= base_url() ?>api/redirects?api_key=<?= $this->session->userdata("api_key") ?>", function(data) {
			$('#dyncontent').html(_.template($("#listing-template").html(), {data:data}));
			$("#quick_search_button").text('Quick Search');
		});
	}



	$(document).on('click', '#save_redirect', function(){
		pointer = $(this);
		var id = $('#r_id').val();
		var from = $('#r_from').val();
		var to = $('#r_to').val();
		params = {id:id,from:from, to:to};
		$.getJSON("<?= base_url() ?>api/redirects/save?jsoncallback=?", params, function(data){
			if(!data.error){

				var item = {id:data.id, from:from, to:to};
				var the_class = data.error ? 'error':'success';
				pointer.parent().parent().append(_.template($('#message').html(), {the_class: the_class, message:data.msg} ));
				if($('#r_id').val() == ''){
					$('#redirect_list').append(_.template($('#add_to_list').html(),{item:item}));
				}else{
					$('#row_'+$('#r_id').val()).text($('#r_from').val()).next().text($('#r_to').val());
				}

				$('#r_to').val('');
				$('#r_from').val('');
				$('#r_id').val('');


			}else{
				var the_class = data.error ? 'error':'success';
				pointer.parent().parent().append(_.template($('#message').html(), {the_class: the_class, message:data.msg} ));
			}

		});

	});

	$(document).on('click', '.edit_redirect', function(){
		var id = $(this).attr('id');
		params = {id:id};
		$.getJSON("<?= base_url() ?>api/redirects/edit?jsoncallback=?", params, function(data){
			$('#r_to').val(data.redirect.to);
			$('#r_from').val(data.redirect.from);
			$('#r_id').val(data.redirect._id);
		});
	});

	$(document).on('click', '.remove_redirect', function(){
		var id = $(this).attr('id');
		pointer = $(this);
		params = {id:id};
		$.getJSON("<?= base_url() ?>api/redirects/delete?jsoncallback=?", params, function(data){
			var the_class = data.error ? 'error':'success';
			$("#filter_pane").append(_.template($('#message').html(), {the_class: the_class, message:data.msg}));
			pointer.parent().parent().remove();
		});
	});



});

</script>

<script type="text/template" id="add_to_list">
	<tr>
    	<td id="row_<%= item.id %>" ><%= item.from %></td>
    	<td ><%= item.to %></td>
    	<td><a class='btn pull-right remove_redirect' id='<%= item.id %>'> remove </a> <a class='btn btn-primary pull-right edit_redirect' style='margin-right:5px;' id='<%= item.id %>'>edit</a>  </td>
    </tr>
</script>






<script type="text/template" id="message">
<div class="alert alert-<%= the_class %> fade in">
	<button type="button" class="close" data-dismiss="alert">×</button>
	<%= message %>
</div>
</script>



<script type="text/template" id="listing-template">
	<div id="contentlist" class="boxed full">
		<div id='content-table'>
			<%= _.template($('#listing-template-content').html(), { content: data.content }) %>
		</div>
	</div>
</script>

<script type='text/template' id='listing-template-content'>
	<table class='table  table-striped' id='redirect_list'>
	    <thead>
	    <tr>
	    	<th width='400px'>From Url</th>
	    	<th width='400px'>To Url</th>
	    	<th></th>
	    </tr>
	    </thead>
	    <tbody>
		<% var x=0; _.each(content, function(item) {  %>
		    <tr>
		    	<td id="row_<%= item._id %>" ><%= item.from %></td>
		    	<td ><%= item.to %></td>
		    	<td><a class='btn pull-right remove_redirect' id='<%= item._id %>'> remove </a> <a class='edit_redirect btn btn-primary pull-right' style='margin-right:5px;' id='<%= item._id %>'>edit</a>  </td>


		    </tr>
		<% x++; }); %>
	    </tbody>
	</table>
</script>

<div id="filter_pane">
<h3>Manage Redirects </h3>

<div <div style="float:left; margin-right:10px; margin-top:4px;">
	<form class="form-inline">
	  	<input type='hidden' name='r_id' id='r_id'>
		<input type='text' name='r_from' id='r_from'class=''placeholder='Redirect from...'>
		<input type='text' name='r_to' id='r_to'class='' placeholder='Redirect to...'>
		<a class='btn btn-success' id='save_redirect'>Save Redirect</a>
	</form>
</div>
<br clear='both'>

</div>
<div id="dyncontent">

</div>