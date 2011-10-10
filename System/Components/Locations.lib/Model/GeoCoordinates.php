<?php
class Model_GeoCoordinates
{
    const LAT = 0;
    const LONG = 1;

    protected static $suffix = array(
        self::LAT  => array(false => 'S', true => 'N'),
        self::LONG => array(false => 'W', true => 'E')
    );

    protected $latitude, $longitude;

    public function __construct($latitude, $longitude)
    {
        $this->setCoordinate($latitude, $longitude);
    }

    public function setCoordinate($latitude, $longitude)
    {
        $this->latitude = $this->parseCoordinate($latitude);
        $this->longitude = $this->parseCoordinate($longitude);
    }

	/**
     * @return array [lat, long]
     */
    public function getDMS()
    {
        return array(
            self::LAT  => $this->toDMS($this->latitude,  self::LAT),
            self::LONG => $this->toDMS($this->longitude, self::LONG)
        );
    }

    protected function toDMS($dec, $type)
    {
        if($type != self::LAT && $type != self::LONG)
        {
            throw new ArgumentException('no valid type given');
        }
        $pos = $dec >= 0;
        if(!$pos)
        {
            $dec *= -1.0;
        }
        //calculate degree part and the rest
        $deg = floor($dec);
        $dec -= $deg;

        //get the minutes
        $dec *= 60;
        $min = floor($dec);
        $dec -= $min;

        //get the seconds
        $sec = round($dec * 60,3);
        $fsec = floor($sec);
        $tpl = '%02d°%02d\'%'.($sec-$fsec < 0.001 ? '02d' : '02.3f').'"%s';
        return sprintf($tpl, $deg, $min, $sec, self::$suffix[$type][$pos]);
    }

    /**
     * @return array [lat, long]
     */
    public function getDecimal()
    {
        return array(
            self::LAT  => $this->latitude,
            self::LONG => $this->longitude
        );
    }

    protected function parseCoordinate($coordinate)
    {
        $decimal = null;
        /*regexp
        CASE 1:
            (-?[\d]+)[\s]*[:°d][\s]*([\d]+)[\s]*[:'][\s]*([\d]+\.[\d]+|[\d]+)[\s"]*([NSEWnsew]?)
            $1: hour
            $2: minutes
            $3: seconds
            $4: direction
            matches:
            	40:26:46N,79:56:55W
            	40:26:46.302N 79:56:55.903W
            	40°26'47"N 79°58'36"W
            	40d 26' 47" N 79d 58' 36" W

        CASE 2:
            (-?[\d]+)[\s]*[:°d][\s]*([\d]+\.[\d]+|[\d]+)[\s"]*([NSEWnsew]?)
            $1: hour
            $2: minutes
            matches:
            	40° 26.7717, -79° 56.93172

        CASE 3:
            (-?[\d]+\.[\d]+)[\s"]*([NSEWnsew]?)
            $1: decimal
            $2: direction
            matches:
            	40.446195N 79.948862W
            	40.446195, -79.948862

        */
        //already decimal
        if(preg_match('/^-?[\d]+$/', $coordinate)
            || preg_match('/^-?[\d]+\.[\d]+$/', $coordinate))
        {
            //decimal - nothing to do
            $decimal = $coordinate;
        }
        //case 1
        elseif(preg_match('/^'.
                    '([NSEW]?)'.
        			'(-?[\d]+)[\s]*[:°d][\s]*'.
        			'([\d]+)[\s]*[:\'][\s]*'.
        			'([\d]+\.[\d]+|[\d]+)[\s"]*'.
        			'([NSEW]?)'.
        			'$/ui', $coordinate,$match))
        {
            list($str, $d1, $deg, $min, $sec, $d2) = $match;
            $d = strtoupper(strlen($d1) > strlen($d2) ? $d1 : $d2);
            $decimal = $this->calcDec($deg, $min, $sec, $d);
        }
        //case 2
        elseif(preg_match('/^'.
        			'([NSEW]?)'.
        			'(-?[\d]+)[\s]*[:°d][\s]*'.
        			'([\d]+\.[\d]+|[\d]+)[\s"]*'.
        			'([NSEW]?)'.
        			'$/ui', $coordinate,$match))
        {
            list($str, $d1, $deg, $min, $d2) = $match;
            $d = strtoupper(strlen($d1) > strlen($d2) ? $d1 : $d2);
            $decimal = $this->calcDec($deg, $min, 0, $d);
        }
        //case 3
        elseif(preg_match('/^'.
                    '([NSEW]?)'.
        			'(-?[\d]+\.[\d]+)[\s"]*'.
        			'([NSEW]?)'.
        			'$/ui', $coordinate,$match))
        {
            list($str, $d1, $deg, $d2) = $match;
            $d = strtoupper(strlen($d1) > strlen($d2) ? $d1 : $d2);
            $decimal = $this->calcDec($deg, 0, 0, $d);
        }
        else
        {
            throw new ArgumentException('unknown coordinate format');
        }
        return $decimal;
    }

    protected function calcDec($deg, $min, $sec, $dir)
    {
        $deg *= 1.0;//convert to float
        $min *= 1.0;
        $sec *= 1.0;
        $sec = $min * 60 + $sec;
        $frac = $sec / 3600;
        $deg = $deg + $frac;
        if($dir == 'S' || $dir == 'W')
        {
            $deg *= -1.0;
        }
        return $deg;
    }
}
?>