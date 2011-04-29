<?php
interface API_Interface_PathComponent
{
	/**
	 * the path component for this element
	 */
	public function getControllerName();

	/**
	 * the path below this element
	 */
	public function resolveSubPath(array $path);
}
?>