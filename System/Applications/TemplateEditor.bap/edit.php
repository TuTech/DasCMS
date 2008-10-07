<?php
/************************************************
* Bambus CMS 
* Created:     23. Sep 08
* License:     GNU GPL Version 2 or later (http://www.gnu.org/copyleft/gpl.html)
* Copyright:   Lutz Selke/TuTech Innovation GmbH 
* Description: template editor interface
************************************************/

echo LGui::beginForm(array(), 'documentform');
try{
	//echo new WSidebar(null);
}
catch(Exception $e){
	//echo $e->getTraceAsString();
}	

$AppController = BAppController::getControllerForID('org.bambuscms.applications.templateeditor');
echo new WOpenDialog($AppController, $File);

//editing allowed?
if($File != null)
{
    echo new WIntroduction($File,null,'mimetype-template');
		
	if(PAuthorisation::has('org.bambuscms.layout.template.create') && PAuthorisation::has('org.bambuscms.layout.template.delete'))
	{
		printf('<input type="hidden" id="filename" size="30" name="filename" value="%s"/>', htmlentities(substr($File,0,-4)));
	}
	echo LGui::beginEditorWrapper();
	echo LGui::editorTextarea(mb_convert_encoding(DFileSystem::Load(SPath::TEMPLATES.$File), 'utf-8','utf-8,iso-8859-1,auto'));
	echo LGui::endEditorWrapper();
	echo new WScript('org.bambuscms.wcodeeditor.run(document.getElementById("editorianid"));');
}
echo LGui::endForm();
?>
