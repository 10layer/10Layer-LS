	<div id="users-box" class="boxed wide">
		<div id="users-list" class="list">
			<div class="title-link">Users</div>
				<table>
					<tr>
						<th>Name</th>
						<th>Email</th>
						<th>Roles</th>
						<th>Permissions</th>
					</tr>
				<?php
					$odd="odd";
					foreach($users as $user) {
				?>
					<tr class="<?= $odd ?>">
						<td><?= anchor("manage/users/edit/".$user->urlid,$user->name) ?></td>
						<td><?= $user->email ?></td>
						<td><?php
							$roles=$this->model_user->getUserRoles($user->id);
							$rolesarr=array();
							foreach($roles as $role) {
								$rolesarr[]=$role->name;
							}
							print(implode(", ",$rolesarr));
						?></td>
						<td><?php
							$permissions=$this->model_user->getUserPermissions($user->id);
							$perms=array();
							foreach($permissions as $permission) {
								$perms[]=$permission->name;
							}
							print(implode(", ",$perms));
						?></td>
						
					</tr>
				<?php
						if (!empty($odd)) {
							$odd="";
						} else {
							$odd="odd";
						}
					}
				?>
				</table>
			</div>
			<div class="button" style="height: 30px">
				<?= anchor("/manage/users/add","Add User") ?>
			</div>
		</div>
		<br clear="both" />
	</div>
</div>