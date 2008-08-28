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
$Files = $Bambus->FileSystem->getFiles('css', array('css'));

$FileOpened = false;
//////////
//upload//
//////////
$allowed = array('css', 'gpl', 'jpeg','jpg','png','gif','svg','mng','eps','ps','tif','tiff','psd','ai','pcx','wmf');
$succesfullUpload = false;
$uploadIsImage = false;
$File = null;
if(isset($_FILES['bambus_image_file']['name']) && BAMBUS_GRP_CREATE)
{ 
    // we have got an upload
    if(file_exists($Bambus->pathTo('design').basename($_FILES['bambus_image_file']['name'])) 
      && empty($post['bambus_overwrite_image_file']))
    {
        SNotificationCenter::alloc()->init()->report('warning', 'upload_failed_because_file_already_exists');
    }
    else
    {
        //file does not exist or we are allowed to overwrite it
        //the suffixes of nice image types are:
        
        //everything else we treat as ordinary file
        $tmp = explode('.', utf8_decode($_FILES['bambus_image_file']['name']));
        $suffix = strtolower(array_pop($tmp));
        $tmp = null;
        if(in_array($suffix, $allowed))
        {
            //we like him
            if(move_uploaded_file($_FILES['bambus_image_file']['tmp_name'], $Bambus->pathTo('design').basename(utf8_decode($_FILES['bambus_image_file']['name']))))
            {
                //i like to move it move it
                SNotificationCenter::alloc()->init()->report('message', 'file_uploaded');
                chmod($Bambus->pathTo('design').basename(utf8_decode($_FILES['bambus_image_file']['name'])), 0666);
                $succesfullUpload = basename(utf8_decode($_FILES['bambus_image_file']['name']));
                //create thumbnail image
                $image = $Bambus->pathTo('design').basename(utf8_decode($_FILES['bambus_image_file']['name']));
                $uploadIsImage = ($suffix != 'css' && $suffix != 'gpl');
            }
            else
            {
                SNotificationCenter::alloc()->init()->report('warning', 'uploded_failed');
            }
        }
        else
        {
            //run away and scream
            SNotificationCenter::alloc()->init()->report('warning', 'upload_failed_because_of_unsupported_file_type');
        }
        
    }
}

if(BAMBUS_APPLICATION_TAB == 'edit_css')
{
	//////////
	//create//
	//////////
		
	if(BAMBUS_GRP_CREATE && !empty($get['_action']) && $get['_action'] == 'create')
	{
		$i = 0;
		while(file_exists($Bambus->pathTo('css').SLocalization::get('new').'-'.++$i.'.css'))
			;
		$File = SLocalization::get('new').'-'.$i.'.css';
		$fileContent = '/* '.SLocalization::get('new_css_file').' */';
		$Bambus->FileSystem->write($Bambus->pathTo('css').$File, $fileContent);
		$FileName = SLocalization::get('new').'-'.$i;
		$allowEdit = false;
		$FileOpened = true;
	}
	
	if(count($Files) > 0)
	{
		if($allowEdit && !empty($get['edit']) && in_array($get['edit'], $Files))
		{
			$File = $get['edit'];
			$FileName = ($File == 'default.css') ? SLocalization::get('default.css') : htmlentities(substr($File, 0, -4));
			$allowEdit = true;
			$fileContent = $Bambus->FileSystem->read($Bambus->pathTo('css').$File);
			$FileOpened = true;
		}
		
		////////
		//save//
		////////
		
		if(BAMBUS_GRP_EDIT && $allowEdit && $FileOpened)
		{
			//content changed?
			if(isset($post['content']))
			{
			   	if($post['content'] != $fileContent)
			   	{
			        //do the save operation
			        if($Bambus->FileSystem->write($Bambus->pathTo('css').$File, $post['content']))
			        {
			        	SNotificationCenter::alloc()->init()->report('message', '.file_saved');
			        	$fileContent = $post['content'];
			        }
			        else
			        {
			        	SNotificationCenter::alloc()->init()->report('alert', 'saving_failed');
			        }
			   	}
			}
		}
		
		//////////////////
		//manager delete//
		//////////////////
		if(!empty($post['action']) && $post['action'] == 'delete' && BAMBUS_GRP_DELETE)
		{
			$files = $Bambus->FileSystem->getFiles('design', $allowed);
			foreach($files as $file)
			{
				if($file == 'default.css')
					continue;
				if(!empty($post['select_'.md5($file)]))
				{
			        if(@unlink($Bambus->pathTo('design').$file)){
			            SNotificationCenter::alloc()->init()->report('message', 'file_deleted');
			        }else{
			            SNotificationCenter::alloc()->init()->report('warning', 'could_not_delete_file');
			        }
					
				}
			}
		}
		
		//////////
		//delete//
		//////////
		
		if(BAMBUS_GRP_DELETE && !empty($get['_action']) && $get['_action'] == 'delete' && $File != 'default.css' && $allowEdit)
		{
			//kill it
			unlink($Bambus->pathTo('css').$File);
		    SNotificationCenter::alloc()->init()->report('message', 'file_deleted');
			$FileOpened = false;
		}
		elseif(BAMBUS_GRP_DELETE && !empty($get['_action']) && $get['_action'] == 'delete' && $File == 'default.css')
		{
			SNotificationCenter::alloc()->init()->report('warning', 'this_file_cannott_be_deleted');
		}
		
		//////////
		//rename//
		//////////
		
		if(BAMBUS_GRP_RENAME && $allowEdit && $FileOpened)
		{
		    if(!empty($post['filename']) && $FileName != $post['filename']&& $FileName != 'default.css' && file_exists($Bambus->pathTo('css').$File))
		    {
				rename($Bambus->pathTo('css').$File, $Bambus->pathTo('css').basename($post['filename']).'.css');
				$FileName = basename($post['filename']);
				$File = basename($post['filename']).'.css';
		        SNotificationCenter::alloc()->init()->report('message', 'file_renamed');
		    }
		}
	}	
	if(count($Files) > 0 && (empty($get['tab']) || $get['tab'] == 'edit_css'))
	{
		$EditingObject = ($File == 'default.css') ? SLocalization::get('default.css').'.css' : $File;	
	}

}
elseif(BAMBUS_APPLICATION_TAB == 'edit_templates')
{
	$allowEdit = true;
	$Suffix = '.tpl';
	$DefaultFile = 'header.tpl';
	$PathName = 'template';
	$Path = $Bambus->pathTo($PathName);
	$doNotDelete = array('page.tpl');
	$ListTypes = array('tpl');
	$Files = $Bambus->FileSystem->getFiles($PathName, $ListTypes);
	
	//////////
	//create//
	//////////
	if(BAMBUS_GRP_CREATE && !empty($get['_action']) && $get['_action'] == 'create')
	{
		$i = 0;
		while(file_exists($Path.SLocalization::get('new').'-'.++$i.$Suffix))
			;
		$File = SLocalization::get('new').'-'.$i.$Suffix;
		$fileContent = '<!-- '.SLocalization::get('new_template').' -->';
		$Bambus->FileSystem->write($Path.$File, $fileContent);
		$FileName = SLocalization::get('new').'-'.$i;
		$allowEdit = false;
		$FileOpened = true;
	}
	
	if(count($Files) > 0)
	{
		if($allowEdit && !empty($get['edit']) && in_array($get['edit'], $Files))
		{
			$File = $get['edit'];
			$FileName = (in_array($File, $doNotDelete)) ? SLocalization::get($File) : htmlentities(substr($File, 0, -4));
			$allowEdit = true;
			$FileOpened = true;
			$fileContent = $Bambus->FileSystem->read($Path.$File);
		}
		
		////////
		//save//
		////////
		
		if(BAMBUS_GRP_EDIT && $allowEdit && $FileOpened)
		{
			//content changed?
			if(isset($post['content']))
			{
			   	if($post['content'] != $fileContent)
			   	{
			        //do the save operation
			        if($Bambus->FileSystem->write($Path.$File, $post['content']))
			        {
			        	$fileContent = $post['content'];
			        }
			        else
			        {
			        	SNotificationCenter::alloc()->init()->report('warning', 'saving_failed');
			        }
			   	}
			}
		}
		
		//////////
		//delete//
		//////////
		
		if(BAMBUS_GRP_DELETE && !empty($get['_action']) && $get['_action'] == 'delete' && !in_array($File, $doNotDelete) && $allowEdit)
		{
			//kill it
			unlink($Path.$File);
		    SNotificationCenter::alloc()->init()->report('message', 'file_deleted');
			$FileOpened = false;
		}
		elseif(BAMBUS_GRP_DELETE && !empty($get['_action']) && $get['_action'] == 'delete' && in_array($File, $doNotDelete))
		{
			SNotificationCenter::alloc()->init()->report('warning', 'this_file_cannott_be_deleted');
		}
		
		//////////
		//rename//
		//////////
		
		if(BAMBUS_GRP_RENAME && $allowEdit && $FileOpened)
		{
		    if(!empty($post['filename']) && $FileName != $post['filename'] && file_exists($Path.$File))
		    {
				rename($Path.$File, $Path.basename($post['filename']).$Suffix);
				$FileName = basename($post['filename']);
				$File = basename($post['filename']).$Suffix;
		        SNotificationCenter::alloc()->init()->report('message', 'file_renamed');
		    }
		}
		$EditingObject = $FileName.'.tpl';
		

	}
}
echo '<form method="post" id="documentform" name="documentform" action="'
	,$Bambus->Linker->createQueryString(array('edit' => $File))
	,'">';

if(BAMBUS_APPLICATION_TAB != 'manage')
{
	try{
		echo new WSidebar(null);
	}
	catch(Exception $e){
		echo $e->getTraceAsString();
		
	}	
}

	echo "\n<div id=\"OFD_Definition\">\n" .
			"<span id=\"OFD_Categories\">\n" .
				"<span>CSS</span>\n" .
				"<span>TPL</span>\n" .
	"</span>\n" .
			"<span id=\"OFD_Items\">";
	$cssFiles = $Bambus->FileSystem->getFiles('css', array('css'));
	$tplFiles = $Bambus->FileSystem->getFiles('template', array('tpl'));
	$Files = array_merge($cssFiles, $tplFiles);
	asort($Files, SORT_STRING);
	foreach($Files as $item)
	{
		$type = strtolower(substr($item,-3));
		printf(
			'<a href="%s">' ."\n\t".
				'<span title="title">%s</span>' ."\n\t".
				'<span title="icon">%s</span>' ."\n\t".
				'<span title="description">%s</span>' ."\n\t".
				'<span title="category">%s</span>' ."\n".
			"</a>\n"
			,$Bambus->Linker->createQueryString(array('edit' => $item,'tab' => 'edit_'.($type == 'css' ? 'css':'templates')))
			,($item == 'default.css') ? SLocalization::get('default.css') : htmlentities(substr($item, 0, -4))
			,$Bambus->Gui->iconPath($type == 'css' ? 'stylesheet':'template', '', 'mimetype','medium')
			,$Bambus->formatSize(filesize($Bambus->pathTo($type == 'css' ? 'css':'template').$item))
			,strtoupper($type)
		);
	}
	echo "</span>\n</div>\n";
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