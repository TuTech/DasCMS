<?php
class init_MError implements runnable 
{
	/*
			  contentID INTEGER PRIMARY KEY,
			  managerREL INTEGER,
			  managerContentID TEXT UNIQUE,
			  title TEXT,
			  pubDate INTEGER,
			  summary TEXT
* */

	private function reportSuccess($target, $type, $successful)
	{
		printf(
			"<li>Creating %s %s... %s</li>",
			$type,
			$target,
			($successful) ? '<span style="color:#4e9a06">OK</span>' : '<span style="color:red">FAILED</span>'
		);
	}
	
	private function insertSQLite(SQLiteDatabase $db, $prefix, $code, $data)
	{
		$prefix = sqlite_escape_string($prefix);
		$code = intval($code);
		$data = sqlite_escape_string($data);
		$now = time();
		$sql =  "INSERT OR IGNORE INTO {$prefix}ContentIndex (managerREL, managerContentID, title, pubDate, summary) ".
		"VALUES ((SELECT managerID FROM Managers WHERE manager = 'MError' LIMIT 1), $code, '$data', $now, '$data');";
		$this->reportSuccess($code.': '.$data, 'Error', $db->queryExec($sql, $err));
	}
	
	private function insertMySQLi(mysqli $db, $prefix, $code, $data)
	{
		$prefix = $db->real_escape_string($prefix);
		$code = intval($code);
		$data = $db->real_escape_string($data);
		$now = time();
		$sql =  "INSERT IGNORE INTO {$prefix}ContentIndex (managerREL, managerContentID, title, pubDate, summary) ".
		"VALUES ((SELECT managerID FROM Managers WHERE manager = 'MError' LIMIT 1), $code, '$data', $now, '$data');";
		$this->reportSuccess($code.': '.$data, 'Error', $db->query($sql));
	}
	
	public function run()
	{
		include_once('System/Component/Base/BObject.php');
		include_once('System/Component/Base/BSystem.php');
		include_once('System/Component/System/SHTTPStatus.php');
		
		$pfx = SetupConfiguration::get('db_table_prefix');
		
		$codes = SHTTPStatus::codes();
		switch(SetupConfiguration::get('db_engine'))
		{

			case 'SQLite':
			$DB = new SQLiteDatabase("Content/DSQL_SQLite/bambus.sqlite", 0600, $err);
			foreach ($codes as $code => $desc) 
			{
				$this->insertSQLite($DB, $pfx, $code, $desc);
			}
			break;
			case 'MySQL':
			
			$DB = new mysqli(
				$this->getCfgOrDefault('db_server'),
				$this->getCfgOrDefault('db_user'),
				$this->getCfgOrDefault('db_password'),
				$this->getCfgOrDefault('db_name'),
				$this->getCfgOrDefault('db_port'),
				$this->getCfgOrDefault('db_socket'));
				//$srv,$usr,$pw,$db,$prt,$sck);
			foreach ($codes as $code => $desc) 
			{
				$this->insertMySQLi($DB, $pfx, $code, $desc);
			}
			break;
			default:throw new Exception('unknown db engine '.SetupConfiguration::get('db_engine'));
		}
	}
	private function getCfgOrDefault($key, $else = null)
	{
		$dat = SetupConfiguration::get($key);
		if(empty($dat))
		{
			$dat = $else;
		}
		return $dat; 
	}
}
?>