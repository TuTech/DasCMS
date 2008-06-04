<?php
/************************************************
* Bambus CMS 
* Created:     11. Okt 06
* License:     GNU GPL Version 2 or later (http://www.gnu.org/copyleft/gpl.html)
* Copyright:   Lutz Selke/TuTech Innovation GmbH 
* Description: 
************************************************/
if(!class_exists("Bambus"))die('No login? No bambus for you, hungry Panda!');
$MPage = MPageManager::alloc()->init();
$Page = $MPage->Open('a66139353d3097e25f08136fe763de46');

$frame = new WPanel();
//$frame = new WTextEditor($Page);
$tb = new WTextBox($Page);
$te = new WTextEditor($Page);
$lbl = new WLabel($Page);
$lbl->setTarget($te);

$frame->add($te, WPanel::CENTER);
$frame->add($tb, WPanel::NORTH);
$frame->add($lbl, WPanel::NORTH);
$frame->add(new WSettings($Page), WPanel::EAST);

$frame->run();

$Page->Save();
?>
