<?php
class String
{
	public static function htmlEncode($string){
		return htmlentities($string, ENT_QUOTES, CHARSET);
	}

	public static function htmlDecode($string){
		return html_entity_decode($string, ENT_QUOTES, CHARSET);
	}
}
?>
