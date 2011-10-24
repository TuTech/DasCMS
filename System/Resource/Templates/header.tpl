{Header}
	<body>
		{View_UIElement_Applications}
		<form method="post" id="documentform" name="documentform" action="{DocumentFormAction}">
			<input type="hidden" name="_action" value="save" id="document-form-action">
			{SideBar}
			{ControllerData}
			{OpenDialog}
			<div id="document">
				{TaskBar}
				<div class="page-margin">