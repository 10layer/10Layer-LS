
<script>
	function leadingZeros(s) {
		if (s<10) {
			return '0'+s;
		}
		return s;
	}
	
	function sqlCurrentDate() {
		var d = new Date();
		return leadingZeros(d.getFullYear())+'-'+leadingZeros(d.getMonth()+1)+'-'+leadingZeros(d.getDate());
	}
	
	function sqlCurrentTime() {
		var d = new Date();
		return leadingZeros(sqlCurrentDate())+' '+leadingZeros(d.getHours())+':'+leadingZeros(d.getMinutes());
	}
</script>
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
						value='' <%= (field.contenttype=='mixed') ? "mixed='mixed' contenttypes='"+field.contenttypes.join(",")+"'" : '' %> />
						
					<% if ((field.external==1) && (field.hidenew==false)) { %>
					<%= _.template($('#button-new-template').html(), { field: field }) %>
					<%
						}
					%>

				</div>


				<span style='position:absolute; top:-12px !important; z-index:10; left:120px; display:none;' class='indicator label label-success'>loading...</span>
				<ul class="options dropdown-menu"></ul>
				<div class="result_container" style="margin-top:5px;">
					<%
						if (field.value) {
							if (!_.isArray(field.value)) {
								field.value=[field.value];
							}
							_.each(field.value, function(urlid) {
					%>
								<%= _.template($('#field-autocomplete-item').html(), { urlid: urlid, field: field }) %>
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
			<div class='controls'>
				<input id='autocomplete_view_<%= field.contenttype %>_<%= field.name %>' type='text' contenttype='<%= field.contenttype %>' contenttype='<%= field.contenttype %>' fieldname='<%= field.name %>' class="autocomplete <%= (field.multiple==1) ? 'multiple' : '' %> <%= field.class %>" value='' <%= (field.contenttype=='mixed') ? "mixed='mixed' contenttypes='"+field.contenttypes.join(",")+"'" : '' %> />
				<div class="items_container offset1 span8"></div>
				<% if ((field.external==1) && (field.hidenew==false)) { %>
				<%= _.template($('#button-new-template').html(), { field: field }) %>
				<%
					}
				%>
			</div>
		</div>
</script>

<script type='text/template' id='field-autocomplete-item'>
	<div style="float:left; padding:4px; margin-right:5px; border:1px solid #ccc; border-radius:5px; -moz-border-radius:5px;">
		<a style="margin-top:-4px;" class="close">&times;</a>
		<span style="float:left;margin-right:3px;" class="label label-info">  </span>
		<input id="autocomplete_<%= field.name %>_<%= urlid %>" type="hidden" name="<%= field.contenttype %>_<%= field.name %><%= (field.multiple=='multiple') ? '[]' : '' %>" value="<%= urlid %>"  />
	</div>
</script>

<script type='text/template' id='edit-field-boolean'>
	<!-- edit-field-boolean -->
	<%= _.template($('#edit-field-checkbox').html(), {field: field} ) %>
</script>

<script type='text/template' id='create-field-boolean'>
	<!-- create-field-boolean -->
	<%= _.template($('#edit-field-checkbox').html(), {field: field} ) %>
</script>

<script type='text/template' id='edit-field-cdn'>
	<!-- edit-field-cdn -->
	<div class='control-group'>
		<label class='control-label <%= field.label_class %>'><%= field.label %></label>
		<div class='controls'>
			<input type='text' name='<%= field.contenttype %>_<%= field.name %>' value='<%= field.value %>' class='<%= field.class %>' readonly='readonly' />
		</div>
	</div>
</script>

<script type='text/template' id='create-field-cdn'>
	<!-- create-field-cdn -->
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
	<%= _.template($('#edit-field-checkbox').html(), {field: field} ) %>
</script>

<script type='text/template' id='edit-field-date'>
	<!-- edit-field-date -->
	<div class='control-group'>
		<label class='control-label <%= field.label_class %>'><%= field.label %></label>
		<div class="controls">
			<input type='text' name='<%= field.contenttype %>_<%= field.name %>' value='<%= (field.value) ? field.value : '' %>' class='datepicker <%= field.class %>' data-date="<%= (field.value) ? field.value : '' %>" data-date-format="yyyy-mm-dd" />
		</div>
	</div>
</script>

<script type='text/template' id='create-field-date'>
	<!-- create-field-date -->
	<%= _.template($('#edit-field-date').html(), {field: field} ) %>
</script>

<script type='text/template' id='edit-field-datetime'>
	<!-- edit-field-datetime -->
	<%
		var val_date = val_hour = val_minute = "";
		if (field.value) {
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
			<input type="hidden" name='<%= field.contenttype %>_<%= field.name %>' value='<%= (field.value) ? field.value : '' %>' />
			<input type='text' id='<%= field.contenttype %>_<%= field.name %>_datetime_date' class='datetime_change datetime_date datepicker <%= field.class %>' data-date-format="yyyy-mm-dd" value="<%= val_date %>" />
			<input type='text' id='<%= field.contenttype %>_<%= field.name %>_datetime_hour' class='datetime_change datetime_hour <%= field.class %>' value="<%= val_hour %>" /> :
			<input type='text' id='<%= field.contenttype %>_<%= field.name %>_datetime_minute' class='datetime_change datetime_minute <%= field.class %>' value="<%= val_minute %>" />
		</div>
	</div>
</script>

<script type='text/template' id='create-field-datetime'>
	<!-- create-field-datetime -->
	<%= _.template($('#edit-field-datetime').html(), {field: field} ) %>
</script>

<script type='text/template' id='edit-field-deepsearch'>
	<!-- edit-field-deepsearch -->
		<div class='control-group deepsearch'>
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
						_.each(field.value, function(urlid) {
				%>
							<%= _.template($('#field-autocomplete-item').html(), { urlid: urlid, field: field }) %>
				<%
						});
					}
				%>
				</div>
			</div>
		</div>
</script>

<script type='text/template' id='create-field-deepsearch'>
	<!-- create-field-deepsearch -->
	<%= _.template($('#edit-field-deepsearch').html(), {field: field} ) %>
</script>

<script type='text/template' id='edit-field-external'>
	<!-- edit-field-external -->
	<div class='control-group'>
		<label class='control-label <%= field.label_class %>'><%= field.label %></label>
		<div class='controls'>
			<select class='chzn-select' data-placeholder="Choose <%= field.label %>" name='<%= field.contenttype %>_<%= field.name %>' id='<%= field.name %>-hook'>
				<option value="0"></option>
			<% 
			$.get(field.options, function(data) {
				var parts=data.split("\n");
				_.each(parts, function(value) {
					$('#'+field.name+'-hook').append('<option value="'+value+'" '+((value==field.value) ? 'selected="selected"' : '')+'>'+value+'</option>');
						
				});
				$("#"+field.name+"-hook").trigger("liszt:updated");
			}); 
			%>
			</select>
		</div>
	</div>
</script>

<script type='text/template' id='create-field-external'>
	<!-- create-field-external -->
	<%= _.template($('#edit-field-external').html(), {field: field} ) %>
</script>

<script type='text/template' id='edit-field-file'>
	<!-- edit-field-file -->
	<div class='control-group'>
		<label class='control-label <%= field.label_class %>'><%= field.label %></label>
		<div class='controls'>
			<input type="file" name="<%= field.contenttype %>_<%= field.name %>" class="file_upload <%= field.class %>" value="<%= field.value %>" />
			<input type="hidden" name="<%= field.contenttype %>_<%= field.name %>" value="<%= field.value %>" />
		</div>
	</div>
</script>

<script type='text/template' id='create-field-file'>
	<!-- create-field-file -->
	<%= _.template($('#edit-field-file').html(), {field: field} ) %>
</script>

<script type='text/template' id='edit-field-hidden'>
	<!-- edit-field-hidden -->
	<% if (field.multiple) { 
			_.each(field.value, function(value) { %>
				<input type='hidden' name='<%= field.contenttype %>_<%= field.name %>[]' value='<%= (value) ? value : '' %>' />
	<% 		});
		} else { %>
			<input type='hidden' name='<%= field.contenttype %>_<%= field.name %>' value='<%= (field.value) ? field.value : '' %>' />
	<% } %>
</script>

<script type='text/template' id='create-field-hidden'>
	<!-- create-field-hidden -->
	<%= _.template($('#edit-field-hidden').html(), {field: field} ) %>
</script>

<script type='text/template' id='edit-field-image'>
	<!-- edit-field-image -->
	<div class='control-group'>
		<label class='control-label <%= field.label_class %>'><%= field.label %></label>
		<div class='controls'>
			<input type="file" name="<%= field.contenttype %>_<%= field.name %>" class="file_upload <%= field.class %>" value="<%= field.value %>" data-url='/edit/fileupload/<%= content_type %>/<%= urlid %>/<%= field.contenttype %>_<%= field.name %>' />
			<input type="hidden" name="<%= field.contenttype %>_<%= field.name %>" value="<%= field.value %>" />
		<% if (urlid) { %>
			<div class="preview-image">
				<img src="/workers/picture/display/<%= urlid %>/cropThumbnailImage/500/300?<%= Math.random() * 1000 %>" />
			</div>
		<% } %>
		<% if (field.linkformat && urlid) { %>
			<label>Download link</label>
			<div class='download_url'><input type='text' class='span8 select_on_click' readonly='readonly' value='<%= field.linkformat.replace('{filename}', field.value) %>' /></div>
		<% } %>
		</div>
	</div>
</script>

<script type='text/template' id='create-field-image'>
	<!-- edit-field-image -->
	<div class='control-group'>
		<label class='control-label <%= field.label_class %>'><%= field.label %></label>
		<div class='controls'>
			<input type="file" name="<%= field.contenttype %>_<%= field.name %>" class="file_upload <%= field.class %>" />
		</div>
	</div>
</script>

<script type='text/template' id='edit-field-nesteditems'>
	<!-- edit-field-nesteditems -->
		<% compfield = field.contenttype; %>
		<div class='control-group'>
			<label class='control-label <%= field.label_class %>'><%= field.label %></label>
			<div class='controls nested_container' contenttype="<%= field.contenttype %>">
				<select class='chzn-select' data-placeholder="Choose <%= field.label %>" name='<%= field.contenttype %>_<%= field.name %>' id='<%= field.name %>-hook'>
				<option value="0"></option>
			<% 
				var url='/api/content/listing/';
				$.get(url, { api_key: $(document.body).data('api_key'), content_type: field.contenttype }, function(data) {
					var sections = [];
					var subsections = [];
					_.each(data.content, function(section) {
						if (!(section[compfield])) {
							sections.push(section);
						} else {
							subsections.push( section );
						}
					});
					var tmp=[];
					_.each(sections, function(section) {
						var s="";
						s="<optgroup label='"+section.title+"'>"+'<option value="'+section._id+'" '+((section.urlid==field.value) ? 'selected="selected"' : '')+'>'+section.title+'</option>';
						_.each(subsections, function(subsection) {
							if (subsection[compfield] == section._id) {
								s+='<option value="'+subsection._id+'" '+((subsection._id == field.value) ? 'selected="selected"' : '')+'>'+subsection.title+'</option>';
							}
						});
						s+='</optgroup>';
						$('#'+field.name+'-hook').append(s);
					});
					$("#"+field.name+"-hook").trigger("liszt:updated");
				}); 
			%>
				</select>
			</div>
		</div>
</script>

<script type='text/template' id='edit-field-nesteditems-item'>
	<% _.each(tree, function(item) { %>
		<% if (item.children) { %>
		<optgroup label='<%= item.title %>'>
			<option value='<%= item.urlid %>'  ><%= item.title %></option>
			<%= (item.children) ? _.template($('#edit-field-nesteditems-list').html(), {tree: item.children}) : '' %>
		</optgroup>
		<% } else { %>
			<option value='<%= item.urlid %>'><%= item.title %></option>
		<% } %>
	<% }); %>
</script>

<script type='text/template' id='create-field-nesteditems'>
	<!-- create-field-nesteditems -->
		<% compfield = field.contenttype; %>
		<div class='control-group'>
			<label class='control-label <%= field.label_class %>'><%= field.label %></label>
			<div class='controls nested_container' contenttype="<%= field.contenttype %>">
				<select class='chzn-select' data-placeholder="Choose <%= field.label %>" name='<%= field.contenttype %>_<%= field.name %>' id='<%= field.name %>-hook'>
				<option value="0"></option>
			<% 
				var url='/api/content/listing/';
				$.get(url, { api_key: $(document.body).data('api_key'), content_type: field.contenttype }, function(data) {
					var sections = [];
					var subsections = [];
					_.each(data.content, function(section) {
						if (!(section[compfield])) {
							sections.push(section);
						} else {
							subsections.push( section );
						}
					});
					var tmp=[];
					_.each(sections, function(section) {
						var s="";
						s="<optgroup label='"+section.title+"'>"+'<option value="'+section._id+'" '+((section.urlid==field.value) ? 'selected="selected"' : '')+'>'+section.title+'</option>';
						_.each(subsections, function(subsection) {
							if (subsection[compfield] == section._id) {
								s+='<option value="'+subsection._id+'" '+((subsection._id == field.value) ? 'selected="selected"' : '')+'>'+subsection.title+'</option>';
							}
						});
						s+='</optgroup>';
						$('#'+field.name+'-hook').append(s);
					});
					$("#"+field.name+"-hook").trigger("liszt:updated");
				}); 
			%>
				</select>
			</div>
		</div>
</script>

<script type='text/template' id='edit-field-password'>
	<!-- edit-field-password -->
	<div class='control-group'>
		<label class='control-label <%= field.label_class %>'><%= field.label %></label>
		<div class="controls">
			<input type='password' name='<%= field.contenttype %>_<%= field.name %>' value='<%= field.value %>' class='<%= field.class %>' />
		</div>
	</div>
</script>

<script type='text/template' id='create-field-password'>
	<!-- create-field-password -->
	<%= _.template($('#edit-field-password').html(), {field: field} ) %>
</script>

<script type='text/template' id='edit-field-radio'>
	<!-- edit-field-radio -->
	<div class='control-group'>
		<label class='control-label <%= field.label_class %>'><%= field.label %></label>
		<div class="controls">
			<div class='radiogroup'>
			<% _.each(field.options, function(option, key) { %>
				<div class='radio'>
					<input type='radio' name='<%= field.contenttype %>_<%= field.name %>' value='<%= key %>' <%= (field.value==key) ? 'checked="checked"' : '' %> />
					<div class='radio_label'><%= option %></div>
				</div>
			<% }); %>
			</div>
		</div>
	</div>
</script>

<script type='text/template' id='create-field-radio'>
	<!-- create-field-radio -->
	<%= _.template($('#edit-field-radio').html(), {field: field} ) %>
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
	<%= _.template($('#edit-field-readonly').html(), {field: field} ) %>
</script>

<script type='text/template' id='edit-field-reverse'>
	<!-- edit-field-reverse -->
	<div class='control-group'>
		<label class='control-label <%= field.label_class %>'><%= field.label %></label>
		<div class='controls'>
			<select class='chzn-select' data-placeholder="Choose <%= field.label %>" name='<%= field.contenttype %>_<%= field.name %>' id='<%= field.name %>-hook'>
				<option value="0"></option>
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

<script type='text/template' id='edit-field-rich'>
	<!-- edit-field-rich -->
		<div class='control-group'>
			<label class='control-label <%= field.label_class %>'><%= field.label %></label>
			<div class='controls'>
				<button id="contentselectButton_<%= field.contenttype %>_<%= field.name %>" contenttype="<%= field.contenttype %>" fieldname="<%= field.name %>" contenttype="<%= field.contenttype %>" class="btn_rich_select btn">Select <%= field.label %></button>
				<% $(document.body).data('onsave', update_rich) %>
				<%= _.template($('#button-new-template').html(), { field: field }) %>
				<div id='link_results_<%= field.contenttype %>_<%= field.name %>'>
					<% if(field.value) {  %>
						<%= _.template($('#field-rich-item').html(), { contenttype: field.contenttype, fieldname: field.name, urlid: field.value, contenttype: field.contenttype }) %>
					<% } %>
				</div>
				<div id="contentselect_<%= field.contenttype %>_<%= field.name %>" class="<%= field.contenttype %>_<%= field.name %>-select popup wide"></div>
			</div>
		</div>
</script>

<script type='text/template' id='create-field-rich'>
	<!-- create-field-rich -->
	<%= _.template($("#edit-field-rich").html(), { field: field, content_type: content_type }) %>
</script>

<script type='text/template' id='field-rich-item'>
		<a href="/edit/picture/<%= urlid %>" class="btn" target="_top">Edit</a>
		<div class="link_results">
			<div class="rich_overlay">
				<button class="close">&times;</button>
				
			</div>
			<div class="selectitem">
				<div class="thumbnail"><img src="/workers/picture/display/<%= urlid %>/cropThumbnailImage/460/200" /></div>
			</div>
			<input type='hidden' name='<%= contenttype %>_<%= fieldname %>' value='<%= urlid %>' />
		</div>
</script>

<script type='text/template' id='field-rich-list'>
	<div id="contentlist" class="<%= contenttype %>-list boxed wide">
		<div class="popupSearchContainer">
			<input type="text" class="popup_search" value="<%= (search=='') ? 'Search…' : search %>" fieldname='<%= fieldname %>' contenttype='<%= contenttype %>' contenttype='<%= contenttype %>' />
			<span class="popupResultsCount"></span>
		</div>
		
		<table>
			<tr> 
				<th></th>
				<th></th>
				<th>Title</th>
				<th>Edit</th>
			</tr>
		<%
			var odd="odd";
			_.each(content, function(item) {
		%>
		<tr class="<%= odd %> <%= contenttype %>-item content-item">
			<td>
				<input type="radio" value="<%= item.content_id %>" class="item-select singleselect" fieldname='<%= fieldname %>' contenttype='<%= contenttype %>' contenttype='<%= contenttype %>' urlid='<%= item.urlid %>' />
			</td>
			<td>
				<img src="/workers/picture/display/<%= item.urlid %>/cropThumbnailImage/40/30" />
			</td>
			<td><%= item.title %></td>
			<td><a href="/edit/<%= contenttype %>/<%= item.urlid %>">Edit</a></td>
		</tr>
		<%
			if (odd=="") {
				odd="odd";
			} else {
				odd="";
			}
		});
		%>
	</table>
	
	</div>
	<br clear="both" />
</script>

<script type='text/template' id='edit-field-select'>
	<!-- edit-field-select -->
		<div class='control-group'>
			<label class='control-label <%= field.label_class %>'><%= field.label %></label>
			<div class='controls'>
				<select class='chzn-select <%= field.class %>' data-placeholder="Choose <%= field.label %>" name='<%= field.contenttype %>_<%= field.name %>'>
				<option value="0"></option>
				<% 
				var keyadjust=0;
				_.each(field.options, function(val, key) {
					if (key==0) {
						keyadjust=1;
					}
				});
				%>
				<% _.each(field.options, function(option, key) { %>
					<option value='<%= ( key + keyadjust) %>' <%= (field.value==( key + keyadjust)) ? 'selected="selected"' : '' %> ><%= option %></option>
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
	<%= _.template($('#edit-field-text').html(), {field: field} ) %>
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
				<textarea name='<%= field.contenttype %>_<%= field.name %>' class='input-xlarge span6 <%= field.class %> <%= (field.showcount!==false) ? 'countchars' : '' %> <%= (_.isNumber(field.showcount)) ? 'countdown' : '' %>' <%= (_.isNumber(field.showcount)) ? 'max="'+field.showcount+'"' : '' %>><%= (field.value) ? field.value : '' %></textarea>
			</div>
		</div>
</script>

<script type='text/template' id='old-fields-template'>
	<!-- old-fields-template -->
	<div class='control-group'>
		<label class='control-label <%= field.label_class %>'><%= field.label %></label>
		<div class='controls'>
		<% 
		var url='/edit/field/'+field.name+'/'+content_type+'/'+urlid;
		$.get(url, function(data) {
			$('#'+field.name+'-hook').html(data);
		}); 
		%>
		<div id='<%= field.name %>-hook'></div>
		</div>
	</div>
</script>

<script type='text/template' id='old-fields-create-template'>
	<label class='<%= field.label_class %>'><%= field.label %></label>
	<% 
		var url='/create/field/'+field.name+'/'+content_type;
		$.get(url, function(data) {
			$('#'+field.name+'-hook').html(data);
		}); 
	%>
	<div id='<%= field.name %>-hook'></div>
	<br clear='both' />
</script>

<script type='text/template' id='button-new-template'>
	<button id="new_<%= field.contenttype %>_<%= field.name %>" contenttype="<%= field.contenttype %>" fieldname="<%= field.name %>" contenttype="<%= field.contenttype %>" class="btn_new btn">New <%= field.label %></button>
	<div class='popup' id='new_dialog_<%= field.contenttype %>_<%= field.name %>'></div>
</script>