<?php


function autoload_1f8069b9640bdf9d6232214935c8e984($class)
{
    $classes = array(
        'rispostaSdIRiceviFile_Type' => __DIR__ .'/rispostaSdIRiceviFile_Type.php',
        'SdIRiceviFile_service' => __DIR__ .'/SdIRiceviFile_service.php',
        'fileSdIBase_Type' => __DIR__ .'/fileSdIBase_Type.php',
        'fileSdI_Type' => __DIR__ .'/fileSdI_Type.php',
        'erroreInvio_Type' => __DIR__ .'/erroreInvio_Type.php',
        'SoapClientDebug' => __DIR__ .'/../SoapClientDebug.php'
    );
    if (!empty($classes[$class])) {
        include $classes[$class];
    };
}

spl_autoload_register('autoload_1f8069b9640bdf9d6232214935c8e984');

// Do nothing. The rest is just leftovers from the code generation.
{
}
