<?php
class ContentProxy_ImageController
{
	protected $proxy;

	public function __construct(ContentProxyController $proxy) {
		$this->proxy = $proxy;
	}
}
?>