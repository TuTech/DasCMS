<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2009-05-29
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage AppController
 */
abstract class BAContentAppController 
    extends 
        BAppController 
    implements 
        ISupportsOpenDialog
{
    /**
     * required permission for class
     * @var string
     */
    protected $contentPermission = '*';
    
    /**
     * content class
     * @var string
     */
    protected $contentClass = 'BContent';
    
    /**
	 * @var BContent
     */
    protected $target = null;
    
    /**
     * returns all data necessary for the open dialog
     * @param array $namedParameters
     * @return array
     * @throws XPermissionDeniedException
     */
    public function provideContentTags(array $namedParameters)
    {
        if(!empty($namedParameters['alias']) 
            && BContent::contentExists($namedParameters['alias']))
        {
            return array('tags' => STag::getSharedInstance()->get($namedParameters['alias']));
        }
    }
    
    /**
     * @throws XPermissionDeniedException
     * @param string $action
     * @return void
     */
    protected function checkPermission($action)
    {
        parent::requirePermission(strtolower($this->contentPermission.'.'.$action));
    }
    
    /**
     * generic open wrapper
     * @param $alias
     * @return unknown_type
     */
    protected function contentCallStatic($function, $alias = null)
    {
        return call_user_func_array($this->contentClass.'::'.$function, array($alias));
    }
    
    
    public function setTarget($target)
    {
        try
        {
            if(!empty($target))
            {
                $this->target = $this->contentCallStatic('Open', $target);
            }
        }
        catch (Exception $e)
        {
            $this->target = null;
        }
    }
    
    public function create(array $param)
    {
        $this->checkPermission('create');
        $success = false;
        if(!empty($param['create']))
        {
            try
            {
                $this->target = $this->contentCallStatic('Create', $param['create']);
                $success = true;
            }
            catch (Exception $e)
            {
                SNotificationCenter::report('warning', 'could_not_create_content');
            }
        }
        return $success;
    }
    
    public function save(array $param)
    {
        $this->checkPermission('change');
        if($this->target != null)
        {
            if(isset($param['content']))
            {
                $this->target->Content = $param['content'];
            }
            if(!empty($param['title']))
            {
                $this->target->Title = $param['title'];
            }
            if(isset($param['subtitle']))
            {
                $this->target->SubTitle = $param['subtitle'];
            }
        }
    }
    
    public function delete(array $param)
    {
        $this->checkPermission('delete');
        if($this->target != null)
        {
            $alias = $this->target->Alias;
            if($this->contentCallStatic('Delete', $alias))
            {
                $this->target = null;
            }
        }
    }
    
    public function commit()
    {
        if($this->target != null && $this->target->isModified())
        {
            $this->target->Save();
        }
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
            $ret = array($this->target);
        }
        return $ret;
    }

    /**
     * opened object 
     * @return string|null 
     */
    public function getOpenDialogTarget()
    {
        return empty($this->target) ? null : $this->target->Alias;
    }
    
    /**
     * returns all data necessary for the open dialog
     * @param array $namedParameters
     * @return array
     * @throws XPermissionDeniedException
     */
    public function provideOpenDialogData(array $namedParameters)
    {
        $this->checkPermission('view');
        $IDindex = $this->contentCallStatic('Index');
        $items = array();
        foreach ($IDindex as $alias => $data) 
        {
        	list($title, $pubdate) = $data;
        	$items[] = array($title, $alias, 0, strtotime($pubdate));
        }
        $data = array(
            'title' => SLocalization::get('open'),
            'nrOfItems' => count($items),
            'iconMap' => array('System/ClientData/Icons/tango/large/mimetypes/'.$this->contentClass.'.png'),
            'smallIconMap' => array('System/ClientData/Icons/tango/extra-small/mimetypes/'.$this->contentClass.'.png'),
            'itemMap' => array('title' => 0, 'alias' => 1, 'icon' => 2, 'pubDate' => 3),//, 'tags' => 4
            'sortable' => array('title' => 'title', 'pubDate' => 'pubDate'),
            'items' => $items
        );
        return $data;
    }
}
?>