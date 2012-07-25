<?php
	link_js("/tlresources/file/js/forms.js");
	link_js("/tlresources/file/js/messaging.js");
?>

<div id="create-content" class="boxed wide">
	<form id="user-form" method="post">
		<input type="hidden" id="doupdate" name="doupdate" value="1" />
		<input type="hidden" id="id" name="id" value="<?= $user->id ?>" />
		<input type="hidden" name="urlid" id="urlid" value="<?= $user->urlid ?>" />
		
		<label class="bigger">Name</label>
		<input type="text" name="name" id="title" class="required bigger" value="<?= $user->name ?>" /><br />
		
		<label>Email</label>
		<input name="email" class="required" value="<?= $user->email ?>"><br />
		
		<label>Roles</label>
		<select multiple="multiple" name="roles[]">
			<?php
				foreach($roles as $role) {
					$selected="";
					if ($this->model_user->hasRole($role->id, $user->id)) {
						$selected="selected='selected'";
					}
			?>
			<option value="<?= $role->id ?>" <?= $selected ?>><?= $role->name ?></option>
			<?php
				}
			?>
		</select><br />
		
		<label>Permissions</label>
		<select multiple="multiple" name="permissions[]">
			<?php
				foreach($permissions as $permission) {
					$selected="";
					if ($this->model_user->hasPermission($permission->id, $user->id)) {
						$selected="selected='selected'";
					}
			?>
			<option value="<?= $permission->id ?>" <?= $selected ?>><?= $permission->name ?></option>
			<?php
				}
			?>
		</select><br />
		
		<label>Account Status</label>
		<select name="status"> 
			<?php
				foreach($statuses as $status) {
					$selected="";
					if ($user->status_id==$status->id) {
						$selected="selected='selected'";
					}
			?>
			<option value="<?=$status->id ?>" <?= $selected ?>><?= $status->name ?></option>
			<?php
				}
			?>
		</select>
		<input type="submit" id="submit" name="submit" value="Update user" class="button" /><br />
	</form>
</div>