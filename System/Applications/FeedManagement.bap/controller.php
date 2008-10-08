<?php
/************************************************
* Bambus CMS 
* Created:     21. Sep 07
* License:     GNU GPL Version 2 or later (http://www.gnu.org/copyleft/gpl.html)
* Copyright:   Lutz Selke/TuTech Innovation GmbH 
* Description: 
************************************************/

$FeedManager = MFeedManager::alloc()->init();
$SideBar = '';

if(RURL::get('_action') == 'create')
{
	$Feed = $FeedManager->Create(SLocalization::get('new_feed'));
	$Feed->Save();
}
$channel = null;
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
if(PAuthorisation::has('org.bambuscms.content.cfeed.change'))
{
	printf('<form method="post" id="documentform" name="documentform" action="%s">', SLink::link());
}

echo $SideBar;


if($savePage)
{
	$channel->Save();
}
$AppController = BAppController::getControllerForID('org.bambuscms.applications.feedmanager');
echo new WOpenDialog($AppController, $channel);

?>