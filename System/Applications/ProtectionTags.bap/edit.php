<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH 
 * @author selke@tutech.de
 * @package org.bambuscms.applications.protectiontags
 * @since 2008-11-12
 * @version 1.0
 */
$tags =  implode(', ', STagPermissions::getProtectedTags());
printf(
    '<form method="post" id="documentform" name="documentform" action="%s">'
	,SLink::link(array())
);
echo new WIntroduction('define_restriction_tags', 'tags_listed_here_restrict_access_to_contents');
echo LGui::editorTextarea($tags);
echo LGui::endForm();
?>