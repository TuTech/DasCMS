<?php
/***
 * @todo remove db bindng from core and use a json file for BObjects class index
 * @todo move classes table to Database.lib
 */
class Setup_Database
	extends _Setup
	implements
		Setup_ForConfiguration
{
	protected $defaults = array(
			'database.engine'	=> 'MySQL',
			'database.server'	=> '127.0.0.1',
			'database.port'		=> 3306,
			'database.socket'	=> null,
			'database.name'		=> 'DasCMS',
			'database.tablePrefix' => '',
			'database.user'		=> 'root',
			'database.password' => ''
	);

	protected $map = array(
			'database.engine'	=> 'db_engine',
			'database.server'	=> 'db_server',
			'database.port'		=> 'db_port',
			'database.socket'	=> 'db_socket',
			'database.name'		=> 'db_name',
			'database.tablePrefix' => 'db_table_prefix',
			'database.user'		=> 'db_user',
			'database.password' => 'db_password'
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

	public function validateInputData() {
		$conData = array();
		foreach ($this->defaults as $key => $default){
			$conData[$this->map[$key]] = $this->inputValueForKey($key, $default);
		}

		$db = new mysqli(
				$conData['db_server'],
				$conData['db_user'],
				$conData['db_password'],
				$conData['db_name'],
				$conData['db_port'],
				$conData['db_socket']
			);
		if ($db->connect_error){
			$this->reportError('database', 'couldn\'t connect to database server');
		}
		if(!$db->select_db($conData['db_name'])){
			$this->reportError('database.name', 'couldn\'t use database');
		}
		$db->close();
		return $this->getReport();
	}
}
?>