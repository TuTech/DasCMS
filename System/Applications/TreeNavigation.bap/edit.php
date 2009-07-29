<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH 
 * @author selke@tutech.de
 * @package org.bambuscms.applications.treenavigation
 * @since 2007-09-21
 * @version 1.0
 */
$edit = SApplication::getControllerContent();
if($edit != null)
{
	try
	{
	    $cSpore = NTreeNavigation::sporeOf($edit);
	    $sporeName = $cSpore->GetName();
	}
	catch (Exception $e)
	{
	    $sporeName = SLocalization::get('not_set');
	}
	?>
	
		<h2><?php echo htmlspecialchars($edit,ENT_QUOTES, CHARSET); ?></h2>
		<h3><?php SLocalization::out('set_target_view'); ?></h3>
		<select name="set_spore">
			<option value=""><?php echo $sporeName; ?></option>
		<?php
		$spores = VSpore::activeSpores();
		foreach ($spores as $spore) 
		{
			if($spore != $sporeName)
			{
		        echo '<option>',htmlentities($spore, ENT_QUOTES,CHARSET),'</option>';
			}
		}
		
			?>
		</select>
		<h3><?php SLocalization::out('edit_navigation_layout'); ?></h3>
		<div id="navigationLayout">
			<div id="_1">
				<input type="hidden" name="_1_fc" id="_1_fc" value="" />
				<input type="hidden" name="_1_n" id="_1_n" value="" />
				<input type="hidden" name="_1_p" id="_1_p" value="0" />
			</div>
		</div>
	<script type="text/javascript">
		function buildNav()
		{
		<?php
		$id = 1;
		$out = false;
		function createNavJS(NTreeNavigationObject $tno, $parentid, $isChild = true)
		{
			global $id,$out;
			$myid = $id;
			$content = SAlias::resolve($tno->getAlias());
			if($content != null)
			{
    			$myid = ++$id;
    			$title = ($content == null) 
    				? '' 
    				: addslashes($content->Title);
    		    if($isChild)
    			{
    				echo 'addChild(\'_'.$parentid.'\', \''.$tno->getAlias().'\', \''.$title.' ('.$tno->getAlias().')\');';
    				$out = true;
    			}
    			else
    			{
    				echo 'addSibling(\'_'.$parentid.'\', \''.$tno->getAlias().'\', \''.$title.' ('.$tno->getAlias().')\');';
    				$out = true;
    			}
    			if($tno->hasChildren())
    			{
    				createNavJS($tno->getFirstChild(), $myid, true);
    			}
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
		if(!$out)
		{
			echo 'addChild(\'_1\');';
		}
		?>
		}
		org.bambuscms.autorun.register(buildNav);
	</script>

<?php	
}
?>