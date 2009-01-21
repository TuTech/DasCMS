<?php
class Import_IMAP_Mail extends _Import_IMAP
{
    private 
        $from,  
        $to, 
        $date,
        $messageId,
        $subject, 
        $text, 
        $rawText;
    
    private function addressString($addesses)
    {
        if(is_array($addesses))
        {
            $adrs = array();
            foreach ($addesses as $address) 
            {
            	$name = isset($address->personal) ? $address->personal.' ' : '';
            	$adrs[] = sprintf("%s<%s@%s>", $name, $address->mailbox, $address->host);
            }
            return implode('; ', $adrs);
        }
        else
        {
            return '';
        }
    }
    
    public function getSubject()
    {
        return $this->subject;
    }
    public function getText()
    {
        return $this->text;
    }
    public function getFrom()
    {
        return $this->from;
    }
    public function getTo()
    {
        return $this->to;
    }
    public function getDate()
    {
        return $this->date;
    }
    
    public function __construct($from, $to, $date, $message_id, $subject, $text)
    {
        $this->from = $this->addressString($from);
        $this->to = $this->addressString($to);
        $this->date = strtotime($date);
        $this->messageId = $message_id;
//        echo '<hr><pre>';
//        echo $text;
//        echo '</pre><hr>';
        $this->subject = mb_convert_encoding($subject, 'UTF-8',"utf-8, auto");
        $text = mb_convert_encoding($text, 'UTF-8',"utf-8, auto");
        $text = preg_replace('/=(\r\n|\r|\n)/misu', '', $text);
        $text = preg_replace_callback(
        	'/=([A-Z0-9][A-Z0-9])/misu', 
            create_function('$m' ,'$a = hexdec($m[1]);return mb_convert_encoding(sprintf("%c",$a),"utf-8", "iso-8859-15, auto");'),
            $text);
        
        $this->rawText = $text;
        //html
        //printf('<h2>%s</h2>', $this->subject);
        if(preg_match('/<html/misu', $text))
        {
            //strip <body...> and things before it
            $text = preg_replace('/\A.*<body[^>]*>/misu', '', $text);
            //strip </body> and things after it
            $text = preg_replace('/<\/body[\s]*>.*\D/misu', '', $text);
            //remove signatures from apple mail and thunderbird @todo remove outlook signatures
            $text = preg_replace('/(<div id="AppleMailSignature"|<pre class="moz-signature").*\D/misu', '' , $text);
        }
        else
        //text
        {
            $text = preg_replace('/[\r\n]--[\s]*[\r\n].*\D/misu', '', $text);
            //replace \r and \r\n with \n
            $text = preg_replace('/\r\n?/misu', "\n", $text);
            $lines = explode("\n", $text);
            $quoteLvl = 0;
            $text = '<div class="Unformated-Text">';
            foreach($lines as $line)
            {
                preg_match('/^([>]*)(.*)$/miu', $line, $match);
                $q = mb_strlen($match[1], 'utf-8');
                $line = $match[2]."\n";
                $str = '';
                if($q > $quoteLvl)
                {
                    $str = '<div class="quote">';
                }
                elseif($q < $quoteLvl)
                {
                    $str = '</div>';
                }
                for($i = 0; $i < abs($q-$quoteLvl); $i++)
                {
                    $text .= $str;
                }
                $text .= $line;
                $quoteLvl = $q;
            }
            str_repeat('</div>', $quoteLvl);
            $text .= '</div>';
        }
        //@todo add qoute tags for ">cite"
        $this->text = $text;
//        $text = preg_replace('/(<\/[^>]>)[\s]*</', "$1\n<", $text);
//        echo ($text);
    }
    
}
?>