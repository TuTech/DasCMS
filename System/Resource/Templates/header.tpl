{Header}
	<body>
		<script type="text/javascript">org.bambuscms.app.controller = "{AppGUID}";</script>
		<div id="BambusHeader">
			{WApplications}
			<div id="BambusRightInfo">
			    <div id="BambusLogout"><a href="Management/?logout">{logout_text}</a></div>
			    <div id="BambusVersionInfo">{bcms_version}</div>
			</div>
			{TaskBar}
		</div>
		<form method="post" id="documentform" name="documentform" action="{DocumentFormAction}">
			{ControllerData}
			{SideBar}
			<div id="BambusContentArea">
				<div id="BambusApplication">
					<div id="objectInspectorActiveFullBox">