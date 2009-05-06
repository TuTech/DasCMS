<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2009-04-28
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Query-MySQL
 */
class QCSearch extends BQuery 
{
    /**
     * @return DSQLResult
     */
    public static function getFeatureIds(array $features)
    {
        if(count($features) == 0)
        {
            throw new XArgumentException('no features given');
        }
        $DB = BQuery::Database();
        $esc = array();
        foreach ($features as $f)
        {
            $esc[] = $DB->escape($f);
        }
        $sqls = array();
        $ops = array(//weighting does not work by now
           // '0.1' => array('%', '%'),
            '0.4' => array('%', ''),
        	'0.7' => array('', '%'),
           	'1.0' => array('', '')
        );
        foreach ($ops as $score => $op)
        {
            $sqls[] = "(SELECT DISTINCT
            		searchFeatureID, '".$score."' AS 'score'
            	FROM SearchFeatures
            	WHERE SearchFeatures.searchFeature LIKE '".
                    $op[0].implode($op[1]."' OR SearchFeatures.searchFeature LIKE '".$op[0], $esc).$op[1]."')";
        }
        $sql = implode(' UNION ', $sqls)." ORDER BY score ASC";
		return $DB->query($sql, DSQL::NUM);
    }
    
    private static function normalize(array $scores, $prefSmall = false)
    {
        $normalized = array();
        $limSero = 0.000001;
        $values = array_values($scores);
        if($prefSmall)
        {
            $min = call_user_func_array('min', $values);
            foreach ($scores as $content => $score)
                $normalized[$content] = floatval($min)/max($score, $limSero);
        }
        else
        {
            $values[] = $limSero;//to prevent max == 0
            $max = call_user_func_array('max', $values);
            foreach ($scores as $content => $score)
                $normalized[$content] = floatval($score)/$max;
        }
        return $normalized;
    }
    
    private static function makeScoreArray(array $contents)
    {
        $scores = array();
        foreach ($contents as $c)
            $scores[$c] = 0.000001;
        return $scores;
    }
    
    private static function outdatedIndexScore(array $contents, array $words)
    {
        $contents = array_map('intval', $contents);
        $words = array_map('intval', $words);
        $sql = "SELECT contentREL, 1
        			FROM SearchIndexOutdated
        			WHERE 
        			contentREL IN (".implode(',', $contents).")";
        $erg = self::makeScoreArray($contents);
        $res = BQuery::Database()->query($sql, DSQL::NUM); 
        while($row = $res->fetch())
        {
            $erg[$row[0]] = $row[1];
        }
        $res->free();
        return self::normalize($erg, true);
    }
    
    private static function freqScore(array $contents, array $words)
    {
        $contents = array_map('intval', $contents);
        $words = array_map('intval', $words);
        $sql = "SELECT contentREL, SUM(featureCount)
        			FROM SearchIndex
        			WHERE 
        			searchFeatureREL IN (".implode(',', $words).")
        			AND contentREL IN (".implode(',', $contents).")
        			GROUP BY contentREL";
        $erg = self::makeScoreArray($contents);
        $res = BQuery::Database()->query($sql, DSQL::NUM); 
        while($row = $res->fetch())
        {
            $erg[$row[0]] = $row[1];
        }
        $res->free();
        return self::normalize($erg, false);
    }
    
    private static function punishAgeScore(array $contents, array $words)
    {
        $contents = array_map('intval', $contents);
        $words = array_map('intval', $words);
        $sql = "SELECT contentREL, DATEDIFF(NOW(),MAX(changeDate))
        			FROM Changes
        			WHERE 
        			contentREL IN (".implode(',', $contents).")
        			GROUP BY contentREL";
        $erg = self::makeScoreArray($contents);
        $res = BQuery::Database()->query($sql, DSQL::NUM); 
        while($row = $res->fetch())
        {
            $erg[$row[0]] = $row[1];
        }
        $res->free();
        return self::normalize($erg, true);
    }
    
    private static function attributeScore(array $contents, array $words)
    {
        $contents = array_map('intval', $contents);
        $words = array_map('intval', $words);//let features fade out if they are too common --> that EXP() stuff
        $sql = "SELECT 
        				SearchIndex.contentREL,
        				EXP((POW(SUM(SearchIndex.featureCount),2)*-1)/200)*AVG(SearchAttributeWeights.weight),
        				CONCAT_WS(',',contentREL,SearchAttributeWeights.searchAttributeWeightID) AS contAtt
        			FROM SearchIndex
        			LEFT JOIN SearchAttributeWeights ON (searchAttributeWeightREL = searchAttributeWeightID)
        			WHERE 
        			searchFeatureREL IN (".implode(',', $words).")
        			AND contentREL IN (".implode(',', $contents).")
        			GROUP BY contAtt";
        $erg = self::makeScoreArray($contents);
        $count = array();
        $res = BQuery::Database()->query($sql, DSQL::NUM); 
        while($row = $res->fetch())
        {
            if(!isset($count[$row[0]]))
            {
                $count[$row[0]] = 0;
            }
            $count[$row[0]]++;
            $erg[$row[0]] += $row[1];
        }
        $res->free();
        //the more attributes contributing here, the better
        foreach ($count as $cid => $cnt)
            $erg[$cid] += $cnt;
        return self::normalize($erg, false);
    }
    
    public static function scoredContents(array $ids)
    {
        $words = array();
        $totalscores = array();
        $DB = BQuery::Database();
        
        //just get the words
        foreach ($ids as $id => $s)
            $words[] = intval($id);
            
        //get contents
        $sql= sprintf("SELECT DISTINCT
  					contentREL, 0 as 'scoreByContentType'
				FROM SearchIndex
                    LEFT JOIN SearchAttributeWeights ON 
                    	(SearchAttributeWeights.searchAttributeWeightID = SearchIndex.searchAttributeWeightREL)
                WHERE 
                    searchFeatureREL IN (%s)"
                    ,implode(',', $words)
        );       
        $res = $DB->query($sql, DSQL::NUM);
        while ($row = $res->fetch())
        {
            $totalscores[$row[0]] = $row[1];
        }
        $res->free();
        $contents = array_keys($totalscores);
        //weightings
        $weights = array(
            '0.2'  => self::freqScore($contents, $words),
            '0.5' => self::punishAgeScore($contents, $words),
        	'0.3'  => self::outdatedIndexScore($contents, $words),
        	'1.0'  => self::attributeScore($contents, $words)
        );
        
        //apply weightings to each content
        foreach($weights as $weight => $scores)
        {
            foreach(array_keys($totalscores) as $content)
            {
                $totalscores[$content] += $weight*$scores[$content];
            }
        }
        $totalscores = self::normalize($totalscores, false);
        
        //prepare output
        $sql = "CREATE TEMPORARY TABLE TMPSearchResults (
                    contentID INTEGER,
                    score DOUBLE
                )";
        $DB->queryExecute($sql);
        if(count($totalscores))
        {
            $sql = "INSERT INTO TMPSearchResults (contentID, score) VALUES ";
            $sep = '';
            foreach($totalscores as $content => $score)
            {
                $sql .= sprintf("%s(%d, %f) ", $sep, $content, $score);
                $sep = ',';
            }
            $DB->queryExecute($sql);
        }    
        
    }
    
    public static function getScoredContent($items = 15, $page = 1)
    {
        $sql = sprintf(
        	"SELECT 
    				Contents.title,
      				Contents.subtitle,
      				Contents.description,
      				Aliases.alias,
      				AVG (TMPSearchResults.score) * COUNT(TMPSearchResults.contentID) / COUNT(*) AS importance
  				FROM TMPSearchResults
  				LEFT JOIN Contents USING (contentID) 
  				LEFT JOIN Aliases ON (Contents.primaryAlias = Aliases.aliasID)
			WHERE 
				Contents.pubDate > '0000-00-00 00:00:00'
				AND Contents.pubDate <= NOW()
				GROUP BY Aliases.alias
			ORDER BY importance DESC LIMIT %d OFFSET %d"
            ,$items+1
            ,$items*($page-1)
        );
        return BQuery::Database()->query($sql, DSQL::NUM); 
    }
    
    public static function fetchConfig($contentId)
    {
        $sql = sprintf("SELECT `option`, `mode`, caption FROM SearchConfig WHERE contentREL = %d", $contentId);
        return BQuery::Database()->query($sql, DSQL::NUM); 
    }
    
    public static function dumpConfig($contentId, array $config)
    {
        $DB = BQuery::Database();
        $DB->queryExecute(sprintf("DELETE FROM SearchConfig WHERE contentREL = %d", $contentId));
        if(count($config) > 0)
        {
            $sql = "INSERT INTO SearchConfig (contentREL, `option`, `mode`, caption) ";
            $dsTpl = sprintf("\n(%d, %%d, %%d, '%%s')", $contentId);
            $sep = '';
            foreach($config as $dataset)
            {
                $sql .= $sep.sprintf($dsTpl, $dataset[0], $dataset[1], $dataset[2]);
                $sep = ', ';
            }
            $DB->queryExecute($sql);
        }
    }
}
?>