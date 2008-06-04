<?php
/**
 * @package Bambus
 * @subpackage BaseClasses
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 28.11.2007
 * @license GNU General Public License 3
 */
abstract class BRenderingEngine extends BObject
{
	public abstract function FormatList($mixed);
	
	public abstract function FormatTable($mixed);
	
	/**
	 * Generate appropiate output code for content attribute data
	 * @param mixed $mixed
	 */
	public function Format($mixed)
	{
		if(is_numeric($mixed))
		{
			return $this->FormatNumber($mixed);
		}
		elseif(is_bool($mixed))
		{
			return $this->FormatBool($mixed);
		}
		elseif(is_array($mixed))
		{
			return $this->FormatList($mixed);
		}
		else
		{
			return $this->FormatText($mixed);
		}
	}
	
	public function FormatDate($mixed)
	{
		$dateformat = SConfiguration::alloc()->init()->Get('cms.dateformat');
		if(empty($dateformat))
		{
			$dateformat = 'r';
		}
		if(!is_numeric($mixed))
		{
			$mixed = strtotime($mixed);
		}
		return date($dateformat, $mixed);
	}
	
	public function FormatBool($mixed)
	{
		return ($mixed != false) ? 'Yes' : 'No';
	}
	
	protected function formatNumberAndCurrency($mixed, $doCurrency = false)
	{
		$locale_info = localeconv();
		$num = is_numeric($mixed) ? $mixed : 0;
		$fnum = number_format($num, $locale_info['frac_digits'], $locale_info['decimal_point'], $locale_info['thousands_sep']);
		
		$cur = ($doCurrency) ? $locale_info['currency_symbol'] : '';
		
		$sign = $num < 0 ? $locale_info['negative_sign'] : $locale_info['positive_sign'];
		$prefix = $num < 0 ? 'n' : 'p';
		
		switch($locale_info[$prefix.'_sign_posn']) {
	        case 0: 
	        	return "(".$fnum.$cur.")";
	        case 1: 
	        	return $sign.$fnum.$cur;
	        case 2:
	         	return $fnum.$cur.$sign;
	        case 3: 
	        	return $sign.$cur.$fnum;
	        case 4: 
	        	return $cur.$fnum.$sign;
	        default: 
	        	return 0;
	    }
	}
	
	public function FormatNumber($mixed)
	{
		return $this->formatNumberAndCurrency($mixed);
	}
	
	public function FormatCurrency($mixed)
	{
		return $this->formatNumberAndCurrency($mixed, true);
	}
	
	protected function quantificationInBytes($size)
	{
		$size = trim($size);
		$bytes = 0;
		//Byte or bit
		$quantifier = 1;
		$mass = 1000;
		if(strtolower(substr($size,-1)) == 'b')
		{
			$quantifier = (substr($size,-1) == 'B') ? 1 : 1/8; 
			$size = substr($size,0,-1);
		}
		if(substr($size,-1) == 'i')
		{
			$mass = 1024;
			$size = substr($size,0,-1);
		}
		switch(strtoupper(substr($size,0,-1)))
		{
			case 'Y': $quantifier *= $mass;
			case 'Z':$quantifier *= $mass;
			case 'E':$quantifier *= $mass;
			case 'P':$quantifier *= $mass;
			case 'T':$quantifier *= $mass;
			case 'G':$quantifier *= $mass;
			case 'M':$quantifier *= $mass;
			case 'K':
				if(substr($size,0,-1) == 'k')
				{
					$quantifier *= 1000;
				}
				elseif(substr($size,0,-1) == 'K')
				{
					$quantifier *= 1024;
				}
				else
				{
					$quantifier *= $mass;
				}
		}
		$rest = substr($size,0,-1);
		if(is_numeric($rest))
		{
			$bytes = $rest * $quantifier;
		}
		return $bytes;
	}
	
	protected function quantifyBytes($bytes)
	{
		$qmode = false;
		$locale_info = localeconv();
		$mass  = $qmode ? 1000 : 1024;
		$massi = $qmode ? 'i' : '';
		
		if(!is_numeric($bytes))
		{
			return 0;
		}
		$suffix = 'B';
		if($bytes < 0)
		{
			$bytes *= 8;
			$suffix = 'b';
		}
		$bytes = floor($bytes);
		$qantifyer = array('','K','M','G','T','P','E','Z','Y');
		$index = 0;
		while($bytes >= $mass)
		{
			$bytes /= $mass;
			$index++;
		}
		return round($bytes, $locale_info['frac_digits']).$qantifyer[$index].$massi.$suffix;
		
	}
	
	public function FormatBytes($mixed)
	{
		return $this->quantifyBytes(is_numeric($mixed) ? $mixed : $this->quantificationInBytes($mixed));
	}
	
	public function FormatText($mixed)
	{
		return $mixed;
	}
}
?>