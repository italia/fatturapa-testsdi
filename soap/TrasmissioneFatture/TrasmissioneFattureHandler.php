<?php

require_once("autoload.php");
require dirname(__FILE__) . '/../../core/config.php';
require dirname(__FILE__) . '/../../core/vendor/autoload.php';

use FatturaPa\Core\Actors\Issuer;
use FatturaPa\Core\Actors\Base;

class TrasmissioneFattureHandler
{

    public function RicevutaConsegna($parametersIn)
    {
        Issuer::receive(
            $notification_blob = $parametersIn->File,
            $filename = $parametersIn->NomeFile,
            $type = 'RicevutaConsegna',
            $status = 'I_DELIVERED'
        );
    }

    public function NotificaMancataConsegna($parametersIn)
    {
        Issuer::receive(
            $notification_blob = $parametersIn->File,
            $filename = $parametersIn->NomeFile,
            $type = 'NotificaMancataConsegna',
            $status = 'I_FAILED_DELIVERY'
        );
    }

    public function NotificaScarto($parametersIn)
    {
        Issuer::receive(
            $notification_blob = $parametersIn->File,
            $filename = $parametersIn->NomeFile,
            $type = 'NotificaScarto',
            $status = 'I_INVALID'
        );
    }

    public function NotificaEsito($parametersIn)
    {
        error_log('======== NotificaEsito');
        aaa;
        $xmlString = base64_decode($parametersIn->File);
        $xml = Base::unpack($xmlString);
        error_log('======== ' . $xml);
        $invoice_id = $xml->IdentificativoSdI;
        $esito = $xml->Esito;
        if ($esito == 'EC01') {
            $status = 'I_ACCEPTED';
        } elseif ($esito == 'EC02') {
            $status = 'I_REFUSED';
        } else {
            throw new \RuntimeException("Invalid Esito $esito");
        }
        Issuer::receive(
            $notification_blob = $parametersIn->File,
            $filename = $parametersIn->NomeFile,
            $type = 'NotificaEsito',
            $status = $status
        );
    }

    public function NotificaDecorrenzaTermini($parametersIn)
    {
        Issuer::receive(
            $notification_blob = $parametersIn->File,
            $filename = $parametersIn->NomeFile,
            $type = 'NotificaDecorrenzaTermini',
            $status = 'I_EXPIRED'
        );
    }

    public function AttestazioneTrasmissioneFattura($parametersIn)
    {
        error_log('==== AttestazioneTrasmissioneFattura');
        Issuer::receive(
            $notification_blob = $parametersIn->File,
            $filename = $parametersIn->NomeFile,
            $type = 'AttestazioneTrasmissioneFattura',
            $status = 'I_IMPOSSIBLE_DELIVERY'
        );
    }
}
