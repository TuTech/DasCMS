<?php
/************************************************
* Bambus CMS 
* Created:     21. Sep 07
* License:     GNU GPL Version 2 or later (http://www.gnu.org/copyleft/gpl.html)
* Copyright:   Lutz Selke/TuTech Innovation GmbH 
* Description: 
************************************************/
if(!class_exists("Bambus"))die('No login? No bambus for you, hungry Panda!');

function getData($key, $GPCarray)
{
	$data = isset($GPCarray[$key]) ? $GPCarray[$key] : '';
	return (get_magic_quotes_gpc()) ? stripslashes($data) : $data;
}

$EditingObject = '';
$edit = null;

if(isset($_GET['edit']) && NTreeNavigation::exists(getData('edit',$_GET)))
{
	if(!empty($_POST['delete_nav']))
	{
		//delete nav
		NTreeNavigation::remove(getData('edit',$_GET));
		NTreeNavigation::save();
	}
	else
	{
		$EditingObject = getData('edit',$_GET).'.nav';
		$edit = getData('edit',$_GET);
	}
}
if($edit != null && isset($_POST['1_p']) && $_POST['1_p'] == '0')//parent of first has to be "0"
{
	//got data
	$data = array(1 => new NTreeNavigationObject('', null, null, null));
	$i = 2;
	//get all nav objects 
	while(isset($_POST[$i.'_p']))
	{
		$cid = getData($i.'_cid', $_POST);
		$data[$i] = new  NTreeNavigationObject($cid, null, null, null);
		$i++;
	}
	//link nav objects
	foreach ($data as $id => $obj) 
	{
		if(!empty($_POST[$id.'_fc']) && array_key_exists($_POST[$id.'_fc'], $data))
		{
			$obj->setFirstChild($data[$_POST[$id.'_fc']]);
		}
		if(!empty($_POST[$id.'_p']) && array_key_exists($_POST[$id.'_p'], $data))
		{
			$obj->setParent($data[$_POST[$id.'_p']]);
		}
		if(!empty($_POST[$id.'_n']) && array_key_exists($_POST[$id.'_n'], $data))
		{
			$obj->setNext($data[$_POST[$id.'_n']]);
		}
	}
	try{
		if(!empty($_POST['set_spore']) && QSpore::exists(getData('set_spore',$_POST)))
    	{
    		$sp = new QSpore(getData('set_spore',$_POST));
    		SNotificationCenter::alloc()->init()->report('message', 'changing target view');
    	}
		else
		{
			$sp = NTreeNavigation::sporeOf($edit);
		}
		NTreeNavigation::set($edit,$sp, $data[1]);
		NTreeNavigation::Save();
		SNotificationCenter::alloc()->init()->report('message', 'saved');
	}
	catch(Exception $e)
	{
		SNotificationCenter::alloc()->init()->report('warning', $e->getMessage());
		//@todo: report error
	}
}
if(isset($_POST['new_nav_name']))
{
	$newNav = getData('new_nav_name', $_POST);
	if(QSpore::exists($newNav))
	{
		//matching spore exists - use it
		$spore = new QSpore($newNav);
	}
	else
	{
		$allSpores = QSpore::sporeNames();
		if(count($allSpores) == 0)
		{
			//no spores - create one
			QSpore::set($newNav,true,null,null);
			QSpore::Save();
			$spore = new QSpore($newNav);
		}
		else
		{
			//the are some spore use whatever comes first
			$spore = new QSpore($allSpores[0]);
		}
	}
	NTreeNavigation::set($newNav,$spore,new NTreeNavigationObject('', null,null,null));
	NTreeNavigation::Save();
	$EditingObject = $newNav.'.nav';
	$edit = $newNav;
}
//side bar
////////////////////	
try
{
	echo new WSidebar(null);
}
catch (Exception $e)
{
	echo "<pre>".$e->getTraceAsString()."</pre>";
}




$navigations = NTreeNavigation::navigations();

if(count($navigations) > 0)
{
    asort($navigations, SORT_STRING);
	echo "\n<div id=\"OFD_Definition\">\n" .
			"<span id=\"OFD_Categories\">\n" .
				"<span>Content-navigation</span>\n" .
				"<span>Meta-navigation</span>\n" .//@todo navigation containing other navigations 
			"</span>\n" .
			"<span id=\"OFD_Items\">";

	//openFileDialog files
    foreach($navigations as $item)
	{
		printf(
			'<a href="%s">' ."\n\t".
				'<span title="title">%s</span>' ."\n\t".
				'<span title="icon">%s</span>' ."\n\t".
				'<span title="description">%s</span>' ."\n\t".
				'<span title="category">%s</span>' ."\n".
			"</a>\n"
			,$Bambus->Linker->createQueryString(array('edit' => $item))
			,htmlspecialchars($item, ENT_QUOTES, 'UTF-8')
			,WIcon::pathFor('navigation', 'mimetype',WIcon::MEDIUM)
			,' '
			,'Content-navigation'
		);
	}
	echo "</span>\n</div>\n";
}
?>
<script language="JavaScript" type="text/javascript">
	var OBJ_ofd;
	OBJ_ofd = new CLASS_OpenFileDialog();
	OBJ_ofd.self = 'OBJ_ofd';
	OBJ_ofd.openIcon = '<?php echo WIcon::pathFor('open') ?>';
	OBJ_ofd.openTranslation = '<?php SLocalization::out('open'); ?>';
	OBJ_ofd.closeIcon = '<?php echo WIcon::pathFor('delete') ?>';
	OBJ_ofd.statusText = '';
	OBJ_ofd.statusAnimation = '<?php echo  WIcon::pathFor('loading', 'animation', WIcon::EXTRA_SMALL);  ?>';
</script>