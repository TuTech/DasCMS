<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-12-12
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Plugin
 */
class UCFileUpload 
    implements ITemplateSupporter, IGlobalUniqueId 
{
    const GUID = 'org.bambuscms.plugins.fileupload';
    public function getClassGUID()
    {
        return self::GUID;
    }
    
    protected static $uploaded = false;
    protected static $message = '';
    protected static $optTags = array();
    
    public function setOptionalTags($param)
    {
        if(!empty($param['tags']))
        {
            self::$optTags = Controller_Tags::parseString($param['tags']);
        }
    }
    
    public function processUpload($param)
    {
        if(!is_array($param))
        {
            $param = array();
        }        
        $tags = '';
        $pub = false;
        if(array_key_exists('tags', $param))
        {
            $tags = $param['tags'];
        }
        if(array_key_exists('publish', $param))
        {
            $pub = !empty($param['publish']);
        }
        foreach (self::$optTags as $otag)
        {
            if(RSent::hasValue('-tag-'.$otag))
            {
                $tags .= ' '.$otag;
            }
        }
        if(RFiles::has('CFile') && PAuthorisation::has('org.bambuscms.content.cfile.create'))
        { 
        	try
        	{
        	    $f = CFile::Create('');
        	    $f->setTags($tags);
        	    if($pub)
        	    {
        	        $f->setPubDate(time());
        	    }
        	    $f->save();
        	    self::$uploaded = true;
        	    self::$message = 'uploaded';
        	    SNotificationCenter::report('message', 'file_uploaded');
        	}
        	catch(Exception $e)
        	{
        	    self::$message = 'upload_failed';
        	    SNotificationCenter::report('warning', 'upload_failed'.$e->getMessage());
        	}
        }
    }
    
    public function uploadMessage($param)
    {
        $html = '';
        if(RURL::has('_upload'))
        {
            $succ = (RURL::get('_upload') == '1') ? 'ok' : 'failed';
            $html = sprintf(
                '<div class="UCFileUpload_upload_%s">%s</div>'
				,$succ
                ,array_key_exists($succ.'Message', $param)
                    ? $param[$succ.'Message']
                    : (SLocalization::get('upload_'.$succ))
            );
        }
        if(self::$message != '')
        {
            $succ = self::$uploaded ? '1' : '-';
            $html = '<script type="text/javascript">top.location.href = "'.
                SLink::base().SLink::link(array('_upload'=>$succ))
                .'";</script>';
        }
        return $html;
    }
    
    public function uploadForm($param)
    {
        $html = '';
        if(PAuthorisation::has('org.bambuscms.content.cfile.create'))
        {
            if(!is_array($param))
            {
                $param = array();
            }
            $maxSize = (array_key_exists('maxSize', $param)) ? $param['maxSize'] : '1000000000';
            $html = '<div class="UCFileUpload_form">';
            $html .= sprintf('<p>%s</p>', (array_key_exists('text', $param)) ? $param['text'] : '');
            $html .= sprintf('<form action="%s" method="post"  enctype="multipart/form-data">', SLink::buildURL());
            $html .= sprintf('<input type="hidden" name="MAX_FILE_SIZE" value="%s" />', $maxSize);
            if(count(self::$optTags))
            {
                $html .= '<ul class="optionalTags"><h3>'.
                        String::htmlEncode((array_key_exists('optTagsText', $param)) ? $param['optTagsText'] : 'Optionale Tags:')
                        .'</h3>';
                foreach (self::$optTags as $otag)
                {
                    $opt = String::htmlEncode($otag);
                    $html .= sprintf(
                    	'<li><input type="checkbox" name="-tag-%s" id="-tag-%s" /><label for="-tag-%s">%s</label></li>'
                                                              ,$opt        ,$opt                  ,$opt,$opt 
                    );
                }
                $html .= '</ul>';
            }
            
            $html .= '<input type="file" name="CFile" />';
            $html .= sprintf('<input type="submit" value="%s" />', (array_key_exists('submitText', $param)) ? $param['submitText'] : 'OK');
            $html .= '</form></div>';
        }
        return $html;
    }
    
/////////////////////////////////
    protected static $functions = array(
        'processUpload' => array('tags', 'publish'),
        'uploadMessage' => array('okMessage', 'failedMessage'),
        'uploadForm' => array('maxSize', 'text', 'submitText', 'optTagsText'),
        'setOptionalTags' => array('tags')
    );

    /**
     * return an array with function => array(0..n => parameters [, 'description' =>  desc])
     *
     * @return array
     */
    public function templateProvidedFunctions()
    {
        return self::$functions;
    }
    
    /**
     * return an array with attributeName => description
     *
     * @return array
     */
    public function templateProvidedAttributes()
    {
        return array();
    }

    /**
	 * @param string $function
	 * @return boolean
	 */
	public function templateCallable($function)
	{
	    return in_array($function, array_keys(self::$functions));
	}
	
	/**
	 * @param string $function
	 * @param array $namedParameters
	 * @return string in utf-8
	 */
	public function templateCall($function, array $namedParameters)
	{
	    if(!$this->templateCallable($function))
	    {
	        throw new XTemplateException('called undefined function');
	    }
        return $this->{$function}($namedParameters);
	}
	
	/**
	 * @param string $property
	 * @return string in utf-8
	 */
	public function templateGet($property)
	{
	    return '';
	}
}
?>