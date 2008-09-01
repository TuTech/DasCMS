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
function getData($key, $GPCarray)
{
	$data = isset($GPCarray[$key]) ? $GPCarray[$key] : '';
	return (get_magic_quotes_gpc()) ? stripslashes($data) : $data;
}

$editExist = (isset($_GET['edit']) && $mp->Exists($_GET['edit']));

//delete
if($editExist && !empty($_POST['delete']) && BAMBUS_GRP_DELETE)
{
	$mp->Delete($_GET['edit']);
	$editExist = false;
}
if(!empty($_POST['action']) && $_POST['action'] == 'delete' && BAMBUS_GRP_DELETE)
{
	foreach ($_POST as $k => $v) 
	{
		if(substr($k,0,7) == 'select_' && !empty($v))
		{
			//delete
			$mp->Delete(substr($k,7));
		}
	}
}

//create
elseif(!empty($_POST['create']) && BAMBUS_GRP_CREATE)
{
	$Title = getData('create', $_POST);
	$Page = $mp->Create($Title);
	$Page->Content = $Title;
}

//open for editing
elseif($editExist && BAMBUS_GRP_EDIT)
{
	$Page = $mp->Open($_GET['edit']);
}

//save data
if(isset($Page) && $Page instanceof CPage && BAMBUS_GRP_EDIT)
{
	if(isset($_POST['content']))
	{
		$Page->Content = getData('content', $_POST);
	}
	if(isset($_POST['filename']))
	{
		$Page->Title = getData('filename', $_POST);
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
		,$Bambus->Linker->createQueryString(array('edit' => $id))
		,htmlentities($name, ENT_QUOTES, 'utf-8')
		,WIcon::pathFor('website', 'mimetype',WIcon::MEDIUM)
		,' '
	);
}
echo "</span>\n</div>\n";

echo '<form method="post" id="documentform" name="documentform" action="'
	,$Bambus->Linker->createQueryString(
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