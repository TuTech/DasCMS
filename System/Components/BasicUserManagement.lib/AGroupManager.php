<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-10-07
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage AppController
 */
class AGroupManager
    extends 
        BAppController 
    implements 
        IGlobalUniqueId  
{
    const GUID = 'org.bambuscms.applications.groupmanager';
        
    /**
     * @return string
     * (non-PHPdoc)
     * @see System/Component/Interface/IGlobalUniqueId#getClassGUID()
     */
    public function getClassGUID()
    {
        return self::GUID;
    }
    
    /**
     * returns all data necessary for the open dialog
     * @param array $namedParameters
     * @return array
     * @throws XPermissionDeniedException
     */
    public function provideOpenDialogData(array $namedParameters)
    {
        if(!$this->isPermitted('view'))
        {
            throw new XPermissionDeniedException('view');
        }
        $items = array();
        $SUsersAndGroups = SUsersAndGroups::getInstance();
        $users = $SUsersAndGroups->listUsers();
        foreach(array_keys($users) as $item)
        {
            $items[] = array(utf8_encode($item), 'u:'.utf8_encode($item), 0, utf8_encode($SUsersAndGroups->getRealName($item)));
        }
        $groups = $SUsersAndGroups->listGroups();
        foreach($groups as $item => $desc)
        {
            if($SUsersAndGroups->isSystemGroup($item))
            {
                continue;
            }
            $items[] = array(utf8_encode($item), 'g:'.utf8_encode($item), 1, utf8_encode($desc));
        }
                $data = array(
            'title' => SLocalization::get('open'),
            'nrOfItems' => count($items),
            'iconMap' => array('System/ClientData/Icons/tango/large/mimetypes/SUser.png','System/ClientData/Icons/tango/large/mimetypes/SGroup.png'),
            'smallIconMap' => array('System/ClientData/Icons/tango/extra-small/mimetypes/SUser.png','System/ClientData/Icons/tango/extra-small/mimetypes/SGroup.png'),
            'itemMap' => array('title' => 0, 'alias' => 1, 'icon' => 2, 'description' => 3),//, 'tags' => 4
            'sortable' => array('title' => 'name', 'icon' => 'type'),
            'items' => $items
        );
        return $data;
    }
    
    /**
     * returns all data necessary for the open dialog
     * @param array $namedParameters
     * @return array
     * @throws XPermissionDeniedException
     */
    public function provideContentTags(array $namedParameters)
    {
        return array();
    }
}
?>