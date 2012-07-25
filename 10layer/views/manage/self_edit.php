<?php
	link_js("/tlresources/file/js/forms.js");
//	link_js("/tlresources/file/js/messaging.js");
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
		
		<label>Password</label>
		<input type="password" name="password" value=""><br />
		<label>Confirm Password</label>
		<input type="password" name="password_check" value=""><br /><br />
		
		<input type="submit" id="submit" name="submit" value="Update"  class="button" /><br />
	</form>
</div>