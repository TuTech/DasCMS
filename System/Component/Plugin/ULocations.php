<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2009-06-19
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Plugin
 */
class ULocations
    extends BPlugin 
    implements 
        IAjaxAPI,
        IGlobalUniqueId,
        IShareable
{
    const GUID = 'org.bambuscms.plugin.locations';
    const CLASS_NAME = 'ULocations';
    
    public function getClassGUID()
    {
        return self::GUID;
    }
    
    public function isAjaxCallableFunction($function, array $parameterNames)
    {
        return($function == 'getLocationData'
            || $function == 'getLocationList'
            || $function == 'setLocationData'
            || $function == 'createLocation')
            ;
    }

    private static function failWithout($perm)
    {
        if (!PAuthorisation::has($perm))
        {
            throw new XPermissionDeniedException('action not allowed');
        }
    }
    
    public function getContentLocation($alias)
    {
        self::failWithout('org.bambuscms.location.view');
        $location = '';
	    $res = QULocations::getContentLocation($alias);
	    if($res->getRowCount())
	    {
	        list($location, $lat, $long, $addr) = $res->fetch();
	    }
	    $res->free();
	    return array('location' => $location,
                    'latitude' => $lat,
	                'longitude' => $long,
	                'address' => $addr);
    }
    
    public function setContentLocation($alias, $location)
    {
        self::failWithout('org.bambuscms.location.edit');
	    return QULocations::setContentLocation($alias, $location);
    }
        
    public function setLocationData($location, $address, $latitude, $longitude)
    {
        self::failWithout('org.bambuscms.location.edit');
        return QULocations::setLocationData($location, $address, $latitude, $longitude);
    }
    
    public function getLocationList(array $params)
    {
        self::failWithout('org.bambuscms.location.list');
        //params: query
        $q = isset($params['query']) ? $params['query'] : '';
        $ret = array();
        $res = QULocations::getLocationList($q);
        while($row = $res->fetch())
        {
            $ret[] = $row[0];
        }
        $res->free();
        return $ret;
    }
    
    public function createLocation(array $params)
    {
        self::failWithout('org.bambuscms.location.create');
        //params: location
    }
    
	//begin IShareable
	
	public static $sharedInstance = NULL;
	
	/**
	 * Enter description here...
	 *
	 * @return ULocations
	 */
	public static function getSharedInstance()
	{
		$class = self::CLASS_NAME;
		if(self::$sharedInstance == NULL && $class != NULL)
		{
			self::$sharedInstance = new $class();
		}
		return self::$sharedInstance;
	}
	//end IShareable
}
?>