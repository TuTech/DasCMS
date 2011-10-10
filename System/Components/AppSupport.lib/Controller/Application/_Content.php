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
abstract class _Controller_Application_Content
    extends 
        _Controller_Application 
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
    protected $contentClass = '_Content';
    
    /**
     * content icon
     * @var string
     */
    protected $contentIcon = '_Content';
    
    /**
	 * @var Interface_Content
     */
    protected $target = null;
    
    /**
     * returns all data necessary for the open dialog
     * @param array $namedParameters
     * @return array
     * @throws AccessDeniedException
     */
    public function provideContentTags(array $namedParameters)
    {
        if(!empty($namedParameters['alias']) 
            && Controller_Content::getInstance()->contentExists($namedParameters['alias']))
        {
            return array('tags' => Controller_Tags::getInstance()->get($namedParameters['alias']));
        }
    }
    
    /**
     * @throws AccessDeniedException
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
                $this->target = Controller_Content::getInstance()->openContent($target, $this->contentClass);
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
        if($this->target != null && $this->target instanceof Interface_Content)
        {
            if(isset($param['content']))
            {
				$this->target->setContent($param['content']);
            }
            if(!empty($param['title']))
            {
				$this->target->setTitle($param['title']);
            }
            if(isset($param['subtitle']))
            {
				$this->target->setSubTitle($param['subtitle']);
            }
        }
    }
    
    public function delete(array $param)
    {
        $this->checkPermission('delete');
        if($this->target != null && $this->target instanceof Interface_Content)
        {
            $alias = $this->target->getAlias();
            if(Controller_Content::getInstance()->deleteContent($alias))
            {
                $this->target = null;
            }
        }
    }
    
    public function commit()
    {
        if($this->target != null)
        {
            $this->target->save();
        }
    } 
    
    /**
     * array(Interface_Content|string file, [string mimetype])
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
        return empty($this->target) ? null : $this->target->getAlias();
    }
    
    /**
     * returns all data necessary for the open dialog
     * @param array $namedParameters
     * @return array
     * @throws AccessDeniedException
     */
    public function provideOpenDialogData(array $namedParameters)
    {
        $this->checkPermission('view');
        $idIndex = Controller_Content::getInstance()->contentIndex($this->contentClass);
        $items = array();
        foreach ($idIndex as $alias => $data)
        {
        	list($title, $pubdate) = $data;
        	$items[] = array($title, $alias, 0, strtotime($pubdate));
        }
        $data = array(
            'title' => SLocalization::get('open'),
            'nrOfItems' => count($items),
            'iconMap' => array('System/ClientData/Icons/tango/large/mimetypes/'.$this->contentIcon.'.png'),
            'smallIconMap' => array('System/ClientData/Icons/tango/extra-small/mimetypes/'.$this->contentIcon.'.png'),
            'itemMap' => array('title' => 0, 'alias' => 1, 'icon' => 2, 'pubDate' => 3),//, 'tags' => 4
            'sortable' => array('title' => 'title', 'pubDate' => 'pubDate'),
            'items' => $items
        );
        return $data;
    }
}
?>