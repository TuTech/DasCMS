{Header}
	<body>
		<div id="BambusHeader">
			{View_UIElement_Applications}
			<div id="BambusRightInfo">
			    <div id="BambusLogout"><a href="Management/?logout">{logout_text}</a></div>
			    <div id="BambusVersionInfo">{bcms_version}</div>
			</div>
			{TaskBar}
		</div>
		<form method="post" id="documentform" name="documentform" action="{DocumentFormAction}">
			<input type="hidden" name="_action" value="save" id="document-form-action">
			{ControllerData}
			{OpenDialog}
			{SideBar}
			<div id="BambusContentArea">
				<div id="BambusApplication">
					<div id="objectInspectorActiveFullBox">