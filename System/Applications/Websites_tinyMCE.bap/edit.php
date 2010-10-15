<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author selke@tutech.de
 * @package org.bambuscms.applications.websites
 * @since 2006-10-11
 * @version 1.0
 */

$enableWYSIWYG = Core::settings()->get('AWebsiteEditor_WYSIWYG');
$enableWYSIWYG = !empty ($enableWYSIWYG);

$Page = SApplication::getControllerContent();
if($enableWYSIWYG && $Page instanceof Interface_Content){
	//disable wysiwyg for contents tagged with "@nowysiwyg"
	$tags = $Page->getTags();
	foreach ($tags as $tag){
		if(strtolower($tag) == '@nowysiwyg'){
			$enableWYSIWYG = false;
			break;
		}
	}
}

if($enableWYSIWYG){
	?>
	<script type="text/javascript">
		if(tinyMCE && tinyMCE.init){
			tinyMCE.init({
				// General options
				mode : "exact",
				theme : "advanced",
				skin : "o2k7",
				skin_variant : "black",
				elements : id,
				plugins : "safari,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template",
				document_base_url:"<?php echo addslashes(SLink::base()); ?>",

				// Theme options
				theme_advanced_buttons1 : "<?php echo addslashes(Core::settings()->get('AWebsiteEditor_WYSIWYG_row1')); ?>",
				theme_advanced_buttons2 : "<?php echo addslashes(Core::settings()->get('AWebsiteEditor_WYSIWYG_row2')); ?>",
				theme_advanced_buttons3 : "<?php echo addslashes(Core::settings()->get('AWebsiteEditor_WYSIWYG_row3')); ?>",
				theme_advanced_buttons4 : "<?php echo addslashes(Core::settings()->get('AWebsiteEditor_WYSIWYG_row4')); ?>",
				theme_advanced_toolbar_location : "top",
				theme_advanced_toolbar_align : "left",
				theme_advanced_statusbar_location : "bottom",
				theme_advanced_resizing : true
			});
		}
	</script>
	<?php
}
if(isset($Page) && $Page instanceof CPage)
{
    echo new View_UIElement_ContentTitle($Page);
    $editor = new View_UIElement_TextEditor($Page->Content);
	$editor->setCodeAssist(!$enableWYSIWYG);
    echo $editor;
}
?>