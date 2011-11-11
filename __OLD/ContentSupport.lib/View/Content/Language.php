<?php
/**
 * Display data from @lang: tags; preferable ISO-639-2-codes
 *
 * @author lse
 */
class View_Content_Language
	extends
		_View_Content_Base
	implements
		Interface_View_DisplayXHTML,
		Interface_View_Content
{

    private static $codemap = null;
    private static $langList = array();

    protected function lookupCode($codeToLookup){
		//load lookup file
        if(self::$codemap == null){
            $contents = file(Core::PATH_SYSTEM_RESOURCES.'ISO-639-2_utf-8.txt');
            self::$codemap = array();
            foreach($contents as $line){
                $codes = array();
                list($codes[0], $_codes, $codes[1], $name, $desc) = explode('|', $line);
                $pos = count(self::$langList);
                self::$langList[$pos] = $name;
                foreach($codes as $code){
                    if(!empty($code)){
                        self::$codemap[$code] = $pos;
                    }
                }
            }
        }

		//default return value = input
        $ret = $codeToLookup;

		//split codes
        $codeParts = explode('_', $codeToLookup);
        while(count($codeParts)){
            $p = array_shift($codeParts);

            if(isset(self::$codemap[$p])){
                $ret = self::$langList[self::$codemap[$p]];
                break;
            }
        }
        return $ret;
    }

	public function toXHTML() {
		$val = '';
		if($this->shouldDisplay()){
			$tags = $this->content->getTags();
            $lang = null;
            foreach($tags as $tag){
                $tag = strtolower($tag);
                if(substr($tag,0,6) == '@lang:'){
                    $lang = substr($tag,6);
                }
            }
            if($lang){
                $lang = $this->lookupCode($lang);
    			$val = $this->wrapXHTML('Language', $lang);
            }
		}
		return $val;
	}
}
?>
