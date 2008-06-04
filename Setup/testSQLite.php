<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"/><style type="text/css">
table{empty-cells: show;}
</style></head><body>
Init SQLite DB
<br /><pre>
<?php
error_reporting(E_ALL|E_STRICT);
$dbfile = "../Content/DSQLite/bambus.sqlite.php";
$DB = new SQLiteDatabase($dbfile, 0600, $err);
print $err;
/* * /
$managers = array('MLinkTo', 'MPageManager');
$contents = array(
"8fbd1a3d138e78196ea9e906f2698c73" => "hoggen",
"175b915dee75030041d016b09b130b2b" => "so...",
"f31319028361ef3cdec202808c90e967" => "foojii",
"5a447c4ac56156954cf53427d0db94a4" => "new page foo & dingens da",
"dd7b29e2af3043e861e280449c16d9e5" => "foggenheimer",
"dccc87176cd1f5c6d6f48af3b846449a" => "bfdsjfdbjkkfdj--dd--f",
"0ef76ffc293c5d7795e0563b3b47cf62" => "Fedora 8 - test",
"3504571af57b3ca165cddf1aad8de7fb" => "create test",
"668601a1d287a7db82077a64b89b0a90" => "urg",
"0d824b195e39a979be307d8ad4945e9f" => "blah ding");
$tags = array(
"8fbd1a3d138e78196ea9e906f2698c73" => array('sno', 'nog', time()),
"175b915dee75030041d016b09b130b2b" => array('daggen','dögger'),
"f31319028361ef3cdec202808c90e967" => array('sno', 'blah', time()),
"5a447c4ac56156954cf53427d0db94a4" => array('sno', 'blah', 'dax', time()),
"dd7b29e2af3043e861e280449c16d9e5" => array("foggenheimer",'nog'),
"dccc87176cd1f5c6d6f48af3b846449a" => array("blah"),
"0ef76ffc293c5d7795e0563b3b47cf62" => array(),
"3504571af57b3ca165cddf1aad8de7fb" => array('daggen', 'blah'),
"668601a1d287a7db82077a64b89b0a90" => array("urg"),
"0d824b195e39a979be307d8ad4945e9f" => array("blah","ding", 'sno')
);

////////
////////Manager data
////////

foreach ($managers as $manager) 
{
	$result = $DB->queryExec("INSERT OR IGNORE INTO Managers (manager) VALUES ('".$manager."');");
}
$manager = 'MPageManager';
$res = $DB->query("SELECT * FROM Managers WHERE 1;");
while($arr = $res->fetch())
{
	
	$managers[$arr[1]] = $arr[0];
}
print_r($managers);

////////
////////Content data
////////

function readIndex($id)
{
	$data = '';
	if(file_exists($id.'.html'))
	{
		$data = 
			substr(
				preg_replace(
					"/[\\s\\n\\r\\t]+/",
					" ",
					strip_tags(
						implode(
							file($id.'.html')
						)
					)
				),
				1500,
				1024
			);
	}
	return $data;
}

foreach ($contents as $contentID => $title) 
{
	echo "<b> ". $title."</b><br />";
	$managers = array();
	$DB->queryExec("BEGIN TRANSACTION;");

	$result = $DB->query("SELECT COUNT(managerContentID) FROM ContentIndex WHERE managerContentID = '".$contentID."'");
	$dat = $result->fetch();
	if($dat[0] == 0)
	{
		//does not exist => insert
		echo "<p>inserting ". $title."&gt; ";
		echo $DB->queryExec("INSERT INTO ContentIndex ".
			"(managerREL, managerContentID, title, pubDate, summary)VALUES((SELECT managerID FROM Managers WHERE manager = 'MPageManager'),'".
			sqlite_escape_string($contentID)."', '".
			sqlite_escape_string($title)."',".(time()-rand(0,time()/2)).",'".sqlite_escape_string(readIndex($contentID))."');", $err) ? '...done' : '...failed ('.$err.')';
		echo "</p>";
	}
	else
	{
		//update 
		echo "<p>updating ". $title."&gt; ";
		$sql = sprintf("UPDATE ContentIndex SET title='%s', pubDate=%d, summary='%s' WHERE managerContentID LIKE '%s';"
			,sqlite_escape_string(str_rot13($title))
			,time()+rand(0,1000000000)-500000000
			,sqlite_escape_string('blah dingens')
			,sqlite_escape_string($contentID)
			);
		echo $DB->queryExec($sql, $err) ? '...done' : '...failed ('.$err.')';
		echo "</p>";	
	}
	echo "<br />";
	$sql = sprintf("INSERT INTO Changes (contentREL, title, size, changeDate, username)".
					"VALUES((SELECT contentID FROM ContentIndex WHERE managerContentID = '%s'), '%s', '%d', '%d', '%s');"
		,sqlite_escape_string($contentID)
		,sqlite_escape_string($title)
		,rand(1,1000000)
		,time()
		,sqlite_escape_string($_SERVER['REMOTE_ADDR'])
	);
	echo $DB->queryExec($sql, $err) ? '...done' : '...failed ('.$err.')';
	$DB->queryExec("COMMIT;");
}

////////
////////tag data
////////

foreach ($tags as $contentID => $tags) 
{
	$DB->queryExec("BEGIN TRANSACTION;");
	//remove all rels to cid
	echo '<p>tagging...';
	$sql = sprintf("DELETE FROM relContentTags WHERE contentREL = "
		."(SELECT contentID FROM ContentIndex WHERE managerContentID = '%s')", sqlite_escape_string($contentID));
	echo $DB->queryExec($sql, $err) ? 'cleaned...' : 'clean failed ('.$err.')...';
	foreach ($tags as $tag) 
	{
		//insert ignore tag
		$sql = sprintf("INSERT OR IGNORE INTO Tags (tag)VALUES('%s')"
			,sqlite_escape_string($tag));
		echo $DB->queryExec($sql, $err) ? 'inserted...' : 'insert failed ('.$err.')...';
		$sql = sprintf("INSERT INTO relContentTags (contentREL, tagREL)VALUES(
				(SELECT contentID FROM ContentIndex WHERE managerContentID = '%s'),
				(SELECT tagID FROM Tags WHERE tag LIKE '%s')
			)"
			,sqlite_escape_string($contentID)
			,sqlite_escape_string($tag)
		);
		echo $DB->queryExec($sql, $err) ? 'linked...' : 'linking failed ('.$err.')...';
		//link tag
	}
	echo 'done</p>';
	$DB->queryExec("COMMIT;");
}

/**/

echo '<table border="1"><th>Manager</th><td>ID</th></tr>';
$res = $DB->query("SELECT * FROM Managers WHERE 1;");
while($arr = $res->fetch())
{
	echo '<tr><td>', $arr[1], '</td><td>', $arr[0],'</td></tr>';
}
echo '</table>';



echo '<table border="1">';
$res = $DB->query("SELECT * FROM Aliases WHERE 1;", SQLITE_ASSOC);
$first = true;
while($arr = $res->fetch())
{
	if($first)
	{
		echo '<tr>';
		foreach ($arr as $k => $v) 
		{
			echo '<th>',$k,'</th>';
		}
		echo '<tr>';
	}
	$first = false;
	echo '<tr>';
	foreach ($arr as $k => $v) 
	{
		echo '<th>',$v,'</th>';
	}
	echo '<tr>';
}
echo '</table>';
////////
////////Testing 1: get list by tags
////////
function getLatest($items = 0, $offset = 0, $ofManger = null, $tagged = "", $latestFirst = true)
{
	//Define what we want
	$sql = 	"SELECT ContentIndex.title AS Title, ContentIndex.pubDate AS PubDate, Managers.manager AS Manager,ContentIndex.managerContentID AS ContentID FROM ContentIndex LEFT JOIN Managers ".
			"ON (ContentIndex.managerREL = Managers.managerID) ";  
	
	//initialize tags
	if($tagged != "")
	{
//		$STag = STag::alloc()->init();
//		$tags = $STag->parseTagStr($tagged);
		$tagged = preg_replace("/[\\r\\n\\t,;\s]+/u", ";", $tagged);
		$tags = explode(';', $tagged);
	}
	
	//filter by manager
	$sql .= ($ofManger == null) ? "WHERE 1 " : "WHERE Managers.manager LIKE '".sqlite_escape_string($ofManger)."' ";
	
	//filter by tag
	if(isset($tags))
	{
		$esc = array();
		$tags = array_unique($tags);
		foreach ($tags as $tag) 
		{
			$sql .= "AND ContentIndex.contentID IN (SELECT relContentTags.contentREL FROM relContentTags LEFT JOIN Tags ON (Tags.tagID = relContentTags.tagREL) ".
				"WHERE Tags.tag LIKE '".sqlite_escape_string($tag)."') ";
		}
	}
	
	//order 
	$sql .= "ORDER BY ContentIndex.pubDate ".(($latestFirst) ? "DESC " : "ASC ");
	
	//limit and number of items - no limit -> offset is useless
	if($items > 0 && is_numeric($items))
	{
		//offset,limit/limit
		$sql .= ($offset > 0 && is_numeric($offset)) ? "LIMIT ".$offset.",".$items." " : "LIMIT ".$items." ";
	}
	return $sql;
}



$alldat = array();
$sql = getLatest(
	5
	,(isset($_GET['o']) && is_numeric($_GET['o']) ? $_GET['o'] : 0)
	,'MPageManager'
	,"sno blah"//" blah; snoggen , ;;;;drög"
	,true
);
echo $sql;
$res = $DB->query($sql, $err);
echo $err;
$first = true;
echo "<h2>Data tagged sno & blah</h2>";
echo '<table border="1">';
$alldat = array();
while($arr = $res->fetch(SQLITE_ASSOC))
{
	echo '<tr>';
	if($first)
	{
		foreach (array_keys($arr) as $ttl) 
		{
			echo '<th>', $ttl, '</th>';
		}
		echo '</tr><tr>';
		$first = false;
	}
	foreach ($arr as $key => $val) 
	{
		if($key == 'PubDate')
		{
			echo '<td>', date('r',$val), '</td>';
		}
		else
		{
			echo '<td>', $val, '</td>';
		}
	}
	echo $err;
	echo '</tr>';
}
echo '</table>';
	
////////
////////Testing 2: fulltext search
////////
function search($query, $needsAll = false, $items = 0, $offset = 0, $ofManger = null, $tagged = "")
{
	//Define what we want
	$sql = 	"SELECT ContentIndex.title AS Title, ContentIndex.pubDate AS PubDate, Managers.manager AS Manager,ContentIndex.managerContentID AS ContentID, ContentIndex.summary AS Summary FROM ContentIndex LEFT JOIN Managers ".
			"ON (ContentIndex.managerREL = Managers.managerID) ";  
	
	//initialize tags
	if($tagged != "")
	{
//		$STag = STag::alloc()->init();
//		$tags = $STag->parseTagStr($tagged);
		$tagged = preg_replace("/[\\r\\n\\t,;\s]+/u", ";", $tagged);
		$tags = explode(';', $tagged);
	}
	$querystr = preg_replace("/[\\r\\n\\t,;\s]+/u", ";", $query);
	$querywords = explode(';', $querystr);
	$querywords = array_unique($querywords);
	
	//filter by manager
	$sql .= ($ofManger == null) ? "WHERE 1 " : "WHERE Managers.manager LIKE '".sqlite_escape_string($ofManger)."' ";
	$concat = $needsAll ? ' AND ' : ' OR ';
//	$sql .= ' AND (';
	$qrys = array();
	foreach ($querywords as $word) 
	{
		if(strlen($word) < 3)
		{
			continue;
		}
		$qrys[] = "ContentIndex.summary LIKE '%".sqlite_escape_string($word)."%'";
	}
	if(count($qrys) > 0)
	{
		$sql .= ' AND ('.implode($concat, $qrys).') ';
	}
	else
	{
		throw new Exception("query too unspecific");
	}
//	$sql .= ') ';
	//filter by tag
	if(isset($tags))
	{
		$esc = array();
		$tags = array_unique($tags);
		foreach ($tags as $tag) 
		{
			$sql .= "AND ContentIndex.contentID IN (SELECT relContentTags.contentREL FROM relContentTags LEFT JOIN Tags ON (Tags.tagID = relContentTags.tagREL) ".
				"WHERE Tags.tag LIKE '".sqlite_escape_string($tag)."') ";
		}
	}
	
	//order 
	$sql .= "ORDER BY ContentIndex.pubDate DESC ";
	
	//limit and number of items - no limit -> offset is useless
	if($items > 0 && is_numeric($items))
	{
		//offset,limit/limit
		$sql .= ($offset > 0 && is_numeric($offset)) ? "LIMIT ".$offset.",".$items." " : "LIMIT ".$items." ";
	}
	return $sql;
}



$alldat = array();
$src = isset($_GET['s']) ? $_GET['s'] : '';//"PDF-Datei";//c't nach pdf";
echo "<h2>Data searched for \"".htmlentities($src)."\"</h2>";
try{
	$sql = search(
		$src
		,true
		,5
		,(isset($_GET['o']) && is_numeric($_GET['o']) ? $_GET['o'] : 0)
		,'MPageManager'
		,""//sno blah"//" blah; snoggen , ;;;;drög"
	);
	echo $sql;
	$res = $DB->query($sql, $err);
	echo $err;
	$first = true;
	
	echo '<table border="1">';
	$alldat = array();
	while($arr = $res->fetch(SQLITE_ASSOC))
	{
		echo '<tr>';
		if($first)
		{
			foreach (array_keys($arr) as $ttl) 
			{
				echo '<th>', $ttl, '</th>';
			}
			echo '</tr><tr>';
			$first = false;
		}
		foreach ($arr as $key => $val) 
		{
			if($key == 'PubDate')
			{
				echo '<td>', date('r',$val), '</td>';
			}
			else
			{
				echo '<td>', $val, '</td>';
			}
		}
		echo $err;
		echo '</tr>';
	}
	echo '</table>';
}
catch(Exception $e)
{
	echo "<h2>Suchkriterien nicht ausreichend</h2>";
}
////////
////////Testing 3 list all
////////
echo "<h2>All Data</h2>";
$res = $DB->query("SELECT * FROM ContentIndex WHERE 1 ORDER BY title;");
$first = true;
echo '<table border="1">';
$alldat = array();
while($arr = $res->fetch(SQLITE_ASSOC))
{
	$alldat[] = $arr;
}
foreach($alldat as $arr)
{
	echo '<tr>';
	if($first)
	{
		foreach (array_keys($arr) as $ttl) 
		{
			echo '<th>', $ttl, '</th>';
		}
		echo '<th>tags</th>';
		echo '<th>aliases</th>';
		echo '</tr><tr>';
	}
	foreach ($arr as $val) 
	{
		echo '<td>', $val, '</td>';
	}
	$tagres = $DB->query(sprintf("SELECT Tags.tag FROM Tags ".
			"LEFT JOIN relContentTags ON (relContentTags.tagREL = Tags.tagID) ".
			"WHERE relContentTags.contentREL = %d ORDER BY Tags.tag;", sqlite_escape_string($arr['contentID'])));
	$tags = array();
	echo $err;
	while($tag = $tagres->fetch(SQLITE_NUM))
	{
		$tags[] = $tag[0];
	}
	echo "<td>".implode(', ', $tags)."</td>";
	
	
////////Aliases
	
	$aliases = $DB->query(sprintf("SELECT alias, active FROM Aliases ".
		"WHERE contentREL = %d ORDER BY alias;", sqlite_escape_string($arr['contentID'])));
	$als = array();
	echo $err;
	while($al = $aliases->fetch(SQLITE_NUM))
	{
		$als[] = empty($al[1]) ? $al[0] : sprintf('<b>%s</b>', $al[0]);
	}
	echo "<td>".implode(', ', $als)."</td>";
	$first = false;
	echo '</tr>';
}
echo '</table>';
?></pre></body></html>