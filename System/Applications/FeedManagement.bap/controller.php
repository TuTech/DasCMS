<?php
/************************************************
* Bambus CMS 
* Created:     21. Sep 07
* License:     GNU GPL Version 2 or later (http://www.gnu.org/copyleft/gpl.html)
* Copyright:   Lutz Selke/TuTech Innovation GmbH 
* Description: 
************************************************/
if(!class_exists("Bambus"))die('No login? No bambus for you, hungry Panda!');

$FeedManager = MFeedManager::alloc()->init();
$SideBar = '';
function getData($key, $GPCarray)
{
	$data = isset($GPCarray[$key]) ? $GPCarray[$key] : '';
	return (get_magic_quotes_gpc()) ? stripslashes($data) : $data;
}

if(!empty($get['_action']))
{
	$prompt = getData('_action', $_GET);
	if(getData('_action', $_GET) == 'create' && !empty($prompt))
	{
		$Feed = $FeedManager->Create($prompt);
		$Feed->Save();
	}
}
$savePage = false;
if($FeedManager->Exists(getData('edit', $_GET)))
{
	$channel = $FeedManager->Open(getData('edit', $_GET));
	
	//title
	$fname = getData('fileNameInput', $_POST);
	if(!empty($fname))
	{
		$channel->Title = $fname;
		$savePage =  true;
	}

	if(isset($post['type']))
	{
		list($mode, $type) = explode('-', getData('type', $_POST));
		$channel->FilterType = $type;
		$channel->Filter = ($mode == 'predef') ? getData('filter', $_POST) : null;
		$savePage =  true;
	}
	
	if(isset($post['itemsperpage']))
	{
		$channel->ItemsPerPage = getData('itemsperpage', $_POST);
		$savePage =  true;
	}
	
	if(isset($post['overview']))
	{
		switch (getData('overview', $_POST))
		{
			case 't':
				$channel->OverViewMode = CFeed::TITLE;
				break;
			case 'ts':
				$channel->OverViewMode = CFeed::TITLE_AND_SUMMARY;
				break;
			case 'tc':
				$channel->OverViewMode = CFeed::TITLE_AND_CONTENT;
				break;
			default:
				$channel->OverViewMode = getData('overview_template', $_POST);
		}
		$savePage =  true;
	}
	
	if(isset($post['detailview']))
	{
		switch (getData('detailview', $_POST))
		{
			case 'd':
				$channel->DetailViewMode = CFeed::DISABLED;
				break;
			case 'tc':
				$channel->DetailViewMode = CFeed::TITLE_AND_CONTENT;
				break;
			case 'c':
				$channel->DetailViewMode = CFeed::CONTENT;
				break;
			default:
				$channel->DetailViewMode = getData('detailview_template', $_POST);
		}
		$savePage =  true;
	}
	//side bar
	////////////////////	
	try
	{
		$SideBar = new WSidebar($channel);
		$savePage = $savePage;// || $channel->MetaUpdated;//true;//
	}
	catch (Exception $e)
	{
		echo "<pre style=\"background:#a40000;position:absolute;top:200px;left:200px;z-index:10000\">".$e->getTraceAsString()."</pre>";
	}
	////////////////////

}
if(BAMBUS_GRP_EDIT)
{
	printf('<form method="post" id="documentform" name="documentform" action="%s">', 
		$Bambus->Linker->createQueryString());
}

echo $SideBar;


if($savePage)
{
	$channel->Save();
}
//open file dialog
if($FeedManager->Items > 0)
{
    $channels = $FeedManager->Index;
    asort($channels, SORT_LOCALE_STRING);
	$channelIds = array_keys($channels);
	echo "\n<div id=\"OFD_Definition\">\n" .
			"<span id=\"OFD_Categories\">\n" .
				"<span>".SLocalization::get('news_channel')."</span>\n" .
			"</span>\n" .
			"<span id=\"OFD_Items\">";

	//openFileDialog files
    foreach($channels as $item => $name)
	{
		printf(
			'<a href="%s">' ."\n\t".
				'<span title="title">%s</span>' ."\n\t".
				'<span title="icon">%s</span>' ."\n\t".
				'<span title="description">%s</span>' ."\n\t".
				'<span title="category">%s</span>' ."\n".
			"</a>\n"
			,$Bambus->Linker->createQueryString(array('edit' => $item))
			,htmlspecialchars($name)
			,$Bambus->Gui->iconPath('news-channel', '', 'mimetype','medium')
			,($item === 0) ? SLocalization::get('provides_all_published_objects') : ' '
			,SLocalization::get('news_channel')
		);
	}
	echo "</span>\n</div>\n";
}
if($channel != null)
{
	$EditingObject = $channel->Title.'.feed';
}

?>
<script language="JavaScript" type="text/javascript">
	var OBJ_ofd;
	OBJ_ofd = new CLASS_OpenFileDialog();
	OBJ_ofd.self = 'OBJ_ofd';
	OBJ_ofd.openIcon = '<?php echo $Bambus->Gui->iconPath('open', 'open', 'action', 'small'); ?>';
	OBJ_ofd.openTranslation = '<?php SLocalization::out('open'); ?>';
	OBJ_ofd.closeIcon = '<?php echo $Bambus->Gui->iconPath('delete', 'delete', 'action', 'small'); ?>';
	OBJ_ofd.statusText = '';
	OBJ_ofd.statusAnimation = '<?php echo $Bambus->Gui->iconPath('loading', 'loading', 'animation', 'extra-small'); ?>';
</script>