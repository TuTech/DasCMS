<?php
/************************************************
* Bambus CMS 
* Created:     11. Okt 06
* License:     GNU GPL Version 2 or later (http://www.gnu.org/copyleft/gpl.html)
* Copyright:   Lutz Selke/TuTech Innovation GmbH 
* Description: 
************************************************/
if(!class_exists("Bambus"))die('No login? No bambus for you, hungry Panda!');
$Bambus->using('Pages');

$allowEdit = true;
$manager = false;

$currentId = 0;
$currentPage = NULL;

//list all available documents
if($Bambus->Pages->Count > 0)
{
	if(!empty($get['edit']) && $Bambus->Pages->exists($get['edit']))
	{
		$currentPage = $Bambus->Pages->open($get['edit']);
		$currentId = $get['edit'];
	}
	elseif(empty($get['tab']) || $get['tab'] != 'migrate')
	{
		$allowEdit = false;
	}
	else
	{
		$manager = true;
	}
}

//save wysiwyg status in user profile
if(isset($post['WYSIWYGStatus']))
{
	$WYSIWYGStatus = ($post['WYSIWYGStatus'] == 'off') ? 'off' : 'on';
	$Bambus->UsersAndGroups->setMyPreference('WYSIWYGStatus', $WYSIWYGStatus);
}

//create
if(BAMBUS_GRP_CREATE && !empty($get['_action']) && $get['_action'] == 'create')
{
	$page = $Bambus->Pages->create(
		 $Bambus->Translation->new_document.' - '.date($Bambus->Configuration->dateformat)	//title
		,$Bambus->Translation->new_document						//content
	);
	$idOfPage = $Bambus->Pages->save($page);
	if($idOfPage != false)
	{
		$currentId = $idOfPage;
		$currentPage = $Bambus->Pages->open($currentId);
		$Bambus->Linker->set('get', 'edit', $idOfPage);
	}
	$allowEdit = false;
}

//changing allowed?
if($currentPage != NULL && (BAMBUS_GRP_PHP || ($currentPage->Type != 'PHP'))  && $allowEdit && !$manager)
{
	$savePage = false;

	//change content or meta//
	if(BAMBUS_GRP_EDIT)
	{
		//content
		if(isset($post['content']))
		{
	       $raw = (get_magic_quotes_gpc())? stripslashes($_POST['content']) : $_POST['content'];
	       if($currentPage->Content != $raw)
	       {
	      	 	$currentPage->Content = $raw;
	      	 	$savePage = true;
	       }
	   	}
	   	
	   	//page type
	   	$meta_types = (BAMBUS_GRP_PHP) ? array('HTML','PHP') : array('HTML');
		if(isset($post['metatype']) && $post['metatype'] != $currentPage->Type && in_array($post['metatype'], $meta_types))
		{
			$savePage = true;
			$currentPage->Type = $post['metatype'];
		}
		
		//html meta keywords
		if(isset($post['meta_keys']) && $post['meta_keys'] != $currentPage->metakeys)
		{
			$savePage = true;
			$currentPage->metakeys = preg_replace("/[\\s\\n\\r\\t]+/", ' ', $post['meta_keys']);
		}
		
		//title image
		if(isset($post['title_image']) && $post['title_image'] != $currentPage->title_image)
		{
			$savePage = true;
			$currentPage->title_image = $post['title_image'];
		}
		
		//title image
		if(isset($post['enclosure']) && $post['enclosure'] != $currentPage->enclosure)
		{
			$savePage = true;
			$currentPage->enclosure = $post['enclosure'];
		}
		
		//time it will be in the feed/news
		if(isset($post["publish_date"]))
		{
			$pubDate = '';
			if(!empty($post['publish_on']) 
				&& !empty($post["publish_date"]) 
				&& (($date = strtotime($post["publish_date"])) !== -1))
			{
				$pubDate = $date;
			}
			if($currentPage->publish != $pubDate)
			{
				$currentPage->publish = $pubDate;
				$savePage = true;
			}
		}		
		//time it will be removed from feed/news
		if(isset($post["expires_date"]))
		{
			$expDate = '';
			if(!empty($post['expires_on']) 
				&& !empty($post["expires_date"]) 
				&& (($date = strtotime($post["expires_date"])) !== -1))
			{
				$expDate = $date;
			}
			if($currentPage->expire != $expDate)
			{
				$currentPage->expire = $expDate;
				$savePage = true;
			}
		}
	}
	
	
	//rename//
	if(BAMBUS_GRP_RENAME && !empty($_POST['filename']) && $currentPage->Title != $_POST['filename'])
    {
		$savePage = true;
        $currentPage->Title = (get_magic_quotes_gpc())? stripslashes($_POST['filename']) : $_POST['filename'];
    }
    //changes happend
	if($savePage)
	{
		$Bambus->Pages->save($currentPage);
	}
}

//delete
//--old
if(BAMBUS_GRP_DELETE)
{
	
	if(!empty($get['_action']) && $get['_action'] == 'delete' && !$manager && $Bambus->Pages->Count > 0 && $currentPage != NULL)
	{
		//kill it
	    $Bambus->Pages->delete($currentPage);
	    $currentId = 0;
		$currentPage = NULL;
	}
	
	if(isset($post['action']) && $post['action'] == 'delete' && $manager && $Bambus->Pages->Count > 0)
	{
		$documentIDs = $Bambus->Pages->Ids;
		$index = $Bambus->Pages->Index;
		foreach($documentIDs as $id)
		{
			if(isset($post['select_'.$id]) && $Bambus->Pages->exists($id))
			{
				$page = $Bambus->Pages->open($id);
				$page->Type = 'HTML';
				$Bambus->Pages->save($page);
				$Bambus->Pages->deleteId($id);
				if($currentId == $id)
				{
					$currentId = 0;
					$currentPage = NULL;
				}
			}
		}
	}
}
if($currentPage != NULL)
{
	$EditingObject = $currentPage->Title_ISO;
	$EditingObject = (!empty($EditingObject)) ? $EditingObject.'.'.strtolower($currentPage->Type) : '';
	////////////////////	
	try
	{
		$SideBar = new WSidebar($currentPage);
		$savePage = $currentPage->MetaUpdated;//true;//
	}
	catch (Exception $e)
	{
		echo "<pre>".$e->getTraceAsString()."</pre>";
	}
	////////////////////
}
if($Bambus->Pages->Count > 0)
{
    $documents = $Bambus->Pages->Index;
    asort($documents, SORT_STRING);
	$documentIds = array_keys($documents);
	echo "\n<div id=\"OFD_Definition\">\n" .
			"<span id=\"OFD_Categories\">\n" .
				"<span>HTML</span>\n" .
				"<span>PHP</span>\n" .
			"</span>\n" .
			"<span id=\"OFD_Items\">";

	//openFileDialog files
    foreach($documents as $item => $name)
	{
		$object = $Bambus->Pages->open($item, true);
		if($object instanceof Pages_Page)
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
				,$Bambus->Gui->iconPath(
					(strtolower($object->Type) == 'php') 
						? 'application-php' 
						: ((strtolower($object->Type) == 'html') 
							? 'document' 
							: 'text')
					, '', 'mimetype','medium')
				,' '.$object->Creator
				,$object->Type
			);
		}
	}
	echo "</span>\n</div>\n";
}
if(empty($get['tab']) || $get['tab'] != 'manage_documents')
{
?>
<script language="JavaScript" type="text/javascript">
	var OBJ_ofd;
	OBJ_ofd = new CLASS_OpenFileDialog();
	OBJ_ofd.self = 'OBJ_ofd';
	OBJ_ofd.openIcon = '<?php echo $Bambus->Gui->iconPath('open', 'open', 'action', 'small'); ?>';
	OBJ_ofd.openTranslation = '<?php echo utf8_encode(html_entity_decode($Bambus->Translation->open)); ?>';
	OBJ_ofd.closeIcon = '<?php echo $Bambus->Gui->iconPath('delete', 'delete', 'action', 'small'); ?>';
	OBJ_ofd.statusText = '';
	OBJ_ofd.statusAnimation = '<?php echo $Bambus->Gui->iconPath('loading', 'loading', 'animation', 'extra-small'); ?>';
</script>
<?php
}
?>
