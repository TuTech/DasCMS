<?php
abstract class _Import_HTTP extends _Import 
{
    /**
     * @return string
     */
    protected function httpGet($uri)
    {
        $data = '';
        $fp = fopen($uri, 'r');
        if(!$fp)
        {
            throw new XFileNotFoundException('could not open uri', $uri);
        }
        while(!feof($fp))
        {
            $data .= fread($fp, 1024);
        }
        fclose($fp);
        return $data;
    }
}
?>