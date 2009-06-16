<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2009-06-15
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Plugin
 */
class UEventPlanner 
    extends BPlugin 
    implements 
        IAjaxAPI,
        IGlobalUniqueId
{
    const GUID = 'org.bambuscms.plugin.ueventplanner';
    const CLASS_NAME = 'UEventPlanner';
    
    public function getClassGUID()
    {
        return self::GUID;
    }
    
    public function isAjaxCallableFunction($function, array $parameterNames)
    {
        return($function == 'scheduleEvent'
            || $function == 'listEvents'
            || $function == 'removeEvent')
            && in_array('alias', $parameterNames)
            && (//scheduleEvents and removeEvent have to have begin and end paramss
                (in_array('begin', $parameterNames) && in_array('end', $parameterNames)) 
                || $function == 'listEvents'
            );
    }

    private static function failWithout($perm)
    {
        if (!PAuthorisation::has($perm))
        {
            throw new XPermissionDeniedException('you are not allowed to schedule events');
        }
    }
    
    public function scheduleEvent(array $params)
    {
        self::failWithout('org.bambuscms.event.schedule');
        $data = array('success' => 0, 'message' => 'event_not_scheduled');
        $begin = strtotime($params['begin']);
        $end = strtotime($params['end']);
        if($begin >= $end)
        {
            //flip to be in the right order
            $tmp = $end;
            $end = $begin;
            $begin = $tmp;
        }
        if($begin == 0 || $end == 0)
        {
            $data['message'] = 'invalid_date_for_schedule';
        }
        elseif (QUEventPlanner::scheduleEvent($params['alias'], $begin, $end))
        {
            $data['success'] = 1;
            $data['message'] = 'event_scheduled';
        }
        return $data;
    }
    
    public function listEvents(array $params)
    {
        self::failWithout('org.bambuscms.event.list');
        $list = array();
        $res = QUEventPlanner::listEvents($params['alias']);
        while ($row = $res->fetch())
        {
            $list[] = array('b' => $row[0], 'e' => $row[1], 'd' => $row[2] == 'Y');
        }
        $res->free();
        return array('items' => $list);
    }
    
    public function removeEvent(array $params)
    {
        self::failWithout('org.bambuscms.event.remove');
        $data = array('success' => 0, 'message' => 'event_not_removed');
        $begin = strtotime($params['begin']);
        $end = strtotime($params['end']);
        if (QUEventPlanner::removeEvent($params['alias'], $begin, $end))
        {
            $data['success'] = 1;
            $data['message'] = 'event_removed';
        }
        return $data;
    }
}
?>