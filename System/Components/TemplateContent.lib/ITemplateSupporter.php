<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-03-26
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Interface
 */
interface ITemplateSupporter
{
    /**
     * return an array with function => array(0..n => parameters [, 'description' =>  desc])
     *
     * @return array
     */
    public function templateProvidedFunctions();
    
    /**
     * return an array with attributeName => description
     *
     * @return array
     */
    public function templateProvidedAttributes();
    
	/**
	 * check function availability and permissions
	 * 
	 * @param string $function
	 * @return boolean
	 */
	public function templateCallable($function);
	
	/**
	 * Call a function from this object
	 *
	 * USE UTF-8 ENCODING FOR RETURN VALUES
	 * 
	 * @param string $function
	 * @param array $namedParameters
	 * @return string
	 */
	public function templateCall($function, array $namedParameters);
	
	/**
	 * Get a property from an object
	 * return in proper format (e.g format date as set in config)
	 * 
	 * USE UTF-8 ENCODING FOR RETURN VALUES
	 * 
	 * @param string $property
	 * @return string
	 */
	public function templateGet($property);
}
?>