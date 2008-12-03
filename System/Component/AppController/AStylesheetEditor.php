<?php
class AStylesheetEditor 
    extends 
        BAppController 
    implements 
        IACProviderOpenDialogData,
        IGlobalUniqueId  
{
    const GUID = 'org.bambuscms.applications.stylesheeteditor';
    
    public function getClassGUID()
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
        $index = DFileSystem::FilesOf(SPath::DESIGN, '/\.css$/i');
        $items = array();
        foreach($index as $item)
        {
            $items[] = array(DFileSystem::name($item), $item, 0, filesize(SPath::DESIGN.$item), filemtime(SPath::DESIGN.$item));
        }
        $data = array(
            'title' => SLocalization::get('open'),
            'nrOfItems' => count($items),
            'iconMap' => array('System/Icons/tango/large/mimetypes/LStylesheet.png'),
            'smallIconMap' => array('System/Icons/tango/extra-small/mimetypes/LStylesheet.png'),
            'itemMap' => array('title' => 0, 'alias' => 1, 'icon' => 2, 'size' => 3, 'modified' => 4),//, 'tags' => 4
            'sortable' => array('title' => 'title', 'size' => 'size', 'modified' => 'modified'),
            'items' => $items,
            'captions' => array(
                'detail' => SLocalization::get('detail'),
                'icon' => SLocalization::get('icon'),
                'list' => SLocalization::get('list'),
                'asc' => SLocalization::get('asc'),
                'desc' => SLocalization::get('desc'),
                'searchByTitle' => SLocalization::get('search_by_title'),
                'size' => SLocalization::get('size'),
                'notPublished' => SLocalization::get('not_published'),
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