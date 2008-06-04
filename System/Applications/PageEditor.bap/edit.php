<?php
/************************************************
* Bambus CMS 
* Created:     11. Okt 06
* License:     GNU GPL Version 2 or later (http://www.gnu.org/copyleft/gpl.html)
* Copyright:   Lutz Selke/TuTech Innovation GmbH 
* Description: 
************************************************/
if(!class_exists("Bambus"))die('No login? No bambus for you, hungry Panda!');

if($currentPage != NULL)
{
	if($Bambus->Pages->Count > 0)
	{
		if(BAMBUS_GRP_EDIT && (BAMBUS_GRP_PHP || $currentPage->Type != 'PHP'))
		{
			printf(
				'<form method="post" id="documentform" name="documentform" action="%s">'
				,$Bambus->Linker->createQueryString(array('edit' => $currentPage->Id))
			);
		}
		
echo $SideBar;		
		
		?>
		<div id="objectInspectorActiveFullBox">
	
	<?php
	
		//editing allowed?
		if(BAMBUS_GRP_EDIT && (BAMBUS_GRP_PHP || $currentPage->Type != 'PHP'))
		{
			//////////////////////////////
			//begin of formating toolbar//
			//////////////////////////////
			
			printf(
				'<input id="fileNameInput" type="hidden" class="textinput" size="30" name="filename" value="%s"/>'
				,($currentPage->Title)
			);
			$WYSIWYGStatus = $Bambus->UsersAndGroups->getMyPreference('WYSIWYGStatus');
			printf(
				"<input type=\"hidden\" id=\"WYSIWYGStatus\" name=\"WYSIWYGStatus\" value=\"%s\" />"
				,(empty($WYSIWYGStatus) || $WYSIWYGStatus == 'on')
					? 'on'
					: 'off'
			);
			////////////////////////
			//standard text editor//
			////////////////////////
		
			
			//end of formating toolbar//
					
			echo $Bambus->Gui->beginEditorWrapper();
		
			//editor (WYSIWYG part)//
			
			if($currentPage->Type == 'HTML')
			{
				printf(
					'<iframe id="wysiwygeditor" class="hiddenEditor" src="Management/WYSIWYGiFrame.php?doc=%s"></iframe>'
					, $currentPage->Id
				);
			} 
			
			//editor (standard part)//
			
			echo $Bambus->Gui->editorTextarea($currentPage->Content);
			echo $Bambus->Gui->endEditorWrapper();
			echo $Bambus->Gui->beginScript();
			echo 'initeditor();';
			echo $Bambus->Gui->endScript();
		}
		echo '</div>';
	}
}
else
{
	echo $Bambus->Gui->script('BCMSRunFX[BCMSRunFX.length] = function(){OBJ_ofd.show()};');
}
	
?>