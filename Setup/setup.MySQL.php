<?php
class setup_MySQL implements runnable 
{
	private function reportSuccess($target, $type, $db, $successful, $action = 'Creating')
	{
		printf(
			"<li>%s %s %s... %s</li>",
			$action,
			$type,
			$target,
			($successful) ? '<span style="color:#4e9a06">OK</span>' : '<span style="color:red">FAILED ('.mysqli_error($db) .')</span>'
		);
	}
	
	public function run()
	{
		if(SetupConfiguration::get('db_engine') != 'MySQL')
		{
			echo '<li>nothing to do...</li>';
			return;
		}
		
		$config = array();
		$keymap = array(
			'host' => 'db_server',
			'port' => 'db_port',
			'user' => 'db_user',
			'password' => "db_password",
			'socket' => "db_socket",
			'database' => "db_name",
			'prefix' => 'db_table_prefix',
			'drop' => 'db_setup_drop_tables'
		);
		foreach ($keymap as $own => $conf) 
		{
			$config[$own] = SetupConfiguration::get($conf);
			$config[$own] = empty($config[$own]) ? null : $config[$own];
		}
			
		if(empty($config['database']))
		{
			throw new Exception('database must not be empty');
		}
		$DB = new mysqli($config['host'],$config['user'],$config['password'],null,$config['port'],$config['socket']);
		//$DB = new mysqli('127.0.0.1','root');//,$config['password'],null,$config['port'],$config['socket']);
		$DB->set_charset('utf-8');
		$res = $DB->query('show databases');
		//$res = new mysqli_result();
		$exists = false;
		while($foo = $res->fetch_array())
		{
			echo '<li>Found DB: '.$foo[0].'</li>';
			$exists = ($foo[0] == $config['database']) || $exists;
		}
		if(!$exists)
		{
			$res = $DB->query('CREATE DATABASE '.$DB->real_escape_string($config['database']));
			if(empty($res))
			{
				throw new Exception('create database failed');
			}
		}
		$PREFIX = empty($config['prefix']) ? '' : $DB->real_escape_string($config['prefix']);
		
		$this->reportSuccess($config['database'], 'Database',$DB,
		$DB->query('USE '.$DB->real_escape_string($config['database'])),'Using');
		$Tables = array(
			'Managers','ContentIndex','Changes','Tags','Aliases','relContentTags'
		);
		if($config['drop'])
		{
			foreach ($Tables as $tbl) 
			{
				$this->reportSuccess($PREFIX.$tbl, 'Table',$DB,
					$DB->query("DROP TABLE ".$PREFIX.$DB->real_escape_string($tbl)), 'Dropping');
			}
		}	
		$encoding = 'ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci';
		$this->reportSuccess($PREFIX.'Managers', 'Table',$DB,
			$DB->query("CREATE TABLE IF NOT EXISTS  {$PREFIX}Managers(
			  managerID INTEGER PRIMARY KEY AUTO_INCREMENT NOT NULL,
			  manager varchar(64) UNIQUE NOT NULL
			){$encoding};"));
		
		$this->reportSuccess($PREFIX.'ContentIndex', 'Table',$DB,
			$DB->query("CREATE TABLE IF NOT EXISTS {$PREFIX}ContentIndex(
			  contentID INTEGER PRIMARY KEY AUTO_INCREMENT NOT NULL,
			  managerREL INTEGER NOT NULL,
			  managerContentID varchar(32) UNIQUE NOT NULL,
			  title TEXT NOT NULL,
			  pubDate INTEGER,
			  summary TEXT
			){$encoding};"));
		
		$this->reportSuccess($PREFIX.'Changes', 'Table',$DB,
			$DB->query("CREATE TABLE IF NOT EXISTS {$PREFIX}Changes(
			  changeID INTEGER PRIMARY KEY AUTO_INCREMENT NOT NULL,
			  contentREL INTEGER NOT NULL,
			  title varchar(255) NOT NULL,
			  size INTEGER,
			  changeDate INTEGER NOT NULL,
			  username varchar(64) NOT NULL
			){$encoding};"));
		
		$this->reportSuccess($PREFIX.'Tags', 'Table',$DB,
			$DB->query("CREATE TABLE IF NOT EXISTS {$PREFIX}Tags(
			  tagID INTEGER PRIMARY KEY AUTO_INCREMENT NOT NULL,
			  tag varchar(64) UNIQUE NOT NULL,
			  blocked INTEGER
			){$encoding};"));
		
		$this->reportSuccess($PREFIX.'Aliases', 'Table',$DB,
			$DB->query("CREATE TABLE IF NOT EXISTS {$PREFIX}Aliases(
			  aliasID INTEGER PRIMARY KEY AUTO_INCREMENT NOT NULL,
			  alias varchar(128) UNIQUE NOT NULL,
			  active INTEGER,
			  contentREL INTEGER NOT NULL
			){$encoding};"));
		
		$this->reportSuccess($PREFIX.'relContentTags', 'Relation',$DB,
			$DB->query("CREATE TABLE IF NOT EXISTS {$PREFIX}relContentTags(
			  contentREL INTEGER NOT NULL,
			  tagREL INTEGER NOT NULL
			){$encoding};"));/**/
		$DB->close();
	}
}
?>