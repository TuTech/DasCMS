Init SQLite DB
<?php
error_reporting(E_ALL|E_STRICT);
$dbfile = "../Content/BSystem/bambus.sqlite";
unlink($dbfile);
$DB = new SQLiteDatabase($dbfile, 0666, $err);

print $err;

echo "<br /><b>Managers</b>...";
var_dump($DB->queryExec("CREATE TABLE Managers(
  managerID INTEGER PRIMARY KEY,
  manager TEXT UNIQUE
);"));

echo "<br /><b>ContentIndex</b>...";
var_dump($DB->queryExec("CREATE TABLE ContentIndex(
  contentID INTEGER PRIMARY KEY,
  managerREL INTEGER,
  managerContentID TEXT UNIQUE,
  title TEXT,
  pubDate INTEGER,
  summary TEXT
);"));

echo "<br /><b>Changes</b>...";
var_dump($DB->queryExec("CREATE TABLE Changes(
  changeID INTEGER PRIMARY KEY,
  contentREL INTEGER,
  title TEXT,
  size INTEGER,
  changeDate INTEGER,
  username TEXT
);"));

echo "<br /><b>Tags</b>...";
var_dump($DB->queryExec("CREATE TABLE Tags(
  tagID INTEGER PRIMARY KEY,
  tag TEXT UNIQUE,
  blocked INTEGER
);"));

echo "<br /><b>Aliases</b>...";
var_dump($DB->queryExec("CREATE TABLE Aliases(
  aliasID INTEGER PRIMARY KEY,
  alias TEXT UNIQUE,
  active INTEGER,
  contentREL INTEGER
);"));

echo "<br /><b>relContentTags</b>...";
var_dump($DB->queryExec("CREATE TABLE relContentTags(
  contentREL INTEGER,
  tagREL INTEGER
);"));

//history: change date, user, filesize (, diff?)
echo "<br />";
echo "<br /><b>Table dump:</b><br /><pre>";

$result = $DB->query("SELECT name,sql FROM sqlite_master
WHERE type='table'
ORDER BY name;");
while($arr = $result->fetch())
	print ("<b>".$arr[0])."</b>\n".$arr[1]."\n\n";
echo "</pre><br />";
?>
DONE