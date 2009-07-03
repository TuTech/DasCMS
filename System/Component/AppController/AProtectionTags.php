<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2009-03-11
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage AppController
 */
class AProtectionTags
    extends 
        BAppController 
    implements 
        IGlobalUniqueId  
{
    const GUID = 'org.bambuscms.applications.protectiontags';
        
    /**
     * @return string
     * (non-PHPdoc)
     * @see System/Component/Interface/IGlobalUniqueId#getClassGUID()
     */
    public function getClassGUID()
    {
        return self::GUID;
    }
    
    public function save(array $data)
    {
        parent::requirePermission('org.bambuscms.system.permissions.tags.change');
        $tags = STag::parseTagStr(isset($data['content']) ? $data['content'] : '');
        STagPermissions::setProtectedTags($tags);
        SNotificationCenter::report('message', 'tags_set');
    }
}
?>