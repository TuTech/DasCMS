{Header}
	<body>
		{View_UIElement_Applications}
		<form method="post" id="documentform" name="documentform" action="{DocumentFormAction}">
			<input type="hidden" name="_action" value="save" id="document-form-action">
			{ControllerData}
			{OpenDialog}
			<div id="document">
				<div id="document-front" class="document-side">
					<div class="document-flip"></div>
					{TaskBar}
					<div class="page-margin">
