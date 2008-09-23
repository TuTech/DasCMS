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

$OFD = new WOpenFileDialog();
$OFD->registerCategory('template');
$tplFiles = DFileSystem::FilesOf(SPath::TEMPLATES, '/\.tpl/i');
foreach($tplFiles as $item)
{
    $OFD->addItem(
        'template',
        $item,
        SLink::link(array('edit' => $item,'tab' => 'edit_templates')),
        'template', 
        DFileSystem::formatSize(filesize(SPath::TEMPLATES.$item))
    );
}
$OFD->render();

//editing allowed?
if($File != null)
{
    echo new WIntroduction($File,null,'mimetype-template');
		
	if(PAuthorisation::has('org.bambus-cms.layout.template.create') && PAuthorisation::has('org.bambus-cms.layout.template.delete'))
	{
		printf('<input type="hidden" id="filename" size="30" name="filename" value="%s"/>', htmlentities(substr($File,0,-4)));
	}
	echo LGui::beginEditorWrapper();
	echo LGui::editorTextarea(mb_convert_encoding(DFileSystem::Load(SPath::TEMPLATES.$File), 'utf-8','utf-8,iso-8859-1,auto'));
	echo LGui::endEditorWrapper();
	echo new WScript('org.bambuscms.wcodeeditor.run(document.getElementById("editorianid"));');
	echo new WScript('
(function(){
	var h = -190;
    if($("editorianid").offsetTop)
    {
		h = function(){return ($("editorianid").offsetTop+5)*-1;};
	}
    org.bambuscms.display.setAutosize("editorianid",0,h);
})();


');
}
else
{
	echo new WScript('org.bambuscms.autorun.register(function(){OBJ_ofd.show()});');
}
echo LGui::endForm();
?>
