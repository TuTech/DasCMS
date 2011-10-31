<?php
class ContentProxy_ConnectionController
{
	protected $proxy;

	public function __construct(ContentProxyController $proxy) {
		$this->proxy = $proxy;
	}
}
?>