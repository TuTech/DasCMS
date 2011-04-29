<?php
class Exception_HTTP extends Exception{
	public function __construct($code, $previous = null) {
		$message = SHTTPStatus::byCode($code);
		parent::__construct($message, $code, $previous);
	}
}
?>