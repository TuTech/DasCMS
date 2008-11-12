<?php
/************************************************
* Bambus CMS 
* Created:     11. Okt 06
* License:     GNU GPL Version 2 or later (http://www.gnu.org/copyleft/gpl.html)
* Copyright:   Lutz Selke/TuTech Innovation GmbH 
* Description: css controller
************************************************/
if(RSent::has('content') && PAuthorisation::has('org.bambuscms.system.permissions.tags.change'))
{
    $tags = STag::parseTagStr(RSent::get('content'));
    STagPermissions::setProtectedTags($tags);
}
?>