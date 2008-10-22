<?php
/************************************************
* Bambus CMS 
* Created:     16. Okt 06
* License:     GNU GPL Version 2 or later (http://www.gnu.org/copyleft/gpl.html)
* Copyright:   Lutz Selke/TuTech Innovation GmbH 
* Description: 
************************************************/

if(PAuthorisation::has('org.bambuscms.layout.navigation.ntreenavigation.change'))
{
	printf(
		'<form method="post" id="documentform" name="documentform" action="%s"><input type="hidden" name="posted" value="1" />', SLink::link()
	);
}
if($edit != null)
{
	
	
	?>
	
	<div id="objectInspectorActiveFullBox">
		<h2><?php echo htmlspecialchars($edit,ENT_QUOTES, 'utf-8'); ?></h2>
		<h3><?php SLocalization::out('set_target_view'); ?></h3>
		<select name="set_spore">
			<option value=""><?php SLocalization::out('dont_change'); ?></option>
		<?php
		$spores = QSpore::activeSpores();
		foreach ($spores as $spore) 
		{
			echo '<option>',htmlentities($spore, ENT_QUOTES,'UTF-8'),'</option>';
		}
		
			?>
		</select>
		<h3><?php SLocalization::out('edit_navigation_layout'); ?></h3>
		<div id="navigationLayout">
			<div id="1">
				<input type="hidden" name="1_fc" id="1_fc" value="" />
				<input type="hidden" name="1_n" id="1_n" value="" />
				<input type="hidden" name="1_p" id="1_p" value="0" />
			</div>
		</div>
	</div>
	<script type="text/javascript">
		function buildNav()
		{
		<?php
		$id = 1;
		function createNavJS(NTreeNavigationObject $tno, $parentid, $isChild = true)
		{
			global $id;
			$myid = ++$id;
			$content = SAlias::resolve($tno->getAlias());
			$title = ($content == null) 
				? '' 
				: htmlspecialchars($content->Title, ENT_QUOTES, 'utf-8');
			if($isChild)
			{
				echo 'addChild(\''.$parentid.'\', \''.$tno->getAlias().'\', \''.$title.' ('.$tno->getAlias().')\');';
			}
			else
			{
				echo 'addSibling(\''.$parentid.'\', \''.$tno->getAlias().'\', \''.$title.' ('.$tno->getAlias().')\');';
			}
			if($tno->hasChildren())
			{
				createNavJS($tno->getFirstChild(), $myid, true);
			}
			if($tno->hasNext())
			{
				createNavJS($tno->getNext(), $myid, false);
			}
		}
		$root = NTreeNavigation::getRoot($edit);
		if($root->hasChildren())
		{
			createNavJS($root->getFirstChild(), $id, true);
		}
		else
		{
			echo 'addChild(\'1\');';
		}
		?>
		}
		org.bambuscms.autorun.register(buildNav);
	</script>

<?php	
}
if(PAuthorisation::has('org.bambuscms.layout.navigation.ntreenavigation.change'))
{
	echo '</form>';
}	
?>