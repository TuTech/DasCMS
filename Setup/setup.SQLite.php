<?php
class setup_SQLite implements runnable 
{
	private function write($file,$data)
	{
		if(!($fp = fopen($file, 'w+')) || !fwrite($fp, $data))
		{
			throw new Exception('cound not write to file '. $file);
		}
		fclose($fp);
	}
	
	private function throwNotEmpty($err = null)
	{
		if(!empty($err))
		{
			throw new Exception($err);
		}
	}
	
	private function reportSuccess($target, $type, $successful)
	{
		printf(
			"<li>Creating %s %s... %s</li>",
			$type,
			$target,
			($successful) ? '<span style="color:#4e9a06">OK</span>' : '<span style="color:red">FAILED</span>'
		);
	}
	
	public function run()
	{
		if(SetupConfiguration::get('db_engine') != 'SQLite')
		{
			echo '<li>nothing to do...</li>';
			return;
		}		
		if(!is_dir('Content/DSQL_SQLite/'))
		{
			mkdir('Content/DSQL_SQLite/');
		}
		$this->write(
			'Content/DSQL_SQLite/.htaccess',
			"order deny,allow\ndeny from all"
		);
		
		$this->reportSuccess('Directory Lock', '.htaccess',file_exists('Content/DSQL_SQLite/.htaccess'));
		$dbfile = "Content/DSQL_SQLite/bambus.sqlite";
		$DB = new SQLiteDatabase($dbfile, 0600, $err);
		$this->throwNotEmpty($err);
		$this->reportSuccess('Managers', 'Table',
			$DB->queryExec("CREATE TABLE Managers(
			  managerID INTEGER PRIMARY KEY,
			  manager TEXT UNIQUE
			);"));
		
		$this->reportSuccess('ContentIndex', 'Table',
			$DB->queryExec("CREATE TABLE ContentIndex(
			  contentID INTEGER PRIMARY KEY,
			  managerREL INTEGER,
			  managerContentID TEXT UNIQUE,
			  title TEXT,
			  pubDate INTEGER,
			  summary TEXT
			);"));
		
		$this->reportSuccess('Changes', 'Table',
			$DB->queryExec("CREATE TABLE Changes(
			  changeID INTEGER PRIMARY KEY,
			  contentREL INTEGER,
			  title TEXT,
			  size INTEGER,
			  changeDate INTEGER,
			  username TEXT
			);"));
		
		$this->reportSuccess('Tags', 'Table',
			$DB->queryExec("CREATE TABLE Tags(
			  tagID INTEGER PRIMARY KEY,
			  tag TEXT UNIQUE,
			  blocked INTEGER
			);"));
		
		$this->reportSuccess('Aliases', 'Table',
			$DB->queryExec("CREATE TABLE Aliases(
			  aliasID INTEGER PRIMARY KEY,
			  alias TEXT UNIQUE,
			  active INTEGER,
			  contentREL INTEGER
			);"));
		
		$this->reportSuccess('Content-Tags', 'Relation',
			$DB->queryExec("CREATE TABLE relContentTags(
			  contentREL INTEGER,
			  tagREL INTEGER
			);"));
	}
}
?>