<div class="editortoolbar">
	<form method="post" action="{bambus_my_uri}" onsubmit="document.getElementById('waitani').style.display = 'block';">
		<div class="text">{translate:username}:</div>
		<input type="text" class="textinput" name="bambus_cms_username" />
		<div class="text">{translate:password}:</div>
		<input type="password" class="textinput" name="bambus_cms_password" />
		<input type="hidden" name="bambus_cms_login" value="yes" />
		<img src="System/Icons/16x16/animations/loading.gif" style="float:right;padding:10px;display:none;" id="waitani" alt="" />
		<input type="submit" class="submitinput" onclick="this.style.display = 'none';" value="{translate:login}" />
		<br class="clear" />
	</form>
</div>
