<?php
class ContentProxy_CategorizationController
{
	protected $proxy;

	public function __construct(ContentProxyController $proxy) {
		$this->proxy = $proxy;
	}
}
?>