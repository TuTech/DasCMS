<?php
class AGroupManager
    extends 
        BAppController 
    implements 
        IACProviderOpenDialogData,
        IGlobalUniqueId  
{
    const GUID = 'org.bambuscms.applications.groupmanager';
    
    public function getGUID()
    {
        return self::GUID;
    }
    
    /**
     * returns all data necessary for the open dialog
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
        $SUsersAndGroups = SUsersAndGroups::alloc()->init();
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
            'iconMap' => array('System/Icons/tango/large/mimetypes/SUser.png','System/Icons/tango/large/mimetypes/SGroup.png'),
            'smallIconMap' => array('System/Icons/tango/extra-small/mimetypes/SUser.png','System/Icons/tango/extra-small/mimetypes/SGroup.png'),
            'itemMap' => array('title' => 0, 'alias' => 1, 'icon' => 2, 'description' => 3),//, 'tags' => 4
            'sortable' => array('title' => 'name', 'icon' => 'type'),
            'items' => $items,
            'captions' => array(
                'detail' => SLocalization::get('detail'),
                'icon' => SLocalization::get('icon'),
                'list' => SLocalization::get('list'),
                'asc' => SLocalization::get('asc'),
                'desc' => SLocalization::get('desc'),
                'description' => SLocalization::get('description'),
                'searchByTitle' => SLocalization::get('search_by_title'),
                'type' => SLocalization::get('type'),
                'name' => SLocalization::get('name'),
                'title' => SLocalization::get('title'),
                'modified' => SLocalization::get('modified'),
            )
        );
        return $data;
    }
    
    /**
     * delete a bunch of items
     */
    public function delete(array $items)
    {
        
    }
    
    /**
     * create a new item
     */
    public function create($title, array $options)
    {
        
    }
    
    /**
     * set all kinds of possible meta attributes
     */
    public function setAttributes(array $attributes)
    {
        
    }
    
    
    
    
}
?>