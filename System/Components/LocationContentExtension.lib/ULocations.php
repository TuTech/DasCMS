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
    implements 
        IAjaxAPI,
        IGlobalUniqueId,
        Interface_Singleton
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
        $location = '';
        $location = null; $lat = null; $long = null; $addr = null;
		$res = Core::Database()
			->createQueryForClass($this)
			->call('get')
			->withParameters($alias);
	    if($row = $res->fetchResult())
	    {
	        list($location, $lat, $long, $addr) = $row;
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
		$dbid = Core::Database()
			->createQueryForClass($this)
			->call('getId')
			->withParameters($location)
			->fetchSingleValue();
		if($dbid == null){
			//set location to null/remove location data
			Core::Database()
				->createQueryForClass($this)
				->call('unlink')
				->withParameters($alias)
				->execute();
		}
		else{
			//set new location data
			Core::Database()
				->createQueryForClass($this)
				->call('link')
				->withParameters($dbid, $alias, $dbid)
				->execute();
		}
    }
        
    public function setLocationData($location, $address, $latitude, $longitude)
    {
        self::failWithout('org.bambuscms.location.edit');
		$address = empty($address) ? null : $address;
		$latitude = empty($latitude) ? null : $latitude;
		$longitude = empty($longitude) ? null : $longitude;
		return Core::Database()
			->createQueryForClass($this)
			->call('set')
			->withParameters($location, $latitude, $longitude, $address, $latitude, $longitude, $address)
			->execute();
    }
    
    public function getLocationList(array $params)
    {
        self::failWithout('org.bambuscms.location.list');
        //params: query
        $q = isset($params['query']) ? '%'.$params['query'].'%' : '%';
		return Core::Database()
			->createQueryForClass($this)
			->call('list')
			->withParameters($q)
			->fetchList();
    }
    
    public function createLocation(array $params)
    {
        self::failWithout('org.bambuscms.location.create');
        //params: location
    }
    
	//begin Interface_Singleton
	
	public static $sharedInstance = NULL;
	
	/**
	 * Enter description here...
	 *
	 * @return ULocations
	 */
	public static function getInstance()
	{
		$class = self::CLASS_NAME;
		if(self::$sharedInstance == NULL && $class != NULL)
		{
			self::$sharedInstance = new $class();
		}
		return self::$sharedInstance;
	}
	//end Interface_Singleton
}
?>