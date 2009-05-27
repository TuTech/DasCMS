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
        HRequestingClassSettingsEventHandler,
        HUpdateClassSettingsEventHandler,
        HWillSendHeadersEventHandler
{
    const CLASS_NAME = 'UHeaderServices';
    public function HandleRequestingClassSettingsEvent(ERequestingClassSettingsEvent $e)
    {
        $classes = SComponentIndex::getSharedInstance()->ImplementationsOf('IHeaderService');
        $active = BContent::getContentsChainedToClass($this);
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
                    $data[$section][$GUID] = array(isset($active[$GUID]), AConfiguration::TYPE_CHECKBOX, null, $title);
                }
            }    
        }
        foreach ($data as $sect => $secData)
        {
            $e->addClassSettings($this, $sect, $secData);
        }
    }
    
    public function HandleUpdateClassSettingsEvent(EUpdateClassSettingsEvent $e)
    {
        $data = $e->getClassSettings($this);
        $cfg = array();
        foreach ($data as $key => $val)
        {
            if(!empty($data[$key]))
            {
                $cfg[] = $key;
            }
        }
        $DSQL = DSQL::getSharedInstance();
        $DSQL->beginTransaction();
        BContent::releaseContentChainsToClass($this);
        BContent::chainContentsToClass($this, $cfg);
        $DSQL->commit();
    }
    
    public function HandleWillSendHeadersEvent(EWillSendHeadersEvent $e)
    {
        try{
            $res = QUHeaderServices::getServicesToEmbed(self::CLASS_NAME);
            while ($row = $res->fetch())
            {
                list($class, $alias) = $row;
                if(is_callable($class.'::sendHeaderService'))
                {
                    call_user_func($class.'::sendHeaderService', $alias, $e);
                }
            }
        }
        catch (Exception $ex)
        {
            SErrorAndExceptionHandler::reportException($ex);
        }
    }
}
?>