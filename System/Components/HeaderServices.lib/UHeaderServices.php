<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2009-05-27
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Plugin
 */
class UHeaderServices
    extends BPlugin 
    implements 
        Event_Handler_RequestingClassSettings,
        Event_Handler_UpdateClassSettings,
        Event_Handler_WillSendHeaders
{
    const CLASS_NAME = 'UHeaderServices';
    public function handleEventRequestingClassSettings(Event_RequestingClassSettings $e)
    {
		$classes = Core::getClassesWithInterface('IHeaderService');
        $active = Controller_Content::getInstance()->getContentsChainedToClass(self::CLASS_NAME);
		$activeIndex = array();
		foreach ($active as $k => $v){
			$activeIndex[$v] = 1;
		}
        $data = array();
        //get all items of all classes 
        //get a list of globally enabled items
        //build checkbox 
        foreach ($classes as $class)
        {
            $items = call_user_func($class.'::getHeaderServideItems');
            foreach ($items as $section => $itemData)
            {
                if(!isset($data[$section]))
                {
                    $data[$section] = array();
                }
                foreach ($itemData as $GUID => $title)
                {
                    $data[$section][$GUID] = array(isset($activeIndex[$GUID]), Settings::TYPE_CHECKBOX, null, $title);
                }
            }    
        }
        foreach ($data as $sect => $secData)
        {
            if(count($secData) > 0)
            {
                $e->addClassSettings($this, $sect, $secData);
            }
        }
    }
    
    public function handleEventUpdateClassSettings(Event_UpdateClassSettings $e)
    {
        $data = $e->getClassSettings($this);
        $classes = Core::getClassesWithInterface('IHeaderService');
        $avail = array();
        $cfg = array();
        $rem = array();
        $hasData = false;
        foreach ($classes as $class)
        {
            $items = call_user_func($class.'::getHeaderServideItems');
            foreach ($items as $section => $itemData)
            {
                foreach ($itemData as $GUID => $title)
                {
                    if(isset($data[$GUID]))
                    {
                        if(!empty($data[$GUID]))
                        {
                            $cfg[] = $GUID;
                        }
                        else
                        {
                            $rem[] = $GUID;
                        }
                    }
                }
            }    
        }
        $DSQL = DSQL::getInstance();
        $DSQL->beginTransaction();
        $coco = Controller_Content::getInstance();
        $coco->releaseContentChainsToClass($this, $rem);
        $coco->chainContentsToClass($this, $cfg);
        $DSQL->commit();
    }
    
    public function handleEventWillSendHeaders(Event_WillSendHeaders $e)
    {
        try{
			$res = Core::Database()
				->createQueryForClass(self::CLASS_NAME)
				->call('getServices')
				->withParameters(self::CLASS_NAME);
            while ($row = $res->fetchResult())
            {
                list($class, $alias) = $row;
                if(is_callable($class.'::sendHeaderService'))
                {
                    call_user_func($class.'::sendHeaderService', $alias, $e);
                }
            }
			$res->free();
        }
        catch (Exception $ex)
        {
            SErrorAndExceptionHandler::reportException($ex);
        }
    }
}
?>