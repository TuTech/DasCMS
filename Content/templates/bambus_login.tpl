<form method="post" action="{bambus_my_uri}">
	<table border="0" cellspacing="2" id="logintable">
		<tr>
			<td colspan="2" class="loginmesssage">{bambus_cms_message}</td>
		</tr>
		<tr>
			<td colspan="2" class="logininfo">{translate:please_log_in}</td>
		</tr>
		<tr>
			<td>{translate:user}:</td>
			<td><input type="text" class="textinput" name="bambus_cms_username" /></td>
		</tr>
		<tr>
			<td>{translate:password}:</td>
			<td><input type="password" class="textinput" name="bambus_cms_password" /></td>
		</tr>
	</table>
	<input type="hidden" name="bambus_cms_login" value="yes" />
	<input type="submit" class="submitinput" value="{translate:login}" />
</form>
