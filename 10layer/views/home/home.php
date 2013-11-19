<?php 
	$data["menu1_active"]="home";
	$this->load->view('templates/header',$data);
?>
<?php
	$this->socketio->js();
?>
<script src="/resources/js/jquery.pagination.js"></script>
<script src="/resources/knockout/knockout-2.2.1.js"></script>
<script src="/resources/bootstrap-datepicker/js/bootstrap-datepicker-ck.js"></script>
<script type="text/javascript" src="/resources/js/models/listing.js"></script>
<script>
	$(function() {
		
		$(document.body).data('api_key', '<?= $this->session->userdata('api_key') ?>');
		$(document.body).data('content_type', "");
		$(document.body).data('page', 'list');
		$(document.body).data('base_url', '<?= base_url() ?>');

		ko.applyBindings(new ListingModelView());
				
	}); //End of $(function)
	
</script>

<?php
	$this->load->view("content/listing_table");
?>

<div class="modal hide fade" id="msgdialog" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
		<h3 id="msgdialog-header"></h3>
	</div>
	<div class="modal-body" id="msgdialog-body">
	</div>
	<div class="modal-footer">
		<div id="msgdialog-buttons" class="btn-group">
		</div>
	</div>
</div>

<div class="modal hide fade" id="msgdialogDelete" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
		<h3 id="msgdialog-header">Confirm Delete</h3>
	</div>
	<div class="modal-body" id="msgdialog-body">
		<p>Are you sure you want to delete <span data-bind="text:selected_count"></span> documents?</p>
	</div>
	<div class="modal-footer">
		<div id="msgdialog-buttons" class="btn-group">
			<button data-bind="click:clickDoDelete" class="btn btn-danger" data-dismiss="modal" aria-hidden="true" id="btn_confirm_multi_delete">Delete</button> <button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
		</div>
	</div>
</div>

<?php
	$this->load->view("templates/footer");
?>