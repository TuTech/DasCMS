<?php
class pre_setup_BaseLayout implements runnable 
{
	const HEADER = "<?php /* Bambus Data File */ header(\"HTTP/1.0 404 Not Found\"); exit(); ?>\n";
	
	private function write($file,$data)
	{
		if(!($fp = fopen($file, 'w+')) || !fwrite($fp, $data))
		{
			throw new Exception('cound not write to file '. $file);
		}
		fclose($fp);
	}
	public function cleanUp()
	{
		$files = array(
			'Content/configuration/users.php',
			'Content/configuration/groups.php',
			'Content/configuration/system.php',
			"Content/DSQL_SQLite/bambus.sqlite",
			'Content/DSQL_SQLite/.htaccess',
			'Content/QSpore/index.php',
			'Content/templates/page.tpl',
			'Content/stylesheets/default.css',
			'Content/SComponentIndex/classes.php',
			'Content/SComponentIndex/interfaces.php'
		);
		echo '<h4>Cleaning up older data</h4><ol style="color:#f57900">';
		foreach ($files as $file) 
		{
			if(file_exists($file) && @unlink($file))printf("<li>deleting %s</li>\n", $file);
		}
		$dirs = array(
			'Content/configuration',
			'Content/DSQL_SQLite',
			'Content/download',
			'Content/images',
			'Content/logs',
			'Content/stylesheets',
			'Content/temp',
			'Content/templates',
			'Content/SComponentIndex',
		);
		foreach ($dirs as $dir) 
		{
			if(is_dir($dir) && @rmdir($dir))printf("<li>removing %s/</li>\n", $dir);
		}
		echo '</ol>';
	}	
	public function run()
	{
		$this->cleanUp();
		//write config system.php
		$config = array(
			"webmaster" => "webmaster@domain.tld",
			"logAccess" => 0,
			"logChanges" => 0,
			"copyright" => '',
			"meta_description" => 'Capricore CMS',
			"meta_keywords" => 'Capricore CMS',
			"db_engine" => 'SQLite',
			"db_server" => '',
			"db_user" => '',
			"db_port" => '',
			"db_socket" => '',
			"db_password" => '',
			"db_name" => '',
			"db_table_prefix" => '',
			'db_setup_drop_tables' => 0,
			"cms_uri" => '',
			"dateformat" => 'c',
			"404redirect" => 0,
			"htaccessfile" => '.htaccess',
			"logo" => '',
			"sitename" => 'Capricore CMS Installation',
			"logout_on_exit" => 1,
			"confirm_for_exit" => 1
		);
		if(file_exists('Setup/base_config.php'))
		{
			$data = file('Setup/base_config.php');
			foreach ($data as $line) 
			{
				$strip = (trim(mb_convert_encoding($line,'ISO-8859-1','utf-8, ISO-8859-1, auto')));
				preg_match('/^\s*([a-zA-Z_]+)\s*=\s*(["\']?)([^\s\'"]*)\2\s*;/is',$strip,$matches);
				if(isset($matches[3]) && array_key_exists($matches[1], $config))
				{
					$config[$matches[1]] = $matches[3];
				}
			}
		}
		else
		{
			echo '<li>no config</li>';
		}
		
		echo '<h4>Setting up new data</h4>';
		//default dirs
		$dirs = array(
			'Content/configuration/',
			'Content/QSpore/',
			'Content/download/',
			'Content/images/',
			'Content/logs/',
			'Content/stylesheets/',
			'Content/temp/',
			'Content/templates/'
		);
		if(!is_dir('Content'))
		{
			@mkdir('Content');
		}
		foreach ($dirs as $dir) 
		{
			if(!is_dir($dir))
			{
				@mkdir($dir);
			}
			if(!is_dir($dir))
			{
				throw new Exception('Could not create folder "'.$dir.'"');
			}	
			if(!is_writable($dir))
			{
				throw new Exception($dir.' is not writeable');
			}	
		}
		///////////
		//Config
		///////////


		SetupConfiguration::setup($config);
		$this->write(
			'Content/configuration/system.php',
			self::HEADER.serialize($config)
		);
		$this->write(
			'Content/QSpore/index.php',
			"<?php exit(); ?>\na:1:{s:4:\"page\";a:3:{i:0;b:1;i:1;s:10:\"MError:404\";i:2;s:10:\"MError:404\";}}"
		);
		//write group file groups.php
		$data = array("Administrator" => '',"CMS" => '',"Create" => '',"Delete" => '',"Edit" => '',"Rename" => '',"PHP" => '');
		$this->write(
			'Content/configuration/groups.php', 
			self::HEADER.serialize($data)
		);
		global $_SERVER;
		//write users file users.php
		$pw = substr(base64_encode(md5(getmypid().'-'.$_SERVER['REMOTE_ADDR'].'-'.strval(microtime(true)*100).'-'.crc32(rand()))),0,8);
		//$pw = substr($pw,rand(0,14),4).substr($pw,rand(14,28),4);
		echo '<code style="border:1px solid #2e3436;background:#eeeeec;padding:10px;display:block;margin:2px;">Your Username is: "admin"<br />Your password is: "'.$pw.'"</code>';
		
		require_once('System/Component/System/SUser.php');
		$user = new SUser($pw, 'Administrator');
        $user->joinGroups("Administrator");
		$dat = array('admin' => $user);
		$this->write(
			'Content/configuration/users.php', 
			self::HEADER.
			serialize($dat)
		);
//		      'a:1:{s:5:"admin";'.
//                'O:5:"SUser":11:{'.
//                's:16:"password:private";s:32:"'.md5($pw).'";'.
//                's:16:"realName:private";s:13:"Administrator";'.
//                's:13:"email:private";b:0;'.
//                's:14:"groups:private";a:1:{i:0;s:13:"Administrator";}'.
//                's:19:"permissions:private";a:0:{}'.
//                's:18:"attributes:private";a:2:{s:21:"last_management_login";i:0;s:22:"management_login_count";i:0;}'.
//                's:20:"primaryGroup:private";s:0:"";'.
//                's:30:"applicationPreferences:private";a:0:{}'.
//                's:38:"applicationPreferenceKeyForces:private";a:0:{}'.
//                's:35:"applicationPreferenceForces:private";a:0:{}'.
//                's:24:"preferenceForced:private";b:0;}}'
	}
}
?>