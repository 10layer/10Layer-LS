<script type='text/template' id='edit-field-autocomplete'>
	<!-- edit-field-autocomplete -->
		<div class='control-group'>

			<label class='control-label <%= field.label_class %>'><%= field.label %></label>
			<div class='controls' style="position:relative;">
				<div class="input-append" style="position:relative;">
					<% var multiple = (field.multiple==true) ? 'multiple' : ''; %>
					<input multiple='<%= multiple %>' 
						id='autocomplete_view_<%= field.contenttype %>_<%= field.name %>' 
						type='text' 
						contenttype='<%= field.contenttype %>' 
						fieldname='<%= field.name %>' 
						class="autocomplete <%= (field.multiple==1) ? 'multiple' : '' %> <%= field.class %>" 
						value='' 
						contenttypes='<%= field.content_types %>' />

						
					<% if ((field.external==1) && (field.hidenew==false)) { %>
					<%= _.template($('#button-new-template').html(), { field: field }) %>
					<%
						}
					%>

				</div>


				<span style='position:absolute; top:5px !important; z-index:10; left:390px; display:none;' class='indicator label label-success'>loading...</span>
				<ul class="options dropdown-menu"></ul>
				<div class="result_container" style="margin-top:5px;">
					<%
						if (field.value) {
							if (!_.isArray(field.value)) {
								field.value=[field.value];
							}

							_.each(field.value, function(item) {
					%>
								<%= _.template($('#field-autocomplete-item').html(), { title: item.title, field: field , item:item}) %>
					<%
							});
						}
					%>
				</div>
				
			</div>
		</div>
</script>


<script type='text/template' id='create-field-autocomplete'>
	<!-- create-field-autocomplete -->
		<div class='control-group'>
			<label class='control-label <%= field.label_class %>'><%= field.label %></label>
			<div class='controls' style="position:relative;">
				<div class="input-append" style="position:relative;">
					<% var multiple = (field.multiple==true) ? 'multiple' : ''; %>
					<input multiple='<%= multiple %>' 
						id='autocomplete_view_<%= field.contenttype %>_<%= field.name %>' 
						type='text' 
						contenttype='<%= field.contenttype %>' 
						fieldname='<%= field.name %>' 
						class="autocomplete <%= (field.multiple==1) ? 'multiple' : '' %> <%= field.class %>" 
						value='' <%= (field.contenttype=='mixed') ? "mixed='mixed" : '' %> 
						contenttypes='<%= field.content_types %>' />
						

						
					<% if ((field.external==1) && (field.hidenew==false)) { %>
					<%= _.template($('#button-new-template').html(), { field: field }) %>
					<%
						}
					%>

				</div>


				<span style='position:absolute; top:5px !important; z-index:10; left:390px; display:none;' class='indicator label label-success'>loading...</span>
				<ul class="options dropdown-menu"></ul>
				<div class="result_container" style="margin-top:5px;">
					<%
						if (field.value) {
							if (!_.isArray(field.value)) {
								field.value=[field.value];
							}
							_.each(field.value, function(item) {
					%>
								<%= _.template($('#field-autocomplete-item').html(), { title: item.title, field: field , item:item}) %>
					<%
							});
						}
					%>
				</div>
				
			</div>
		</div>
</script>

<script type='text/template' id='field-autocomplete-item'>
	<div style="float:left; padding:4px; margin-right:5px; border:1px solid #ccc; border-radius:5px; -moz-border-radius:5px;">
		<a style="margin-top:-4px;" class="close">&times;</a>
		<span style="float:left;margin-right:3px;" class="label label-info"> <%= (title) ? title : '' %>  </span>
		<%  var urlid = (item._id) ? item._id : ''; %>
		<input id="autocomplete_<%= field.name %>_<%= urlid %>" type="hidden" name="<%= field.contenttype %>_<%= field.name %><%= (field.multiple=='multiple' || field.multiple) ? '[]' : '' %>" value="<%= (item._id) ? item._id : '' %>"  />
	</div>
</script>

<script type='text/template' id='edit-field-checkbox'>
	<!-- edit-field-checkbox -->
	<div class='control-group'>
	    <label class='control-label <%= field.label_class %>'><%= field.label %></label>
	    <div class="controls">
	    	<input type='checkbox' name='<%= field.contenttype %>_<%= field.name %>' value='1' class='<%= field.class %>' <%= (field.value==1) ? "checked='checked'" : '' %> />
	    </div>
	</div>
</script>

<script type='text/template' id='create-field-checkbox'>
	<!-- create-field-checkbox -->
	<div class='control-group'>
	    <label class='control-label <%= field.label_class %>'><%= field.label %></label>
	    <div class="controls">
	    	<input type='checkbox' name='<%= field.contenttype %>_<%= field.name %>' value='1' class='<%= field.class %>' <%= (field.defaultValue) ? "checked='checked'" : '' %> />
	    </div>
	</div>
</script>

<script type='text/template' id='edit-field-date'>
	<!-- edit-field-date -->
	<div class='control-group'>
		<label class='control-label <%= field.label_class %>'><%= field.label %></label>
		<div class="controls">
			<input type="hidden" name='<%= field.contenttype %>_<%= field.name %>' value='<%= (field.value) ? stringToDate(field.value) : '' %>' />
			<input type='text' name='<%= field.contenttype %>_<%= field.name %>_datetime' value='<%= (field.value) ? dateToString(field.value) : '' %>' class='datetime_change datetime_date my-datepicker <%= field.class %>' data-date="<%= (field.value) ? dateToString(field.value) : '' %>" data-date-format="yyyy-mm-dd" />
		</div>
	</div>
</script>

<script type='text/template' id='create-field-date'>
	<!-- create-field-date -->
	<%
		var val = false;
		if (field.defaultValue) {
			val = field.defaultValue;
			if (val.toLowerCase() == "now" || val.toLowerCase() == "today") {
				var d = new Date();
				val = d.getFullYear()+"-"+leadingZeros(d.getMonth()+1)+"-"+leadingZeros(d.getDate());
			} else {
				val = dateToString(val);
			}
		}
	%>
	<div class='control-group'>
		<label class='control-label <%= field.label_class %>'><%= field.label %></label>
		<div class="controls">
			<input type="hidden" name='<%= field.contenttype %>_<%= field.name %>' value='<%= (val) ? stringToDate(val) : '' %>' />
			<input type='text' name='<%= field.contenttype %>_<%= field.name %>_datetime' value='<%= (val) ? dateToString(val) : '' %>' class='datetime_change datetime_date my-datepicker <%= field.class %>' data-date="<%= (val) ? dateToString(val) : '' %>" data-date-format="yyyy-mm-dd" />
		</div>
	</div>
</script>

<script type='text/template' id='edit-field-datetime'>
	<!-- edit-field-datetime -->
	<%
		var val_date = val_hour = val_minute = "";
		field.value = dateToString(field.value);
		if (field.value) {
			//console.log(field.value);
			parts = field.value.split(" ");
			val_date = parts[0];
			if (parts[1]) {
				times = parts[1].split(":");
				val_hour = times[0];
				(times[1]) ? val_minute = times[1] : val_minute = "00";
			}
		}
	%>
	<div class='control-group'>
		<label class='control-label <%= field.label_class %>'><%= field.label %></label>
		<div class="controls">
			<input type="hidden" name='<%= field.contenttype %>_<%= field.name %>' value='<%= (field.value) ? stringToDate(field.value) : '' %>' />
			<input type='text' id='<%= field.contenttype %>_<%= field.name %>_datetime_date' class='datetime_change datetime_date my-datepicker <%= field.class %>' data-date-format="yyyy-mm-dd" value="<%= val_date %>" />
			<input type='text' id='<%= field.contenttype %>_<%= field.name %>_datetime_hour' class='datetime_change datetime_hour <%= field.class %>' value="<%= val_hour %>" /> :
			<input type='text' id='<%= field.contenttype %>_<%= field.name %>_datetime_minute' class='datetime_change datetime_minute <%= field.class %>' value="<%= val_minute %>" />
		</div>
	</div>
</script>

<script type='text/template' id='create-field-datetime'>
	<!-- create-field-datetime -->
	<%
		var val_date = val_hour = val_minute = "";
		field.value = dateToString(field.value);
		if (field.defaultValue) {
			if (field.defaultValue.toLowerCase() == "now" || field.defaultValue.toLowerCase() == "today") {
				var d = new Date();
				field.defaultValue = d.getFullYear()+"-"+(d.getMonth()+1)+"-"+d.getDate()+" "+d.getHours()+":"+d.getMinutes();
			}
			parts = field.defaultValue.split(" ");
			val_date = parts[0];
			if (parts[1]) {
				times = parts[1].split(":");
				val_hour = times[0];
				(times[1]) ? val_minute = times[1] : val_minute = "00";
			}
		}
	%>
	<div class='control-group'>
		<label class='control-label <%= field.label_class %>'><%= field.label %></label>
		<div class="controls">
			<input type="hidden" name='<%= field.contenttype %>_<%= field.name %>' value='<%= (field.defaultValue) ? stringToDate(field.defaultValue) : '' %>' />
			<input type='text' id='<%= field.contenttype %>_<%= field.name %>_datetime_date' class='datetime_change datetime_date my-datepicker <%= field.class %>' data-date-format="yyyy-mm-dd" value="<%= val_date %>" />
			<input type='text' id='<%= field.contenttype %>_<%= field.name %>_datetime_hour' class='datetime_change datetime_hour <%= field.class %>' value="<%= val_hour %>" /> :
			<input type='text' id='<%= field.contenttype %>_<%= field.name %>_datetime_minute' class='datetime_change datetime_minute <%= field.class %>' value="<%= val_minute %>" />
		</div>
	</div>
</script>

<script type='text/template' id='edit-field-search'>
	<!-- edit-field-search -->
		<div class='control-group search'>
			<label class='control-label <%= field.label_class %>'><%= field.label %></label>
			<div class='controls'>
				<div class="input-append">
					<input id="deepsearch_view_<%= field.contenttype %>_<%= field.name %>" type="text" contenttype="<%= field.contenttype %>" contenttype="<%= field.contenttype %>" fieldname="<%= field.name %>" class="span2 deepsearch_input <%= (field.multiple==1) ? 'multiple' : '' %> <%= field.class %>" value="" <% (field.contenttype=='mixed') ? "mixed='mixed' contenttypes='"+field.contenttypes.join(",")+"'" : '' %> />
					<button class="btn deepsearch-search" type="button">Search</button>
				</div>
				<ul class="deepsearch-options dropdown-menu"></ul>
				<div class="deepsearch-results result_container" style="margin-top:5px;">
				<%
					if (field.value) {
						if (!_.isArray(field.value)) {
							field.value=[field.value];
						}
						_.each(field.value, function(item) {
					%>
								<%= _.template($('#field-autocomplete-item').html(), { title: item.title, field: field , item:item}) %>
					<%
							});
						}
					%>
				</div>
			</div>
		</div>
</script>

<script type='text/template' id='create-field-search'>
	<!-- create-field-search -->
	<%= _.template($('#edit-field-search').html(), {field: field} ) %>
</script>

<script type='text/template' id='edit-field-file'>
	<!-- edit-field-file -->
	<%= _.template($('#edit-field-image').html(), {field: field} ) %>
</script>

<script type='text/template' id='create-field-file'>
	<!-- create-field-file -->
	<%= _.template($('#create-field-image').html(), {field: field} ) %>
</script>

<script type='text/template' id='edit-field-hidden'>
	<!-- edit-field-hidden -->
	<input type="hidden" name="<%= field.contenttype %>_<%= field.name %>" value="<%= (field.value) ? field.value : '' %>" />
</script>

<script type='text/template' id='create-field-hidden'>
	<!-- create-field-file -->
	<input type="hidden" name="<%= field.contenttype %>_<%= field.name %>" value="<%= nullStr(field.defaultValue) %>" />
</script>

<script type='text/template' id='edit-field-image'>
	<!-- edit-field-image -->
	<div class='control-group'>
		<label class='control-label <%= field.label_class %>'><%= field.label %></label>
		<div class='controls'>
			<input type="file" name="<%= field.contenttype %>_<%= field.name %>_element" class="file_upload <%= field.class %>" value="<%= (field.value) ? field.value : '' %>" data-contenttype="<%= field.contenttype %>" data-name="<%= field.name %>" data-multiple="<%= (field.multiple) ? 1 : 0 %>" <%= (field.multiple) ? 'multiple="true"' : '' %> />
			
		<div class="row progress-container">
		</div>
		<div class="row">
			<div class="preview-image-items">
			<%
			if (field.multiple) {
				_.each(field.value, function(item, index) { %>
					<%= _.template($("#field-image-item").html(), {value: item, field: field, index: index }) %>	
				<% });
			} else {
				if (field.value) {
			%>
				<%= _.template($("#field-image-item").html(), {field: field }) %>	
			<%
				}
			}
			%>
			</div>
		</div>
		
		<% if (field.linkformat && urlid) { %>
			<label>Download link</label>
			<div class='download_url'><input type='text' class='span8 select_on_click' readonly='readonly' value='<%= field.linkformat.replace('{filename}', (field.value) ? field.value : '') %>' /></div>
		<% } %>
		</div>
	</div>
</script>

<script type='text/template' id='create-field-image'>
	<!-- create-field-image -->
	<div class='control-group'>
		<label class='control-label <%= field.label_class %>'><%= field.label %></label>
		<div class='controls'>
			<input type="file" name="<%= field.contenttype %>_<%= field.name %>_element" class="file_upload <%= field.class %>" value="<%= (field.value) ? field.value : '' %>" data-contenttype="<%= field.contenttype %>" data-name="<%= field.name %>" data-multiple="<%= (field.multiple) ? 1 : 0 %>" <%= (field.multiple) ? 'multiple="true"' : '' %> />
			<div class="row progress-container">
			</div>
			<div class="row">
				<div class="preview-image-items"></div>
			</div>
		</div>
	</div>
</script>

<script type="text/template" id="field-image-progress">
	<div class="span3" id="<%= uid %>">
		<div class="progress progress-striped active" style="display: none">
			<div class="bar" style="width: 0%;"><%= filename %></div>
		</div>
		<div class="alert" style="display: none"></div>
	</div>
</script>

<script type='text/template' id='field-image-item'>
	<%
	value = false;
	if (typeof index !== "undefined") {
		value = field.value[index];
	}
	if (value === false) {
		if (_.isArray(field.value)) {
			value = field.value[0];
		} else {
			value = field.value;
		}
	}
	%>
	<div class="row">
		<input type="hidden" class="file_value" name="<%= field.contenttype %>_<%= field.name %><%= (field.multiple) ? '[]' : '' %>" value="<%= (value) ? value : '' %>" />
		<div class="well span6">
			<div class="row">
				<div class="span6"><h4><%= baseName(value) %></h4></div>
			</div>
			<% if (isImage(value)) { %>
			<div class="span3">
				<img src="/api/files/image?filename=<%= encodeURIName(value).replace('/content/', '') %>&width=150&height=115&render=true" />
			</div>
			<% } %>
			<div class="btn-group btn-group-vertical">
				<a href="#" class="btn image-remove"><i class="icon-remove"></i> Remove</a>
				<a href="#" class="btn image-link"><i class="icon-edit"></i> Link</a>
				<% 
					var d = value.replace("/content/", "").replace(/\//g, "-");
				%>
				<a href="/api/files/download<%= encodeURIName(d) %>?api_key=<%= $(document.body).data('api_key') %>" target="_blank" class="btn"><i class="icon-download"></i> Download</a>
			</div>
			<div class="link-show span6 hide">Link <input type="text" name="link" value="<%= encodeURIName(value) %>" class='span5 select_on_click' readonly='readonly' /></div>
		</div>
	</div>
</script>

<script type='text/template' id='edit-field-password'>
	<!-- edit-field-password -->
	<div class='control-group'>
		<label class='control-label <%= field.label_class %>'><%= field.label %></label>
		<div class="controls">
			<input type='password' name='<%= field.contenttype %>_<%= field.name %>' value='<%= (field.value) ? field.value : '' %>' class='<%= field.class %>' />
		</div>
	</div>
</script>

<script type='text/template' id='create-field-password'>
	<!-- create-field-password -->
	<div class='control-group'>
		<label class='control-label <%= field.label_class %>'><%= field.label %></label>
		<div class="controls">
			<input type='password' name='<%= field.contenttype %>_<%= field.name %>' value='<%= nullStr(field.defaultValue) %>' class='<%= field.class %>' />
		</div>
	</div>
</script>

<script type='text/template' id='edit-field-radio'>
	<!-- edit-field-radio -->
	<div class='control-group'>
		<label class='control-label <%= field.label_class %>'><%= field.label %></label>
		<div class="controls">
			<div class='radiogroup'>
			<% _.each(field.options, function(option, key) { %>
				<div class='radio'>
					<input type='radio' name='<%= field.contenttype %>_<%= field.name %>' value='<%= option %>' <%= (field.value === option) ? 'checked="checked"' : '' %> />
					<div class='radio_label'><%= option %></div>
				</div>
			<% }); %>
			</div>
		</div>
	</div>
</script>

<script type='text/template' id='create-field-radio'>
	<!-- create-field-radio -->
	<div class='control-group'>
		<label class='control-label <%= field.label_class %>'><%= field.label %></label>
		<div class="controls">
			<div class='radiogroup'>
			<% _.each(field.options, function(option, key) { %>
				<div class='radio'>
					<input type='radio' name='<%= field.contenttype %>_<%= field.name %>' value='<%= option %>' <%= (field.defaultValue==option) ? 'checked="checked"' : '' %> />
					<div class='radio_label'><%= option %></div>
				</div>
			<% }); %>
			</div>
		</div>
	</div>
</script>

<script type='text/template' id='edit-field-readonly'>
	<!-- edit-field-readonly -->
	<div class='control-group'>
		<label class='control-label <%= field.label_class %>'><%= field.label %></label>
		<div class="controls">
			<input type='text' name='<%= field.contenttype %>_<%= field.name %>' value='<%= (field.value) ? field.value : '' %>' class='<%= field.class %>' readonly='readonly' />
		</div>
	</div>
</script>

<script type='text/template' id='create-field-readonly'>
	<!-- create-field-readonly -->
	<div class='control-group'>
		<label class='control-label <%= field.label_class %>'><%= field.label %></label>
		<div class="controls">
			<input type='text' name='<%= field.contenttype %>_<%= field.name %>' value='<%= nullStr(field.defaultValue) %>' class='<%= field.class %>' readonly='readonly' />
		</div>
	</div>
</script>

<script type='text/template' id='edit-field-reverse'>
	<!-- edit-field-reverse -->
	<div class='control-group'>
		<label class='control-label <%= field.label_class %>'><%= field.label %></label>
		<div class='controls'>
			<select class='chzn-select' data-placeholder="Choose <%= field.label %>" name='<%= field.contenttype %>_<%= field.name %>' id='<%= field.name %>-hook'>
				<option value=""></option>
			<%
				$.get('/api/content/listing', { api_key: $(document.body).data('api_key'), content_type: field.contenttype }, function(data) {
					_.each(data.content, function(item) {
						$('#'+field.name+'-hook').append('<option value="'+item._id+'" '+((item._id==field.value) ? 'selected="selected"' : '')+'>'+item.title+'</option>');
						
					});
					$("#"+field.name+"-hook").trigger("liszt:updated");
				}); 
			%>
			</select>
		</div>
	</div>
</script>

<script type='text/template' id='create-field-reverse'>
	<!-- create-field-reverse -->
	<%= _.template($('#edit-field-reverse').html(), {field: field} ) %>
</script>

<script type='text/template' id='edit-field-select'>
	<!-- edit-field-select -->
		<div class='control-group'>
			<label class='control-label <%= field.label_class %>'><%= field.label %></label>
			<div class='controls'>
				<select class='chzn-select <%= field.class %>' data-placeholder="Choose <%= field.label %>" name='<%= field.contenttype %>_<%= field.name %><%= (field.multiple) ? "[]" : "" %>' <%= (field.multiple) ? "multiple='multiple'" : "" %>>
				<option value=""></option>
				<% _.each(field.options, function(option, key) { %>
					<option value='<%= option %>' 
					<%
						if (_.isArray(field.value)) {
							var selected = false;
							_.each(field.value, function(item) {
								if (item._id) {
									if (item._id == option) {
										selected = true;
									}
								} else {
									if (item == option) {
										selected = true;
									}
								}
							});
						} else {
							if (field.value == option) {
								selected = true;
							}
						}
					%>
					<%= (selected) ? 'selected="selected"' : '' %> ><%= (!_.isNumber(key)) ? key : option %></option>
				<% }); %>
				</select>
			</div>
		</div>
</script>

<script type='text/template' id='create-field-select'>
	<!-- create-field-select -->
	<%= _.template($('#edit-field-select').html(), {field: field} ) %>
</script>

<script type='text/template' id='edit-field-text'>
	<!-- edit-field-text -->
		<div class='control-group'>
			<label class='control-label <%= field.label_class %>'><%= field.label %></label>
			<div class="controls">
				<input type='text' name='<%= field.contenttype %>_<%= field.name %>' value='<%= (field.value) ? field.value : '' %>' class='<%= field.class %>' />
			</div>
		</div>
</script>

<script type='text/template' id='create-field-text'>
	<!-- create-field-text -->
	<div class='control-group'>
		<label class='control-label <%= field.label_class %>'><%= field.label %></label>
		<div class="controls">
			<input type='text' name='<%= field.contenttype %>_<%= field.name %>' value='<%= nullStr(field.defaultValue) %>' class='<%= field.class %>' />
		</div>
	</div>
</script>

<script type='text/template' id='edit-field-textarea'>
	<!-- edit-field-textarea -->
		<div class='control-group'>
			<label class='<%= field.label_class %> control-label'><%= field.label %></label>
			<div class="controls">
				<textarea name='<%= field.contenttype %>_<%= field.name %>' class='input-xlarge span6 <%= field.class %> <%= (field.showcount!==false) ? 'countchars' : '' %> <%= (_.isNumber(field.showcount)) ? 'countdown' : '' %>' <%= (_.isNumber(field.showcount)) ? 'max="'+field.showcount+'"' : '' %>><%= (field.value) ? field.value : '' %></textarea>
			</div>
		</div>
</script>

<script type='text/template' id='create-field-textarea'>
	<!-- create-field-textarea -->
		<div class='control-group'>
			<label class='<%= field.label_class %> control-label'><%= field.label %></label>
			<div class="controls">
				<textarea name='<%= field.contenttype %>_<%= field.name %>' class='input-xlarge span6 <%= field.class %> <%= (field.showcount!==false) ? 'countchars' : '' %> <%= (_.isNumber(field.showcount)) ? 'countdown' : '' %>' <%= (_.isNumber(field.showcount)) ? 'max="'+field.showcount+'"' : '' %>><%= nullStr(field.defaultValue) %></textarea>
			</div>
		</div>
</script>

<script type='text/template' id='edit-field-wysiwyg'>
	<!-- edit-field-wysiwyg -->
		<div class='control-group'>
			<label class='<%= field.label_class %> control-label'><%= field.label %></label>
			<div class="controls">
				<textarea name='<%= field.contenttype %>_<%= field.name %>' class='wysiwyg input-xlarge span6 <%= field.class %> <%= (field.showcount!==false) ? 'countchars' : '' %> <%= (_.isNumber(field.showcount)) ? 'countdown' : '' %>' <%= (_.isNumber(field.showcount)) ? 'max="'+field.showcount+'"' : '' %>><%= (field.value) ? field.value : '' %></textarea>
			</div>
		</div>
</script>

<script type='text/template' id='create-field-wysiwyg'>
	<!-- create-field-wysiwyg -->
		<div class='control-group'>
			<label class='<%= field.label_class %> control-label'><%= field.label %></label>
			<div class="controls">
				<textarea name='<%= field.contenttype %>_<%= field.name %>' class='wysiwyg input-xlarge span6 <%= field.class %> <%= (field.showcount!==false) ? 'countchars' : '' %> <%= (_.isNumber(field.showcount)) ? 'countdown' : '' %>' <%= (_.isNumber(field.showcount)) ? 'max="'+field.showcount+'"' : '' %>><%= nullStr(field.defaultValue) %></textarea>
			</div>
		</div>
</script>

<script type='text/template' id='edit-field-zone'>
	<!-- edit-field-zone -->
		<div class='control-group'>
			<label class='<%= field.label_class %> control-label'><%= field.label %></label>
			<div class="controls">
				<% _.each(field.value, function(item) { %>
				<%= _.template($("#field-zone-item").html(), { fieldname: field.contenttype+"_"+ field.name, zone: item }) %>
				<% }); %>
				<a href="#" data-fieldname="<%= field.contenttype %>_<%= field.name %>" class="add-zone btn btn-primary" style="margin-top: 20px"><i class="icon-plus icon-white"></i> Add a zone</a>
			</div>
		</div>
</script>

<script type='text/template' id='create-field-zone'>
	<!-- create-field-zone -->
		<div class='control-group'>
			<label class='<%= field.label_class %> control-label'><%= field.label %></label>
			<div class="controls">
				<a href="#" data-fieldname="<%= field.contenttype %>_<%= field.name %>" class="add-zone btn btn-primary" style="margin-top: 20px"><i class="icon-plus icon-white"></i> Add a zone</a>
			</div>
		</div>
</script>

<script type='text/template' id='field-zone-item'>
	<div class="row">
		<div class="span10">
			<label>Name</label>
			<input type="text" name="zone_name" value="<%= nullStr(zone.zone_name) %>" class="zone-field" />
			<label>Url ID</label>
			<input type="text" name="zone_urlid" value="<%= nullStr(zone.zone_urlid) %>" class="zone-field" />
			<label>Auto or manual</label>
			<select name="zone_auto" class="zone-field">
				<option value="manual">Manual</option>
				<option value="auto">Auto</option>
			</select>
			<label>Min items</label>
			<input type="text" name="zone_min_items" value="<%= nullStr(zone.zone_min_items) %>" class="zone-field" />
			<label>Max items</label>
			<input type="text" name="zone_max_items" value="<%= nullStr(zone.zone_max_items) %>" class="zone-field" />
			<label>Position ID</label>
			<select name="zone_position_id" class="zone-field">
				<% for(var x=1; x < 10; x++) { %>
				<option <%= (zone.zone_position_id==x) ? 'selected="selected"' : '' %>><%= x %></option>
				<% } %>
			</select>
			<label>Content Types</label>
			<select name="zone_content_types" class="zone-field select-content-types" multiple="multiple">
				<% _.each(content_types, function(item) { %>
					<option value="<%= item._id %>" <%= ((zone.zone_content_types) && (zone.zone_content_types.indexOf(item._id)) >= 0) ? "selected='selected'" : '' %>><%= item.name %></option>
				<% }); %>
			</select>
			<input type="hidden" class="zone-data" name="<%= fieldname %>[<%= zone.zone_urlid %>]" value='<%= JSON.stringify(zone) %>' />
			<div style="margin-top: 20px"><a href="#" class="btn btn-warning btn-mini remove-zone">Remove zone</a></div>
		</div>
	</div>
</script>

<script type='text/template' id='button-new-template'>
	<button id="new_<%= field.contenttype %>_<%= field.name %>" contenttype="<%= field.contenttype %>" fieldname="<%= field.name %>" contenttype="<%= field.contenttype %>" class="btn_new btn">New <%= field.label %></button>
	<span style='display:none;margin-left:10px;' class='label label-success'>Loading...</span>
	<div class='popup' id='new_dialog_<%= field.contenttype %>_<%= field.name %>'></div>
</script>

<script type='text/template' id='create_auto_complete_new'>
	<div style='margin-left:0;' class='main_form_container span10'>
		<div>
			<div id="edit-content" class="span10" >
				<h3>Create - <%= content_type %></h3>
				<span id='element_pointer' pointer='<%= element_pointer %>'></span>
				<form id='contentform' class='form-horizontal span12' method='post' enctype='multipart/form-data' action='<?= base_url() ?>api/content/save?api_key=<%= $(document.body).data('api_key') %>'>
				<input type='hidden' name='action' value='submit' />
				<% _.each(data.meta, function(field) { %>
					<% if (!field.hidden) { %>
						<%= _.template($('#create-field-'+field.type).html(), { field: field, urlid: false, content_type: content_type  }) %>
					<% } %>
				<% }); %>
				
				
				<a style='float:left; margin-right:10px;' class="btn btn-primary btn-mini inpage_create" contenttype="<%= content_type %>" fieldname="<%= name %>" >Save <%= content_type %> </a>
				<a style='float:left; margin-right:10px;' class="btn btn-success btn-mini inpage_cancel" contenttype="<%= content_type %>" fieldname="<%= name %>" >Cancel / Done </a>
				
				
				<div style='display:none; width:400px; border:1px solid #ccc; float:left' class="progress progress-striped active">
						<div class="bar" style="width: 0%;"></div>
				</div>

				</form>

			</div>
			<br clear='both'>
		</div>

		<div class="over_lay slider span10"></div>

	</div>

</script>