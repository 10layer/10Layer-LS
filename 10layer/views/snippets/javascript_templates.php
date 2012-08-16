
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
	<div class="row">
		<div class='control-group span6'>
			<label class='control-label <%= field.label_class %>'><%= field.label %></label>
			<div class='controls'>
				<input id='autocomplete_view_<%= field.tablename %>_<%= field.name %>' type='text' tablename='<%= field.tablename %>' contenttype='<%= field.contenttype %>' fieldname='<%= field.name %>' class="autocomplete <%= (field.multiple==1) ? 'multiple' : '' %> <%= field.class %>" value='' <%= (field.contenttype=='mixed') ? "mixed='mixed' contenttypes='"+field.contenttypes.join(",")+"'" : '' %> />
				<% if ((field.external==1) && (field.hidenew==false)) { %>
				<%= _.template($('#button-new-template').html(), { field: field }) %>
				<%
					}
				%>
				</div>
				<div class="items_container offset1 span8">
				
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
	<label class='<%= field.label_class %>'><%= field.label %></label>
	<input id='autocomplete_view_<%= field.tablename %>_<%= field.name %>' type='text' tablename='<%= field.tablename %>' contenttype='<%= field.contenttype %>' fieldname='<%= field.name %>' class="autocomplete <%= (field.multiple==1) ? 'multiple' : '' %> <%= field.class %>" value='' <%= (field.contenttype=='mixed') ? "mixed='mixed' contenttypes='"+field.contenttypes.join(",")+"'" : '' %> />
	<br clear="both" />
	
	<div class="aligner">
	<ul class="items_container"></ul>
	</div>

	<% if (field.external==1) { %>
	<br clear="both"><br clear="both">
	<button style="margin-left: 110px" id="add_relation_<%= field.tablename %>_<%= field.name %>" contenttype="<%= field.contenttype %>" fieldname="<%= field.name %>" tablename="<%= field.tablename %>" class="add-relation"><span class="ui-button-text">New</button>
	<br clear="both">
	<%
		}
	%>
</script>

<script type='text/template' id='field-autocomplete-item'>
	<div class="autocomplete_item span2 well">
		<button class="close">&times;</button>
		<span class="ui-button-text">
			<%= urlid %>
		</span>
		<input id="autocomplete_<%= field.name %>_<%= urlid %>" type="hidden" name="<%= field.tablename %>_<%= field.name %><%= (field.multiple==1) ? '[]' : '' %>" value="<%= urlid %>"  />
	</div>
</script>

<script type='text/template' id='edit-field-boolean'>
	<!-- edit-field-boolean -->
	<div class="row">
		<div class='control-group span8'>
			<label class='control-label <%= field.label_class %>'><%= field.label %></label>
			<div class='controls'>
				<input type='checkbox' name='<%= field.tablename %>_<%= field.name %>' value='1' class='<%= field.class %>' <%= (field.value==1) ? "checked='checked'" : '' %> />
			</div>
		</div>
	</div>
</script>

<script type='text/template' id='create-field-boolean'>
	<label class='<%= field.label_class %>'><%= field.label %></label>
	<input type='checkbox' name='<%= field.tablename %>_<%= field.name %>' value='1' class='<%= field.class %>' <%= (field.value==1 || field.value==true) ? "checked='checked'" : '' %> />
	<br clear='both' />
</script>

<script type='text/template' id='edit-field-cdn'>
	<!-- edit-field-cdn -->
	<div class='control-group'>
		<label class='control-label <%= field.label_class %>'><%= field.label %></label>
		<div class='controls'>
			<input type='text' name='<%= field.tablename %>_<%= field.name %>' value='<%= field.value %>' class='<%= field.class %>' readonly='readonly' />
		</div>
	</div>
</script>

<script type='text/template' id='create-field-cdn'>
	<!-- create-field-cdn -->
</script>

<script type='text/template' id='edit-field-checkbox'>
	<!-- edit-field-checkbox -->
	<div class="row">
		<div class='control-group span8'>
			<label class='control-label <%= field.label_class %>'><%= field.label %></label>
			<div class="controls">
				<input type='checkbox' name='<%= field.tablename %>_<%= field.name %>' value='1' class='<%= field.class %>' <%= (field.value==1) ? "checked='checked'" : '' %> />
			</div>
		</div>
	</div>
</script>

<script type='text/template' id='create-field-checkbox'>
	<label class='<%= field.label_class %>'><%= field.label %></label>
	<input type='checkbox' name='<%= field.tablename %>_<%= field.name %>' value='1' class='<%= field.class %>' <%= (field.value==1 || field.value==true) ? "checked='checked'" : '' %> />
	<br clear='both' />
</script>

<script type='text/template' id='edit-field-date'>
	<!-- edit-field-date -->
	<div class='control-group'>
		<label class='control-label <%= field.label_class %>'><%= field.label %></label>
		<div class="controls">
			<input type='text' name='<%= field.tablename %>_<%= field.name %>' value='<%= field.value %>' class='datepicker <%= field.class %>' />
		</div>
	</div>
</script>

<script type='text/template' id='create-field-date'>
	<label class='<%= field.label_class %>'><%= field.label %></label>
	<input type='text' name='<%= field.tablename %>_<%= field.name %>' value='<%= (field.value=="now" || field.value=="Today") ? sqlCurrentDate() : field.value  %>' class='datepicker <%= field.class %>' />
	<br clear='both' />
</script>

<script type='text/template' id='edit-field-datetime'>
	<!-- edit-field-datetime -->
	<div class='control-group'>
		<label class='control-label <%= field.label_class %>'><%= field.label %></label>
		<div class="controls">
			<input type='text' name='<%= field.tablename %>_<%= field.name %>' value='<%= field.value %>' class='datetimepicker <%= field.class %>' />
		</div>
	</div>
</script>

<script type='text/template' id='create-field-datetime'>
	<label class='<%= field.label_class %>'><%= field.label %></label>
	<input type='text' name='<%= field.tablename %>_<%= field.name %>' value='<%= (field.value=="now") ? sqlCurrentTime() : ''  %>' class='datetimepicker <%= field.class %>' />
	<br clear='both' />
</script>

<script type='text/template' id='edit-field-deepsearch'>
	<!-- edit-field-deepsearch -->
	<div class="row">
		<div class='control-group deepsearch span8'>
			<label class='control-label <%= field.label_class %>'><%= field.label %></label>
			<div class='controls'>
				<input id="deepsearch_view_<%= field.tablename %>_<%= field.name %>" type="text" tablename="<%= field.tablename %>" contenttype="<%= field.contenttype %>" fieldname="<%= field.name %>" class="deepsearch_input <%= (field.multiple==1) ? 'multiple' : '' %> <%= field.class %>" value="" <% (field.contenttype=='mixed') ? "mixed='mixed' contenttypes='"+field.contenttypes.join(",")+"'" : '' %> />
				<br clear="both" />
				<div class="deepsearch_results well span2" style="height:300px; overflow:auto; float:left;"></div>
				<div class="selected_results well span2" style="height:300px; overflow:auto; ">
				<%
					if (field.data) {
						var x=0;
						_.each(field.data, function(data) {
							var value=data.content_id;
							var title=data.fields.title.value;
							var start_date=data.fields.start_date.value;
				%>
					<div class='deepsearch_selected_item'>
					<input id="deepsearch_<%= field.contenttype %>_<%= field.name %>_<%= value %>" type="hidden" name="<%= field.tablename %>_<%= field.name %><%= (field.multiple==1) ? '[]' : '' %>" value="<%= value %>"  />
					<span><%= title %> (<%= start_date %>)</span>
					</div>
				
<%				
						});
					}
%>				
				</div>
			</div>
		</div>
	</div>
</script>

<script type='text/template' id='create-field-deepsearch'>
<div class="deepsearch">
	<label class='<%= field.label_class %>'><%= field.label %></label>
	<input id="deepsearch_view_<%= field.tablename %>_<%= field.name %>" type="text" tablename="<%= field.tablename %>" contenttype="<%= field.contenttype %>" fieldname="<%= field.name %>" class="deepsearch_input <%= (field.multiple==1) ? 'multiple' : '' %> <%= field.class %>" value="" <% (field.contenttype=='mixed') ? "mixed='mixed' contenttypes='"+field.contenttypes.join(",")+"'" : '' %> />
	<br clear="both" />
	<div class="deepsearch_results" style=" padding: 5px; background-color: #FFF; border: 1px #CCC solid; width:290px; height:300px; overflow:auto; float:left;"></div>
	<div class="selected_results" style=" padding: 5px; background-color: #FFF; border: 1px #CCC solid; width:290px; height:300px; overflow:auto; float:right;">
	</div>
</div>
</script>

<script type='text/template' id='edit-field-drilldown'>
	<%= _.template($("#old-fields-template").html(), { field: field, urlid: urlid, content_type: content_type }) %>
</script>

<script type='text/template' id='create-field-drilldown'>
	<%= _.template($("#old-fields-create-template").html(), { field: field, content_type: content_type }) %>
</script>

<script type='text/template' id='edit-field-embed'>
	<%= _.template($("#old-fields-template").html(), { field: field, urlid: urlid, content_type: content_type }) %>
</script>

<script type='text/template' id='create-field-embed'>
	<%= _.template($("#old-fields-create-template").html(), { field: field, content_type: content_type }) %>
</script>

<script type='text/template' id='edit-field-external'>
	<%= _.template($("#old-fields-template").html(), { field: field, urlid: urlid, content_type: content_type }) %>
</script>

<script type='text/template' id='create-field-external'>
	<%= _.template($("#old-fields-create-template").html(), { field: field, content_type: content_type }) %>
</script>

<script type='text/template' id='edit-field-file'>
	<!-- edit-field-file -->
	<div class='control-group'>
		<label class='control-label <%= field.label_class %>'><%= field.label %></label>
		<div class='controls'>
			<input type="file" name="<%= field.tablename %>_<%= field.name %>" class="file_upload <%= field.class %>" value="<%= field.value %>" />
			<input type="hidden" name="<%= field.tablename %>_<%= field.name %>" value="<%= field.value %>" />
		</div>
	</div>
</script>

<script type='text/template' id='create-field-file'>
	<label class='<%= field.label_class %>'><%= field.label %></label>
	<input type="file" name="<%= field.tablename %>_<%= field.name %>" class="file_upload <%= field.class %>" value="<%= field.value %>" />
</script>

<script type='text/template' id='edit-field-hidden'>
	<!-- edit-field-hidden -->
	<% if (field.multiple) { 
		_.each(field.value, function(value) { %>
			<input type='hidden' name='<%= field.tablename %>_<%= field.name %>[]' value='<%= value %>' />
	<% 	});
		} else { %>
	<input type='hidden' name='<%= field.tablename %>_<%= field.name %>' value='<%= field.value %>' />
	<% } %>
</script>

<script type='text/template' id='create-field-hidden'>
	<!-- create-field-hidden -->
	<input type='hidden' name='<%= field.tablename %>_<%= field.name %>' value='' />
</script>

<script type='text/template' id='edit-field-image'>
	<!-- edit-field-image -->
	<div class='control-group'>
		<label class='control-label <%= field.label_class %>'><%= field.label %></label>
		<div class='controls'>
			<input type="file" name="<%= field.tablename %>_<%= field.name %>" class="file_upload <%= field.class %>" value="<%= field.value %>" data-url='/edit/fileupload/<%= content_type %>/<%= urlid %>/<%= field.tablename %>_<%= field.name %>' />
			<input type="hidden" name="<%= field.tablename %>_<%= field.name %>" value="<%= field.value %>" />
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
	<div class='field-image'>
	<label class='<%= field.label_class %>'><%= field.label %></label>
	<input type="file" name="<%= field.tablename %>_<%= field.name %>" class="file_upload <%= field.class %>" />
	<br clear='both' />
	</div>
</script>

<script type='text/template' id='edit-field-nesteditems'>
	<!-- edit-field-nesteditems -->
	<div class="row">
		<div class='contol-group'>
			<label class='control-label <%= field.label_class %>'><%= field.label %></label>
			<div class='controls nested_container' contenttype="<%= field.contenttype %>">
				<select class='chzn-select' data-placeholder="Choose <%= field.label %>" name='<%= field.tablename %>_<%= field.name %>' id='<%= field.name %>-hook'>
				<option value="0"></option>
			<% 
				var url='/list/jsonnested/'+field.contenttype+'/1';
				$.get(url, function(data) {
					_.each(data.tree, function(item) {
						var s="";
						s="<optgroup label='"+item.title+"'>"+'<option value="'+item.urlid+'" '+((item.urlid==field.value) ? 'selected="selected"' : '')+'>'+item.title+'</option>';
						_.each(item.children, function(child) {
							s+='<option value="'+child.urlid+'" '+((child.urlid==field.value) ? 'selected="selected"' : '')+'>'+item.title+' - '+child.title+'</option>';
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
	<label class='<%= field.label_class %>'><%= field.label %></label>
	<div class='nested_container' contenttype="<%= field.contenttype %>"> 
		<div class="selected_item">
		
			<input id="nestedselect_view_<%= field.tablename %>_<%= field.name %>" name="<%= field.tablename %>_<%= field.name %>" type="hidden" tablename="<%= field.tablename %>" contenttype="<%= field.contenttype %>" fieldname="<%= field.name %>" class="nestedselect <%= field.class %>" value="<%= field.value %>" <%= (field.contenttype=='mixed') ? "mixed='mixed' contenttypes='"+field.contenttypes.join(",")+"'" : '' %> />
			<div class="nesteditems_item_label1"><%= (field.data) ? field.data.fields.title.value : 'None selected' %></div>
		</div>
		
		<div class="nested_items"></div>
	</div>
	<br clear="both"/>
</script>

<script type='text/template' id='edit-field-password'>
	<label class='<%= field.label_class %>'><%= field.label %></label>
	<input type='password' name='<%= field.tablename %>_<%= field.name %>' value='<%= field.value %>' class='<%= field.class %>' />
	<br clear='both' />
</script>

<script type='text/template' id='create-field-password'>
	<label class='<%= field.label_class %>'><%= field.label %></label>
	<input type='password' name='<%= field.tablename %>_<%= field.name %>' value='' class='<%= field.class %>' />
	<br clear='both' />
</script>

<script type='text/template' id='edit-field-radio'>
	<!-- edit-field-radio -->
	<div class='control-group'>
		<label class='control-label <%= field.label_class %>'><%= field.label %></label>
		<div class="controls">
		<div class='radiogroup'>
		<% _.each(field.options, function(option, key) { %>
			<div class='radio'>
				<input type='radio' name='<%= field.tablename %>_<%= field.name %>' value='<%= key %>' <%= (field.value==key) ? 'checked="checked"' : '' %> />
				<div class='radio_label'><%= option %></div>
			</div>
		<% }); %>
		</div>
		</div>
	</div>
</script>

<script type='text/template' id='create-field-radio'>
	<label class='<%= field.label_class %>'><%= field.label %></label>
	<div class='radiogroup'>
	<% _.each(field.options, function(option, key) { %>
		<div class='radio'>
			<input type='radio' name='<%= field.tablename %>_<%= field.name %>' value='<%= key %>' <%= (field.value==key) ? 'checked="checked"' : '' %> />
			<div class='radio_label'><%= option %></div>
		</div>
	<% }); %>
	</div>
	<br clear='both' />
</script>

<script type='text/template' id='edit-field-readonly'>
	<label class='<%= field.label_class %>'><%= field.label %></label>
	<input type='text' name='<%= field.tablename %>_<%= field.name %>' value='<%= field.value %>' class='<%= field.class %>' readonly='readonly' />
	<br clear='both' />
</script>

<script type='text/template' id='create-field-readonly'>
	<label class='<%= field.label_class %>'><%= field.label %></label>
	<input type='text' name='<%= field.tablename %>_<%= field.name %>' value='<%= (field.value!=false) ? field.value : '' %>' class='<%= field.class %>' readonly='readonly' />
	<br clear='both' />
</script>

<script type='text/template' id='edit-field-reverse'>
	<!-- edit-field-reverse -->
	<div class='control-group'>
		<label class='control-label <%= field.label_class %>'><%= field.label %></label>
		<div class='controls'>
			<select class='chzn-select' data-placeholder="Choose <%= field.label %>" name='<%= field.tablename %>_<%= field.name %>' id='<%= field.name %>-hook'>
				<option value="0"></option>
			<% 
				var url='/list/jsonlist/'+field.contenttype;
				$.get(url, function(data) {
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
	<label class='<%= field.label_class %>'><%= field.label %></label>
	<select name='<%= field.tablename %>_<%= field.name %>' id='<%= field.name %>-hook'>
		<option value='0'></option>
	<% 
		var url='/list/jsonlist/'+field.contenttype;
		$.get(url, function(data) {
			_.each(data.content, function(item) {
				$('#'+field.name+'-hook').append('<option value="'+item.content_id+'">'+item.title+'</option>');
			});
		}); 
	%>
	</select>
	<br clear='both' />
</script>

<script type='text/template' id='edit-field-rich'>
	<!-- edit-field-rich -->
	<div class="row">
		<div class='control-group span8'>
			<label class='control-label <%= field.label_class %>'><%= field.label %></label>
			<div class='controls'>
				<button id="contentselectButton_<%= field.tablename %>_<%= field.name %>" contenttype="<%= field.contenttype %>" fieldname="<%= field.name %>" tablename="<%= field.tablename %>" class="btn_rich_select btn">Select <%= field.label %></button>
				<% $(document.body).data('onsave', update_rich) %>
				<%= _.template($('#button-new-template').html(), { field: field }) %>
				<div id='link_results_<%= field.contenttype %>_<%= field.name %>'>
					<% if(field.value) {  %>
						<%= _.template($('#field-rich-item').html(), { contenttype: field.contenttype, fieldname: field.name, urlid: field.value, tablename: field.tablename }) %>
					<% } %>
				</div>
				<div id="contentselect_<%= field.contenttype %>_<%= field.name %>" class="<%= field.contenttype %>_<%= field.name %>-select popup wide"></div>
			</div>
		</div>
	</div>
</script>

<script type='text/template' id='create-field-rich'>
	<%= _.template($("#old-fields-create-template").html(), { field: field, content_type: content_type }) %>
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
			<input type='hidden' name='<%= tablename %>_<%= fieldname %>' value='<%= urlid %>' />
		</div>
</script>

<script type='text/template' id='field-rich-list'>
	<div id="contentlist" class="<%= contenttype %>-list boxed wide">
		<div class="popupSearchContainer">
			<input type="text" class="popup_search" value="<%= (search=='') ? 'Search…' : search %>" fieldname='<%= fieldname %>' contenttype='<%= contenttype %>' tablename='<%= tablename %>' />
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
				<input type="radio" value="<%= item.content_id %>" class="item-select singleselect" fieldname='<%= fieldname %>' contenttype='<%= contenttype %>' tablename='<%= tablename %>' urlid='<%= item.urlid %>' />
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

<script type='text/template' id='edit-field-section'>
	<%= _.template($("#old-fields-template").html(), { field: field, urlid: urlid, content_type: content_type }) %>
</script>

<script type='text/template' id='create-field-section'>
	<%= _.template($("#old-fields-create-template").html(), { field: field, content_type: content_type }) %>
</script>

<script type='text/template' id='edit-field-select'>
	<!-- edit-field-select -->
	<div class="row">
		<div class='control-group'>
			<label class='control-label <%= field.label_class %>'><%= field.label %></label>
			<div class='controls'>
				<select class='chzn-select <%= field.class %>' data-placeholder="Choose <%= field.label %>" name='<%= field.tablename %>_<%= field.name %>'>
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
	</div>
</script>

<script type='text/template' id='create-field-select'>
	<label class='<%= field.label_class %>'><%= field.label %></label>
	<select class='<%= field.class %>' name='<%= field.tablename %>_<%= field.name %>'>
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
		<option value='<%= ( key + keyadjust) %>' <%= (field.value==( key + keyadjust) ) ? 'selected="selected"' : '' %> ><%= option %></option>
	<% }); %>
	</select>
	<br clear='both' />
</script>

<script type='text/template' id='edit-field-text'>
	<!-- edit-field-text -->
	<div class="row ">
		<div class='control-group span8'>
			<label class='control-label <%= field.label_class %>'><%= field.label %></label>
			<div class="controls">
				<input type='text' name='<%= field.tablename %>_<%= field.name %>' value='<%= field.value %>' class='<%= field.class %>' />
			</div>
		</div>
	</div>
</script>

<script type='text/template' id='create-field-text'>
	<label class='<%= field.label_class %>'><%= field.label %></label>
	<input type='text' name='<%= field.tablename %>_<%= field.name %>' value='<%= (field.value!=false) ? field.value : '' %>' class='<%= field.class %>' />
	<br clear='both' />
</script>

<script type='text/template' id='edit-field-textarea'>
	<!-- edit-field-textarea -->
	<div class="row">
		<div class='control-group span8'>
			<label class='<%= field.label_class %> control-label'><%= field.label %></label>
			<div class="controls">
				<textarea name='<%= field.tablename %>_<%= field.name %>' class='input-xlarge span6 <%= field.class %> <%= (field.showcount!==false) ? 'countchars' : '' %> <%= (_.isNumber(field.showcount)) ? 'countdown' : '' %>' <%= (_.isNumber(field.showcount)) ? 'max="'+field.showcount+'"' : '' %>><%= field.value %></textarea>
			</div>
		</div>
	</div>
</script>

<script type='text/template' id='create-field-textarea'>
	<label class='<%= field.label_class %>'><%= field.label %></label>
	<textarea name='<%= field.tablename %>_<%= field.name %>' class='<%= field.class %> <%= (field.showcount!==false) ? 'countchars' : '' %> <%= (_.isNumber(field.showcount)) ? 'countdown' : '' %>' <%= (_.isNumber(field.showcount)) ? 'max="'+field.showcount+'"' : '' %>><%= (field.value!=false) ? field.value : '' %></textarea>
	<br clear='both' />
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
	<button id="new_<%= field.tablename %>_<%= field.name %>" contenttype="<%= field.contenttype %>" fieldname="<%= field.name %>" tablename="<%= field.tablename %>" class="btn_new btn">New <%= field.label %></button>
	<div class='popup' id='new_dialog_<%= field.tablename %>_<%= field.name %>'></div>
</script>

<script type='text/template' id='create-template'>
	<%	
		if (typeof popup == 'undefined') {
			popup = false;
		}
	%>
	<div id="create-content" class="boxed wide">
		<h2>Create - <%= content_type %></h2>
		<form id='form_create_<%= content_type %>' class='<%= (popup) ? "popupform" : "contentform" %>' method='post' enctype='multipart/form-data' action='<?= base_url() ?>create/ajaxsubmit/<%= content_type %>'>
		<input type='hidden' name='action' value='submit' />
		<% _.each(data.fields, function(field) { %>
			<% if (!field.hidden) { %>
				<%= _.template($('#create-field-'+field.type).html(), { field: field, urlid: false, content_type: content_type  }) %>
			<% } %>
		<% }); %>
		<% if (popup) { %>
		<button contenttype="<%= content_type %>" fieldname="<%= name %>" class='dosubmit_popup'>Save</button>
		<% } %>
		</form>
	</div>
	<% if (popup == false) { %>
	<div id="sidebar" class="pin">
		<div id="sidebar_accordian">
			<h3><a href="#">Actions</a></h3>
			<div>
				<button id="dosubmit_right">Save</button><br />
				<br />
			</div>
		</div>
	</div>
	<% } %>
</script>