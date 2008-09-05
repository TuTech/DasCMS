<?php
/************************************************
* Bambus CMS 
* Created:     11. Okt 06
* License:     GNU GPL Version 2 or later (http://www.gnu.org/copyleft/gpl.html)
* Copyright:   Lutz Selke/TuTech Innovation GmbH 
* Description: css controller
************************************************/
if(!class_exists("Bambus"))die('No login? No bambus for you, hungry Panda!');
$allowEdit = true;

$FileOpened = false;
$mp = MPageManager::alloc()->init();

$editExist = (RURL::has('edit')) && $mp->Exists(RURL::get('edit'));

//delete
if($editExist && RSent::get('delete') != '' && BAMBUS_GRP_DELETE)
{
	$mp->Delete(RURL::get('edit'));
	$editExist = false;
}
if(RSent::get('action') == 'delete' && BAMBUS_GRP_DELETE)
{
	foreach (RSent::data() as $k => $v) 
	{
		if(substr($k,0,7) == 'select_' && !empty($v))
		{
			//delete
			$mp->Delete(substr($k,7));
		}
	}
}

//create
elseif(RSent::hasValue('create') && BAMBUS_GRP_CREATE)
{
	$Title = RSent::get('create');
	$Page = $mp->Create($Title);
	$Page->Content = $Title;
}

//open for editing
elseif($editExist && BAMBUS_GRP_EDIT)
{
	$Page = $mp->Open(RURL::get('edit'));
}

//save data
if(isset($Page) && $Page instanceof CPage && BAMBUS_GRP_EDIT)
{
	if(RSent::has('content'))
	{
		$Page->Content = RSent::get('content');
	}
	if(RSent::has('filename'))
	{
		$Page->Title = RSent::get('filename');
	}
	
}




echo "\n<div id=\"OFD_Definition\">\n" .
	"<span id=\"OFD_Categories\">\n" .
		"<span>TPL</span>\n" .
	"</span>\n" .
	"<span id=\"OFD_Items\">";
$Files = $mp->Index;
asort($Files, SORT_STRING);
foreach($Files as $id => $name)
{
	printf(
		'<a href="%s">' ."\n\t".
			'<span title="title">%s</span>' ."\n\t".
			'<span title="icon">%s</span>' ."\n\t".
			'<span title="description">%s</span>' ."\n\t".
			'<span title="category">TPL</span>' ."\n".
		"</a>\n"
		,SLink::link(array('edit' => $id))
		,htmlentities($name, ENT_QUOTES, 'utf-8')
		,WIcon::pathFor('website', 'mimetype',WIcon::MEDIUM)
		,' '
	);
}
echo "</span>\n</div>\n";

echo '<form method="post" id="documentform" name="documentform" action="'
	,SLink::link(
		array(
			'edit' => (isset($Page) && $Page instanceof CPage)? $Page->Id :''
			)
		)
	,'">';

if(BAMBUS_APPLICATION_TAB != 'manage' && isset($Page))
{
	try{
		echo new WSidebar($Page);
		if($Page instanceof CPage)
		{
			//FIXME - do not do allways
			$Page->Save();
		}
	}
	catch(Exception $e){
		echo $e->getTraceAsString();
	}	
	
}
?>
<script language="JavaScript" type="text/javascript">
	var OBJ_ofd;
	OBJ_ofd = new CLASS_OpenFileDialog();
	OBJ_ofd.self = 'OBJ_ofd';
	OBJ_ofd.openIcon = '<?php echo WIcon::pathFor('open'); ?>';
	OBJ_ofd.openTranslation = '<?php SLocalization::out('open'); ?>';
	OBJ_ofd.closeIcon = '<?php echo WIcon::pathFor('delete'); ?>';
	OBJ_ofd.statusText = '';
	OBJ_ofd.statusAnimation = '<?php echo  WIcon::pathFor('loading', 'animation', WIcon::EXTRA_SMALL); ?>';
</script>