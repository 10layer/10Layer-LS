<?php
	link_js("/resources/js/forms.js");
	link_js("/resources/js/messaging.js");
?>

<div id="create-content" class="boxed wide">
	<form id="user-form" method="post">
		<input type="hidden" id="doupdate" name="doupdate" value="1" />
		
		<label class="bigger">Name</label>
		<input type="text" name="name" id="title" class="required bigger" value="" /><br />
		
		<label>Email</label>
		<input name="email" class="required" value=""><br />
		
		<label>Roles</label>
		<select multiple="multiple" name="roles[]">
			<?php
				foreach($roles as $role) {
					
			?>
			<option value="<?= $role->id ?>" ><?= $role->name ?></option>
			<?php
				}
			?>
		</select><br />
		
		<label>Permissions</label>
		<select multiple="multiple" name="permissions[]">
			<?php
				foreach($permissions as $permission) {
					
			?>
			<option value="<?= $permission->id ?>" ><?= $permission->name ?></option>
			<?php
				}
			?>
		</select><br />
		
		<label>Password</label>
		<input name="password" type="password" value="" /><br />
		<label>Confirm Password</label>
		<input name="password_confirm" type="password" value="" /><br />
		<div class="hint"><b>Hint:</b> To let the user select a password, leave the password field blank</div>
		
		<input type="submit" id="submit" name="submit" value="Add user" class="button" /><br />
	</form>
</div>