<?php
/**
 * @package Bambus
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 26.03.2008
 * @license GNU General Public License 3
 */
interface ITemplateSupporter
{
    /**
     * return an array with function => array(0..n => parameters [, 'description' =>  desc])
     *
     * @return array
     */
    public function TemplateProvidedFunctions();
    
    /**
     * return an array with attributeName => description
     *
     * @return array
     */
    public function TemplateProvidedAttributes();
    
	/**
	 * check function availability and permissions
	 * 
	 * @param string $function
	 * @return boolean
	 */
	public function TemplateCallable($function);
	
	/**
	 * Call a function from this object
	 *
	 * USE UTF-8 ENCODING FOR RETURN VALUES
	 * 
	 * @param string $function
	 * @param array $namedParameters
	 * @return string
	 */
	public function TemplateCall($function, array $namedParameters);
	
	/**
	 * Get a property from an object
	 * return in proper format (e.g format date as set in config)
	 * 
	 * USE UTF-8 ENCODING FOR RETURN VALUES
	 * 
	 * @param string $property
	 * @return string
	 */
	public function TemplateGet($property);
}
?>