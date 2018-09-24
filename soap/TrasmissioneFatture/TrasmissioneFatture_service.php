<?php

class TrasmissioneFatture_service extends \SoapClient
{

    /**
     * @var array $classmap The defined classes
     */
    private static $classmap = array (
      'fileSdIBase_Type' => '\\fileSdIBase_Type',
      'fileSdI_Type' => '\\fileSdI_Type',
      'rispostaSdIRiceviFile_Type' => '\\rispostaSdIRiceviFile_Type',
    );

/*
    public function __doRequest($request, $location, $action, $version, $one_way = 0) {
        echo('request: '. $request . PHP_EOL);
        echo('location: '. $location . PHP_EOL);
        echo('action: '. $action . PHP_EOL);
        echo('version: '. $version . PHP_EOL);
        echo('one_way: '. $one_way . PHP_EOL);
        return \SoapClient::__doRequest($request, $location, $action, $version, $one_way);
    }
*/

    // https://www.binarytides.com/modify-soapclient-request-php/
    public function __doRequest($request, $location, $action, $version, $one_way = NULL) 
    {
        echo('modified __doRequest' . PHP_EOL);
        $soap_request = $request;
        
        $header = array(
            'Content-type: text/xml;charset="utf-8"',
            'Accept: text/xml',
            'Cache-Control: no-cache',
            'Pragma: no-cache',
            'SOAPAction: "$action"',
            'Content-length: '.strlen($soap_request),
        );
        
        $soap_do = curl_init();
        
        $url = $location;
        
        $options = array( 
            CURLOPT_RETURNTRANSFER => true,
            //CURLOPT_HEADER         => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
            
            CURLOPT_USERAGENT      => 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)',
            CURLOPT_VERBOSE        => true,
            CURLOPT_URL            => $url ,
            
            CURLOPT_POSTFIELDS => $soap_request ,
            CURLOPT_HTTPHEADER => $header ,
        );
        
        curl_setopt_array($soap_do , $options);
        
        $output = curl_exec($soap_do);
        
        if( $output === false) 
        {
            $err = 'Curl error: ' . curl_error($soap_do);
            
            print $err;
        } 
        else
        {
            ///Operation completed successfully
        }
        curl_close($soap_do);
        
        // Uncomment the following line, if you actually want to do the request
        // return parent::__doRequest($request, $location, $action, $version);
        
        return $output;
    }

    /**
     * @param array $options A array of config values
     * @param string $wsdl The wsdl file to use
     */
    public function __construct(array $options = array(), $wsdl = null)
    {
        foreach (self::$classmap as $key => $value) {
            if (!isset($options['classmap'][$key])) {
                $options['classmap'][$key] = $value;
            }
        }
        $options = array_merge(array (
        'features' => 1,
        ), $options);
        if (!$wsdl) {
            $wsdl = ROOT . 'TrasmissioneFatture/TrasmissioneFatture_v1.1.wsdl';
        }
        parent::__construct($wsdl, $options);
    }

    /**
     * @param fileSdI_Type $ricevuta
     * @return void
     */
    public function RicevutaConsegna(fileSdI_Type $ricevuta)
    {
        return $this->__soapCall('RicevutaConsegna', array($ricevuta));
    }

    /**
     * @param fileSdI_Type $mancataConsegna
     * @return void
     */
    public function NotificaMancataConsegna(fileSdI_Type $mancataConsegna)
    {
        return $this->__soapCall('NotificaMancataConsegna', array($mancataConsegna));
    }

    /**
     * @param fileSdI_Type $scarto
     * @return void
     */
    public function NotificaScarto(fileSdI_Type $scarto)
    {
        return $this->__soapCall('NotificaScarto', array($scarto));
    }

    /**
     * @param fileSdI_Type $esito
     * @return void
     */
    public function NotificaEsito(fileSdI_Type $esito)
    {
        return $this->__soapCall('NotificaEsito', array($esito));
    }

    /**
     * @param fileSdI_Type $decorrenzaTermini
     * @return void
     */
    public function NotificaDecorrenzaTermini(fileSdI_Type $decorrenzaTermini)
    {
        return $this->__soapCall('NotificaDecorrenzaTermini', array($decorrenzaTermini));
    }

    /**
     * @param fileSdI_Type $attestazioneTrasmissioneFattura
     * @return void
     */
    public function AttestazioneTrasmissioneFattura(fileSdI_Type $attestazioneTrasmissioneFattura)
    {
        echo('AttestazioneTrasmissioneFattura------------------:');
        echo('attestazioneTrasmissioneFattura->file: '.json_encode($attestazioneTrasmissioneFattura->getFile()));
        echo('length(attestazioneTrasmissioneFattura->file) = ' . strlen($attestazioneTrasmissioneFattura->getFile()));
        echo('------------------END');
        return $this->__soapCall('AttestazioneTrasmissioneFattura', array($attestazioneTrasmissioneFattura));
    }
}
