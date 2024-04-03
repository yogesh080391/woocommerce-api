<h2>Register API User</h2>
<form id="create-user" action="" method="post" >
	<table class="form-table" role="presentation">
		<tbody>
			<tr class="user-user-login-wrap">
				<th><label for="user_login">Username <span class="description">(required)</span></label></th>
				<td><input type="text" name="user_login" id="user_login" value="" class="regular-text" > </td>
			</tr>
			<tr class="user-user-login-wrap">
				<th><label>Password <span class="description">(required)</span></label></th>
				<td><input type="password" name="password" id="password" value="" class="regular-text" > </td>
			</tr>
			<tr class="user-user-login-wrap">
				<th><label>Confirm Password <span class="description">(required)</span></label></th>
				<td><input type="password" name="confirm_password" id="confirm_password" value="" class="regular-text" > </td>
			</tr>
			<tr class="user-email-wrap">
				<th><label for="email">Email <span class="description">(required)</span></label></th>
				<td><input type="email" name="email" id="email"  value="" class="regular-text ltr" ></td>
			</tr>
			<tr class="user-first-name-wrap">
				<th><label for="first_name">First Name <span class="description">(required)</span></label></th>
				<td><input type="text" name="first_name" id="first_name" value="" class="regular-text" ></td>
			</tr>

			<tr class="user-last-name-wrap">
				<th><label for="last_name">Last Name <span class="description">(required)</span></label></th>
				<td><input type="text" name="last_name" id="last_name" value="" class="regular-text" ></td>
			</tr>
			<tr class="user-last-name-wrap">
				<th><label for="last_name">Company Name <span class="description">(required)</span></label></th>
				<td><input type="text" name="company_name" id="company_name" value="" class="regular-text" ></td>
			</tr>
	
		</tbody>
	</table>

	<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Add API User"></p>
</form>
<h2 class="confirguration-test" style="display: none;">Test Confirguration</h2>
<form id="confirguration-test" action="" method="post" class="confirguration-test" style="display: none;">
	<table class="form-table" role="presentation">
		<tbody>
			<tr class="user-user-login-wrap">
				<th><label for="user_login">Username</th>
				<td><input type="text" name="user_login"  value="" class="regular-text" readonly> </td>
			</tr>
			<tr class="user-user-login-wrap">
				<th><label for="user_login">Password</th>
				<td><input type="text" name="password" value="" class="regular-text" readonly></td>
			</tr>
		</tbody>
	</table>

	<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Test Confirguration"></p>
</form>
<?php
// $userdata = get_user_by('login', $username = 'apidev');
// $result = wp_check_password($password='#BBzO2pSffocVhLp$Ze4&ocQ', $userdata->user_pass, $userdata->ID);
// print_r($result);