<?php 
$users = WoocommerceAPI::getUsersList();
?>
<table class="wp-list-table widefat fixed striped table-view-list users">
	<thead>
		<tr>
	
			<td class="manage-column column-username column-primary sortable desc"><span>Username</span></td>
			<th class="manage-column">Name</th>
			<th class="manage-column"><span>Email</span></th>
			<th class="manage-column">Registered</th>
		</tr>
	</thead>

	<tbody>
		<?php
		if(sizeof($users)>0)
		{ 
			foreach ($users as $key => $user) 
			{
			?>
				<tr>
					<td class="manage-column"><?php echo $user->user_login;?></td>
					<td class="manage-column"><?php echo $user->display_name;?></td>
					<td class="manage-column"><?php echo $user->user_email;?></td>
					<td class="manage-column"><?php echo $user->user_registered;?></td>
				</tr>
			<?php
			}
		}
		?>	
	</tbody>
</table>