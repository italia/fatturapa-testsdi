<?php

require_once("autoload.php");
require '../../core/config.php';
require '../../core/vendor/autoload.php';

use FatturaPa\Core\Actors\Issuer;

class TrasmissioneFattureHandler
{

    public function RicevutaConsegna($parametersIn)
    {
        Issuer::receive(
            $notification_blob = $parametersIn->File,
            $filename = $parametersIn->NomeFile,
            $type = 'RicevutaConsegna',
            $status = 'I_ACCEPTED'
        );
    }

    public function NotificaMancataConsegna($parametersIn)
    {
        Issuer::receive(
            $notification_blob = $parametersIn->File,
            $filename = $parametersIn->NomeFile,
            $type = 'NotificaMancataConsegna',
            $status = 'I_DELIVERED'
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
        // TODO: set the status to I_ACCEPTED or I_REFUSED
        // depending on the content of the Esito field in the notification blob:
        // Esito == EC01 => I_ACCEPTED
        // Esito == EC02 => I_REFUSED
        $status = 'I_ACCEPTED';
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
        Issuer::receive(
            $notification_blob = $parametersIn->File,
            $filename = $parametersIn->NomeFile,
            $type = 'AttestazioneTrasmissioneFattura',
            $status = 'I_DELIVERED'
        );
    }
}
