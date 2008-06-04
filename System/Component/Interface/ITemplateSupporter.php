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
	 * if $function given return boolean usable,
	 * else return array of available template functions 
	 * 
	 * function array:
	 * functionName => array(array(parameterName => type), string description)
	 *
	 * @param string|null $function
	 * @return boolean|array
	 */
	public function TemplateCallable($function = null);
	
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