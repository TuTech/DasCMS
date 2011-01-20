<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2010-10-20
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage AppController
 */
class Controller_Application_Search
    extends
        _Controller_Application
    implements
        IGlobalUniqueId,
        ISupportsOpenDialog
{
    const GUID = 'org.bambuscms.applications.search';

    /**
	 * @var CSearch
     */
    protected $target = null;

    public function setTarget($target)
    {
        try
        {
            if(!empty($target))
            {
                $this->target = Controller_Content::getInstance()->openContent($target, 'CSearch');
            }
        }
        catch (Exception $e)
        {
            $this->target = null;
        }
    }

    public function create(array $param)
    {
        parent::requirePermission('org.bambuscms.content.csearch.create');
        $success = false;
        if(!empty($param['create']))
        {
            try
            {
                $this->target = CSearch::Create($param['create']);
                $this->target->changeSearchIndexingStatus(false);
                $success = true;
            }
            catch (Exception $e)
            {
                SNotificationCenter::report('warning', 'could_not_create_search');
            }
        }
        return $success;
    }

    public function save(array $param)
    {
        parent::requirePermission('org.bambuscms.content.csearch.change');
        if($this->target != null)
        {
            if(!empty($param['title']))
            {
                $this->target->setTitle($param['title']);
            }
            if(isset($param['subtitle']))
            {
                $this->target->setSubTitle($param['subtitle']);
            }
			if(!empty ($param['items_per_page'])){
				$this->target->setItemsPerPage($param['items_per_page']);
			}
			if(!empty ($param['query_string'])){
				$this->target->setQueryString($param['query_string']);
			}
			if(!empty ($param['allow_extend_query_string'])){
				$this->target->setAllowExtendQueryString($param['allow_extend_query_string']);
			}
			if(!empty ($param['order'])){
				$this->target->setOrder($param['order']);
			}
			if(!empty ($param['empty_result_message'])){
				$this->target->setEmptyResultMessage($param['empty_result_message']);
			}
			if(!empty ($param['allow_overwrite_order'])){
				$this->target->setAllowOverwriteOrder($param['allow_overwrite_order']);
			}

			//composites
			if(isset($param['content_formatter']))
            {
                $this->target->setChildContentFormatter($param['content_formatter']);
            }
			if(isset($param['target_view']))
            {
                $this->target->setTargetView($param['target_view']);
            }
        }
    }

    public function delete(array $param)
    {
        parent::requirePermission('org.bambuscms.content.csearch.delete');
        if($this->target != null)
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
     * @return string
     * (non-PHPdoc)
     * @see System/Component/Interface/IGlobalUniqueId#getClassGUID()
     */
    public function getClassGUID()
    {
        return self::GUID;
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
     * @return array
     * @throws XPermissionDeniedException
     */
    public function provideOpenDialogData(array $namedParameters)
    {
        if(!$this->isPermitted('view'))
        {
            throw new XPermissionDeniedException('view');
        }
        $idIndex = Controller_Content::getInstance()->contentIndex('CSearch');
        $items = array();
        foreach ($idIndex as $alias => $data)
        {
        	list($title, $pubdate) = $data;
        	$items[] = array($title, $alias, 0, strtotime($pubdate));
        }
        $data = array(
            'title' => SLocalization::get('open'),
            'nrOfItems' => count($items),
            'iconMap' => array(CSearch::defaultIcon()->asSize(View_UIElement_Icon::LARGE)->getPath()),
            'smallIconMap' => array(CSearch::defaultIcon()->asSize(View_UIElement_Icon::EXTRA_SMALL)->getPath()),
            'itemMap' => array('title' => 0, 'alias' => 1, 'icon' => 2, 'pubDate' => 3),//, 'tags' => 4
            'sortable' => array('title' => 'title', 'pubDate' => 'pubDate'),
            'items' => $items
        );
        return $data;
    }
}
?>