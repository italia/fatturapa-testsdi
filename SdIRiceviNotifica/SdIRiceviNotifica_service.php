<?php

class SdIRiceviNotifica_service extends \SoapClient
{

    /**
     * @var array $classmap The defined classes
     */
    private static $classmap = array (
      'fileSdIBase_Type' => '\\fileSdIBase_Type',
      'fileSdI_Type' => '\\fileSdI_Type',
      'fileSdIConMetadati_Type' => '\\fileSdIConMetadati_Type',
      'rispostaRiceviFatture_Type' => '\\rispostaRiceviFatture_Type',
      'rispostaSdINotificaEsito_Type' => '\\rispostaSdINotificaEsito_Type',
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
        $wsdl = './SdIRiceviNotifica/SdIRiceviNotifica_v1.0.wsdl';
      }
      parent::__construct($wsdl, $options);
    }

    /**
     * @param fileSdI_Type $parameters
     * @return rispostaSdINotificaEsito_Type
     */
    public function NotificaEsito(fileSdI_Type $parameters)
    {
      return $this->__soapCall('NotificaEsito', array($parameters));
    }

}
