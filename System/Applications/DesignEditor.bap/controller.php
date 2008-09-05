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
$Files = DFileSystem::FilesOf(SPath::DESIGN, '/\.css/i');
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
    if(file_exists(SPath::DESIGN.basename($_FILES['bambus_image_file']['name'])) 
      && empty($post['bambus_overwrite_image_file']))
    {
        SNotificationCenter::report('warning', 'upload_failed_because_file_already_exists');
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
            if(move_uploaded_file($_FILES['bambus_image_file']['tmp_name'], SPath::DESIGN.basename(utf8_decode($_FILES['bambus_image_file']['name']))))
            {
                //i like to move it move it
                SNotificationCenter::report('message', 'file_uploaded');
                chmod(SPath::DESIGN.basename(utf8_decode($_FILES['bambus_image_file']['name'])), 0666);
                $succesfullUpload = basename(utf8_decode($_FILES['bambus_image_file']['name']));
                //create thumbnail image
                $image = SPath::DESIGN.basename(utf8_decode($_FILES['bambus_image_file']['name']));
                $uploadIsImage = ($suffix != 'css' && $suffix != 'gpl');
            }
            else
            {
                SNotificationCenter::report('warning', 'uploded_failed');
            }
        }
        else
        {
            //run away and scream
            SNotificationCenter::report('warning', 'upload_failed_because_of_unsupported_file_type');
        }
        
    }
}

if(BAMBUS_APPLICATION_TAB == 'edit_css')
{
	//////////
	//create//
	//////////
		
	if(BAMBUS_GRP_CREATE && RURL::get('_action') == 'create')
	{
		$i = 0;
		while(file_exists(SPath::DESIGN.SLocalization::get('new').'-'.++$i.'.css'))
			;
		$File = SLocalization::get('new').'-'.$i.'.css';
		$fileContent = '/* '.SLocalization::get('new_css_file').' */';
		DFileSystem::Save(SPath::DESIGN.$File, $fileContent);
		$FileName = SLocalization::get('new').'-'.$i;
		$allowEdit = false;
		$FileOpened = true;
	}
	
	if(count($Files) > 0)
	{
		if($allowEdit && RURL::hasValue('edit') && in_array(RURL::get('edit'), $Files))
		{
			$File = RURL::get('edit');
			$FileName = ($File == 'default.css') ? SLocalization::get('default.css') : htmlentities(substr($File, 0, -4));
			$allowEdit = true;
			$fileContent = DFileSystem::Load(SPath::DESIGN.$File);
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
			        if(DFileSystem::Save(SPath::DESIGN.$File, $post['content']))
			        {
			        	SNotificationCenter::report('message', '.file_saved');
			        	$fileContent = $post['content'];
			        }
			        else
			        {
			        	SNotificationCenter::report('alert', 'saving_failed');
			        }
			   	}
			}
		}
		
		//////////////////
		//manager delete//
		//////////////////
		if(!empty($post['action']) && $post['action'] == 'delete' && BAMBUS_GRP_DELETE)
		{
			$files = DFileSystem::FilesOf(SPath::DESIGN, '/\.('.implode('|', $allowed).')/i');
			foreach($files as $file)
			{
				if($file == 'default.css')
					continue;
				if(!empty($post['select_'.md5($file)]))
				{
			        if(@unlink(SPath::DESIGN.$file)){
			            SNotificationCenter::report('message', 'file_deleted');
			        }else{
			            SNotificationCenter::report('warning', 'could_not_delete_file');
			        }
					
				}
			}
		}
		
		//////////
		//delete//
		//////////
		
		if(BAMBUS_GRP_DELETE && RURL::get('_action') == 'delete' && $File != 'default.css' && $allowEdit)
		{
			//kill it
			unlink(SPath::DESIGN.$File);
		    SNotificationCenter::report('message', 'file_deleted');
			$FileOpened = false;
		}
		elseif(BAMBUS_GRP_DELETE && RURL::get('_action') == 'delete' && $File == 'default.css')
		{
			SNotificationCenter::report('warning', 'this_file_cannott_be_deleted');
		}
		
		//////////
		//rename//
		//////////
		
		if(BAMBUS_GRP_RENAME && $allowEdit && $FileOpened)
		{
		    if(!empty($post['filename']) && $FileName != $post['filename']&& $FileName != 'default.css' && file_exists(SPath::DESIGN.$File))
		    {
				rename(SPath::DESIGN.$File, SPath::DESIGN.basename($post['filename']).'.css');
				$FileName = basename($post['filename']);
				$File = basename($post['filename']).'.css';
		        SNotificationCenter::report('message', 'file_renamed');
		    }
		}
	}	
	if(count($Files) > 0 && (!RURL::has('tab') || RURL::get('tab') == 'edit_css'))
	{
		$EditingObject = ($File == 'default.css') ? SLocalization::get('default.css').'.css' : $File;	
	}

}
elseif(BAMBUS_APPLICATION_TAB == 'edit_templates')
{
	$allowEdit = true;
	$Suffix = '.tpl';
	$DefaultFile = 'header.tpl';
	$Path = SPath::TEMPLATES;
	$doNotDelete = array('page.tpl');
	$ListTypes = array('tpl');
	//$Files = $Bambus->File System->getFiles($PathName, $ListTypes);
	$Files = DFileSystem::FilesOf($Path, '/\.('.implode('|', $ListTypes).')/i');
	
	//////////
	//create//
	//////////
	if(BAMBUS_GRP_CREATE && RURL::get('_action') == 'create')
	{
		$i = 0;
		while(file_exists($Path.SLocalization::get('new').'-'.++$i.$Suffix))
			;
		$File = SLocalization::get('new').'-'.$i.$Suffix;
		$fileContent = '<!-- '.SLocalization::get('new_template').' -->';
		DFileSystem::Save($Path.$File, $fileContent);
		$FileName = SLocalization::get('new').'-'.$i;
		$allowEdit = false;
		$FileOpened = true;
	}
	
	if(count($Files) > 0)
	{
		if($allowEdit && RURL::hasValue('edit') && in_array(RURL::get('edit'), $Files))
		{
			$File = RURL::get('edit');
			$FileName = (in_array($File, $doNotDelete)) ? SLocalization::get($File) : htmlentities(substr($File, 0, -4));
			$allowEdit = true;
			$FileOpened = true;
			$fileContent = DFileSystem::Load($Path.$File);
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
			        if(DFileSystem::Save($Path.$File, $post['content']))
			        {
			        	$fileContent = $post['content'];
			        }
			        else
			        {
			        	SNotificationCenter::report('warning', 'saving_failed');
			        }
			   	}
			}
		}
		
		//////////
		//delete//
		//////////
		
		if(BAMBUS_GRP_DELETE && RURL::get('_action') == 'delete' && !in_array($File, $doNotDelete) && $allowEdit)
		{
			//kill it
			unlink($Path.$File);
		    SNotificationCenter::report('message', 'file_deleted');
			$FileOpened = false;
		}
		elseif(BAMBUS_GRP_DELETE && RURL::get('_action') == 'delete' && in_array($File, $doNotDelete))
		{
			SNotificationCenter::report('warning', 'this_file_cannott_be_deleted');
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
		        SNotificationCenter::report('message', 'file_renamed');
		    }
		}
		$EditingObject = $FileName.'.tpl';
		

	}
}
echo '<form method="post" id="documentform" name="documentform" action="'
	,SLink::link(array('edit' => $File))
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
	$cssFiles = DFileSystem::FilesOf(SPath::DESIGN, '/\.css/i');//$Bambus->File System->getFiles('css', array('css'));
	$tplFiles = DFileSystem::FilesOf(SPath::TEMPLATES, '/\.tpl/i');//$Bambus->File System->getFiles('template', array('tpl'));
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
			,SLink::link(array('edit' => $item,'tab' => 'edit_'.($type == 'css' ? 'css':'templates')))
			,($item == 'default.css') ? SLocalization::get('default.css') : htmlentities(substr($item, 0, -4))
			,WIcon::pathFor($type == 'css' ? 'stylesheet':'template', 'mimetype', WIcon::MEDIUM)
			,DFileSystem::formatSize(filesize(($type == 'css' ? (SPath::DESIGN) : (SPath::TEMPLATES)).$item))
			,strtoupper($type)
		);
	}
	echo "</span>\n</div>\n";
?>
<script language="JavaScript" type="text/javascript">
	var OBJ_ofd;
	OBJ_ofd = new CLASS_OpenFileDialog();
	OBJ_ofd.self = 'OBJ_ofd';
	OBJ_ofd.openIcon = '<?php echo WIcon::pathFor('open');?>';
	OBJ_ofd.openTranslation = '<?php SLocalization::out('open'); ?>';
	OBJ_ofd.closeIcon = '<?php echo WIcon::pathFor('delete'); ?>';
	OBJ_ofd.statusText = '';
	OBJ_ofd.statusAnimation = '<?php echo WIcon::pathFor('loading', 'animation', WIcon::EXTRA_SMALL); ?>';
</script>