<?php
/**
 * @static 
 */
class SHTTPStatus 
    extends 
        BSystem 
{
	/**
	 * @var array
	 */
	private static $httpStatusCodes = array(
		//Informational 1xx
		100 => "Continue",
		101 => "Switching Protocols",
		102 => "Processing",

		//Successful 2xx
		200 => "OK",
		201 => "Created",
		202 => "Accepted",
		203 => "Non-Authoritative Information",
		204 => "No Content",
		205 => "Reset Content",
		206 => "Partial Content",
		207 => "Multi-Status",

		//Redirection 3xx
		300 => "Multiple Choices",
		301 => "Moved Permanently",
		302 => "Found",
		303 => "See Other",
		304 => "Not Modified",
		305 => "Use Proxy",
		//306 is unused
		307 => "Temporary Redirect",

		//Client Error 4xx
		400 => "Bad Request",
		401 => "Unauthorized",
		402 => "Payment Required",
		403 => "Forbidden",
		404 => "Not Found",
		405 => "Method Not Allowed",
		406 => "Not Acceptable",
		407 => "Proxy Authentication Required",
		408 => "Request Timeout",
		409 => "Conflict",
		410 => "Gone",
		
		411 => "Length Required",
		412 => "Precondition Failed",
		413 => "Request Entity Too Large",
		414 => "Request-URI Too Long",
		415 => "Unsupported Media Type",
		416 => "Requested Range Not Satisfiable",
		417 => "Expectation Failed",
		//418 is unused
		//419 is unused
		//420 is unused
		//421 is unused
		422 => "Unprocessable Entity",
		423 => "Locked",
		424 => "Failed Dependency",
		425 => "No Code",
		426 => "Upgrade Required",
		
		//Server Error 5xx
		500 => "Internal Server Error",
		501 => "Not Implemented",
		502 => "Bad Gateway",
		503 => "Service Unavailable",
		504 => "Gateway Timeout",
		505 => "HTTP Version Not Supported",
		507 => "Insufficient Storage",
		//508 is unused
		//509 is unused
		510 => "Not Extended"
	);

	/**
	 * get error message (or complete http header if $fullResponse == true) for $code 
	 *
	 * @param int $code
	 * @param boolean $fullResponse
	 * @return string
	 */
	public static function byCode($code, $fullResponse = true)
	{
		$status = (array_key_exists($code, self::$httpStatusCodes)) ? $code : 500;
		return (($fullResponse) ? 'HTTP/1.1 '.$status.' ' : '' ).self::$httpStatusCodes[$status];
	}

	/**
	 * returns given code if its a valid code or null
	 *
	 * @param int $code
	 * @return int|null
	 */
	public static function validate($code)
	{
		return (array_key_exists($code, self::$httpStatusCodes)) ? $code : null;
	}
	
	/**
	 * lookup http error string an returns its errno or null
	 *
	 * @param string $string
	 * @param boolean $fillResponse
	 * @return int|null
	 */
	public static function byString($string, $fillResponse = true)
	{
		$error = null;
		foreach (self::$httpStatusCodes as $code => $status)
		{
			if(strcasecmp($string, $status) == 0)
			{
				$error = $code;
				break;
			}
		}
		return $error;
	}
	
	/**
	 * returns all codes
	 *
	 * @return array (code => message)
	 */
	public static function codes()
	{
		return self::$httpStatusCodes;
	}

	/**
	 * static class
	 */
	private function __construct(){}
}
?>