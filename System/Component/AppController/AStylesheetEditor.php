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
class AStylesheetEditor 
    extends 
        BAppController 
    implements 
        IGlobalUniqueId,
        ISupportsOpenDialog
{
    const GUID = 'org.bambuscms.applications.stylesheeteditor';
    private $content = null;
    
    /**
     * @return string
     * (non-PHPdoc)
     * @see System/Component/Interface/IGlobalUniqueId#getClassGUID()
     */
    public function getClassGUID()
    {
        return self::GUID;
    }
    
    public function setTarget($target)
    {
        if(!empty($target))
        {
            $target = basename($target);
            if(file_exists(SPath::DESIGN.$target))
            {
                $this->target = $target;
            }
        }
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
        $index = DFileSystem::FilesOf(SPath::DESIGN, '/\.css$/i');
        $items = array();
        foreach($index as $item)
        {
            $items[] = array(DFileSystem::name($item), $item, 0, filesize(SPath::DESIGN.$item), filemtime(SPath::DESIGN.$item));
        }
        $data = array(
            'title' => SLocalization::get('open'),
            'nrOfItems' => count($items),
            'iconMap' => array('System/ClientData/Icons/tango/large/mimetypes/LStylesheet.png'),
            'smallIconMap' => array('System/ClientData/Icons/tango/extra-small/mimetypes/LStylesheet.png'),
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
     * opened object 
     * @return string|null 
     */
    public function getOpenDialogTarget()
    {
        return $this->target;
    }
    
    public function getSavedContent()
    {
        return $this->content;
    }
    
    /**
     * array(BContent|string file, [string mimetype])
     * 
     * @return array
     */
    public function getSideBarTarget()
    {
        $ret = array();
        if($this->target)
        {
            $ret = array($this->target, 'text/css');
        }
        return $ret;
    }
    
    public function create(array $param)
    {
        parent::requirePermission('org.bambuscms.layout.stylesheet.create');
        if(isset($param['create']) && preg_match('/^[a-zA-Z0-9\._-]+$/', $param['create']))
        {
            if(substr(strtolower($param['create']),-4) != '.css')
            {
                $param['create'] = $param['create'].'.css';
            }
        	$this->content = '/* '.SLocalization::get('new_css_file').' */';
        	DFileSystem::Save(SPath::DESIGN.$param['create'], $this->content);
        	$this->target = $param['create'];
        }
        elseif(isset($param['create']))
        {
            SNotificationCenter::report('warning', 'invalid_characters_in_file_name');
        }
    }
    
    public function save(array $param)
    {
        parent::requirePermission('org.bambuscms.layout.stylesheet.change');
        if($this->target != null
            && isset($param['content']))
        {
            $this->content = $param['content'];
            DFileSystem::Save(SPath::DESIGN.$this->target, $param['content']);
            SNotificationCenter::report('message', 'saved');
        }
    }
    
    public function delete(array $param)
    {
        parent::requirePermission('org.bambuscms.layout.stylesheet.delete');
        if($this->target != null)
        {
            SErrorAndExceptionHandler::muteErrorOnce();
            if(unlink(SPath::DESIGN.$this->target))
            {
                SNotificationCenter::report('message', 'file_deleted');
                $this->target = null;
            }
            else
            {
                SNotificationCenter::report('warning', 'could_not_delete_file');
            }
        }
    }
}
?>