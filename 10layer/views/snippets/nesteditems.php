<script>
	$(function() {
		
		$(".single_item_button").live("click" ,function() {
			var resultdiv=$(this).next();
			if(resultdiv.is(':hidden') ) {
    			$.get('/list/nested/<?= $field->contenttype ?>/1', function(data) {
  					resultdiv.html(data);
  					resultdiv.toggle();
				});	
			}else{
				resultdiv.toggle();
			}
			
			
						
		});
		
		$(".small_item").live("click", function(){
			var value = $(this).attr("id");
			var display_term = $(this).parentsUntil(".section_list").parent().prev().prev();
			display_term.html($(this).html());
			var value_holder = display_term.prev();
			value_holder.val(value);
			display_term.next().next().hide();
			
		});
		
						
		
	});

</script>


<?php
	if (!empty($id)) {
		$this->db->select("content.urlid, content.id AS content_id, content.title, content_types.urlid AS content_type, content.content_type_id");
		$this->db->join("content", "content.id=content_content.content_link_id");
		$this->db->join("content_types","content.content_type_id=content_types.id");
		$this->db->where("content_content.content_id",$id);
		$this->db->where("content_content.fieldname",$field->name);
		$result=$this->db->get("content_content");
		
		//echo $this->db->last_query();
		
		//print_r($field);
		
		
		$contents=array();
		if ($result->num_rows()>0) {
			if ($field->multiple) {
				foreach($result->result() as $row) {
					$field->value[]=$row->content_id;
				}
			} else {
				$row=$result->row();
				$field->value=$row->content_id;
			}
		}
	}

?>


<div class="single_item_actions">

	<input id="nestedselect_view_<?= $field->tablename ?>_<?= $field->name ?>" name="<?= $field->tablename ?>_<?= $field->name ?>" type="hidden" tablename="<?= $field->tablename ?>" contenttype="<?= $field->contenttype ?>" fieldname="<?= $field->name ?>" class="nestedselect <?= $field->class ?>" value="<?php if(isset($field->data)){ print $field->value; } ?>" <?php if ($field->contenttype=='mixed') { ?> mixed='mixed' contenttypes='<?= implode(",",$field->contenttypes) ?>' <?php } ?> />
	
	

	<div class="single_item_label">
		<?php if(isset($field->data)){ print $field->data->fields["title"]->value; } else{ print "Click on change..."; }  ?>
	</div>
	<div class="single_item_button">
		Change...
	</div>
	<div class="section_list" >
	
	</div>
	
		
	<br clear="both"/><br clear="both"/>
	
	
	<button style="margin-left: 110px" id="add_relation_<?= $field->tablename ?>_<?= $field->name ?>" contenttype="<?= $field->contenttype ?>" fieldname="<?= $field->name ?>" tablename="<?= $field->tablename ?>" class="add-relation ui-button-text-icons ui-button ui-widget ui-state-default ui-corner-all " role="button" aria-disabled="false"><span class="ui-button-text"><span class="ui-button-icon-primary ui-icon ui-icon-plusthick"></span>New</span></button>



</div>

