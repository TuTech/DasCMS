<?php
/************************************************
* Bambus CMS 
* Created:     16. Okt 06
* License:     GNU GPL Version 2 or later (http://www.gnu.org/copyleft/gpl.html)
* Copyright:   Lutz Selke/TuTech Innovation GmbH 
* Description: 
************************************************/
if(!class_exists("Bambus"))die('No login? No bambus for you, hungry Panda!');
print('<form method="post" id="documentform" name="documentform" action="Management/?editor=Frinkenstein.bap&tab=migrate">');
$frame->render();	
echo '</form>';
?>
