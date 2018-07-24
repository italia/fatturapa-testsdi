<?php


 function autoload_62addf545eac710997eead623a64a5ce($class)
{
    $classes = array(
        'RicezioneFatture_service' => __DIR__ .'/RicezioneFatture_service.php',
        'fileSdIBase_Type' => __DIR__ .'/fileSdIBase_Type.php',
        'fileSdI_Type' => __DIR__ .'/fileSdI_Type.php',
        'fileSdIConMetadati_Type' => __DIR__ .'/fileSdIConMetadati_Type.php',
        'rispostaRiceviFatture_Type' => __DIR__ .'/rispostaRiceviFatture_Type.php',
        'rispostaSdINotificaEsito_Type' => __DIR__ .'/rispostaSdINotificaEsito_Type.php',
        'esitoRicezione_Type' => __DIR__ .'/esitoRicezione_Type.php',
        'esitoNotifica_Type' => __DIR__ .'/esitoNotifica_Type.php'
    );
    if (!empty($classes[$class])) {
        include $classes[$class];
    };
}

spl_autoload_register('autoload_62addf545eac710997eead623a64a5ce');

// Do nothing. The rest is just leftovers from the code generation.
{
}
