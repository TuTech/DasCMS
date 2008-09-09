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

if(RURL::get('_action') == 'create')
{
	$Feed = $FeedManager->Create(SLocalization::get('new_feed'));
	$Feed->Save();
}
$savePage = false;
if($FeedManager->Exists(RURL::get('edit')))
{
	$channel = $FeedManager->Open(RURL::get('edit'));
	
	//title
	$fname = RSent::get('fileNameInput');
	if(!empty($fname))
	{
		$channel->Title = $fname;
		$savePage =  true;
	}

	if(RSent::hasValue('type'))
	{
		list($mode, $type) = explode('-', RSent::get('type'));
		$channel->FilterType = $type;
		$channel->Filter = ($mode == 'predef') ? RSent::get('filter') : null;
		$savePage =  true;
	}
	
	if(RSent::hasValue('itemsperpage'))
	{
		$channel->ItemsPerPage = RSent::get('itemsperpage');
		$savePage =  true;
	}
	
	if(RSent::hasValue('overview'))
	{
		switch (RSent::get('overview'))
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
				$channel->OverViewMode = RSent::get('overview_template');
		}
		$savePage =  true;
	}
	
	if(RSent::has('detailview') )
	{
		switch (RSent::get('detailview'))
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
				$channel->DetailViewMode = RSent::get('detailview_template');
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
	printf('<form method="post" id="documentform" name="documentform" action="%s">', SLink::link());
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
			,SLink::link(array('edit' => $item))
			,htmlspecialchars($name)
			,WIcon::pathFor('news-channel', 'mimetype',WIcon::MEDIUM)
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
	OBJ_ofd.openIcon = '<?php echo WIcon::pathFor('open'); ?>';
	OBJ_ofd.openTranslation = '<?php SLocalization::out('open'); ?>';
	OBJ_ofd.closeIcon = '<?php echo WIcon::pathFor('delete'); ?>';
	OBJ_ofd.statusText = '';
	OBJ_ofd.statusAnimation = '<?php echo  WIcon::pathFor('loading', 'animation', WIcon::EXTRA_SMALL);  ?>';
</script>