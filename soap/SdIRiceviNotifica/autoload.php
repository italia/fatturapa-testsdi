<?php


function autoload_4cea4f2f235176cb633350cacae2d9ba($class)
{
    $classes = array(
        'SdIRiceviNotifica_service' => __DIR__ .'/SdIRiceviNotifica_service.php',
        'fileSdIBase_Type' => __DIR__ .'/fileSdIBase_Type.php',
        'fileSdI_Type' => __DIR__ .'/fileSdI_Type.php',
        'fileSdIConMetadati_Type' => __DIR__ .'/fileSdIConMetadati_Type.php',
        'rispostaRiceviFatture_Type' => __DIR__ .'/rispostaRiceviFatture_Type.php',
        'rispostaSdINotificaEsito_Type' => __DIR__ .'/rispostaSdINotificaEsito_Type.php',
        'esitoRicezione_Type' => __DIR__ .'/esitoRicezione_Type.php',
        'esitoNotifica_Type' => __DIR__ .'/esitoNotifica_Type.php',
        'SoapClientDebug' => __DIR__ .'/../SoapClientDebug.php'
    );
    if (!empty($classes[$class])) {
        include $classes[$class];
    };
}

spl_autoload_register('autoload_4cea4f2f235176cb633350cacae2d9ba');

// Do nothing. The rest is just leftovers from the code generation.
{
}
