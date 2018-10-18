<?php

class SdIRiceviFile_service extends \SoapClient
{

    /**
     * @var array $classmap The defined classes
     */
    private static $classmap = array (
      'fileSdIBase_Type' => '\\fileSdIBase_Type',
      'fileSdI_Type' => '\\fileSdI_Type',
      'rispostaSdIRiceviFile_Type' => '\\rispostaSdIRiceviFile_Type',
    );

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
            $wsdl = dirname(__FILE__) . '/SdIRiceviFile_v1.0.wsdl';
        }
        
        parent::__construct($wsdl, $options);
    }

    /**
     * @param fileSdIBase_Type $parametersIn
     * @return rispostaSdIRiceviFile_Type
     */
    public function RiceviFile(fileSdIBase_Type $parametersIn)
    {
        try {
            $result=$this->__soapCall('RiceviFile', array($parametersIn));
            return $result;
        } catch (Exception $e) {
            $response=$this->__getLastResponse();
            $message=$e->getMessage();
            return $message.":".$response;
        }
    }
}
