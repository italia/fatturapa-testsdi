<?php

require_once("autoload.php");
require '../../core/config.php';
require '../../core/vendor/autoload.php';

use FatturaPa\Core\Actors\Base;
use FatturaPa\Core\Actors\Issuer;

class TrasmissioneFattureHandler
{

    private function receive($parametersIn, $type)
    {
        error_log("invoice_uuid = $invoice_uuid");
        $invoice_uuid = 31;
        Base::receive($invoice_uuid = $invoice_uuid, $type = $type, $notification_blob = $parametersIn->File, $NomeFile = $parametersIn->NomeFile);
    }

    public function RicevutaConsegna($parametersIn)
    {
        error_log('START------------------:');
        error_log('parametersIn: '.json_encode($parametersIn));
        error_log('------------------END');

        self::receive($parametersIn, "RC");
    }

    public function NotificaMancataConsegna($parametersIn)
    {
        self::receive($parametersIn, "MC");
    }

    public function NotificaScarto($parametersIn)
    {
        self::receive($parametersIn, "NS");
    }

    public function NotificaEsito($parametersIn)
    {
        self::receive($parametersIn, "NE");
    }

    public function NotificaDecorrenzaTermini($parametersIn)
    {
        self::receive($parametersIn, "DT");
    }

    public function AttestazioneTrasmissioneFattura($parametersIn)
    {
        error_log('AttestazioneTrasmissioneFattura------------------:');
        error_log('parametersIn: '.json_encode($parametersIn));
        error_log('length(patametersIn->file) = ' . strlen($parametersIn->File));
        error_log('------------------END');
        $xmlString = base64_decode($parametersIn->File);
        error_log('========== xmlString');
        error_log($xmlString);
        error_log('========== /xmlString');
        $xml = Base::unpack($xmlString);
        error_log('========== xml');
        error_log($xml);
        error_log('========== /xml');
        $invoice_id = $xml->AttestazioneTrasmissioneFattura->IdentificativoSdI;
        error_log('========== invoice_id');
        error_log($invoice_id);
        error_log('========== /invoice_id');
        self::receive($parametersIn, "AT");
        Issuer::delivered([$invoice_id]);
    }
}
