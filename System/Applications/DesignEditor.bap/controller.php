<?php
/************************************************
* Bambus CMS 
* Created:     11. Okt 06
* License:     GNU GPL Version 2 or later (http://www.gnu.org/copyleft/gpl.html)
* Copyright:   Lutz Selke/TuTech Innovation GmbH 
* Description: css controller
************************************************/
$allowEdit = true;
$Files = DFileSystem::FilesOf(SPath::DESIGN, '/\.css/i');
$FileOpened = false;

//////////
//create//
//////////
	
if(PAuthorisation::has('org.bambuscms.layout.stylesheet.create') && RURL::get('_action') == 'create')
{
	$i = 0;
	while(file_exists(SPath::DESIGN.SLocalization::get('new').'-'.++$i.'.css'))
		;
	$File = SLocalization::get('new').'-'.$i.'.css';
	$fileContent = '/* '.SLocalization::get('new_css_file').' */';
	DFileSystem::Save(SPath::DESIGN.$File, $fileContent);
	$FileName = SLocalization::get('new').'-'.$i;
	$allowEdit = false;
	$FileOpened = true;
}

if(count($Files) > 0)
{
	if($allowEdit && RURL::hasValue('edit') && in_array(RURL::get('edit'), $Files))
	{
		$File = RURL::get('edit');
		$FileName = ($File == 'default.css') ? SLocalization::get('default.css') : htmlentities(substr($File, 0, -4));
		$allowEdit = true;
		$fileContent = DFileSystem::Load(SPath::DESIGN.$File);
		$FileOpened = true;
	}
	
	////////
	//save//
	////////
	
	if(PAuthorisation::has('org.bambuscms.layout.stylesheet.change') && $allowEdit && $FileOpened)
	{
		//content changed?
		if(RSent::has('content'))
		{
		   	if(RSent::get('content','utf-8') != $fileContent)
		   	{
		        //do the save operation
		        if(DFileSystem::Save(SPath::DESIGN.$File, RSent::get('content', 'utf-8')))
		        {
		        	SNotificationCenter::report('message', '.file_saved');
		        	$fileContent = RSent::get('content', 'utf-8');
		        }
		        else
		        {
		        	SNotificationCenter::report('alert', 'saving_failed');
		        }
		   	}
		}
	}
	
	//////////
	//delete//
	//////////
	
	if(PAuthorisation::has('org.bambuscms.layout.stylesheet.delete') && RURL::get('_action') == 'delete' && $File != 'default.css' && $allowEdit)
	{
		//kill it
		unlink(SPath::DESIGN.$File);
	    SNotificationCenter::report('message', 'file_deleted');
		$FileOpened = false;
		$File = null;
	}
	elseif(PAuthorisation::has('org.bambuscms.layout.stylesheet.delete') && RURL::get('_action') == 'delete' && $File == 'default.css')
	{
		SNotificationCenter::report('warning', 'this_file_cannott_be_deleted');
	}
	
	//////////
	//rename//
	//////////
	
	if(PAuthorisation::has('org.bambuscms.layout.stylesheet.create') && PAuthorisation::has('org.bambuscms.layout.stylesheet.delete') && $allowEdit && $FileOpened)
	{
	    if(RSent::hasValue('filename') && $FileName != RSent::get('filename')&& '' != RSent::get('filename') && $FileName != 'default.css' && file_exists(SPath::DESIGN.$File))
	    {
			rename(SPath::DESIGN.$File, SPath::DESIGN.basename(RSent::get('filename')).'.css');
			$FileName = basename(RSent::get('filename'));
			$File = basename(RSent::get('filename')).'.css';
	        SNotificationCenter::report('message', 'file_renamed');
	    }
	}
}	

echo '<form method="post" id="documentform" name="documentform" action="'
	,SLink::link(array('edit' => isset($File) ? $File : ''))
	,'">';

try{
	$panel = new WSidePanel();
	$panel->setMode(
	    WSidePanel::MEDIA_LOOKUP|
	    WSidePanel::HELPER);
	echo $panel;
}
catch(Exception $e){
	echo $e->getTraceAsString();
	
}	
$AppController = BAppController::getControllerForID('org.bambuscms.applications.stylesheeteditor');
echo new WOpenDialog($AppController, isset($File) ? $File : '');

?>