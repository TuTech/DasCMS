<?php
/***
 * @todo remove db bindng from core and use a json file for objects class index
 * @todo move classes table to Database.lib
 */
class Setup_Core 
	extends _Setup
	implements 
		Setup_ForDatabaseTables,
		Setup_ForConfiguration
{
	protected $defaults = array(
			'date.timezone'		=> 'UTC',
			'date.format'		=> 'c',
			'system.locale'		=> 'en-GB',
			'system.webmasterEMail'	=> '',
			'website.errors.show' => false,
			'website.errors.mailWebmaster' => false
	);

	protected $map = array(
			'date.timezone'		=> 'timezone',
			'date.format'		=> 'dateformat',
			'system.locale'		=> 'locale',
			'system.webmasterEMail'	=> 'webmaster',
			'website.errors.show' => 'show_errors_on_website',
			'website.errors.mailWebmaster' => 'mail_webmaster_on_error'
	);

	public function runConfigurationSetup() {
		$config = Core::settings();
		foreach ($this->defaults as $key => $default){
			$config->set(
					$this->map[$key],
					$this->inputValueForKey($key, $default)
				);
		}
	}

	public function runDatabaseTablesSetup() {
		$this->setupInDatabase('classesTable');
	}
}
?>