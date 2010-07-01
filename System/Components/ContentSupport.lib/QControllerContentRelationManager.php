<?php
class QControllerContentRelationManager extends BQuery
{
	/**
	 * @param string $class
	 * @param string $owner
	 * @param string $content
	 * @return int
	 */
	public static function createChain($class, $owner, $content){
		$db = parent::Database();
		$sql = "INSERT IGNORE INTO relContentsClassesChainedContents
					(
						chainingClassREL,
						ownerContentREL,
						chainedContentREL
					)VALUES(
						(SELECT classID    FROM Classes WHERE class = '%s'),
						(SELECT contentREL FROM Aliases WHERE alias = '%s'),
						(SELECT contentREL FROM Aliases WHERE alias = '%s')
					)";
		$sql = sprintf($sql, $db->escape($class), $db->escape($owner), $db->escape($content));
		return $db->queryExecute($sql);
	}

	/**
	 *
	 * @param string $class
	 * @return DSQLResult
	 */
	public static function getAllChainedToClass($class){
		$db = parent::Database();
		$sql = "SELECT Aliases.alias
					FROM relContentsClassesChainedContents
						LEFT JOIN Contents ON (relContentsClassesChainedContents.chainedContentREL = Contents.contentID)
						LEFT JOIN Aliases ON (Contents.primaryAlias = Aliases.aliasID)
					WHERE relContentsClassesChainedContents.chainingClassREL = (SELECT classID FROM Classes WHERE class = '%s')";
		return $db->query(sprintf($sql, $db->escape($class)), DSQL::NUM);

	}

	public static function getAllChainedToClassAndContent($class, $content){
		$db = parent::Database();
		$sql = "SELECT Aliases.alias
					FROM relContentsClassesChainedContents
						LEFT JOIN Contents ON (relContentsClassesChainedContents.chainedContentREL = Contents.contentID)
						LEFT JOIN Aliases ON (Contents.primaryAlias = Aliases.aliasID)
					WHERE
						relContentsClassesChainedContents.ownerContentREL = (SELECT contentREL FROM Aliases WHERE alias = '%s')
						AND
						relContentsClassesChainedContents.chainingClassREL = (SELECT classID FROM Classes WHERE class = '%s')";
		return $db->query(sprintf($sql, $db->escape($content), $db->escape($class)), DSQL::NUM);
	}

	//getClassesRetaining
	public static function getClassesChaining($content){
		$db = parent::Database();
		$sql = "SELECT Classes.class
					FROM relContentsClassesChainedContents
						LEFT JOIN Aliases ON (relContentsClassesChainedContents.chainedContentREL = Aliases.contentREL)
						LEFT JOIN Classes ON (relContentsClassesChainedContents.chainingClassREL = Classes.classID)
					WHERE
						Aliases.alias = '%s'";
		return $db->query(sprintf($sql, $db->escape($content)), DSQL::NUM);
	}

	//getRetainees
	public static function getContentsChaining($content){
		$db = parent::Database();
		$sql = "SELECT ChainerAliases.alias
					FROM relContentsClassesChainedContents
						LEFT JOIN Aliases ON (relContentsClassesChainedContents.chainedContentREL = Aliases.contentREL)
						LEFT JOIN Contents ON (relContentsClassesChainedContents.chainedContentREL = Contents.contentID)
						LEFT JOIN Aliases AS ChainerAliases ON (Contents.primaryAlias = ChainerAliases.aliasID)
					WHERE
						Aliases.alias = '%s'";
		return $db->query(sprintf($sql, $db->escape($content)), DSQL::NUM);
	}

	public static function getRetainCount($content){
		$db = parent::Database();
		$sql = "SELECT COUNT(*)
					FROM relContentsClassesChainedContents
					WHERE relContentsClassesChainedContents.chainedContentREL = (SELECT contentREL FROM Aliases WHERE alias = '%s')";
		return $db->query(sprintf($sql, $db->escape($content)), DSQL::NUM);
	}

	public static function isRetained($content, $owner, $class){
		$db = parent::Database();
		$sql = "SELECT COUNT(*)
					FROM relContentsClassesChainedContents
					WHERE
						relContentsClassesChainedContents.chainedContentREL = (SELECT contentREL FROM Aliases WHERE alias = '%s')";
		$sql = sprintf($sql, $db->escape($content));
		if($owner != null){
			$sql .= sprintf(
					"	AND relContentsClassesChainedContents.ownerContentREL = (SELECT contentREL FROM Aliases WHERE alias = '%s')",
					$db->escape($owner)
			);
		}
		if($class != null){
			$sql .= sprintf(
					"	AND relContentsClassesChainedContents.chainingClassREL = (SELECT classID FROM Classes WHERE class = '%s')",
					$db->escape($class)
			);
		}
		return $db->query(sprintf($sql, $db->escape($content)), DSQL::NUM);
	}

	public static function deleteClassChains($class){
		$db = parent::Database();
		$sql = "DELETE
					FROM relContentsClassesChainedContents
					WHERE
						chainingClassREL = (SELECT classID FROM Classes WHERE class = '%s')";
		$sql = sprintf($sql, $db->escape($class));
		return $db->queryExecute($sql);
	}

	public static function deleteClassChainsForOwner($class, $owner){
		$db = parent::Database();
		$sql = "DELETE 
					FROM relContentsClassesChainedContents
					WHERE
						chainingClassREL = (SELECT classID FROM Classes WHERE class = '%s')
						AND
						ownerContentREL = (SELECT contentREL FROM Aliases WHERE alias = '%s')";
		$sql = sprintf($sql, $db->escape($class), $db->escape($owner));
		return $db->queryExecute($sql);
	}

	public static function deleteOwnerChains($owner){
		$db = parent::Database();
		$sql = "DELETE
					FROM relContentsClassesChainedContents
					WHERE
						ownerContentREL = (SELECT contentREL FROM Aliases WHERE alias = '%s')";
		$sql = sprintf($sql, $db->escape($owner));
		return $db->queryExecute($sql);
	}

	public static function deleteChain($class, $owner, $content){
		$db = parent::Database();
		$sql = "DELETE
					FROM relContentsClassesChainedContents
					WHERE
						chainingClassREL = (SELECT classID FROM Classes WHERE class = '%s')
						AND
						ownerContentREL = (SELECT contentREL FROM Aliases WHERE alias = '%s')
						AND
						chainedContentREL = (SELECT contentREL FROM Aliases WHERE alias = '%s')";
		$sql = sprintf($sql, $db->escape($class), $db->escape($owner), $db->escape($content));
		return $db->queryExecute($sql);
	}


}

?>
