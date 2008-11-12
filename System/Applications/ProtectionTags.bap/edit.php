<?php
/************************************************
* Bambus CMS 
* Created:     11. Okt 06
* License:     GNU GPL Version 2 or later (http://www.gnu.org/copyleft/gpl.html)
* Copyright:   Lutz Selke/TuTech Innovation GmbH 
* Description: css editor interface
************************************************/
//document title
	
if(PAuthorisation::has('org.bambuscms.system.permissions.tags.change'))
{
    $tags =  implode(', ', STagPermissions::getProtectedTags());
    printf(
        '<form method="post" id="documentform" name="documentform" action="%s">'
    	,SLink::link(array())
    );
    echo LGui::beginEditorWrapper();
    echo new WIntroduction('define_restriction_tags', 'tags_listed_here_restrict_access_to_contents');
	echo LGui::editorTextarea($tags);
	echo LGui::endEditorWrapper();
	echo LGui::endForm();
}

?>