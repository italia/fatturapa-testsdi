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
        $wsdl = './TrasmissioneFatture/TrasmissioneFatture_v1.1.wsdl';
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
      return $this->__soapCall('AttestazioneTrasmissioneFattura', array($attestazioneTrasmissioneFattura));
    }

}
