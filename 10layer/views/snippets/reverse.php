<?php
	if (!empty($id)) {
		$this->db->select("content.urlid, content.id AS content_id, content.title, content_types.urlid AS content_type, content.content_type_id");
		$this->db->join("content", "content.id=content_content.content_link_id");
		$this->db->join("content_types","content.content_type_id=content_types.id");
		$this->db->where("content_content.content_id",$id);
		$this->db->where("content_content.fieldname",$field->name);
		$result=$this->db->get("content_content");
		
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
	$result_contenttype=$this->db->get_where("content_types",array("urlid"=>$field->contenttype));
	$result_options=$this->db->get_where("content",array("content_type_id"=>$result_contenttype->row()->id));
	foreach($result_options->result() as $option) {
		$field->options[$option->id]=$option->title;
	}
?>

	<select
		<?php
			if ($field->multiple) {
		?>
	multiple='multiple' 
		<?php
			}
		?>
	name="<?= $field->tablename ?>_<?= $field->name ?><?php
			if ($field->multiple) {
		?>[]
		<?php
			}
		?>" class="<?= $field->contenttype ?>_<?= $field->name ?> <?= $field->class ?> <?php if ($field->multiple) {
		?>multiple
		<?php
		}
		?>">
		<?php
			if (!$field->multiple) { //We only show no option for single selects
		?>
			<option value="0"></option>
		<?php
			}
			//We don't want to have a key of zero, which is what will happen if we get an array without explicit keys
			$keyadjust=0;
			foreach($field->options as $key=>$val) {
				if ($key==0) {
					$keyadjust=1;
				}
		?>
		<option value="<?= $key+$keyadjust ?>"
		<?php
			if (!is_array($field->value)) {
				if ($field->value==($key+$keyadjust)) {
		?>
			selected="selected"
			<?php
				}
			?>
		<?php
			} else {
				if (in_array(($key+$keyadjust),$field->value)) {
				?>
			selected="selected"
				<?php
				}
			}
		?>><?= $val ?></option>
		<?php
			}
		?>
	</select>
	<br clear="both" />
	<button style="margin-left: 110px" id="add_relation_<?= $field->tablename ?>_<?= $field->name ?>" contenttype="<?= $field->contenttype ?>" fieldname="<?= $field->name ?>" tablename="<?= $field->tablename ?>" class="add-relation ui-button-text-icons ui-button ui-widget ui-state-default ui-corner-all " role="button" aria-disabled="false"><span class="ui-button-text"><span class="ui-button-icon-primary ui-icon ui-icon-plusthick"></span>New</span></button>
